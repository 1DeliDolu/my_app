Harika — şimdiye kadar yaptıklarımızı **User (kullanıcı sistemi)** açısından özetleyelim 💡

Senin projenin geldiği noktada artık sadece ürün ve kategori değil,

**rolleri (roles)** ve **giriş sistemi (auth)** olan bir kullanıcı yapısı da var.

Aşağıda hem teknik hem kavramsal olarak **adım adım** anlatıyorum 👇

---

## 👥 1️⃣ **User Entity’nin Oluşumu**

Öncelikle sistemde kimlik doğrulama (login/register) işlemleri yapabilmek için bir **User** entity oluşturduk.

**Komut:**

```bash
php bin/console make:user
```

### 🔧 Elde Edilen Yapı:

* `src/Entity/User.php`
* `src/Repository/UserRepository.php`

### 🧱 Entity’nin Alanları:

| Alan      | Tip     | Açıklama                                             |
| --------- | ------- | ------------------------------------------------------ |
| id        | integer | benzersiz kullanıcı kimliği                         |
| email     | string  | kullanıcı adı / login alanı                        |
| password  | string  | hashed parola                                          |
| roles     | array   | kullanıcı rolleri (örneğin `ROLE_ADMIN`)         |
| firstName | string  | kullanıcı adı                                       |
| lastName  | string  | kullanıcı soyadı                                    |
| type      | string  | kullanıcı tipi (`admin`,`employee`,`customer`) |

**Kısaca:**

`User` tablosu artık her tür kullanıcıyı (admin, çalışan, müşteri) tek tabloda tutabiliyor.

Rol yönetimi `roles` alanıyla yapılıyor.

---

## 🧩 2️⃣ **Roller (Roles) ve Türler (Types)**

Her kullanıcının iki farklı kavramsal özelliği var:

| Kavram                 | Örnek         | Açıklama                                                    |
| ---------------------- | -------------- | ------------------------------------------------------------- |
| **Role (Yetki)** | `ROLE_ADMIN` | Symfony güvenlik sistemi için yetki belirteci               |
| **Type (Tür)**  | `admin`      | Bizim iş mantığımızda kullanıcı tipi (daha okunabilir) |

Böylece:

* `ROLE_ADMIN` → yönetici paneline erişebilir
* `ROLE_EMPLOYEE` → stok/ürün düzenleme yetkisine sahip olabilir
* `ROLE_CUSTOMER` → alışveriş yapabilir, sipariş oluşturabilir

Symfony’nin güvenlik sistemi `roles` alanını kontrol ederek erişim izni verir.

---

## 🔐 3️⃣ **Security (Kimlik Doğrulama) Altyapısı**

Symfony’de kullanıcı sistemi için `make:auth` komutu kullanıldı:

```bash
php bin/console make:auth
```

Bu komut oluşturdu:

* `SecurityController` (login & logout işlemleri)
* `login.html.twig` (giriş formu)
* `security.yaml` ayarları

### Artık:

* `/login` → kullanıcı girişi yapılır
* `/logout` → oturum kapatılır
* Symfony `UserInterface` implementasyonu sayesinde kullanıcı verisi session’da tutulur.

---

## 🧱 4️⃣ **User Fixtures (Örnek Kullanıcılar)**

Geliştirme aşamasında sisteme giriş yapabilmek için üç örnek kullanıcı oluşturduk:

| Ad       | Email                 | Role              | Şifre          |
| -------- | --------------------- | ----------------- | --------------- |
| Admin    | `admin@shop.com`    | `ROLE_ADMIN`    | `admin123`    |
| Employee | `employee@shop.com` | `ROLE_EMPLOYEE` | `employee123` |
| Customer | `customer@shop.com` | `ROLE_CUSTOMER` | `customer123` |

Bu kullanıcılar `AppFixtures.php` içinde `UserPasswordHasherInterface` kullanılarak oluşturuldu.

Her birinin `type` alanı (`admin`, `employee`, `customer`) olarak belirlendi.

> 💡 Parolalar hash’lenerek kaydediliyor (plain text değil).

---

## ⚙️ 5️⃣ **Migration ve Veritabanı**

User entity oluşturulduktan sonra şu komutlarla tablo eklendi:

```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

Artık veritabanında `user` tablosu var.

Sütunları:

```
id | email | roles | password | first_name | last_name | type
```

Fixtures yüklendiğinde bu tabloya 3 kayıt eklendi.

---

## 🧩 6️⃣ **Erişim Kontrolleri (Authorization)**

Symfony’de erişim kontrolü iki düzeyde yapılabiliyor:

### ① Controller bazında:

```php
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
public function adminDashboard(): Response
{
    // only admins can access
}
```

### ② Twig (template) bazında:

```twig
{% if is_granted('ROLE_ADMIN') %}
    <a href="{{ path('app_admin_dashboard') }}">Admin Panel</a>
{% endif %}
```

Bu sayede, örneğin navbar’da yalnızca `ROLE_ADMIN` kullanıcılarının görebileceği menüler tanımlanabilir.

---

## 🧠 7️⃣ **Rol Bazlı Sayfa Mantığı (Planlama)**

Bu noktada sistemde üç tür kullanıcı var ve ilerleyen aşamada:

* **Admin:** tüm sistemi yönetir (ürün, kategori, kullanıcı, sipariş)
* **Employee:** ürün ekleyebilir, stok yönetir
* **Customer:** ürünleri görüntüler, sipariş verir

Bu yapı “tek tablo, çok rol” stratejisiyle inşa edildi.

Avantajı:

* Yönetimi kolay
* Symfony Security doğrudan `roles` alanını kullanır

---

## ✅ **Sonuç**

| Aşama | İşlem                                | Sonuç                                     |
| ------ | -------------------------------------- | ------------------------------------------ |
| 1      | `User`entity oluşturuldu            | Veritabanında kullanıcı tablosu var     |
| 2      | `type`ve `roles`eklendi            | Kullanıcı türleri & yetkileri ayrıldı |
| 3      | `make:auth`ile login sistemi kuruldu | `/login`ve `/logout`çalışıyor      |
| 4      | 3 kullanıcı fixture eklendi          | Admin, Employee, Customer hazır           |
| 5      | Migration çalıştırıldı           | DB yapısı güncel                        |
| 6      | Erişim kontrolleri tanımlandı       | Sayfa bazlı yetki yönetimi aktif         |

---
