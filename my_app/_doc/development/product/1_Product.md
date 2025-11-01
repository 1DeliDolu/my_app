## 🧭 **Symfony E-Ticaret Projesi – Şu Ana Kadar Yapılanlar**

### 🧩 1. Symfony Projesi Oluşturuldu

* Komut ile proje başlatıldı:
  ```bash
  symfony new my_app_symfony --webapp
  ```
* Symfony’nin varsayılan dizin yapısı kuruldu (`src/`, `templates/`, `config/`, `public/` vs.).

---

### 🗄️ 2. Veritabanı Bağlantısı Kuruldu

* `.env` dosyasında `DATABASE_URL` tanımlandı:

  ```env
  DATABASE_URL="mysql://yyyyyyyy:xxxxxxx@127.0.0.1:3306/aaaaaaa?serverVersion=8.0.32&charset=utf8mb4"
  ```
* İlk başta bağlantı hatası (“Access denied...”) alındı.

  🔧 Çözüm:

  * MySQL’de kullanıcı oluşturuldu:
    ```sql
    CREATE USER 'yyyyyyy'@'localhost' IDENTIFIED BY 'xxxxxx';
    CREATE DATABASE aaaaaaa;
    GRANT ALL PRIVILEGES ON app.* TO 'yyyyyyyy'@'localhost';
    FLUSH PRIVILEGES;
    ```

  ✅ Sonuç: Symfony artık MySQL veritabanına bağlanabiliyor.

---

### 🧱 3. **Product** (Ürün) Entity’si Oluşturuldu

* Komut:
  ```bash
  php bin/console make:entity Product
  ```
* Alanlar eklendi:| Alan        | Tip                | Nullable | Açıklama           |
  | ----------- | ------------------ | -------- | -------------------- |
  | id          | integer            | no       | Otomatik ID          |
  | name        | string(255)        | no       | Ürün adı          |
  | description | text               | yes      | Açıklama           |
  | price       | float              | no       | Fiyat                |
  | stock       | integer            | no       | Stok adedi           |
  | image       | string(255)        | yes      | Görsel URL          |
  | createdAt   | datetime_immutable | no       | Oluşturulma zamanı |
  | updatedAt   | datetime           | yes      | Güncellenme zamanı |
* `src/Entity/Product.php` içinde lifecycle callback ve constructor eklendi:
  ```php
  #[ORM\HasLifecycleCallbacks]
  class Product
  {
      public function __construct()
      {
          $this->createdAt = new \DateTimeImmutable();
      }

      #[ORM\PrePersist]
      public function setCreatedAtValue(): void
      {
          if (!$this->createdAt) {
              $this->createdAt = new \DateTimeImmutable();
          }
      }

      #[ORM\PreUpdate]
      public function setUpdatedAtValue(): void
      {
          $this->updatedAt = new \DateTime();
      }
  }
  ```

---

### 🧩 4. Doctrine Migration Yapıldı

* Migration dosyası oluşturuldu:

  ```bash
  php bin/console make:migration
  ```
* Veritabanı güncellendi:

  ```bash
  php bin/console doctrine:migrations:migrate
  ```

  ✅ `product` tablosu MySQL üzerinde oluşturuldu.

---

### 🧪 5. Faker ile Test Verisi (Fixture) Eklendi

* Gerekli paketler kuruldu:
  ```bash
  composer require orm-fixtures fakerphp/faker
  ```
* `AppFixtures` içinde 20 adet sahte ürün oluşturuldu:
  ```php
  $product = new Product();
  $product->setName($faker->words(3, true));
  $product->setDescription($faker->paragraph());
  $product->setPrice($faker->randomFloat(2, 10, 500));
  $product->setStock($faker->numberBetween(0, 100));
  $product->setImage('https://picsum.photos/200/300');
  $manager->persist($product);
  ```
* Fixture yüklenirken ilk başta şu hatalar alındı:
  * `created_at cannot be null`
  * `updated_at cannot be null`
* 🔧 Çözüm:
  * `createdAt` constructor’da set edildi.
  * `updatedAt` sütunu nullable hale getirildi (`#[ORM\Column(nullable: true)]`).
  * Fixture sorunsuz çalışır hale geldi.

---

### ✅ 6. Şu Anki Durum

Proje şu aşamada:

* Symfony framework kurulmuş durumda.
* MySQL bağlantısı çalışıyor.
* Doctrine ORM yapılandırıldı.
* `Product` tablosu başarıyla oluşturuldu.
* Fixture ile test ürünleri eklenebiliyor.

---
