## 🧩 UID Bileşeni

UID bileşeni, UUID ve ULID gibi benzersiz tanımlayıcılar (UID) ile çalışmak için araçlar sağlar.

### ⚙️ Kurulum

```
composer require symfony/uid
```

Bu bileşeni bir Symfony uygulaması dışında yüklüyorsanız, Composer tarafından sağlanan sınıf otomatik yükleme mekanizmasını etkinleştirmek için kodunuzda `vendor/autoload.php` dosyasını dahil etmelisiniz. Daha fazla bilgi için bu makaleyi okuyun.

---

## 🧠 UUID’ler

UUID’ler (evrensel olarak benzersiz tanımlayıcılar), yazılım endüstrisinde en popüler UID türlerinden biridir. UUID’ler, genellikle beş grup halinde gösterilen 128 bitlik sayılardır:

`xxxxxxxx-xxxx-Mxxx-Nxxx-xxxxxxxxxxxx`

(Burada M, UUID sürümünü; N ise UUID varyantını gösterir.)

---

### 🕰️ UUID v1 (Zamana Dayalı)

Zaman damgası ve cihazınızın MAC adresini kullanarak UUID oluşturur (UUIDv1 spesifikasyonuna bakın). Her ikisi de otomatik olarak elde edilir, bu yüzden yapıcıya bir argüman geçirmenize gerek yoktur:

```php
use Symfony\Component\Uid\Uuid;

$uuid = Uuid::v1();
// $uuid bir Symfony\Component\Uid\UuidV1 örneğidir
```

 **UUIDv1 yerine UUIDv7 kullanmanız önerilir** , çünkü daha iyi entropi sağlar.

---

### 🔐 UUID v2 (DCE Güvenliği)

UUIDv1’e benzer, ancak **ID çakışması olasılığı çok yüksektir** (UUIDv2 spesifikasyonuna bakın). DCE (Distributed Computing Environment) kimlik doğrulama mekanizmasının bir parçasıdır ve oluşturulan UUID, kullanıcının POSIX UID (kullanıcı/grup kimliği) değerlerini içerir.

Bu varyant  **Uid bileşeni tarafından uygulanmamıştır** .

---

### 🧾 UUID v3 (İsim Tabanlı, MD5)

Belirli bir ad alanına (namespace) ait, deterministik UUID’ler oluşturur (UUIDv3 spesifikasyonuna bakın). Bu varyant, rastgele dizelerden **belirli bir biçimde aynı UUID’nin** üretilmesini sağlar. Namespace ve name değerlerinin md5 hash’ini kullanır:

```php
use Symfony\Component\Uid\Uuid;

$namespace = Uuid::fromString(Uuid::NAMESPACE_OID);
// veya rastgele bir namespace oluşturabilirsiniz:
// $namespace = Uuid::v4();

$uuid = Uuid::v3($namespace, $name);
// $uuid bir Symfony\Component\Uid\UuidV3 örneğidir
```

Standartta tanımlı varsayılan namespace’ler:

* `Uuid::NAMESPACE_DNS` – DNS girdileri için
* `Uuid::NAMESPACE_URL` – URL’ler için
* `Uuid::NAMESPACE_OID` – Nesne tanımlayıcıları (OID) için
* `Uuid::NAMESPACE_X500` – X500 DN’leri için

---

### 🎲 UUID v4 (Rastgele)

Rastgele UUID üretir (UUIDv4 spesifikasyonuna bakın). Rastgeleliği sayesinde merkezi bir koordinasyona gerek olmadan sistemler arası benzersizliği sağlar. Nerede ve ne zaman üretildiğine dair hiçbir bilgi içermez:

```php
use Symfony\Component\Uid\Uuid;

$uuid = Uuid::v4();
// $uuid bir Symfony\Component\Uid\UuidV4 örneğidir
```

---

### 🧮 UUID v5 (İsim Tabanlı, SHA-1)

UUIDv3 ile aynıdır ancak md5 yerine **sha1** algoritmasını kullanır (UUIDv5 spesifikasyonuna bakın). Daha güvenlidir ve hash çakışmalarına karşı daha dayanıklıdır.

---

### 🗂️ UUID v6 (Yeniden Sıralanmış Zamana Dayalı)

UUIDv1’in zaman temelli alanlarını yeniden düzenler ve **sözlük sıralamasına uygun hale getirir** (ULID’ler gibi). Bu sayede **veritabanı indeksleme performansını artırır** (UUIDv6 spesifikasyonuna bakın):

```php
use Symfony\Component\Uid\Uuid;

$uuid = Uuid::v6();
// $uuid bir Symfony\Component\Uid\UuidV6 örneğidir
```

UUIDv6 yerine  **UUIDv7 kullanmanız önerilir** , çünkü daha iyi entropi sağlar.

---

### ⏱️ UUID v7 (UNIX Zaman Damgası)

Yüksek çözünürlüklü Unix Epoch zaman damgasına dayalı olarak zaman sıralı UUID üretir

(1 Ocak 1970 UTC’den itibaren geçen milisaniye sayısı) (UUIDv7 spesifikasyonuna bakın).

UUIDv1 ve UUIDv6 yerine  **UUIDv7 kullanmanız önerilir** , çünkü daha iyi entropi ve daha kesin zaman sıralaması sağlar:

```php
use Symfony\Component\Uid\Uuid;

$uuid = Uuid::v7();
// $uuid bir Symfony\Component\Uid\UuidV7 örneğidir
```

---

### 🧪 UUID v8 (Özel)

Deneysel veya satıcıya özgü kullanımlar için **RFC uyumlu bir biçim** sağlar (UUIDv8 spesifikasyonuna bakın). UUID değerini kendiniz oluşturmalısınız. Tek gereklilik, varyant ve sürüm bitlerini doğru şekilde ayarlamaktır:

```php
use Symfony\Component\Uid\Uuid;

$uuid = Uuid::v8('d9e7a184-5d5b-11ea-a62a-3499710062d0');
// $uuid bir Symfony\Component\Uid\UuidV8 örneğidir
```

---

### 🔄 Mevcut UUID’den Nesne Oluşturma

UUID değeri başka bir biçimde oluşturulmuşsa aşağıdaki yöntemlerden biriyle `Uuid` nesnesi oluşturabilirsiniz:

```php
$uuid = Uuid::fromString('d9e7a184-5d5b-11ea-a62a-3499710062d0');
$uuid = Uuid::fromBinary("\xd9\xe7\xa1\x84\x5d\x5b\x11\xea\xa6\x2a\x34\x99\x71\x00\x62\xd0");
$uuid = Uuid::fromBase32('6SWYGR8QAV27NACAHMK5RG0RPG');
$uuid = Uuid::fromBase58('TuetYWNHhmuSQ3xPoVLv9M');
$uuid = Uuid::fromRfc4122('d9e7a184-5d5b-11ea-a62a-3499710062d0');
```

---

## 🏭 UUID Fabrikası Kullanımı

UUID üretimini yapılandırmak için `UuidFactory` kullanabilirsiniz. Önce yapılandırma dosyalarında fabrika davranışını tanımlayın:

```php
// config/packages/uid.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $container->extension('framework', [
        'uid' => [
            'default_uuid_version' => 7,
            'name_based_uuid_version' => 5,
            'name_based_uuid_namespace' => '6ba7b810-9dad-11d1-80b4-00c04fd430c8',
            'time_based_uuid_version' => 7,
            'time_based_uuid_node' => 121212121212,
        ],
    ]);
};
```

Sonrasında servislerinize `UuidFactory`’yi enjekte ederek tanımladığınız yapılandırmaya göre UUID üretebilirsiniz:

```php
namespace App\Service;

use Symfony\Component\Uid\Factory\UuidFactory;

class FooService
{
    public function __construct(
        private UuidFactory $uuidFactory,
    ) {
    }

    public function generate(): void
    {
        $uuid = $this->uuidFactory->create();
        $nameBasedUuid = $this->uuidFactory->nameBased(/** ... */);
        $randomBasedUuid = $this->uuidFactory->randomBased();
        $timestampBased = $this->uuidFactory->timeBased();

        // ...
    }
}
```


## 🔄 UUID’leri Dönüştürme

UUID nesnesini farklı tabanlara dönüştürmek için aşağıdaki yöntemleri kullanabilirsiniz:

```php
$uuid = Uuid::fromString('d9e7a184-5d5b-11ea-a62a-3499710062d0');

$uuid->toBinary();  // string(16) "\xd9\xe7\xa1\x84\x5d\x5b\x11\xea\xa6\x2a\x34\x99\x71\x00\x62\xd0"
$uuid->toBase32();  // string(26) "6SWYGR8QAV27NACAHMK5RG0RPG"
$uuid->toBase58();  // string(22) "TuetYWNHhmuSQ3xPoVLv9M"
$uuid->toRfc4122(); // string(36) "d9e7a184-5d5b-11ea-a62a-3499710062d0"
$uuid->toHex();     // string(34) "0xd9e7a1845d5b11eaa62a3499710062d0"
$uuid->toString();  // string(36) "d9e7a184-5d5b-11ea-a62a-3499710062d0"
```

> 🆕 **toString()** metodu Symfony 7.1’de tanıtılmıştır.

Bazı UUID sürümlerini birbirine dönüştürebilirsiniz:

```php
// V1'i V6 veya V7'ye dönüştürme
$uuid = Uuid::v1();

$uuid->toV6(); // Symfony\Component\Uid\UuidV6 örneği döner
$uuid->toV7(); // Symfony\Component\Uid\UuidV7 örneği döner

// V6'yı V7'ye dönüştürme
$uuid = Uuid::v6();

$uuid->toV7(); // Symfony\Component\Uid\UuidV7 örneği döner
```

> 🆕 **toV6(), toV7()** metotları Symfony 7.1’de tanıtılmıştır.

---

## ⚙️ UUID’lerle Çalışmak

`Uuid` sınıfıyla oluşturulan UUID nesneleri aşağıdaki yöntemleri kullanabilir

(Bunlar PHP uzantısındaki `uuid_*()` fonksiyonlarına denktir):

```php
use Symfony\Component\Uid\NilUuid;
use Symfony\Component\Uid\Uuid;

// UUID'nin null olup olmadığını kontrol etme
$uuid = Uuid::v4();
$uuid instanceof NilUuid; // false
```

```php
// UUID türünü kontrol etme
use Symfony\Component\Uid\UuidV4;
$uuid = Uuid::v4();
$uuid instanceof UuidV4; // true
```

```php
// UUID’nin oluşturulma zamanını alma (sadece bazı türlerde mevcut)
$uuid = Uuid::v1();
$uuid->getDateTime(); // \DateTimeImmutable örneği döner
```

```php
// Bir değerin geçerli UUID olup olmadığını kontrol etme
$isValid = Uuid::isValid($uuid); // true veya false
```

```php
// UUID’leri karşılaştırma ve eşitlik kontrolü
$uuid1 = Uuid::v1();
$uuid4 = Uuid::v4();
$uuid1->equals($uuid4); // false
```

```php
// Bu metodun dönüş değeri:
//   * int(0)   → $uuid1 ve $uuid4 eşitse
//   * int > 0  → $uuid1, $uuid4’ten büyükse
//   * int < 0  → $uuid1, $uuid4’ten küçükse
$uuid1->compare($uuid4); // örneğin int(4)
```

Farklı UUID formatlarıyla çalışıyorsanız ve bunları doğrulamak istiyorsanız, `isValid()` metodundaki `$format` parametresiyle beklediğiniz UUID formatını belirtebilirsiniz:

```php
use Symfony\Component\Uid\Uuid;

$isValid = Uuid::isValid('90067ce4-f083-47d2-a0f4-c47359de0f97', Uuid::FORMAT_RFC_4122);
$isValid = Uuid::isValid('3aJ7CNpDMfXPZrCsn4Cgey', Uuid::FORMAT_BASE_32 | Uuid::FORMAT_BASE_58);
```

Kullanılabilir sabitler:

* `Uuid::FORMAT_BINARY`
* `Uuid::FORMAT_BASE_32`
* `Uuid::FORMAT_BASE_58`
* `Uuid::FORMAT_RFC_4122`
* `Uuid::FORMAT_RFC_9562` (Uuid::FORMAT_RFC_4122 ile eşdeğer)
* `Uuid::FORMAT_ALL` → tüm formatları kabul eder

Varsayılan olarak sadece **RFC 4122** formatı kabul edilir.

> 🆕 `$format` parametresi ve ilgili sabitler Symfony 7.2’de tanıtılmıştır.

---

## 🗄️ Veritabanlarında UUID Saklama

Doctrine kullanıyorsanız, UUID nesnelerini otomatik olarak dönüştüren `uuid` Doctrine türünü kullanabilirsiniz:

```php
// src/Entity/Product.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Column(type: UuidType::NAME)]
    private Uuid $someProperty;

    // ...
}
```

UUID değerlerini otomatik oluşturmak için bir Doctrine ID generator da vardır:

```php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    // ...
}
```

UUID’leri birincil anahtar olarak kullanmak  **genellikle performans açısından önerilmez** :

* İndeksler daha yavaştır ve daha fazla yer kaplar (çünkü UUID’ler 128 bit’tir).
* Sırasız UUID’ler indeks parçalanmasına neden olur.

Sadece **UUID v6 ve UUID v7** bu parçalanma sorununu çözer (ancak indeks boyutu sorunu devam eder).

Doctrine’un yerleşik `findOneBy()` gibi yöntemleri, UUID türlerini otomatik olarak algılar ve SQL sorgusunu buna göre oluşturur.

Ancak DQL veya manuel sorgu oluşturuyorsanız, parametre türünü açıkça belirtmelisiniz:

```php
// src/Repository/ProductRepository.php

use Doctrine\DBAL\ParameterType;
use Symfony\Bridge\Doctrine\Types\UuidType;

class ProductRepository extends ServiceEntityRepository
{
    public function findUserProducts(User $user): array
    {
        $qb = $this->createQueryBuilder('p')
            ->setParameter('user', $user->getUuid(), UuidType::NAME)
            ->setParameter('user', $user->getUuid()->toBinary(), ParameterType::BINARY);

        // ...
    }
}
```

---

## 🧮 ULID’ler

ULID’ler (Universally Unique Lexicographically Sortable Identifier),

genellikle 26 karakterlik şu biçimde gösterilen 128 bit sayılardır:

`TTTTTTTTTTRRRRRRRRRRRRRRRR`

(T: zaman damgası, R: rastgele bitler)

ULID’ler, UUID’lerin pratik olmadığı durumlarda bir alternatiftir.

UUID ile 128 bit uyumluluk sağlar, **sözlük sıralamasına göre sıralanabilir**

ve 36 karakter yerine **26 karakter** uzunluğundadır.

Aynı milisaniye içinde birden fazla ULID üretilirse, sıralanabilirliği korumak için rastgele bölüm 1 bit artırılır.

---

### 🔢 ULID Üretme

Rastgele bir ULID üretmek için `Ulid` sınıfını başlatın:

```php
use Symfony\Component\Uid\Ulid;

$ulid = new Ulid();  // örn. 01AN4Z07BY79KA1307SR9X4MV3
```

ULID değeri zaten oluşturulmuşsa, aşağıdaki yöntemlerden biriyle `Ulid` nesnesi oluşturabilirsiniz:

```php
$ulid = Ulid::fromString('01E439TP9XJZ9RPFH3T1PYBCR8');
$ulid = Ulid::fromBinary("\x01\x71\x06\x9d\x59\x3d\x97\xd3\x8b\x3e\x23\xd0\x6d\xe5\xb3\x08");
$ulid = Ulid::fromBase32('01E439TP9XJZ9RPFH3T1PYBCR8');
$ulid = Ulid::fromBase58('1BKocMc5BnrVcuq2ti4Eqm');
$ulid = Ulid::fromRfc4122('0171069d-593d-97d3-8b3e-23d06de5b308');
```

---

### 🏭 ULID Fabrikası Kullanımı

UUID’lerde olduğu gibi, ULID’ler için de `UlidFactory` sınıfı vardır:

```php
namespace App\Service;

use Symfony\Component\Uid\Factory\UlidFactory;

class FooService
{
    public function __construct(
        private UlidFactory $ulidFactory,
    ) {
    }

    public function generate(): void
    {
        $ulid = $this->ulidFactory->create();

        // ...
    }
}
```

---

### 🚫 Nil ULID

ULID null değerlerini temsil etmek için özel bir `NilUlid` sınıfı vardır:

```php
use Symfony\Component\Uid\NilUlid;

$ulid = new NilUlid();
// eşdeğeri: $ulid = new Ulid('00000000000000000000000000');
```


## 🔄 ULID’leri Dönüştürme

ULID nesnesini farklı tabanlara dönüştürmek için aşağıdaki yöntemleri kullanabilirsiniz:

```php
$ulid = Ulid::fromString('01E439TP9XJZ9RPFH3T1PYBCR8');

$ulid->toBinary();  // string(16) "\x01\x71\x06\x9d\x59\x3d\x97\xd3\x8b\x3e\x23\xd0\x6d\xe5\xb3\x08"
$ulid->toBase32();  // string(26) "01E439TP9XJZ9RPFH3T1PYBCR8"
$ulid->toBase58();  // string(22) "1BKocMc5BnrVcuq2ti4Eqm"
$ulid->toRfc4122(); // string(36) "0171069d-593d-97d3-8b3e-23d06de5b308"
$ulid->toHex();     // string(34) "0x0171069d593d97d38b3e23d06de5b308"
```

---

## ⚙️ ULID’lerle Çalışmak

`Ulid` sınıfı ile oluşturulan ULID nesneleri aşağıdaki yöntemleri kullanabilir:

```php
use Symfony\Component\Uid\Ulid;

$ulid1 = new Ulid();
$ulid2 = new Ulid();

// Belirli bir değerin geçerli bir ULID olup olmadığını kontrol etme
$isValid = Ulid::isValid($ulidValue); // true veya false

// ULID oluşturulma zamanını alma
$ulid1->getDateTime(); // \DateTimeImmutable örneği döner

// ULID’leri karşılaştırma ve eşitlik kontrolü
$ulid1->equals($ulid2); // false
// Bu metot $ulid1 <=> $ulid2 sonucunu döndürür
$ulid1->compare($ulid2); // örneğin int(-1)
```

---

## 🗄️ Veritabanlarında ULID Saklama

Doctrine kullanıyorsanız, ULID nesnelerini otomatik olarak dönüştüren `ulid` Doctrine türünü kullanabilirsiniz:

```php
// src/Entity/Product.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Column(type: UlidType::NAME)]
    private Ulid $someProperty;

    // ...
}
```

ULID değerlerini otomatik olarak oluşturmak için bir Doctrine ID generator da vardır:

```php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

class Product
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    private ?Ulid $id;

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    // ...
}
```

ULID’leri birincil anahtar olarak kullanmak  **genellikle performans açısından önerilmez** .

ULID’ler, değerler sıralı olduğu için indeks parçalanması sorunu yaşamaz,

ancak indeksler daha yavaştır ve daha fazla yer kaplar (çünkü ULID’ler 128 bit’tir).

Doctrine’un yerleşik `findOneBy()` gibi yöntemleri ULID türlerini otomatik olarak tanır

ve SQL sorgusunu buna göre oluşturur. Ancak DQL sorguları veya elle oluşturulan sorgularda

parametre türünü açıkça belirtmeniz gerekir:

```php
// src/Repository/ProductRepository.php

use Symfony\Bridge\Doctrine\Types\UlidType;

class ProductRepository extends ServiceEntityRepository
{
    public function findUserProducts(User $user): array
    {
        $qb = $this->createQueryBuilder('p')
            // ULID olduğunu belirtmek için üçüncü argümanda UlidType::NAME kullanın
            ->setParameter('user', $user->getUlid(), UlidType::NAME)

            // Alternatif olarak, Doctrine’in beklediği biçime dönüştürebilirsiniz
            ->setParameter('user', $user->getUlid()->toBinary());

        // ...
    }
}
```

---

## 🧰 UUID/ULID Oluşturma ve İnceleme (Konsolda)

Bu bileşen, konsolda UUID ve ULID oluşturmak ve incelemek için çeşitli komutlar sağlar.

Varsayılan olarak etkin değildir; bu komutları kullanmadan önce aşağıdaki yapılandırmayı eklemeniz gerekir:

```php
// config/services.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Uid\Command\GenerateUlidCommand;
use Symfony\Component\Uid\Command\GenerateUuidCommand;
use Symfony\Component\Uid\Command\InspectUlidCommand;
use Symfony\Component\Uid\Command\InspectUuidCommand;

return static function (ContainerConfigurator $container): void {
    // ...

    $services
        ->set(GenerateUlidCommand::class)
        ->set(GenerateUuidCommand::class)
        ->set(InspectUlidCommand::class)
        ->set(InspectUuidCommand::class);
};
```

Artık aşağıdaki şekilde UUID/ULID üretebilirsiniz

(Tüm seçenekleri görmek için komutlara `--help` ekleyin):

```bash
php bin/console uuid:generate --random-based

php bin/console uuid:generate --time-based=now --node=fb3502dc-137e-4849-8886-ac90d07f64a7

php bin/console uuid:generate --count=2 --format=base58

php bin/console ulid:generate

php bin/console ulid:generate --time="2021-02-02 14:00:00"

php bin/console ulid:generate --count=2 --format=rfc4122
```

Yeni UID’ler oluşturmanın yanı sıra, belirli bir UID’nin tüm bilgilerini görmek için

aşağıdaki komutlarla bunları inceleyebilirsiniz:

```bash
php bin/console uuid:inspect d0a3a023-f515-4fe0-915c-575e63693998
```

```
 ---------------------- --------------------------------------
  Label                  Value
 ---------------------- --------------------------------------
  Version                4
  Canonical (RFC 4122)   d0a3a023-f515-4fe0-915c-575e63693998
  Base 58                SmHvuofV4GCF7QW543rDD9
  Base 32                6GMEG27X8N9ZG92Q2QBSHPJECR
 ---------------------- --------------------------------------
```

```bash
php bin/console ulid:inspect 01F2TTCSYK1PDRH73Z41BN1C4X
```

```
 --------------------- --------------------------------------
  Label                 Value
 --------------------- --------------------------------------
  Canonical (Base 32)   01F2TTCSYK1PDRH73Z41BN1C4X
  Base 58               1BYGm16jS4kX3VYCysKKq6
  RFC 4122              0178b5a6-67d3-0d9b-889c-7f205750b09d
 --------------------- --------------------------------------
  Timestamp             2021-04-09 08:01:24.947
 --------------------- --------------------------------------
```


## 🔄 ULID’leri Dönüştürme

ULID nesnesini farklı tabanlara dönüştürmek için aşağıdaki yöntemleri kullanabilirsiniz:

```php
$ulid = Ulid::fromString('01E439TP9XJZ9RPFH3T1PYBCR8');

$ulid->toBinary();  // string(16) "\x01\x71\x06\x9d\x59\x3d\x97\xd3\x8b\x3e\x23\xd0\x6d\xe5\xb3\x08"
$ulid->toBase32();  // string(26) "01E439TP9XJZ9RPFH3T1PYBCR8"
$ulid->toBase58();  // string(22) "1BKocMc5BnrVcuq2ti4Eqm"
$ulid->toRfc4122(); // string(36) "0171069d-593d-97d3-8b3e-23d06de5b308"
$ulid->toHex();     // string(34) "0x0171069d593d97d38b3e23d06de5b308"
```

---

## ⚙️ ULID’lerle Çalışmak

`Ulid` sınıfı ile oluşturulan ULID nesneleri aşağıdaki yöntemleri kullanabilir:

```php
use Symfony\Component\Uid\Ulid;

$ulid1 = new Ulid();
$ulid2 = new Ulid();

// Belirli bir değerin geçerli bir ULID olup olmadığını kontrol etme
$isValid = Ulid::isValid($ulidValue); // true veya false

// ULID oluşturulma zamanını alma
$ulid1->getDateTime(); // \DateTimeImmutable örneği döner

// ULID’leri karşılaştırma ve eşitlik kontrolü
$ulid1->equals($ulid2); // false
// Bu metot $ulid1 <=> $ulid2 sonucunu döndürür
$ulid1->compare($ulid2); // örneğin int(-1)
```

---

## 🗄️ Veritabanlarında ULID Saklama

Doctrine kullanıyorsanız, ULID nesnelerini otomatik olarak dönüştüren `ulid` Doctrine türünü kullanabilirsiniz:

```php
// src/Entity/Product.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Column(type: UlidType::NAME)]
    private Ulid $someProperty;

    // ...
}
```

ULID değerlerini otomatik olarak oluşturmak için bir Doctrine ID generator da vardır:

```php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

class Product
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    private ?Ulid $id;

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    // ...
}
```

ULID’leri birincil anahtar olarak kullanmak  **genellikle performans açısından önerilmez** .

ULID’ler, değerler sıralı olduğu için indeks parçalanması sorunu yaşamaz,

ancak indeksler daha yavaştır ve daha fazla yer kaplar (çünkü ULID’ler 128 bit’tir).

Doctrine’un yerleşik `findOneBy()` gibi yöntemleri ULID türlerini otomatik olarak tanır

ve SQL sorgusunu buna göre oluşturur. Ancak DQL sorguları veya elle oluşturulan sorgularda

parametre türünü açıkça belirtmeniz gerekir:

```php
// src/Repository/ProductRepository.php

use Symfony\Bridge\Doctrine\Types\UlidType;

class ProductRepository extends ServiceEntityRepository
{
    public function findUserProducts(User $user): array
    {
        $qb = $this->createQueryBuilder('p')
            // ULID olduğunu belirtmek için üçüncü argümanda UlidType::NAME kullanın
            ->setParameter('user', $user->getUlid(), UlidType::NAME)

            // Alternatif olarak, Doctrine’in beklediği biçime dönüştürebilirsiniz
            ->setParameter('user', $user->getUlid()->toBinary());

        // ...
    }
}
```

---

## 🧰 UUID/ULID Oluşturma ve İnceleme (Konsolda)

Bu bileşen, konsolda UUID ve ULID oluşturmak ve incelemek için çeşitli komutlar sağlar.

Varsayılan olarak etkin değildir; bu komutları kullanmadan önce aşağıdaki yapılandırmayı eklemeniz gerekir:

```php
// config/services.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Uid\Command\GenerateUlidCommand;
use Symfony\Component\Uid\Command\GenerateUuidCommand;
use Symfony\Component\Uid\Command\InspectUlidCommand;
use Symfony\Component\Uid\Command\InspectUuidCommand;

return static function (ContainerConfigurator $container): void {
    // ...

    $services
        ->set(GenerateUlidCommand::class)
        ->set(GenerateUuidCommand::class)
        ->set(InspectUlidCommand::class)
        ->set(InspectUuidCommand::class);
};
```

Artık aşağıdaki şekilde UUID/ULID üretebilirsiniz

(Tüm seçenekleri görmek için komutlara `--help` ekleyin):

```bash
php bin/console uuid:generate --random-based

php bin/console uuid:generate --time-based=now --node=fb3502dc-137e-4849-8886-ac90d07f64a7

php bin/console uuid:generate --count=2 --format=base58

php bin/console ulid:generate

php bin/console ulid:generate --time="2021-02-02 14:00:00"

php bin/console ulid:generate --count=2 --format=rfc4122
```

Yeni UID’ler oluşturmanın yanı sıra, belirli bir UID’nin tüm bilgilerini görmek için

aşağıdaki komutlarla bunları inceleyebilirsiniz:

```bash
php bin/console uuid:inspect d0a3a023-f515-4fe0-915c-575e63693998
```

```
 ---------------------- --------------------------------------
  Label                  Value
 ---------------------- --------------------------------------
  Version                4
  Canonical (RFC 4122)   d0a3a023-f515-4fe0-915c-575e63693998
  Base 58                SmHvuofV4GCF7QW543rDD9
  Base 32                6GMEG27X8N9ZG92Q2QBSHPJECR
 ---------------------- --------------------------------------
```

```bash
php bin/console ulid:inspect 01F2TTCSYK1PDRH73Z41BN1C4X
```

```
 --------------------- --------------------------------------
  Label                 Value
 --------------------- --------------------------------------
  Canonical (Base 32)   01F2TTCSYK1PDRH73Z41BN1C4X
  Base 58               1BYGm16jS4kX3VYCysKKq6
  RFC 4122              0178b5a6-67d3-0d9b-889c-7f205750b09d
 --------------------- --------------------------------------
  Timestamp             2021-04-09 08:01:24.947
 --------------------- --------------------------------------
```
