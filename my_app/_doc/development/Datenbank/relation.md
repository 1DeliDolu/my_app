
# 🧩 Symfony E-Commerce — Database Relationships (Tablolar Arası İlişkiler)

---

## 📦 1️⃣ `User` Entity

**Tablo:** `user`

### 🔹 Alanlar

| Alan        | Tür              | Açıklama                                       |
| ----------- | ----------------- | ------------------------------------------------ |
| id          | int               | Primary key                                      |
| email       | string            | Kullanıcı e-postası                           |
| password    | string            | Şifre                                           |
| first_name  | string            | Ad                                               |
| last_name   | string            | Soyad                                            |
| roles       | json              | Kullanıcı rolleri (ROLE_USER, ROLE_ADMIN, vb.) |
| avatar      | string (nullable) | Profil resmi                                     |
| is_verified | bool              | Email doğrulama durumu                          |

---

### 🔗 İlişkiler

| İlişki            | Tip           | Hedef       | Açıklama                                   |
| ------------------- | ------------- | ----------- | -------------------------------------------- |
| **addresses** | `OneToMany` | `Address` | Kullanıcının birden fazla adresi olabilir |
| **orders**    | `OneToMany` | `Order`   | Kullanıcı birçok sipariş oluşturabilir  |

---

### 🧠 Doctrine Kod Örneği

```php
#[ORM\OneToMany(mappedBy: 'user', targetEntity: Address::class, cascade: ['persist', 'remove'])]
private Collection $addresses;

#[ORM\OneToMany(mappedBy: 'user', targetEntity: Order::class)]
private Collection $orders;
```

---

## 🏠 2️⃣ `Address` Entity

**Tablo:** `address`

### 🔹 Alanlar

| Alan        | Tür   | Açıklama                                  |
| ----------- | ------ | ------------------------------------------- |
| id          | int    | Primary key                                 |
| street      | string | Sokak ve kapı bilgisi                      |
| city        | string | Şehir                                      |
| state       | string | Eyalet (opsiyonel)                          |
| country     | string | Ülke                                       |
| postal_code | string | Posta kodu                                  |
| phone       | string | Telefon numarası                           |
| type        | string | Adres tipi (örneğin “home”, “office”) |

---

### 🔗 İlişkiler

| İlişki         | Tip           | Hedef     | Açıklama                                                  |
| ---------------- | ------------- | --------- | ----------------------------------------------------------- |
| **user**   | `ManyToOne` | `User`  | Her adres bir kullanıcıya aittir                          |
| **orders** | `OneToMany` | `Order` | (Opsiyonel) Aynı adresle birden fazla sipariş verilebilir |

---

### 🧠 Doctrine Kod Örneği

```php
#[ORM\ManyToOne(inversedBy: 'addresses')]
#[ORM\JoinColumn(nullable: false)]
private ?User $user = null;
```

---

## 🛍️ 3️⃣ `Category` Entity

**Tablo:** `category`

### 🔹 Alanlar

| Alan        | Tür   | Açıklama    |
| ----------- | ------ | ------------- |
| id          | int    | Primary key   |
| name        | string | Kategori adı |
| description | text   | Açıklama    |

---

### 🔗 İlişkiler

| İlişki           | Tip           | Hedef       | Açıklama                                 |
| ------------------ | ------------- | ----------- | ------------------------------------------ |
| **products** | `OneToMany` | `Product` | Bir kategoriye birçok ürün ait olabilir |

---

### 🧠 Doctrine Kod Örneği

```php
#[ORM\OneToMany(mappedBy: 'category', targetEntity: Product::class)]
private Collection $products;
```

---

## 🧾 4️⃣ `Product` Entity

**Tablo:** `product`

### 🔹 Alanlar

| Alan        | Tür          | Açıklama           |
| ----------- | ------------- | -------------------- |
| id          | int           | Primary key          |
| name        | string        | Ürün adı          |
| description | text          | Ürün açıklaması |
| price       | decimal(10,2) | Fiyat                |
| stock       | int           | Stok miktarı        |
| created_at  | datetime      | Oluşturulma tarihi  |
| updated_at  | datetime      | Güncelleme tarihi   |

---

### 🔗 İlişkiler

| İlişki             | Tip           | Hedef         | Açıklama                                       |
| -------------------- | ------------- | ------------- | ------------------------------------------------ |
| **category**   | `ManyToOne` | `Category`  | Her ürün bir kategoriye aittir                 |
| **orderItems** | `OneToMany` | `OrderItem` | Ürün birçok sipariş satırında yer alabilir |

---

### 🧠 Doctrine Kod Örneği

```php
#[ORM\ManyToOne(inversedBy: 'products')]
#[ORM\JoinColumn(nullable: false)]
private ?Category $category = null;

#[ORM\OneToMany(mappedBy: 'product', targetEntity: OrderItem::class)]
private Collection $orderItems;
```

---

## 🧾 5️⃣ `Order` Entity

**Tablo:** `order`

### 🔹 Alanlar

| Alan       | Tür          | Açıklama                                                      |
| ---------- | ------------- | --------------------------------------------------------------- |
| id         | int           | Primary key                                                     |
| status     | string        | “pending”, “paid”, “shipped”, “cancelled” gibi durumlar |
| total      | decimal(10,2) | Toplam tutar                                                    |
| created_at | datetime      | Oluşturulma tarihi                                             |

---

### 🔗 İlişkiler

| İlişki                  | Tip           | Hedef         | Açıklama                     |
| ------------------------- | ------------- | ------------- | ------------------------------ |
| **user**            | `ManyToOne` | `User`      | Siparişi veren kullanıcı    |
| **shippingAddress** | `ManyToOne` | `Address`   | Teslimat adresi                |
| **items**           | `OneToMany` | `OrderItem` | Siparişe ait ürün kalemleri |

---

### 🧠 Doctrine Kod Örneği

```php
#[ORM\ManyToOne(inversedBy: 'orders')]
#[ORM\JoinColumn(nullable: false)]
private ?User $user = null;

#[ORM\ManyToOne]
#[ORM\JoinColumn(nullable: false)]
private ?Address $shippingAddress = null;

#[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderItem::class, cascade: ['persist', 'remove'])]
private Collection $items;
```

---

## 📦 6️⃣ `OrderItem` Entity

**Tablo:** `order_item`

### 🔹 Alanlar

| Alan       | Tür          | Açıklama                          |
| ---------- | ------------- | ----------------------------------- |
| id         | int           | Primary key                         |
| quantity   | int           | Ürün miktarı                     |
| unit_price | decimal(10,2) | Birim fiyat                         |
| subtotal   | decimal(10,2) | Ara toplam (quantity × unit_price) |

---

### 🔗 İlişkiler

| İlişki          | Tip           | Hedef       | Açıklama                      |
| ----------------- | ------------- | ----------- | ------------------------------- |
| **order**   | `ManyToOne` | `Order`   | Her satır bir siparişe aittir |
| **product** | `ManyToOne` | `Product` | Her satır bir ürüne aittir   |

---

### 🧠 Doctrine Kod Örneği

```php
#[ORM\ManyToOne(inversedBy: 'items')]
#[ORM\JoinColumn(nullable: false)]
private ?Order $order = null;

#[ORM\ManyToOne(inversedBy: 'orderItems')]
#[ORM\JoinColumn(nullable: false)]
private ?Product $product = null;
```

---

## 🗺️ 7️⃣ İlişki Haritası (ER Diagram Mantığında)

```text
┌──────────────┐          ┌──────────────┐
│    User      │ 1     * │   Address     │
│──────────────│─────────│───────────────│
│ id           │         │ user_id (FK)  │
└──────────────┘         └───────────────┘
        │1
        │
        │*
┌──────────────┐          ┌──────────────┐
│   Order      │ 1     * │  OrderItem    │
│──────────────│─────────│───────────────│
│ user_id (FK) │         │ order_id (FK) │
│ address_id   │         │ product_id(FK)│
└──────────────┘         └───────────────┘
        │
        │
        │*
┌──────────────┐
│   Product     │
│───────────────│
│ category_id(FK)│
└───────────────┘
        │
        │*
        │
┌──────────────┐
│  Category     │
└──────────────┘
```

---

## 🔗 Özet (İlişkiler Tablosu)

| Kaynak               | Hedef     | Tip                                              | Anlam |
| -------------------- | --------- | ------------------------------------------------ | ----- |
| User → Address      | OneToMany | Kullanıcının birden fazla adresi vardır      |       |
| Address → User      | ManyToOne | Her adres bir kullanıcıya aittir               |       |
| User → Order        | OneToMany | Kullanıcı birden fazla sipariş oluşturabilir |       |
| Order → User        | ManyToOne | Her sipariş bir kullanıcıya aittir            |       |
| Order → Address     | ManyToOne | Her siparişin bir teslimat adresi vardır       |       |
| Order → OrderItem   | OneToMany | Sipariş birçok ürün kaleminden oluşur       |       |
| OrderItem → Order   | ManyToOne | Her kalem bir siparişe bağlıdır              |       |
| Product → Category  | ManyToOne | Her ürün bir kategoriye aittir                 |       |
| Category → Product  | OneToMany | Bir kategoride birçok ürün olabilir           |       |
| Product → OrderItem | OneToMany | Ürün birçok sipariş kaleminde yer alabilir   |       |
| OrderItem → Product | ManyToOne | Kalem bir ürüne aittir                         |       |

---

## 🧠 Örnek: Doctrine Kullanımı (Kod Üzerinden)

### 1️⃣ Kullanıcının tüm siparişleri

```php
$user = $userRepository->find(1);
$orders = $user->getOrders();
```

### 2️⃣ Siparişteki ürünlerin listesi

```php
foreach ($order->getItems() as $item) {
    echo $item->getProduct()->getName();
}
```

### 3️⃣ Ürünün bulunduğu siparişleri görmek

```php
$product = $productRepository->find(10);
foreach ($product->getOrderItems() as $oi) {
    echo $oi->getOrder()->getId();
}
```

### 4️⃣ Kullanıcının tüm adresleri

```php
foreach ($user->getAddresses() as $address) {
    echo $address->getCity();
}
```

---

## ✅ Genel Özet

| Entity              | İlişkili Olduğu Tablolar | Ana İlişki Tipleri  |
| ------------------- | --------------------------- | --------------------- |
| **User**      | Address, Order              | 1-to-Many             |
| **Address**   | User, Order                 | Many-to-1             |
| **Category**  | Product                     | 1-to-Many             |
| **Product**   | Category, OrderItem         | Many-to-1 / 1-to-Many |
| **Order**     | User, Address, OrderItem    | Many-to-1 / 1-to-Many |
| **OrderItem** | Order, Product              | Many-to-1             |

---



---

# 🧱 Symfony Doctrine Fixtures — Sample Data (Test Verisi)

---

## ⚙️ 1️⃣ Gerekli Paket Kurulumu

Eğer daha önce kurmadıysan DoctrineFixturesBundle’ı ekle:

```bash
composer require --dev orm-fixtures fakerphp/faker
```

> 🇹🇷 Açıklama:
>
> * `orm-fixtures` → Veritabanına örnek veri yükleme sistemi
> * `fakerphp/faker` → Rastgele test verileri (isim, fiyat, adres vs.)

---

## 📂 2️⃣ Fixture Dosyası Oluştur

```bash
php bin/console make:fixtures AppFixtures
```

Bu komut şunu oluşturur:

```
src/DataFixtures/AppFixtures.php
```

---

## 🧩 3️⃣ Fixture Kodları

Aşağıdaki kodu **`AppFixtures.php`** dosyasına tamamen yapıştır 👇

```php
<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Address;
use App\Entity\Order;
use App\Entity\OrderItem;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        /** ---------- USERS ---------- */
        $users = [];
        for ($i = 1; $i <= 5; $i++) {
            $user = new User();
            $user->setEmail("user$i@example.com");
            $user->setFirstName($faker->firstName());
            $user->setLastName($faker->lastName());
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
            $user->setIsVerified(true);
            $manager->persist($user);
            $users[] = $user;
        }

        /** ---------- CATEGORIES ---------- */
        $categories = [];
        $categoryNames = ['Electronics', 'Books', 'Clothing', 'Toys', 'Home & Kitchen', 'Sports', 'Beauty', 'Automotive'];

        foreach ($categoryNames as $name) {
            $category = new Category();
            $category->setName($name);
            $category->setDescription($faker->sentence());
            $manager->persist($category);
            $categories[] = $category;
        }

        /** ---------- PRODUCTS ---------- */
        $products = [];
        for ($i = 1; $i <= 30; $i++) {
            $product = new Product();
            $product->setName($faker->words(3, true));
            $product->setDescription($faker->paragraph());
            $product->setPrice($faker->randomFloat(2, 5, 500));
            $product->setStock($faker->numberBetween(10, 100));
            $product->setCategory($faker->randomElement($categories));
            $product->setCreatedAt(new \DateTimeImmutable());
            $product->setUpdatedAt(new \DateTimeImmutable());
            $manager->persist($product);
            $products[] = $product;
        }

        /** ---------- ADDRESSES ---------- */
        $addresses = [];
        foreach ($users as $user) {
            for ($i = 0; $i < 2; $i++) {
                $address = new Address();
                $address->setStreet($faker->streetAddress());
                $address->setCity($faker->city());
                $address->setState($faker->state());
                $address->setCountry($faker->country());
                $address->setPostalCode($faker->postcode());
                $address->setPhone($faker->phoneNumber());
                $address->setType($faker->randomElement(['home', 'work']));
                $address->setUser($user);
                $manager->persist($address);
                $addresses[] = $address;
            }
        }

        /** ---------- ORDERS & ORDER ITEMS ---------- */
        for ($i = 1; $i <= 10; $i++) {
            $order = new Order();
            $order->setUser($faker->randomElement($users));
            $order->setShippingAddress($faker->randomElement($addresses));
            $order->setStatus($faker->randomElement(['pending', 'paid', 'shipped']));
            $order->setCreatedAt(new \DateTimeImmutable());

            $total = 0;

            $numItems = $faker->numberBetween(2, 5);
            for ($j = 0; $j < $numItems; $j++) {
                $product = $faker->randomElement($products);
                $qty = $faker->numberBetween(1, 3);

                $item = new OrderItem();
                $item->setOrder($order);
                $item->setProduct($product);
                $item->setQuantity($qty);
                $item->setUnitPrice($product->getPrice());
                $item->setSubtotal($product->getPrice() * $qty);
                $manager->persist($item);

                $total += $product->getPrice() * $qty;
            }

            $order->setTotal($total);
            $manager->persist($order);
        }

        $manager->flush();
    }
}
```

---

## 🇹🇷 Açıklama

| Bölüm              | Açıklama                                                                           |
| -------------------- | ------------------------------------------------------------------------------------ |
| **Users**      | 5 kullanıcı oluşturur (email:`user1@example.com`, şifre:`password`)          |
| **Categories** | 8 adet sabit kategori oluşturur                                                     |
| **Products**   | 30 ürün, rastgele kategoriyle ilişkilendirilir                                    |
| **Addresses**  | Her kullanıcıya 2 adres eklenir                                                    |
| **Orders**     | 10 sipariş oluşturulur                                                             |
| **OrderItems** | Her siparişte 2–5 ürün satırı bulunur                                          |
| **Toplam**     | ~5 kullanıcı, 8 kategori, 30 ürün, 10 sipariş, 20 adres, 30–50 sipariş kalemi |

---

## 🚀 4️⃣ Fixture Çalıştırma

```bash
php bin/console doctrine:fixtures:load
```

Cevap olarak şunu göreceksin:

```
Careful, database will be purged. Do you want to continue? (yes/no) [no]:
 > yes
 > purging database
 > loading App\DataFixtures\AppFixtures
```

💡 Bu işlem:

* Veritabanını sıfırlar (TRUNCATE)
* Yukarıdaki örnek verileri ekler

---

## 🧠 5️⃣ Kontrol Etme (Doctrine Console)

### Tüm ürünleri listele:

```bash
php bin/console doctrine:query:sql "SELECT id, name, price FROM product LIMIT 5"
```

### Kullanıcıları listele:

```bash
php bin/console doctrine:query:sql "SELECT id, email FROM user"
```

### Siparişleri görüntüle:

```bash
php bin/console doctrine:query:sql "SELECT id, total, status FROM `order`"
```

---

## 📊 6️⃣ İlişki Doğrulama

Doctrine üzerinden kontrol etmek istersen:

```php
// Controller veya Tinker içinde test
$user = $userRepository->find(1);
foreach ($user->getOrders() as $order) {
    dump($order->getTotal());
}
```

Ya da:

```php
$order = $orderRepository->find(1);
foreach ($order->getItems() as $item) {
    echo $item->getProduct()->getName();
}
```

---

## ✅ Özet

| Veri Türü   | Sayı  | Açıklama                   |
| ------------- | ------ | ---------------------------- |
| 👤 User       | 5      | Otomatik oluşturuldu        |
| 🏠 Address    | 10     | Kullanıcı başına 2 adres |
| 🏷️ Category | 8      | Sabit liste                  |
| 📦 Product    | 30     | Rastgele kategorilere ait    |
| 🧾 Order      | 10     | Rastgele kullanıcı + adres |
| 📋 OrderItem  | 30–50 | Her siparişe 2–5 ürün    |

---

Bu noktada artık:

* `/product` sayfasında 30 rastgele ürün,
* `/cart` sayfasında sepet sistemi,
* `/checkout` sayfasında ödeme (test),
* `/orders` sayfasında örnek siparişler

  tamamen çalışır durumda olacak ✅

---
