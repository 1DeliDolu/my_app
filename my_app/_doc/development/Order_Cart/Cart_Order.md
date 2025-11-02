
# 🧾 Symfony E-Commerce – Cart & Order System

---

## 📦 1️⃣ CartService (Session-Based Cart)

> Dosya: `src/Service/CartService.php`

```php
<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private const CART_KEY = 'cart_items';

    public function __construct(
        private RequestStack $requestStack,
        private ProductRepository $productRepository
    ) {}

    public function add(int $productId, int $quantity = 1): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get(self::CART_KEY, []);
        $cart[$productId] = ($cart[$productId] ?? 0) + $quantity;
        $session->set(self::CART_KEY, $cart);
    }

    public function set(int $productId, int $quantity): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get(self::CART_KEY, []);
        if ($quantity <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = $quantity;
        }
        $session->set(self::CART_KEY, $cart);
    }

    public function remove(int $productId): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get(self::CART_KEY, []);
        unset($cart[$productId]);
        $session->set(self::CART_KEY, $cart);
    }

    public function clear(): void
    {
        $this->requestStack->getSession()->remove(self::CART_KEY);
    }

    public function getDetailedItems(): array
    {
        $cart = $this->requestStack->getSession()->get(self::CART_KEY, []);
        $items = [];

        foreach ($cart as $productId => $qty) {
            $product = $this->productRepository->find($productId);
            if ($product) {
                $items[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'unitPrice' => $product->getPrice(),
                    'subtotal' => $product->getPrice() * $qty,
                ];
            }
        }

        return $items;
    }

    public function getTotal(): float
    {
        return array_sum(array_column($this->getDetailedItems(), 'subtotal'));
    }
}
```

### 🇹🇷 Açıklama

* Session tabanlı sepet yönetimi
* Fonksiyonlar:
  * `add()` → ürün ekler
  * `set()` → miktar değiştirir
  * `remove()` → ürünü siler
  * `clear()` → sepeti tamamen temizler
  * `getDetailedItems()` → Product nesneleriyle sepet detayları
  * `getTotal()` → toplam tutarı hesaplar

---

## 🧩 2️⃣ CartController

> Dosya: `src/Controller/CartController.php`

```php
<?php

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cart')]
final class CartController extends AbstractController
{
    #[Route('/', name: 'app_cart_index')]
    public function index(CartService $cartService): Response
    {
        return $this->render('cart/index.html.twig', [
            'items' => $cartService->getDetailedItems(),
            'total' => $cartService->getTotal(),
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add')]
    public function add(int $id, CartService $cartService): Response
    {
        $cartService->add($id);
        $this->addFlash('success', 'Product added to cart.');
        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/remove/{id}', name: 'app_cart_remove')]
    public function remove(int $id, CartService $cartService): Response
    {
        $cartService->remove($id);
        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/clear', name: 'app_cart_clear')]
    public function clear(CartService $cartService): Response
    {
        $cartService->clear();
        return $this->redirectToRoute('app_cart_index');
    }
}
```

---

## 🧾 3️⃣ Cart Template (Twig)

> Dosya: `templates/cart/index.html.twig`

```twig
{% extends 'base.html.twig' %}
{% block title %}🛒 My Cart{% endblock %}

{% block body %}
<h2>🛒 My Cart</h2>

{% for message in app.flashes('success') %}
  <div class="alert alert-success">{{ message }}</div>
{% endfor %}

<table class="table table-bordered mt-3">
  <thead>
    <tr>
      <th>Product</th>
      <th>Price</th>
      <th>Qty</th>
      <th>Subtotal</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    {% for row in items %}
      <tr>
        <td>{{ row.product.name }}</td>
        <td>{{ row.unitPrice|number_format(2) }} ₺</td>
        <td>{{ row.quantity }}</td>
        <td>{{ row.subtotal|number_format(2) }} ₺</td>
        <td><a href="{{ path('app_cart_remove', {id: row.product.id}) }}" class="btn btn-sm btn-danger">Remove</a></td>
      </tr>
    {% else %}
      <tr><td colspan="5" class="text-center text-muted">Cart is empty</td></tr>
    {% endfor %}
  </tbody>
</table>

<h4 class="text-end mt-3">Total: {{ total|number_format(2) }} ₺</h4>

<div class="text-end">
  <a href="{{ path('app_cart_clear') }}" class="btn btn-outline-danger me-3">Clear Cart</a>
  <a href="{{ path('app_checkout') }}" class="btn btn-primary">Proceed to Checkout</a>
</div>
{% endblock %}
```

---

## 🏷️ 4️⃣ Order Entity

> Dosya: `src/Entity/Order.php`

```php
<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $status = 'pending';

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $total = '0.00';

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Address $shippingAddress = null;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderItem::class, cascade: ['persist', 'remove'])]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    // ... getters & setters ...
}
```

---

## 📦 5️⃣ OrderItem Entity

> Dosya: `src/Entity/OrderItem.php`

```php
<?php

namespace App\Entity;

use App\Repository\OrderItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $unitPrice = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $subtotal = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $order = null;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    // ... getters & setters ...
}
```

---

## 💳 6️⃣ Checkout Controller (Sipariş Oluşturma)

> Dosya: `src/Controller/CheckoutController.php`

```php
<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Form\CheckoutFormType;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class CheckoutController extends AbstractController
{
    #[Route('/checkout', name: 'app_checkout')]
    public function checkout(Request $request, CartService $cart, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(CheckoutFormType::class, null, ['user' => $user]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order = new Order();
            $order->setUser($user);
            $order->setShippingAddress($form->get('shippingAddress')->getData());
            $order->setStatus('pending');
            $order->setCreatedAt(new \DateTimeImmutable());

            $total = 0;
            foreach ($cart->getDetailedItems() as $row) {
                $item = new OrderItem();
                $item->setOrder($order);
                $item->setProduct($row['product']);
                $item->setQuantity($row['quantity']);
                $item->setUnitPrice($row['unitPrice']);
                $item->setSubtotal($row['subtotal']);
                $em->persist($item);
                $total += $row['subtotal'];
            }
            $order->setTotal($total);
            $em->persist($order);
            $em->flush();

            $cart->clear();
            $this->addFlash('success', 'Order placed successfully!');
            return $this->redirectToRoute('app_order_show', ['id' => $order->getId()]);
        }

        return $this->render('checkout/index.html.twig', [
            'form' => $form->createView(),
            'items' => $cart->getDetailedItems(),
            'total' => $cart->getTotal(),
        ]);
    }
}
```

---

## 🧾 7️⃣ Checkout Form

> Dosya: `src/Form/CheckoutFormType.php`

```php
<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CheckoutFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $options['user'];

        $builder
            ->add('shippingAddress', EntityType::class, [
                'class' => Address::class,
                'choice_label' => fn(Address $a) => sprintf('%s, %s, %s', $a->getStreet(), $a->getCity(), $a->getCountry()),
                'choices' => $user->getAddresses(),
                'label' => 'Shipping Address',
                'placeholder' => 'Select address',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['user' => null]);
    }
}
```

---

## 🧾 8️⃣ Checkout Template

> Dosya: `templates/checkout/index.html.twig`

```twig
{% extends 'base.html.twig' %}
{% block title %}Checkout{% endblock %}

{% block body %}
<h2>Checkout</h2>

<ul class="list-group mb-3">
  {% for row in items %}
    <li class="list-group-item d-flex justify-content-between">
      <span>{{ row.product.name }} × {{ row.quantity }}</span>
      <strong>{{ row.subtotal|number_format(2) }} ₺</strong>
    </li>
  {% endfor %}
  <li class="list-group-item d-flex justify-content-between">
    <span>Total</span>
    <strong>{{ total|number_format(2) }} ₺</strong>
  </li>
</ul>

{{ form_start(form) }}
  {{ form_row(form.shippingAddress) }}
  <button class="btn btn-primary mt-3">Place Order</button>
{{ form_end(form) }}
{% endblock %}
```

---

## 🧭 9️⃣ Navbar Cart (🛒) Badge

> Dosya: `templates/base.html.twig`

```twig
<li class="nav-item position-relative">
  <a class="nav-link" href="{{ path('app_cart_index') }}">
    🛒 Cart
    {% if cartItemCount > 0 %}
      <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill">
        {{ cartItemCount }}
      </span>
    {% endif %}
  </a>
</li>
```

> `AppExtension` (Twig Global): `src/Twig/AppExtension.php`

```php
<?php

namespace App\Twig;

use App\Service\CartService;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(private CartService $cartService) {}

    public function getGlobals(): array
    {
        return [
            'cartItemCount' => count($this->cartService->getDetailedItems()),
        ];
    }
}
```

---

# ✅ Özet

| Katman     | Dosya                           | Görev                               |
| ---------- | ------------------------------- | ------------------------------------ |
| Service    | `CartService.php`             | Sepet işlemleri (Session)           |
| Controller | `CartController.php`          | Sepeti görüntüleme, ekleme, silme |
| Twig       | `cart/index.html.twig`        | Sepet sayfası                       |
| Entity     | `Order.php`,`OrderItem.php` | Sipariş & ürün satırları        |
| Controller | `CheckoutController.php`      | Sipariş oluşturma                  |
| Form       | `CheckoutFormType.php`        | Adres seçimi                        |
| Twig       | `checkout/index.html.twig`    | Sipariş onayı                      |
| Navbar     | `base.html.twig`              | 🛒 İkon + ürün sayısı           |

---
