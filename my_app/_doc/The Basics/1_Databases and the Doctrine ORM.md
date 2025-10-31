# Veritabanları ve Doctrine ORM

Symfony, veritabanlarıyla çalışmak için **Doctrine** adlı güçlü PHP kütüphane setini kullanır. Doctrine, hem **ilişkisel veritabanlarını** (örneğin MySQL, PostgreSQL) hem de **NoSQL veritabanlarını** (örneğin MongoDB) destekler.

> 🎥 **Video tercih ediyorsanız:** [Doctrine Screencast serisine](https://symfonycasts.com/) göz atabilirsiniz.

---

## 🔖 İçerik

Symfony dokümantasyonu veritabanı konusunu üç ana makaleye ayırır:

1. **Bu makale:** Symfony uygulamalarında ilişkisel veritabanlarıyla çalışmanın önerilen yolunu açıklar.
2. **Düşük seviyeli SQL erişimi:** PDO benzeri ham SQL sorgularıyla çalışmayı açıklayan ayrı bir makaleye bakın.
3. **MongoDB desteği:** NoSQL veritabanlarıyla çalışmak için [DoctrineMongoDBBundle](https://symfony.com/doc/current/bundles/DoctrineMongoDBBundle/index.html) dokümanına başvurun.

---

## ⚙️ Doctrine’in Kurulumu

Doctrine desteğini ve kod üretimi için MakerBundle’ı yükleyin:

```bash
composer require symfony/orm-pack
composer require --dev symfony/maker-bundle
```

---

## 🧩 Veritabanı Yapılandırması

Veritabanı bağlantı bilgileri `DATABASE_URL` adlı ortam değişkeninde saklanır.

Geliştirme ortamında bu değeri `.env` dosyasında bulabilir ve düzenleyebilirsiniz:

```bash
# .env (veya değişikliklerinizi commit etmemek için .env.local içinde geçersiz kılın)

# Bu satırı kendinize göre düzenleyin!
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=8.0.37"

# MariaDB kullanımı:
# doctrine/dbal < 3.7 için:
# DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=mariadb-10.5.8"
# doctrine/dbal 3.7 ve üzeri için:
# DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=10.5.8-MariaDB"

# SQLite kullanımı:
# DATABASE_URL="sqlite:///%kernel.project_dir%/var/app.db"

# PostgreSQL kullanımı:
# DATABASE_URL="postgresql://db_user:db_password@127.0.0.1:5432/db_name?serverVersion=12.19 (Debian 12.19-1.pgdg120+1)&charset=utf8"

# Oracle kullanımı:
# DATABASE_URL="oci8://db_user:db_password@127.0.0.1:1521/db_name"
```

> 🔐 **Not:**
>
> Eğer kullanıcı adı, parola, host veya veritabanı adı URI içinde özel karakterler (`: / ? # [ ] @ ! $ & ' ( ) * + , ; =`) içeriyorsa, bu karakterleri **encode** etmeniz gerekir.
>
> `urlencode()` fonksiyonunu veya ortam değişkeni işlemcisini (`%env(urlencode:DATABASE_USER)%` gibi) kullanabilirsiniz.
>
> Bu durumda, `config/packages/doctrine.yaml` dosyasında `resolve:` önekini kaldırmalısınız:
>
> ```yaml
> url: '%env(DATABASE_URL)%'
> ```

---

## 🏗️ Veritabanı Oluşturma

Bağlantı parametrelerini ayarladıktan sonra Doctrine sizin için veritabanını oluşturabilir:

```bash
php bin/console doctrine:database:create
```

Ayrıca `config/packages/doctrine.yaml` dosyasında `server_version` gibi başka seçenekleri de yapılandırabilirsiniz (örneğin MySQL 8.0.37 kullanıyorsanız `server_version: 8.0.37`).

---

## 📜 Ekstra Komutlar

Doctrine, veritabanı yönetimi için pek çok komut sağlar.

Tüm komutları görmek için şu komutu çalıştırın:

```bash
php bin/console list doctrine
```



![1761943346182](image/1_DatabasesandtheDoctrineORM/1761943346182.png)


# Bir Entity (Varlık) Sınıfı Oluşturma

Uygulamanızda ürünlerin görüntüleneceği bir sistem geliştirdiğinizi düşünelim. Doctrine veya veritabanı hakkında henüz düşünmeseniz bile, bu ürünleri temsil edecek bir **`Product`** (ürün) nesnesine ihtiyacınız olduğunu zaten biliyorsunuzdur.

Symfony’de bu sınıfı oluşturmak için **`make:entity`** komutunu kullanabilirsiniz. Bu komut sizden birkaç soru sorar ve bu cevaplara göre sınıfınızı oluşturur:

---

## ⚙️ Entity Oluşturma Adımları

```bash
$ php bin/console make:entity
```

**Komut çıktısı:**

```
Class name of the entity to create or update:
> Product

New property name (press <return> to stop adding fields):
> name

Field type (enter ? to see all types) [string]:
> string

Field length [255]:
> 255

Can this field be null in the database (nullable) (yes/no) [no]:
> no

New property name (press <return> to stop adding fields):
> price

Field type (enter ? to see all types) [string]:
> integer

Can this field be null in the database (nullable) (yes/no) [no]:
> no

New property name (press <return> to stop adding fields):
> 
(boş bırakıp Enter’a basın)
```

Ve işte karşınızda yeni oluşturulan sınıf:

---

## 🧱 `Product` Entity’si

```php
// src/Entity/Product.php
namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $price = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    // ... getter ve setter metotları
}
```

---

## 🧩 Ek Bilgiler

### ✅ UUID veya ULID Kullanımı

MakerBundle **v1.57.0** sürümünden itibaren, `make:entity` komutuna

`--with-uuid` veya `--with-ulid` seçeneklerini ekleyebilirsiniz.

Bu sayede Symfony’nin **Uid Component** özelliğini kullanarak `id` alanını

**int** yerine **UUID** veya **ULID** tipinde oluşturabilirsiniz.

---

### 🧱 PHP Attribute Desteği

**v1.44.0** sürümünden itibaren  **MakerBundle** , yalnızca **PHP attribute’ları** kullanan entity sınıflarını destekler.

Yani artık eski `@ORM\Column()` gibi annotation’lar yerine şu format kullanılır:

```php
#[ORM\Column(length: 255)]
```

---

### 💰 Neden Fiyat Birimi `integer`?

Bu sadece bir örnektir, ancak genelde fiyatları tam sayı olarak saklamak  **daha güvenlidir** .

Örneğin: `100` değeri **$1.00 USD** anlamına gelebilir.

Bu yöntem, **ondalık yuvarlama hatalarını** önler.

---

### ⚠️ MySQL 5.6 ve Öncesi – Dizin Uzunluğu Limiti

InnoDB tablo motorunu kullanan MySQL 5.6 ve daha eski sürümlerde

**dizin anahtar uzunluğu** için **767 byte** sınırı vardır.

Bu yüzden, UTF8MB4 kodlamasıyla 255 karakter uzunluğundaki bir `string` sütun `unique=true` olarak ayarlanırsa şu hatayı alabilirsiniz:

```
[PDOException] SQLSTATE[42000]: Syntax error or access violation: 1071 Specified key was too long; max key length is 767 bytes
```

👉 Bu hatayı önlemek için `unique=true` olan string alanların uzunluğunu **190** olarak sınırlandırın.

---

## 🧠 Özet

* Bu sınıf bir **“entity” (varlık)** olarak adlandırılır.
* Doctrine, bu sınıfı veritabanındaki **`product`** tablosuna eşler.
* Her özellik (`name`, `price` vb.) tablo sütununa karşılık gelir.
* Bu eşlemeler PHP attribute’larıyla tanımlanır:
  ```php
  #[ORM\Column(...)]
  ```


# Entity Yönetimi, Migrationlar ve Veritabanına Kayıt İşlemleri

Symfony’de **`make:entity`** komutu, işinizi kolaylaştıran bir yardımcı araçtır.

Ancak unutmayın: **bu sınıf sizin kodunuzdur** — alan (field) ekleme/silme, metot ekleme veya yapılandırmayı değiştirme tamamen size aittir.

---

## 🧩 Doctrine Alan Türleri

Doctrine, her biri kendi seçeneklerine sahip birçok alan türünü destekler.

Tüm listeyi görmek için **[Doctrine Mapping Types](https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html#doctrine-mapping-types)** belgesine göz atabilirsiniz.

Eğer PHP attribute’ları yerine **XML** ile mapping yapmak isterseniz,

`config/packages/doctrine.yaml` dosyasına şu ayarları ekleyebilirsiniz:

```yaml
doctrine:
    orm:
        mappings:
            App:
                type: xml
                dir: '%kernel.project_dir%/config/doctrine'
```

---

## ⚠️ SQL Rezerve Kelimeleri Hakkında

 **GROUP** , **USER** gibi bazı kelimeler SQL tarafından **rezerve edilmiş** anahtar kelimelerdir.

Bunları tablo veya sütun adı olarak kullanmayın.

Eğer kullanmanız gerekiyorsa:

* Tablo adını şu şekilde değiştirin:
  ```php
  #[ORM\Table(name: 'groups')]
  ```
* veya sütun adını özel bir isimle eşleyin:
  ```php
  #[ORM\Column(name: 'group_name')]
  ```

Daha fazla bilgi için: [Doctrine Reserved SQL Keywords](https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html#quoting-reserved-words)

---

## 🧱 Migrationlar: Veritabanı Şeması Oluşturma

Artık `Product` sınıfınız hazır, fakat veritabanında **product** tablosu henüz yok.

Bunu oluşturmak için **DoctrineMigrationsBundle** kullanılır (zaten Symfony ile birlikte gelir):

```bash
php bin/console make:migration
```

> 💡 **v1.56.0** itibarıyla, `--formatted` parametresiyle migration dosyasını daha düzenli bir biçimde oluşturabilirsiniz.

**Komut çıktısı örneği:**

```
SUCCESS!
Next: Review the new migration "migrations/Version20211116204726.php"
Then: Run the migration with php bin/console doctrine:migrations:migrate
```

Migration dosyasını açarsanız, veritabanınızı güncellemek için gereken SQL sorgularını göreceksiniz.

Bu sorguları çalıştırmak için migration’ları yürütün:

```bash
php bin/console doctrine:migrations:migrate
```

Bu komut, veritabanınızda daha önce çalıştırılmamış tüm migration dosyalarını yürütür.

Üretim ortamına (production) geçerken **veritabanınızı güncel tutmak için bu komutu çalıştırmayı unutmayın.**

---

## 🧠 Yeni Alan Eklemek (Schema Güncelleme)

`Product` sınıfına yeni bir özellik (örneğin `description`) eklemek istiyorsanız:

sınıfı elle düzenleyebilirsiniz, ama `make:entity` komutunu tekrar kullanmak daha kolaydır:

```bash
php bin/console make:entity
```

**Komut çıktısı:**

```
Class name of the entity to create or update
> Product

New property name (press <return> to stop adding fields):
> description

Field type (enter ? to see all types) [string]:
> text

Can this field be null in the database (nullable) (yes/no) [no]:
> no
```

Bu işlem sonunda `Product` sınıfınıza şu kod eklenecektir:

```php
// src/Entity/Product.php
use Doctrine\DBAL\Types\Types;

class Product
{
    // ...

    #[ORM\Column(type: Types::TEXT)]
    private string $description;

    // getDescription() ve setDescription() metotları da otomatik eklendi
}
```

---

## 🧩 Yeni Migration Oluşturma

Yeni `description` sütunu henüz veritabanında yok.

Bunu eklemek için yeni bir migration oluşturun:

```bash
php bin/console make:migration
```

Üretilen SQL şu şekilde olacaktır:

```sql
ALTER TABLE product ADD description LONGTEXT NOT NULL
```

Migration sistemi, **entity sınıflarınız** ile **veritabanı durumu** arasındaki farkı algılar

ve gerekli SQL sorgularını otomatik oluşturur.

Migration’ı çalıştırın:

```bash
php bin/console doctrine:migrations:migrate
```

---

### ⚠️ SQLite Kullanıcıları İçin Not

Eğer **SQLite** kullanıyorsanız şu hatayı alabilirsiniz:

```
PDOException: SQLSTATE[HY000]: General error: 1 Cannot add a NOT NULL column with default value NULL
```

Bunu düzeltmek için yeni sütunu **nullable=true** yapın:

```php
#[ORM\Column(type: Types::TEXT, nullable: true)]
```

---

DoctrineMigrationsBundle, çalıştırılmış migration’ları `migration_versions` tablosunda takip eder.

Bu sayede her migration yalnızca **bir kez** çalıştırılır.

> 💡 Her şema değişikliğinde şu iki komutu çalıştırın:
>
> ```bash
> php bin/console make:migration
> php bin/console doctrine:migrations:migrate
> ```
>
> Ve migration dosyalarını **commit edip deploy sırasında çalıştırmayı unutmayın.**

---

## ⚙️ Getter / Setter’ları Yeniden Oluşturma

Yeni alanları manuel olarak eklediyseniz, getter/setter metodlarını şu komutla üretebilirsiniz:

```bash
php bin/console make:entity --regenerate
```

Tüm getter/setter metotlarını yeniden oluşturmak istiyorsanız:

```bash
php bin/console make:entity --regenerate --overwrite
```

---

## 💾 Nesneleri Veritabanına Kaydetme

Artık bir **Product** nesnesini veritabanına kaydetmeye hazırsınız!

Yeni bir controller oluşturalım:

```bash
php bin/console make:controller ProductController
```

Ardından controller’ınızı düzenleyin:

```php
// src/Controller/ProductController.php
namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'create_product')]
    public function createProduct(EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $product->setName('Keyboard');
        $product->setPrice(1999);
        $product->setDescription('Ergonomic and stylish!');

        // Doctrine’a bu nesnenin kaydedilmesini söyle (henüz sorgu çalışmaz)
        $entityManager->persist($product);

        // Veritabanına gerçekten yazar (INSERT sorgusu çalışır)
        $entityManager->flush();

        return new Response('Saved new product with id '.$product->getId());
    }
}
```

---

## 🚀 Test Edelim

Tarayıcınızda şu URL’ye gidin:

```
http://localhost:8000/product
```

Tebrikler 🎉 — artık veritabanındaki `product` tablosuna ilk satırınızı eklediniz!

Doğrulamak için veritabanına doğrudan sorgu atabilirsiniz:

```bash
php bin/console dbal:run-sql 'SELECT * FROM product'
```

---

## 🔍 Kodu Adım Adım İnceleme

| Satır           | Açıklama                                                                                                                                                                                                                     |
| ---------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **13**     | `EntityManagerInterface $entityManager`, Symfony’nin**Entity Manager**servisini controller metoduna enjekte eder. Bu nesne, veritabanı işlemlerinden sorumludur.                                                    |
| **15–18** | `$product`nesnesini normal bir PHP nesnesi gibi oluşturur ve doldurursunuz.                                                                                                                                                 |
| **21**     | `persist($product)`çağrısı Doctrine’e bu nesnenin yönetilmesi gerektiğini söyler. Henüz veritabanına sorgu yapılmaz.                                                                                              |
| **24**     | `flush()`çağrısı yapıldığında Doctrine, yönetilen tüm nesneleri kontrol eder ve gerekli olanları**INSERT**veya**UPDATE**sorguları olarak yürütür. Bu örnekte**INSERT**sorgusu çalışır. |

Eğer `flush()` başarısız olursa, **`Doctrine\ORM\ORMException`** fırlatılır.

Ayrıntılar için **Transactions and Concurrency** konusuna bakın.

---

## 🧠 Özet

Doctrine, entity’lerinizi otomatik olarak takip eder ve değişiklikleri algılar:

* **Yeni nesne** oluşturulursa → `INSERT`
* **Var olan nesne** değiştirilirse → `UPDATE`

Symfony + Doctrine = güçlü, otomatik ve güvenli bir veritabanı yönetimi ✅


# Nesnelerin Doğrulanması (Validating Objects) ve Veritabanından Getirilmesi

Symfony, Doctrine metadata’sını kullanarak **otomatik doğrulama (validation)** işlemlerini kolaylaştırabilir.

Bu sayede, veritabanı kurallarınızdan bazı doğrulama kuralları **otomatik olarak** türetilir.

---

## ✅ Doctrine Metadata ile Otomatik Doğrulama

Symfony Validator bileşeni, Doctrine metadata’sını okuyarak bazı doğrulama kurallarını otomatik oluşturabilir.

Öncelikle, `auto_mapping` seçeneğini etkinleştirmeniz gerekir.

Bu seçenek, hangi entity sınıflarının otomatik olarak analiz edilip doğrulama kurallarının çıkarılacağını belirtir.

---

### Örnek Controller

```php
// src/Controller/ProductController.php
namespace App\Controller;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'create_product')]
    public function createProduct(ValidatorInterface $validator): Response
    {
        $product = new Product();

        // ... örneğin bir formdan gelen verilerle product doldurulur ...

        $errors = $validator->validate($product);
        if (count($errors) > 0) {
            return new Response((string) $errors, 400);
        }

        // ... kayıt işlemi burada devam eder ...
    }
}
```

Bu örnekte `Product` sınıfı herhangi bir özel doğrulama tanımı içermese de,

`auto_mapping` seçeneği aktifse Symfony bazı kuralları **Doctrine metadata’sından tahmin ederek** uygular.

---

### 🔍 Doctrine Metadata → Symfony Validation Eşlemesi

| Doctrine Özelliği | Symfony Doğrulama Kuralı | Notlar                                              |
| ------------------- | -------------------------- | --------------------------------------------------- |
| `nullable=false`  | `NotNull`                | `PropertyInfo`bileşeninin kurulu olması gerekir |
| `type`            | `Type`                   | `PropertyInfo`bileşeninin kurulu olması gerekir |
| `unique=true`     | `UniqueEntity`           | -                                                   |
| `length`          | `Length`                 | -                                                   |

---

### 💡 Otomatik Doğrulamanın Avantajları

* Form bileşeni ve  **API Platform** , dahili olarak Validator bileşenini kullandığından,

  bu otomatik doğrulama kurallarından **formlarınız ve API’leriniz de faydalanır.**
* Böylece veritabanı kısıtlamalarınız otomatik olarak uygulamaya yansır.

> ⚠️ **Ancak:**
>
> Bu özellik yalnızca temel doğrulamaları kapsar.
>
> Kullanıcıdan gelen verilerin geçerliliğini sağlamak için **kendi özel doğrulama kurallarınızı** eklemeniz gerekir.

---

## 🧾 Veritabanından Nesne Getirme (Fetching Objects)

Bir nesneyi veritabanından geri almak çok kolaydır.

Örneğin, `/product/1` adresine giderek bir ürünü görüntülemek istiyorsunuz:

```php
// src/Controller/ProductController.php
namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    #[Route('/product/{id}', name: 'product_show')]
    public function show(EntityManagerInterface $entityManager, int $id): Response
    {
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        return new Response('Check out this great product: '.$product->getName());

        // Alternatif: Twig template ile render et
        // return $this->render('product/show.html.twig', ['product' => $product]);
    }
}
```

---

## 🧱 Repository Kullanımı (Daha Temiz Yaklaşım)

Symfony, **autowiring** sayesinde repository’leri otomatik olarak inject edebilir:

```php
// src/Controller/ProductController.php
namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    #[Route('/product/{id}', name: 'product_show')]
    public function show(ProductRepository $productRepository, int $id): Response
    {
        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('No product found for id '.$id);
        }

        return new Response('Product: '.$product->getName());
    }
}
```

**Test edin:**

```
http://localhost:8000/product/1
```

---

## 🧠 Repository Nedir?

Bir  **repository** , belirli bir entity sınıfına ait verileri veritabanından almak için kullanılan özel bir sınıftır.

Doctrine, her entity için bir repository sınıfı oluşturur (örneğin `ProductRepository`).

---

## 🔎 Repository Yardımcı Metotları

```php
$repository = $entityManager->getRepository(Product::class);

// Bir ürünü ID’sine göre bul
$product = $repository->find($id);

// İsme göre tek bir ürün bul
$product = $repository->findOneBy(['name' => 'Keyboard']);

// İsme ve fiyata göre ara
$product = $repository->findOneBy([
    'name' => 'Keyboard',
    'price' => 1999,
]);

// İsme göre çoklu ürün bul, fiyata göre sırala
$products = $repository->findBy(
    ['name' => 'Keyboard'],
    ['price' => 'ASC']
);

// Tüm ürünleri getir
$products = $repository->findAll();
```

---

## ⚙️ Gelişmiş Sorgular

Repository’lerinizi genişleterek **özel sorgular** yazabilirsiniz.

Bu konu, “Databases and the Doctrine ORM” bölümünün ilerleyen kısımlarında daha detaylı ele alınacaktır.

---

## 🧰 Web Debug Toolbar

Symfony, sayfanın altındaki **web debug toolbar** aracında, yapılan veritabanı sorgularının sayısını ve sürelerini gösterir.

Bu, performans takibi ve optimizasyon için oldukça faydalıdır.

---

### 🎯 Özet

* Doctrine metadata’sı, Symfony Validator bileşeniyle entegre çalışarak otomatik doğrulama kuralları uygular.
* Entity verilerini almak için **Repository** sınıflarını kullanırsınız.
* Doctrine, entity’leri otomatik olarak yönetir ve CRUD işlemlerini kolaylaştırır.
* Web Debug Toolbar, sorguların performansını izlemenizi sağlar.



![1761943530330](image/1_DatabasesandtheDoctrineORM/1761943530330.png)


# Otomatik Nesne Getirme (EntityValueResolver)

Symfony, **EntityValueResolver** özelliği sayesinde, route parametrelerinden otomatik olarak Doctrine entity’lerini bulabilir ve controller metodunuza enjekte edebilir. Bu, controller kodunu önemli ölçüde basitleştirir.

---

## 🧭 Web Debug Toolbar ve Profiler

Eğer bir sayfada **veritabanı sorgu sayısı fazla** ise, web debug toolbar’daki  **veritabanı simgesi sarıya döner** .

Bu, sorguların optimize edilmesi gerektiğini gösterir.

🔍 **İpuçları:**

* Simgeye tıklayarak  **Symfony Profiler** ’ı açabilir ve yürütülen SQL sorgularını inceleyebilirsiniz.
* Eğer web debug toolbar görünmüyorsa, Profiler paketini yükleyin:

```bash
composer require --dev symfony/profiler-pack
```

Daha fazla bilgi için [Symfony Profiler belgelerine](https://symfony.com/doc/current/profiler.html) göz atabilirsiniz.

---

## ⚙️ EntityValueResolver ile Otomatik Getirme

> 🆕 DoctrineBundle **v2.7.1** sürümüyle birlikte **EntityValueResolver autowiring** özelliği eklenmiştir.

Bu özellik, controller’ınızdaki entity sorgularını **otomatik olarak sizin yerinize** yapabilir.

Artık `find()` veya `findOneBy()` çağrıları yazmanıza gerek yoktur.

### 🎯 Basit Örnek

```php
// src/Controller/ProductController.php
namespace App\Controller;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    #[Route('/product/{id}')]
    public function show(Product $product): Response
    {
        // $product otomatik olarak veritabanından getirilir!
        // Eğer bulunmazsa Symfony otomatik olarak 404 hatası döner.
        return new Response('Product: '.$product->getName());
    }
}
```

Burada `{id}` route parametresi, Doctrine’deki `id` sütununa karşılık gelir.

Resolver, `ProductRepository::find($id)` metodunu sizin yerinize çağırır.

---

## 🧩 404 Davranışını Özelleştirme

Eğer entity bulunamadığında  **otomatik 404 gönderilmesini istemiyorsanız** , parametreyi **isteğe bağlı (nullable)** hale getirin:

```php
#[Route('/product/{id}')]
public function show(?Product $product): Response
{
    if (null === $product) {
        // Kendi hata yanıtınızı döndürebilirsiniz
        return new Response('Product not found!', 404);
    }

    return new Response('Product: '.$product->getName());
}
```

---

## 🔧 Belirli Controller’da Devre Dışı Bırakma

EntityValueResolver davranışını global olarak etkinleştirdiyseniz,

belirli bir controller veya argüman için devre dışı bırakabilirsiniz:

```php
public function show(
    #[CurrentUser]
    #[MapEntity(disabled: true)]
    User $user
): Response {
    // Bu durumda User, EntityValueResolver tarafından çözülmez.
}
```

---

## 🪄 Otomatik Getirme Mantığı (Fetch Automatically)

Eğer route parametreleriniz entity’deki property’lerle eşleşiyorsa,

Doctrine bunları otomatik olarak çözümleyebilir:

```php
/**
 * {id} route parametresiyle primary key üzerinden getirme.
 */
#[Route('/product/{id}')]
public function showByPk(Product $product): Response
{
}

/**
 * {slug} parametresini entity’nin slug alanına eşleyerek getirme.
 */
#[Route('/product/{slug:product}')]
public function showBySlug(Product $product): Response
{
}
```

### 🧠 Otomatik Çözümleme Kuralları

1. Eğer route’ta `{id}` varsa → Doctrine, `find()` metodu ile primary key üzerinden arama yapar.
2. Route’taki diğer parametreler (`{slug}`, `{code}` vb.), entity’deki property isimleriyle eşleşiyorsa → `findOneBy()` metodu ile sorgulanır.
3. `{slug:product}` sözdizimi, `slug` parametresinin `$product` argümanına eşlenmesini sağlar ve Doctrine’in `Product` tablosunda `slug` alanını aramasına yol açar.

---

## 🆕 Symfony 7.1 ile Route Parametre Haritalama

Symfony **7.1** sürümünden itibaren, route parametreleri doğrudan controller argümanlarıyla eşlenebilir.

Ayrıca bu eşlemeyi **MapEntity** attribute’u ile manuel olarak yapılandırabilirsiniz.

```php
// src/Controller/ProductController.php
namespace App\Controller;

use App\Entity\Product;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    #[Route('/product/{slug}')]
    public function show(
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Product $product
    ): Response {
        return new Response('Product: '.$product->getName());
    }
}
```

### 🔧 MapEntity ile Yapılandırma Seçenekleri

`#[MapEntity]` attribute’u sayesinde:

* Route parametreleri ile entity property’leri arasında eşleme yapabilirsiniz.
* EntityValueResolver’ın davranışını (örneğin `disabled`, `mapping`, `expr` gibi) kontrol edebilirsiniz.
* Özellikle slug, token veya özel kimliklerle entity getirme işlemleri kolaylaşır.

---

## 🚀 Özet

| Özellik                        | Açıklama                                                                                    |
| ------------------------------- | --------------------------------------------------------------------------------------------- |
| **EntityValueResolver**   | Doctrine entity’lerini route parametrelerine göre otomatik olarak çözer                   |
| **404 Handling**          | Entity bulunamazsa otomatik 404 döner (isteğe bağlı olarak devre dışı bırakılabilir) |
| **MapEntity Attribute**   | Otomatik eşlemeyi özelleştirmenizi sağlar                                                 |
| **Symfony Profiler**      | Gerçekleşen SQL sorgularını ve performansı görmenizi sağlar                            |
| **Symfony 7.1 yeniliği** | Route parametre–entity mapping sözdizimi (`{slug:product}`)                               |

Symfony, Doctrine ve EntityValueResolver birleşimiyle,


# Doctrine ile Nesne Getirme, Güncelleme ve Sorgulama

Symfony’de Doctrine ORM, entity’leri (veritabanı nesnelerini) hem otomatik olarak çözümleyebilir (fetch), hem de özel sorgularla esnek biçimde yönetmenizi sağlar. Aşağıda Doctrine’in gelişmiş kullanımlarını adım adım inceleyelim.

---

## 🪄 Expression (İfade) Kullanarak Nesne Getirme

Bazı durumlarda otomatik çözümleme (`EntityValueResolver`) yeterli olmayabilir.

Bu gibi durumlarda **ExpressionLanguage** bileşeni sayesinde özel ifadelerle entity’leri getirebilirsiniz:

```php
#[Route('/product/{product_id}')]
public function show(
    #[MapEntity(expr: 'repository.find(product_id)')]
    Product $product
): Response {
}
```

Buradaki:

* `repository` → Entity’nin repository sınıfını ifade eder.
* `{product_id}` → Route içindeki parametreyi temsil eder.

---

### 🔁 Liste Döndürme (Entity Listesi Getirme)

Repository metodu bir liste döndürüyorsa, controller argümanınızı `iterable` olarak tanımlayabilirsiniz:

```php
#[Route('/posts_by/{author_id}')]
public function authorPosts(
    #[MapEntity(class: Post::class, expr: 'repository.findBy({"author": author_id}, {}, 10)')]
    iterable $posts
): Response {
}
```

> 🆕 Symfony **7.1** ile birlikte entity listesi eşleme (mapping) desteği eklenmiştir.

---

### ⚙️ Birden Fazla Argümanı Çözümleme

Birden fazla entity’yi aynı route üzerinden çözümleyebilirsiniz:

```php
#[Route('/product/{id}/comments/{comment_id}')]
public function show(
    Product $product,
    #[MapEntity(expr: 'repository.find(comment_id)')]
    Comment $comment
): Response {
}
```

Burada `$product` otomatik çözülürken, `$comment` için özel ifade kullanılmıştır.

---

### 🧠 Request Nesnesine Erişim

`request` değişkeni, ifadelerde kullanılabilir.

Örneğin, `sort` adlı bir query parametresine göre sıralama yapmak isterseniz:

```php
#[Route('/product/{id}/comments')]
public function show(
    Product $product,
    #[MapEntity(expr: 'repository.findOneBy({"product": id}, {"createdAt": request.query.get("sort", "DESC")})')]
    Comment $comment
): Response {
}
```

---

## 🔗 Arayüz (Interface) Üzerinden Nesne Çözümleme

Eğer `Product` sınıfınız bir `ProductInterface` arayüzünü uyguluyorsa, controller’ınızı bu arayüze bağımlı hale getirebilirsiniz.

Öncelikle `resolve_target_entities` seçeneğini yapılandırın.

Daha sonra controller şu şekilde tanımlanabilir:

```php
public function show(
    #[MapEntity]
    ProductInterface $product
): Response {
    // ...
}
```

> 🆕 Symfony **7.3** itibarıyla `EntityValueResolver` artık hedef entity çözümlemeyi (`target entity resolution`) destekler.

---

## 🧩 MapEntity Attribute Seçenekleri

`#[MapEntity]` attribute’u, entity çözümlemenin nasıl yapılacağını ayrıntılı biçimde kontrol etmenizi sağlar.

| Seçenek                | Açıklama                                                           | Örnek                                                                    |
| ----------------------- | -------------------------------------------------------------------- | ------------------------------------------------------------------------- |
| **id**            | Route parametresini primary key olarak eşler.                       | `#[MapEntity(id: 'product_id')]`                                        |
| **mapping**       | Route parametreleri ile entity property’lerini eşler.              | `#[MapEntity(mapping: ['slug' => 'slug'])]`                             |
| **stripNull**     | `findOneBy()`kullanılırken null değerleri sorgudan çıkarır.  | `#[MapEntity(stripNull: true)]`                                         |
| **objectManager** | Varsayılan dışında farklı bir entity manager kullanır.         | `#[MapEntity(objectManager: 'foo')]`                                    |
| **evictCache**    | Doctrine’in cache yerine veritabanından sorgu yapmasını sağlar. | `#[MapEntity(evictCache: true)]`                                        |
| **disabled**      | Bu argüman için EntityValueResolver’ı devre dışı bırakır.   | `#[MapEntity(disabled: true)]`                                          |
| **message**       | Development modunda NotFoundHttpException mesajını özelleştirir. | `#[MapEntity(id: 'product_id', message: 'The product does not exist')]` |

> 🆕 **`message`** seçeneği Symfony **7.1** sürümünde eklendi.

---

## ✏️ Nesne Güncelleme (Updating an Object)

Doctrine’den bir nesne getirdikten sonra, onu tıpkı normal bir PHP nesnesi gibi değiştirebilirsiniz:

```php
// src/Controller/ProductController.php
namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    #[Route('/product/edit/{id}', name: 'product_edit')]
    public function update(EntityManagerInterface $entityManager, int $id): Response
    {
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException('No product found for id '.$id);
        }

        $product->setName('New product name!');
        $entityManager->flush();

        return $this->redirectToRoute('product_show', [
            'id' => $product->getId()
        ]);
    }
}
```

**Güncelleme adımları:**

1. Doctrine üzerinden nesneyi getir.
2. Değişiklikleri yap.
3. `flush()` çağırarak veritabanına uygula.

> `persist()` çağrısına gerek yoktur çünkü Doctrine, entity’yi zaten izlemektedir.

---

## 🗑️ Nesne Silme (Deleting an Object)

Silme işlemi, `remove()` metoduyla yapılır:

```php
$entityManager->remove($product);
$entityManager->flush();
```

> `remove()` metodu, Doctrine’e bu nesnenin silinmesini bildirir.
>
> **DELETE** sorgusu ancak `flush()` çağrıldığında yürütülür.

---

## 🔍 Repository (Depo) ile Sorgulama

Repository, belirli bir entity tipi için veritabanı sorgularını yöneten özel bir sınıftır.

Doctrine, entity oluşturulurken otomatik olarak bir repository sınıfı üretir:

```php
// src/Repository/ProductRepository.php
namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }
}
```

---

### 🔧 Özel Sorgu Metodu Eklemek

```php
// src/Repository/ProductRepository.php

class ProductRepository extends ServiceEntityRepository
{
    /**
     * @return Product[]
     */
    public function findAllGreaterThanPrice(int $price): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT p
             FROM App\Entity\Product p
             WHERE p.price > :price
             ORDER BY p.price ASC'
        )->setParameter('price', $price);

        return $query->getResult();
    }
}
```

> Bu sorgu, SQL’e benzer görünür ama **Doctrine Query Language (DQL)** kullanır.
>
> DQL, tablo adları yerine PHP sınıflarını (`App\Entity\Product`) referans alır.

Kullanımı:

```php
$products = $entityManager
    ->getRepository(Product::class)
    ->findAllGreaterThanPrice(1000);
```

---

## 🧱 Query Builder ile Dinamik Sorgular

Doctrine’un **Query Builder** aracı, nesne yönelimli bir sorgu oluşturma yöntemidir.

Özellikle koşullara göre dinamik sorgular oluşturmak için önerilir.

```php
class ProductRepository extends ServiceEntityRepository
{
    public function findAllGreaterThanPrice(int $price, bool $includeUnavailableProducts = false): array
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.price > :price')
            ->setParameter('price', $price)
            ->orderBy('p.price', 'ASC');

        if (!$includeUnavailableProducts) {
            $qb->andWhere('p.available = TRUE');
        }

        return $qb->getQuery()->execute();
    }
}
```

> Tek bir sonuç almak için:
>
> ```php
> $product = $qb->setMaxResults(1)->getQuery()->getOneOrNullResult();
> ```

---

## 🧮 Doğrudan SQL ile Sorgulama

Bazı durumlarda ham SQL sorgusu yazmak isteyebilirsiniz:

```php
class ProductRepository extends ServiceEntityRepository
{
    public function findAllGreaterThanPrice(int $price): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT * FROM product p
            WHERE p.price > :price
            ORDER BY p.price ASC
        ';

        $resultSet = $conn->executeQuery($sql, ['price' => $price]);

        // Ham veri dizisi döner (entity objesi değil)
        return $resultSet->fetchAllAssociative();
    }
}
```

> Eğer entity objeleri döndürmek isterseniz, **NativeQuery** özelliğini kullanabilirsiniz.

---

## 🧠 Özet

| Konu                                 | Açıklama                                                      |
| ------------------------------------ | --------------------------------------------------------------- |
| **EntityValueResolver**        | Route parametrelerinden entity’leri otomatik çözer.          |
| **ExpressionLanguage**         | Özel ifadelerle entity veya liste sorgulama imkânı sunar.    |
| **MapEntity Attribute**        | Entity eşleme (mapping) davranışını özelleştirir.        |
| **Güncelleme ve Silme**       | `flush()`ile yönetilen değişiklikleri uygular.             |
| **Repository & Query Builder** | Karmaşık veya dinamik sorgular yazmanızı sağlar.           |
| **SQL Desteği**               | Gerektiğinde doğrudan SQL sorguları çalıştırabilirsiniz. |

Symfony ve Doctrine birlikte kullanıldığında, hem güçlü hem de okunabilir bir **veri erişim katmanı** oluşturabilirsiniz.


# Doctrine Yapılandırması, İlişkiler ve Genişletmeler

Doctrine, Symfony’nin en güçlü bileşenlerinden biridir.

Sadece veritabanı işlemlerini değil,  **ilişkileri** , **testleri** ve **otomatik özellikleri** (örneğin createdAt alanını otomatik atama) de kolayca yönetmenizi sağlar.

---

## ⚙️ Doctrine Yapılandırması (Configuration)

Symfony, Doctrine’i `config/packages/doctrine.yaml` dosyası üzerinden yapılandırır.

Tüm mevcut yapılandırma seçeneklerini görmek için **[Doctrine Configuration Reference](https://symfony.com/doc/current/reference/configuration/doctrine.html)** sayfasına göz atabilirsiniz.

Bu sayfada şunları bulabilirsiniz:

* Varsayılan **veritabanı bağlantı ayarları**
* **ORM (Entity Manager)** seçenekleri
* **Cache** , **Proxy** ve **naming strategy** ayarları
* **Auto-mapping** ,  **repository** , ve **schema tool** yapılandırmaları

---

## 🔗 İlişkiler ve Bağlantılar (Relationships and Associations)

Doctrine, tüm klasik veritabanı ilişkilerini destekler:

| İlişki Türü      | Açıklama                                                                                           |
| -------------------- | ---------------------------------------------------------------------------------------------------- |
| **ManyToOne**  | Birçok varlık, bir diğerine bağlıdır (örnek: birçok ürün bir kategoriye aittir).           |
| **OneToMany**  | Bir varlık, birçok diğer varlığa sahiptir (örnek: bir kullanıcı birçok gönderi yazabilir). |
| **OneToOne**   | İki varlık bire bir ilişkilidir (örnek: bir kullanıcı bir profile sahiptir).                   |
| **ManyToMany** | Birçok varlık, birçok diğer varlıkla ilişkilidir (örnek: ürünler ve etiketler).             |

Doctrine, bu ilişkiler için otomatik **foreign key** yönetimi, **cascade** işlemleri, ve **lazy loading** özellikleri sunar.

➡️ Ayrıntılı bilgi için:

📘 [How to Work with Doctrine Associations / Relations](https://symfony.com/doc/current/doctrine/associations.html)

---

## 🧪 Veritabanı Testleri (Database Testing)

Symfony, testlerinizin veritabanıyla **güvenli ve izole bir şekilde** etkileşim kurabilmesini sağlar.

Doctrine’in test senaryolarında nasıl kullanılacağını öğrenmek için şu makaleye bakın:

📘 [Testing Code that Interacts with the Database](https://symfony.com/doc/current/testing/database.html)

Bu rehber, test veritabanı oluşturma, fixture kullanımı ve verilerin test sırasında sıfırlanması gibi konuları içerir.

---

## 🧩 Doctrine Genişletmeleri (Doctrine Extensions)

Doctrine topluluğu, sık kullanılan işlevleri kolaylaştırmak için çeşitli **Doctrine Extensions** paketleri geliştirmiştir.

Bu eklentiler, sık tekrarlanan görevleri otomatikleştirir.

### 🌟 Yaygın Genişletmeler

| Genişletme             | Açıklama                                                         |
| ----------------------- | ------------------------------------------------------------------ |
| **Timestampable** | `createdAt`ve `updatedAt`alanlarını otomatik olarak ayarlar. |
| **Sluggable**     | Otomatik olarak SEO dostu slug’lar üretir.                       |
| **Translatable**  | Çok dilli (i18n) veri yönetimini sağlar.                        |
| **Sortable**      | Nesneleri sıralamak için otomatik sıra alanı oluşturur.       |
| **Loggable**      | Entity değişikliklerini kaydeder (audit trail).                  |

Symfony projelerinde bu genişletmeleri kolayca entegre etmek için şu paketi kullanabilirsiniz:

```bash
composer require stof/doctrine-extensions-bundle
```

Daha fazla bilgi için:

📘 [StofDoctrineExtensionsBundle Documentation](https://symfony.com/doc/current/bundles/StofDoctrineExtensionsBundle/index.html)

---

## 🧠 Özet

| Konu                                   | Açıklama                                                                        |
| -------------------------------------- | --------------------------------------------------------------------------------- |
| **Doctrine Config**              | `doctrine.yaml`dosyasında yapılandırılır.                                  |
| **İlişkiler (Associations)**   | OneToOne, OneToMany, ManyToOne, ManyToMany ilişkilerini destekler.               |
| **Database Testing**             | Doctrine ile veritabanı testlerini izole ve güvenli biçimde yürütür.        |
| **Doctrine Extensions**          | Timestampable, Sluggable, Translatable gibi yaygın işlevleri otomatikleştirir. |
| **StofDoctrineExtensionsBundle** | Doctrine eklentilerini Symfony’ye kolayca entegre etmenizi sağlar.              |

---

Doctrine, Symfony’nin güçlü altyapısıyla birleştiğinde,

veritabanı etkileşimlerinizi **basit, güvenli ve genişletilebilir** hale getirir.
