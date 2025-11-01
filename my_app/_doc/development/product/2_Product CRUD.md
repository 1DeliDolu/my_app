
---

## 🧭 Symfony E-Ticaret Projesi — Özet (Product CRUD’a Kadar)

### 1️⃣ Symfony Projesi Kuruldu

* Yeni proje oluşturuldu:
  ```bash
  symfony new my_app_symfony --webapp
  ```
* Gerekli dizin yapısı (`src`, `templates`, `config`, `public`) kuruldu.

---

### 2️⃣ Veritabanı Bağlantısı Kuruldu

* `.env` dosyasına MySQL bağlantısı eklendi:
  ```
  DATABASE_URL="mysql://my_app_symfony:D0cker@127.0.0.1:3306/app?serverVersion=8.0.32&charset=utf8mb4"
  ```
* MySQL kullanıcı ve veritabanı oluşturuldu:
  ```sql
  CREATE USER 'my_app_symfony'@'localhost' IDENTIFIED BY 'D0cker';
  CREATE DATABASE app;
  GRANT ALL PRIVILEGES ON app.* TO 'my_app_symfony'@'localhost';
  FLUSH PRIVILEGES;
  ```

---

### 3️⃣ Product (Ürün) Entity’si Oluşturuldu

* Komut:
  ```bash
  php bin/console make:entity Product
  ```
* Alanlar: `name`, `description`, `price`, `stock`, `image`, `createdAt`, `updatedAt`
* Lifecycle callbacks ve constructor eklendi → `createdAt` ve `updatedAt` otomatik yönetiliyor.

---

### 4️⃣ Migration ve Database Güncellemesi

* Migration oluşturuldu ve çalıştırıldı:
  ```bash
  php bin/console make:migration
  php bin/console doctrine:migrations:migrate
  ```
* MySQL’de `product` tablosu oluştu.

---

### 5️⃣ Test Verileri (Fixtures)

* Faker ile 20 sahte ürün eklendi (`AppFixtures.php`)
* `php bin/console doctrine:fixtures:load` komutu ile veriler yüklendi.
* `createdAt` / `updatedAt` null hataları düzeltildi.

---

### 6️⃣ Product CRUD Arayüzü Oluşturuldu

* Komut:
  ```bash
  php bin/console make:crud Product
  ```
* Symfony otomatik olarak:
  * `ProductController.php`
  * `ProductType.php`
  * Twig dosyalarını (`index`, `show`, `new`, `edit`) oluşturdu.
* Rotalar eklendi: `/product`, `/product/new`, `/product/{id}`, `/product/{id}/edit`

---

### 7️⃣ Web Üzerinden Test

* Geliştirme sunucusu çalıştırıldı:
  ```bash
  symfony serve -d
  ```
* [http://127.0.0.1:8000/product](http://127.0.0.1:8000/product) adresinde ürünler listeleniyor.
* Ürün ekleme, düzenleme ve silme işlemleri yapılabiliyor.

---

## 🔜 Sonraki Adım

Artık şunlardan birine geçebilirsin:

1. 🏷️ **Category** entity’sini oluşturup ürünlerle ilişki kurmak
2. 🎨 Mevcut CRUD sayfalarını özelleştirip arayüzü güzelleştirmek (Bootstrap, görsel, tablo düzeni)

---
