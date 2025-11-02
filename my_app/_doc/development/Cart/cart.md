---
# 🛒 Cart Module – Modern Guide

## İçindekiler

* [Mimari ve Veri Yapısı](https://chatgpt.com/c/690666ba-a3c4-8330-8196-5067f1b03c12#mimari-ve-veri-yap%C4%B1s%C4%B1)
* [Routes](https://chatgpt.com/c/690666ba-a3c4-8330-8196-5067f1b03c12#routes)
* [CartService](https://chatgpt.com/c/690666ba-a3c4-8330-8196-5067f1b03c12#cartservice)
* [Controller Aksiyonları](https://chatgpt.com/c/690666ba-a3c4-8330-8196-5067f1b03c12#controller-aksiyonlar%C4%B1)
* [Twig Bileşenleri (UI)](https://chatgpt.com/c/690666ba-a3c4-8330-8196-5067f1b03c12#twig-bile%C5%9Fenleri-ui)
* [AJAX &amp; Stimulus Entegrasyonu](https://chatgpt.com/c/690666ba-a3c4-8330-8196-5067f1b03c12#ajax--stimulus-entegrasyonu)
* [Validasyon &amp; Güvenlik](https://chatgpt.com/c/690666ba-a3c4-8330-8196-5067f1b03c12#validasyon--g%C3%BCvenlik)
* [Checkout’a Dönüşüm (Order)](https://chatgpt.com/c/690666ba-a3c4-8330-8196-5067f1b03c12#checkouta-d%C3%B6n%C3%BC%C5%9F%C3%BCm-order)
* [Hızlı Testler](https://chatgpt.com/c/690666ba-a3c4-8330-8196-5067f1b03c12#h%C4%B1zl%C4%B1-testler)
---
## Mimari ve Veri Yapısı

**Amaç:** Sepet verisini hafif, güvenli ve hız odaklı tutmak.

**Depolama:** PHP session.

**Kapsam:** Ürün id, ad, birim fiyat, miktar, satır ara toplam ve genel toplam.

**Session anahtarı:** `cart`

```php
// Session snapshot example (PHP array)
[
  'items' => [
    [
      'product_id' => 10,
      'name' => 'USB-C Charger 65W',
      'unit_price' => 899.90,
      'quantity' => 2,
      'subtotal' => 1799.80
    ],
    // ...
  ],
  'total_quantity' => 2,
  'total_amount' => 1799.80
]
```

**Açıklama (TR):**

* Veriyi minimal tut: stok, resim vb. büyük alanları **taşımayıp** sadece ekranda gerekenleri sakla.
* Fiyatları **decimal (string/float)** olarak sakla ve formatlamayı Twig’te yap.

---

## Routes

```php
// config/routes/cart.php (ya da attributes ile Controller içinde)
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    $routes->add('app_cart_index', '/cart')->controller([App\Controller\CartController::class, 'index']);
    $routes->add('app_cart_add', '/cart/add/{id}')->controller([App\Controller\CartController::class, 'add'])->methods(['POST']);
    $routes->add('app_cart_update', '/cart/update/{id}')->controller([App\Controller\CartController::class, 'update'])->methods(['POST']);
    $routes->add('app_cart_remove', '/cart/remove/{id}')->controller([App\Controller\CartController::class, 'remove'])->methods(['POST']);
    $routes->add('app_cart_clear', '/cart/clear')->controller([App\Controller\CartController::class, 'clear'])->methods(['POST']);
    $routes->add('app_cart_count', '/cart/count')->controller([App\Controller\CartController::class, 'count'])->methods(['GET']);
};
```

**Açıklama (TR):**

* REST benzeri rotalar:  **ekle** ,  **güncelle** ,  **kaldır** ,  **temizle** ,  **sayaç** .
* `count` endpoint’i navbar rozeti için JSON döndürür.

---

## CartService

**Amaç:** Controller’ı şişirmeden tüm sepet mantığını **tek bir servis**te toplamak.

```php
<?php
// src/Service/CartService.php
namespace App\Service;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    public function __construct(private RequestStack $requestStack) {}

    private function getCart(): array
    {
        return $this->requestStack->getSession()->get('cart', ['items' => []]);
    }

    private function saveCart(array $cart): void
    {
        // Recalculate totals
        $totalQty = 0;
        $totalAmount = 0.0;

        foreach ($cart['items'] as &$row) {
            $row['subtotal'] = (float) $row['unit_price'] * (int) $row['quantity'];
            $totalQty += (int) $row['quantity'];
            $totalAmount += (float) $row['subtotal'];
        }
        $cart['total_quantity'] = $totalQty;
        $cart['total_amount'] = $totalAmount;

        $this->requestStack->getSession()->set('cart', $cart);
    }

    public function add(Product $product, int $quantity = 1): array
    {
        $cart = $this->getCart();
        $found = false;

        foreach ($cart['items'] as &$row) {
            if ($row['product_id'] === $product->getId()) {
                $row['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $cart['items'][] = [
                'product_id' => $product->getId(),
                'name' => $product->getName(),
                'unit_price' => (float) $product->getPrice(),
                'quantity' => $quantity,
                'subtotal' => 0.0
            ];
        }

        $this->saveCart($cart);
        return $cart;
    }

    public function update(Product $product, int $quantity): array
    {
        $cart = $this->getCart();

        foreach ($cart['items'] as $i => &$row) {
            if ($row['product_id'] === $product->getId()) {
                $row['quantity'] = max(0, $quantity);
                if ($row['quantity'] === 0) {
                    unset($cart['items'][$i]);
                }
                break;
            }
        }

        $cart['items'] = array_values($cart['items']);
        $this->saveCart($cart);
        return $cart;
    }

    public function remove(Product $product): array
    {
        $cart = $this->getCart();
        foreach ($cart['items'] as $i => $row) {
            if ($row['product_id'] === $product->getId()) {
                unset($cart['items'][$i]);
                break;
            }
        }
        $cart['items'] = array_values($cart['items']);
        $this->saveCart($cart);
        return $cart;
    }

    public function clear(): void
    {
        $this->requestStack->getSession()->set('cart', ['items' => [], 'total_quantity' => 0, 'total_amount' => 0]);
    }

    public function getSummary(): array
    {
        return $this->getCart();
    }

    public function getCount(): int
    {
        return (int) ($this->getCart()['total_quantity'] ?? 0);
    }
}
```

**Açıklama (TR):**

* **Toplam hesapları** her kaydetmede servis yapar.
* Kodu sade tutmak için  **tek sorumluluk** : session ile çalışmak.

---

## Controller Aksiyonları

```php
<?php
// src/Controller/CartController.php
namespace App\Controller;

use App\Entity\Product;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response, JsonResponse};
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cart')]
class CartController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em, private CartService $cart) {}

    #[Route('', name: 'app_cart_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('cart/index.html.twig', [
            'cart' => $this->cart->getSummary(),
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function add(Product $product, Request $request): JsonResponse
    {
        $qty = max(1, (int) $request->request->get('quantity', 1));
        $summary = $this->cart->add($product, $qty);

        return $this->json([
            'success' => true,
            'cartCount' => $summary['total_quantity'],
            'totalAmount' => $summary['total_amount'],
        ]);
    }

    #[Route('/update/{id}', name: 'app_cart_update', methods: ['POST'])]
    public function update(Product $product, Request $request): JsonResponse
    {
        $qty = max(0, (int) $request->request->get('quantity', 1));
        $summary = $this->cart->update($product, $qty);

        return $this->json([
            'success' => true,
            'cartCount' => $summary['total_quantity'],
            'totalAmount' => $summary['total_amount'],
        ]);
    }

    #[Route('/remove/{id}', name: 'app_cart_remove', methods: ['POST'])]
    public function remove(Product $product): JsonResponse
    {
        $summary = $this->cart->remove($product);

        return $this->json([
            'success' => true,
            'cartCount' => $summary['total_quantity'],
            'totalAmount' => $summary['total_amount'],
        ]);
    }

    #[Route('/clear', name: 'app_cart_clear', methods: ['POST'])]
    public function clear(): JsonResponse
    {
        $this->cart->clear();
        return $this->json(['success' => true, 'cartCount' => 0, 'totalAmount' => 0]);
    }

    #[Route('/count', name: 'app_cart_count', methods: ['GET'])]
    public function count(): JsonResponse
    {
        return $this->json(['count' => $this->cart->getCount()]);
    }
}
```

**Açıklama (TR):**

* **JSON** dönüşleri ile frontend kolay güncellenir (badge, toplam).
* Tüm mutasyonlar `POST`; görüntüleme `GET`.

---

## Twig Bileşenleri (UI)

### 1) Sepet Sayfası (`cart/index.html.twig`)

```twig
{% extends 'base.html.twig' %}
{% block title %}My Cart{% endblock %}

{% block body %}
<div class="container">
  <h3 class="mb-3">My Cart</h3>

  {% set items = cart.items|default([]) %}
  {% if items is empty %}
    <div class="alert alert-info">Your cart is empty.</div>
  {% else %}
    <table class="table align-middle">
      <thead>
        <tr>
          <th>Product</th><th class="text-center">Qty</th><th class="text-end">Unit</th><th class="text-end">Subtotal</th><th></th>
        </tr>
      </thead>
      <tbody id="cart-rows">
        {% for row in items %}
          <tr data-product-id="{{ row.product_id }}">
            <td>{{ row.name }}</td>
            <td class="text-center">
              <input type="number" min="0" value="{{ row.quantity }}" class="form-control form-control-sm qty-input" style="width: 80px;">
            </td>
            <td class="text-end">{{ row.unit_price|number_format(2) }} ₺</td>
            <td class="text-end subtotal-cell">{{ row.subtotal|number_format(2) }} ₺</td>
            <td class="text-end">
              <button class="btn btn-sm btn-outline-danger remove-item">Remove</button>
            </td>
          </tr>
        {% endfor %}
      </tbody>
      <tfoot>
        <tr>
          <th colspan="3" class="text-end">Total:</th>
          <th class="text-end" id="cart-total">{{ cart.total_amount|number_format(2) }} ₺</th>
          <th></th>
        </tr>
      </tfoot>
    </table>

    <div class="d-flex justify-content-between">
      <form method="post" action="{{ path('app_cart_clear') }}" data-ajax="true">
        <button class="btn btn-outline-secondary">Clear Cart</button>
      </form>
      <a href="{{ path('app_checkout_summary') }}" class="btn btn-success">Proceed to Checkout</a>
    </div>
  {% endif %}
</div>
{% endblock %}
```

**Açıklama (TR):**

* Miktar inputları ile satır güncelleme; “Remove” ile ürün kaldırma.
* “Clear Cart” temizlik için.

### 2) “Add to Cart” Butonu (Ürün Liste/Detay)

```twig
<button
  class="btn btn-primary add-to-cart"
  data-cart-url="{{ path('app_cart_add', { id: product.id }) }}"
  data-product-name="{{ product.name }}"
  data-quantity-input="#qty-{{ product.id }}">
  🛒 Add to Cart
</button>

<input id="qty-{{ product.id }}" type="number" min="1" value="1" class="form-control d-inline-block" style="width:100px">
```

**Açıklama (TR):**

* **data-** * attribute’ları ile AJAX hedefi ve miktar referansı veriliyor.

---

## AJAX & Stimulus Entegrasyonu

### 1) Navbar Rozeti (Canlı Sayaç)

* `/cart/count` JSON endpoint’i → `{"count": 3}`
* Stimulus controller: **`cart_badge_controller.js`** (senin yapıda zaten var)

Basit mantık:

```javascript
// assets/controllers/cart_badge_controller.js (özet)
this.refresh = async () => {
  const res = await fetch(this.urlValue);
  const { count } = await res.json();
  this.countTarget.textContent = count > 0 ? count : '';
};
```

### 2) Add-to-Cart Handler (Modal/Toast ile)

* **Başarılı ekleme** → badge güncelle + modal/ toast göster
* (Zaten `base.html.twig` içinde vanilla JS veya ayrı Stimulus controller ile gösterildi.)

---

## Validasyon & Güvenlik

* **Quantity negatif/0** → `max(0, quantity)` ile normalize et.
* **CSRF** : Form gönderimleri için `POST` + CSRF token (AJAX’ta header ile custom çözüm veya route’ı sadece XHR kabul edecek şekilde sınırla).
* **Fiyat güvenliği** : Fiyat session’da saklansa bile **gerçek fiyatı** Order oluştururken  **DB’den tekrar çek** .
* **Rate limiting** : Spam ekleme/güncelleme için basit throttle düşünülebilir.

---

## Checkout’a Dönüşüm (Order)

**Akış:**

1. Session’daki cart → `Order` + `OrderItem` entity’lerine kopyalanır.
2. Tutarlar **DB fiyatlarından** hesaplanır (güvenlik).
3. Başarılı ödeme sonrası `cart.clear()`.

**Basit örnek (özet):**

```php
// src/Service/CheckoutService.php (özet)
public function createOrderFromCart(User $user): Order
{
    $summary = $this->cart->getSummary();
    $order = (new Order())->setUser($user)->setStatus('Pending')->setCreatedAt(new \DateTimeImmutable());

    $total = 0.0;
    foreach ($summary['items'] as $row) {
        $product = $this->em->getRepository(Product::class)->find($row['product_id']);
        $unit = (float) $product->getPrice(); // always from DB
        $qty = (int) $row['quantity'];

        $item = (new OrderItem())
            ->setOrder($order)
            ->setProduct($product)
            ->setQuantity($qty)
            ->setUnitPrice($unit)
            ->setSubtotal($unit * $qty);

        $this->em->persist($item);
        $total += $item->getSubtotal();
    }

    $order->setTotal($total);
    $this->em->persist($order);
    $this->em->flush();

    return $order;
}
```

---

## Hızlı Testler

### 1) Manuel (tarayıcı)

* Ürün detay → “Add to Cart” → badge artmalı → modal/ toast görünmeli
* Sepet sayfası → miktar değiştir → toplam güncellenmeli
* Remove & Clear → doğru çalışmalı

### 2) PHPUnit (özet test)

```php
public function testAddToCartIncreasesCount(): void
{
    $client = static::createClient();
    $em = static::getContainer()->get(EntityManagerInterface::class);
    $product = $em->getRepository(Product::class)->findOneBy([]); // fixture

    $client->request('POST', '/cart/add/'.$product->getId(), ['quantity' => 2], [], ['HTTP_X-Requested-With' => 'XMLHttpRequest']);
    $this->assertResponseIsSuccessful();
    $json = json_decode($client->getResponse()->getContent(), true);
    $this->assertTrue($json['success']);
    $this->assertSame(2, $json['cartCount']);
}
```

---
