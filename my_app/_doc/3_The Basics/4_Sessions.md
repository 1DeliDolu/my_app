### 🧠 Symfony Oturumlar (Sessions)

Symfony’nin **HttpFoundation** bileşeni, güçlü ve esnek bir **oturum yönetim sistemi** sunar. Bu sistem, kullanıcıyla yapılan istekler arasında bilgi saklamanıza olanak tanır ve farklı oturum depolama sürücülerini destekleyen, **nesne yönelimli** bir arabirim sağlar.

Symfony oturumları, PHP’nin yerleşik `$_SESSION` değişkeninin ve `session_start()`, `session_regenerate_id()`, `session_id()`, `session_name()` veya `session_destroy()` gibi fonksiyonlarının yerini alacak şekilde tasarlanmıştır.

> ⚙️ **Not:** Oturumlar yalnızca onlardan okuma veya yazma işlemi yaptığınızda başlatılır.

---

## ⚙️ Kurulum

Oturumları yönetebilmek için **HttpFoundation** bileşenini kurmanız gerekir:

```bash
composer require symfony/http-foundation
```

---

## 🚀 Temel Kullanım

Oturum nesnesine `Request` nesnesi veya `RequestStack` servisi üzerinden erişebilirsiniz.

Symfony, servislerde veya denetleyicilerde (`controller`) `RequestStack` tip ipucunu (type-hint) otomatik olarak enjekte eder:

```php
use Symfony\Component\HttpFoundation\Session\Session;

$session = new Session();
$session->start();
```

Bir Symfony denetleyicisinde ise doğrudan `Request` tip ipucu kullanabilirsiniz:

```php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

public function index(Request $request): Response
{
    $session = $request->getSession();

    // ...
}
```

---

## 💾 Oturum Nitelikleri (Session Attributes)

PHP’nin varsayılan oturum yönetimi `$_SESSION` süper globaline dayanır.

Ancak bu, **test edilebilirlik** ve **OOP kapsülleme (encapsulation)** açısından uygun değildir.

Symfony, bu sorunu çözmek için **"session bag"** (oturum çantası) adını verdiği yapıları kullanır.

Her oturum çantası, kendi verisini benzersiz bir ad alanında saklar, böylece `$_SESSION` değişkeni kirlenmeden diğer uygulamalarla sorunsuz şekilde birlikte çalışabilir.

Bir oturum çantası, dizi benzeri davranan bir PHP nesnesidir:

```php
// bir niteliği (attribute) daha sonra kullanılmak üzere saklar
$session->set('attribute-name', 'attribute-value');

// bir niteliği isme göre alır
$foo = $session->get('foo');

// ikinci argüman, nitelik mevcut değilse dönecek varsayılan değerdir
$filters = $session->get('filters', []);
```

> 🔍 Saklanan nitelikler, kullanıcı oturumu boyunca kalıcıdır.
>
> Oturuma erişmek (okumak, yazmak veya kontrol etmek) otomatik olarak oturumu başlatır.
>
> Bu, anonim kullanıcılar için gereksiz oturum başlatmalarına neden olabileceğinden  **performansı olumsuz etkileyebilir** .

> 🧱 CSRF koruması gibi oturuma dayalı özellikler de otomatik olarak oturumu başlatır.

---

## 💬 Flash Mesajları (Flash Messages)

Symfony oturumları, **flash mesajlar** adı verilen özel mesajları saklayabilir.

Flash mesajları **yalnızca bir kez** kullanılır; okunur okunmaz oturumdan silinirler.

Bu özellik, kullanıcı bildirimleri için idealdir.

### Örnek: Form Gönderimi Sonrası Bildirim

```php
use Symfony\Component\HttpFoundation\Session\Session;

$session = new Session();
$session->start();

// flash mesaj çantasını al
$flashes = $session->getFlashBag();

// mesaj ekle
$flashes->add('notice', 'Değişiklikleriniz kaydedildi');
```

Denetleyici isteği işledikten sonra flash mesajını ekler ve yönlendirme yapar.

Mesaj türü (`notice`, `warning`, `error` vb.) istediğiniz herhangi bir isim olabilir.

---

### 🧩 Twig Şablonunda Mesajları Görüntüleme

```twig
{# templates/base.html.twig #}

{# belirli bir türdeki flash mesajı oku ve göster #}
{% for message in app.flashes('notice') %}
    <div class="flash-notice">
        {{ message }}
    </div>
{% endfor %}

{# flash çantasından silmeden okumak için #}
{% for message in app.session.flashbag.peek('notice') %}
    <div class="flash-notice">
        {{ message }}
    </div>
{% endfor %}

{# birden fazla türü oku #}
{% for label, messages in app.flashes(['success', 'warning']) %}
    {% for message in messages %}
        <div class="flash-{{ label }}">
            {{ message }}
        </div>
    {% endfor %}
{% endfor %}

{# tüm flash mesajlarını oku #}
{% for label, messages in app.flashes %}
    {% for message in messages %}
        <div class="flash-{{ label }}">
            {{ message }}
        </div>
    {% endfor %}
{% endfor %}

{# flash çantasını boşaltmadan tüm mesajları oku #}
{% for label, messages in app.session.flashbag.peekAll() %}
    {% for message in messages %}
        <div class="flash-{{ label }}">
            {{ message }}
        </div>
    {% endfor %}
{% endfor %}
```

> 💡 En yaygın kullanılan türler:
>
> `notice`, `warning`, `error` — ancak kendi anahtarlarınızı da tanımlayabilirsiniz.

---

### ⚠️ Önbellekleme (Caching) Notu

Flash mesajlarına erişmek, oturumun başlamasına neden olur.

Bu durumda Symfony, yanıtı **özel (private)** olarak işaretler.

Bu nedenle flash mesajı içeren sayfalar,  **HTTP önbelleklerinde saklanmamalıdır** .

> 🔄 Alternatif olarak, flash mesajlarını ayrı bir HTTP isteğiyle (örneğin Twig Live Component kullanarak)  **asenkron olarak yükleyebilir** , ana sayfayı tamamen önbelleğe alınabilir hale getirebilirsiniz.

---

### ⚙️ Symfony’de Oturum Yapılandırması (Session Configuration)

Symfony framework’ünde  **oturumlar varsayılan olarak etkindir** .

Oturumun nasıl depolanacağı ve diğer yapılandırmalar, `config/packages/framework.yaml` (veya PHP eşdeğeri `framework.php`) dosyasındaki `framework.session` bölümü üzerinden yönetilir:

```php
// config/packages/framework.php
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->session()
        // Oturum desteğini etkinleştirir.
        // Not: Oturum yalnızca okuma veya yazma işlemi yapıldığında başlatılır.
        // Oturum desteğini tamamen kapatmak için bu bölümü silin veya yorum satırı yapın.
        ->enabled(true)
        // Oturum depolama için kullanılacak servis kimliği
        // NULL değeri, Symfony’nin PHP’nin varsayılan oturum mekanizmasını kullanacağı anlamına gelir.
        ->handlerId(null)
        // Oturum çerezlerinin güvenliğini artırır
        ->cookieSecure('auto')
        ->cookieSamesite(Cookie::SAMESITE_LAX)
        ->storageFactoryId('session.storage.factory.native')
    ;
};
```

`handler_id` seçeneğinin `null` olması, Symfony’nin PHP’nin **yerel oturum mekanizmasını** kullanacağı anlamına gelir.

Bu durumda, oturum meta verileri Symfony uygulaması dışında, PHP’nin yönettiği bir dizinde saklanır.

Bu yöntem basit olsa da, **aynı dizine yazan başka uygulamalar** varsa, oturum süresiyle ilgili beklenmeyen sonuçlar oluşabilir.

---

### 💾 Symfony’nin Oturumları Kendisi Yönetmesi

Symfony’nin oturumları kendisinin yönetmesini istiyorsanız, `handler_id` değerini `session.handler.native_file` olarak ayarlayabilirsiniz.

Ayrıca `save_path` seçeneğiyle oturum dosyalarının saklanacağı dizini belirleyebilirsiniz:

```php
// config/packages/framework.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->session()
        ->handlerId('session.handler.native_file')
        ->savePath('%kernel.project_dir%/var/sessions/%kernel.environment%')
    ;
};
```

> 📘 Ayrıntılar için [Symfony Config Reference](https://symfony.com/doc/current/reference/configuration/framework.html#session) sayfasına bakabilirsiniz.

> ⚠️ `php.ini` içinde `session.auto_start = 1` direktifi  **Symfony ile uyumsuzdur** .
>
> Bu ayarı `php.ini`, `.htaccess` veya web sunucusu yapılandırmasında devre dışı bırakın.

---

### 🕓 Symfony 7.2 ve Sonrası

* `sid_length` ve `sid_bits_per_character` seçenekleri **Symfony 7.2’de kullanım dışı bırakılmıştır** ve Symfony 8.0’da tamamen kaldırılacaktır.
* Oturum çerezi artık **Response nesnesi** üzerinden de erişilebilir — bu, CLI veya Roadrunner / Swoole gibi PHP çalışma ortamlarında faydalıdır.

---

## ⏱️ Oturum Boşta Kalma Süresi / Canlı Tutma (Idle Time / Keep Alive)

Bazı durumlarda kullanıcı oturumu belirli bir süre kullanılmadığında **güvenlik nedeniyle oturumun sonlandırılması** istenir.

Örneğin bankacılık uygulamaları genellikle 5–10 dakika hareketsizlikten sonra kullanıcıyı çıkarır.

Burada **cookie_lifetime** değeri uygun değildir çünkü istemci tarafından değiştirilebilir.

Bu nedenle **sunucu tarafında oturum süresini kontrol etmek** gerekir.

### 🧹 1. Yöntem: Garbage Collection (Çöp Toplama)

Oturum süresi, **garbage collection** (GC) mekanizması ile belirli aralıklarla temizlenebilir:

* `cookie_lifetime` uzun bir süreye ayarlanır.
* `gc_maxlifetime` ise, oturumların ne kadar sürede silineceğini belirler.

### 🔍 2. Yöntem: Oturum Erişiminde Süre Kontrolü

Oturum başlatıldıktan sonra, son kullanım zamanına göre manuel olarak süre aşımı kontrolü yapılabilir:

```php
$session->start();
if (time() - $session->getMetadataBag()->getLastUsed() > $maxIdleTime) {
    $session->invalidate();
    throw new SessionExpired(); // örneğin, "oturum süresi doldu" sayfasına yönlendirin
}
```

Symfony, her oturum için aşağıdaki meta verileri sağlar:

```php
$session->getMetadataBag()->getCreated();   // oluşturulma zamanı
$session->getMetadataBag()->getLastUsed();  // son kullanım zamanı
$session->getMetadataBag()->getLifetime();  // çerezin yaşam süresi
```

---

## 🧹 Garbage Collection Yapılandırması

PHP, oturum açıldığında GC işlemini **olasılıksal** olarak tetikler.

Bu, `session.gc_probability` / `session.gc_divisor` değerleriyle belirlenir.

Örneğin:

* `5/100` → %5 olasılık
* `3/4` → %75 olasılık

GC tetiklendiğinde, PHP `session.gc_maxlifetime` değerini baz alarak bu süreden eski oturumları siler.

> 💡 Debian gibi bazı sistemlerde `session.gc_probability = 0` olarak ayarlanmıştır, bu da PHP’nin GC çalıştırmaması anlamına gelir.

Symfony varsayılan olarak `php.ini`’deki değeri kullanır. Ancak bu ayarı Symfony içinde de yapabilirsiniz:

```yaml
# config/packages/framework.yaml
framework:
    session:
        gc_probability: 1
```

Ayrıca `gc_probability`, `gc_divisor` ve `gc_maxlifetime` değerlerini

`NativeSessionStorage` yapıcısına veya `setOptions()` metoduna dizi olarak geçebilirsiniz.

---

## 🗄️ Oturumları Veritabanında Saklama

Symfony, varsayılan olarak oturumları **dosya sistemi** üzerinde saklar.

Ancak uygulamanız **birden fazla sunucu** üzerinde çalışıyorsa, oturumları bir **veritabanında** saklamanız gerekir.

Symfony, ilişkisel (MySQL, PostgreSQL), NoSQL veya anahtar-değer (Redis, Memcached) veritabanlarını destekler.

> 🔥 En yüksek performans için Redis önerilir.

---

### 💨 Redis ile Oturum Saklama

Redis kullanmak için çalışan bir Redis sunucusuna ve `phpredis` eklentisine ihtiyacınız vardır.

#### 1. php.ini Üzerinden

```ini
; php.ini
session.save_handler = redis
session.save_path = "tcp://192.168.0.178:6379?auth=REDIS_PASSWORD"
```

#### 2. Symfony Servisi Üzerinden

```php
// config/services.php
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler;

$container
    ->register('Redis', \Redis::class)
    ->addMethodCall('connect', ['%env(REDIS_HOST)%', '%env(int:REDIS_PORT)%'])
    // ->addMethodCall('auth', ['%env(REDIS_PASSWORD)%']) // gerekirse
    ->register(RedisSessionHandler::class)
    ->addArgument(new Reference('Redis'));
```

```php
// config/packages/framework.php
use Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler;

$framework->session()
    ->handlerId(RedisSessionHandler::class);
```

> ⚠️ Redis **oturum kilitleme (locking)** mekanizması sağlamaz.
>
> Aynı anda iki istek yapıldığında, **yarış durumu (race condition)** oluşabilir (örneğin CSRF token hataları).

> 🔁 Memcached kullanıyorsanız `RedisSessionHandler` yerine `MemcachedSessionHandler` kullanın.

---

### 🧩 İlişkisel Veritabanında Saklama (MySQL, PostgreSQL)

Symfony, oturumları ilişkisel veritabanlarında saklamak için `PdoSessionHandler` sağlar.

#### Servisi Tanımlayın:

```php
// config/services.php
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

$services->set(PdoSessionHandler::class)
    ->args([env('DATABASE_URL')]);
```

#### Symfony’yi Yapılandırın:

```php
// config/packages/framework.php
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

$framework->session()
    ->handlerId(PdoSessionHandler::class);
```

#### Tabloyu Özelleştirme:

```php
$services->set(PdoSessionHandler::class)
    ->args([
        env('DATABASE_URL'),
        ['db_table' => 'customer_session', 'db_id_col' => 'guid'],
    ]);
```

Desteklenen parametreler:

| Parametre           | Varsayılan            | Açıklama                                                                    |
| ------------------- | ---------------------- | ----------------------------------------------------------------------------- |
| `db_table`        | `sessions`           | Oturum tablosunun adı                                                        |
| `db_id_col`       | `sess_id`            | Oturum kimliği sütunu                                                       |
| `db_data_col`     | `sess_data`          | Oturum verisi sütunu                                                         |
| `db_time_col`     | `sess_time`          | Oluşturulma zamanı                                                          |
| `db_lifetime_col` | `sess_lifetime`      | Oturum süresi                                                                |
| `lock_mode`       | `LOCK_TRANSACTIONAL` | Kilitleme stratejisi (`LOCK_NONE`,`LOCK_ADVISORY`,`LOCK_TRANSACTIONAL`) |

---

### 🧱 Veritabanı Şemasını Hazırlama

Doctrine kuruluysa, `make:migration` çalıştırıldığında `sessions` tablosu otomatik oluşturulur.

Kendiniz oluşturmak isterseniz `createTable()` metodunu da kullanabilirsiniz:

```php
try {
    $sessionHandlerService->createTable();
} catch (\PDOException $exception) {
    // tablo oluşturulamadı
}
```

#### SQL Örnekleri

**MariaDB / MySQL**

```sql
CREATE TABLE `sessions` (
    `sess_id` VARBINARY(128) NOT NULL PRIMARY KEY,
    `sess_data` BLOB NOT NULL,
    `sess_lifetime` INTEGER UNSIGNED NOT NULL,
    `sess_time` INTEGER UNSIGNED NOT NULL,
    INDEX `sess_lifetime_idx` (`sess_lifetime`)
) COLLATE utf8mb4_bin, ENGINE = InnoDB;
```

> `BLOB` sütunu 64 KB sınırına sahiptir.
>
> Daha fazla veriye ihtiyaç duyuyorsanız `MEDIUMBLOB` kullanın.

**PostgreSQL**

```sql
CREATE TABLE sessions (
    sess_id VARCHAR(128) NOT NULL PRIMARY KEY,
    sess_data BYTEA NOT NULL,
    sess_lifetime INTEGER NOT NULL,
    sess_time INTEGER NOT NULL
);
CREATE INDEX sess_lifetime_idx ON sessions (sess_lifetime);
```

**Microsoft SQL Server**

```sql
CREATE TABLE sessions (
    sess_id VARCHAR(128) NOT NULL PRIMARY KEY,
    sess_data NVARCHAR(MAX) NOT NULL,
    sess_lifetime INTEGER NOT NULL,
    sess_time INTEGER NOT NULL,
    INDEX sess_lifetime_idx (sess_lifetime)
);
```

---

### 🗃️ NoSQL Veritabanında Oturum Saklama (MongoDB)

Symfony, **MongoDB** üzerinde oturum verilerini saklamak için `MongoDbSessionHandler` sınıfını içerir.

Bu, özellikle **yüksek ölçekli** veya **dağıtık** sistemlerde oturum yönetimini kolaylaştırır.

---

## ⚙️ Başlangıç

Öncelikle, Symfony uygulamanızda **çalışan bir MongoDB bağlantısının** bulunduğundan emin olun.

(Bkz: [DoctrineMongoDBBundle yapılandırma rehberi](https://symfony.com/doc/current/bundles/DoctrineMongoDBBundle/index.html))

Daha sonra `MongoDbSessionHandler` servisini tanımlayıp gerekli parametrelerle yapılandırın:

* **database** → kullanılacak veritabanı adı
* **collection** → oturumların saklanacağı koleksiyon adı

```php
// config/services.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(MongoDbSessionHandler::class)
        ->args([
            service('doctrine_mongodb.odm.default_connection'),
            [
                'database' => '%env("MONGODB_DB")%',
                'collection' => 'sessions',
            ],
        ]);
};
```

---

### 🧩 Symfony’ye Bildirme

Symfony’ye bu servisin oturum yöneticisi olarak kullanılacağını belirtin:

```php
// config/packages/framework.php
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler;
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->session()
        ->handlerId(MongoDbSessionHandler::class);
};
```

Böylece Symfony artık oturum verilerini MongoDB’ye okuyup yazacaktır.

Koleksiyonun elle oluşturulmasına gerek yoktur, ancak **çöp toplama (garbage collection)** performansını artırmak için bir **index** eklemeniz önerilir:

```js
use session_db
db.session.createIndex({ "expires_at": 1 }, { expireAfterSeconds: 0 })
```

---

## ⚙️ Oturum Alanlarını (Field Names) Yapılandırma

Oturum verilerini saklayan koleksiyon, varsayılan olarak bazı alan adları kullanır.

Bu alanları özelleştirmek isterseniz, ikinci argümanda seçenekleri belirtebilirsiniz:

```php
// config/services.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\HttpFoundation\Session\Storage\Handler\MongoDbSessionHandler;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(MongoDbSessionHandler::class)
        ->args([
            service('doctrine_mongodb.odm.default_connection'),
            [
                'database' => '%env("MONGODB_DB")%',
                'collection' => 'sessions',
                'id_field' => '_guid',
                'expiry_field' => 'eol',
            ],
        ]);
};
```

### 📘 Yapılandırılabilir Parametreler

| Parametre        | Varsayılan    | Açıklama                            |
| ---------------- | -------------- | ------------------------------------- |
| `id_field`     | `_id`        | Oturum kimliği (session ID) alanı   |
| `data_field`   | `data`       | Oturum verilerinin saklandığı alan |
| `time_field`   | `time`       | Oturumun oluşturulma zamanı         |
| `expiry_field` | `expires_at` | Oturumun sona erme zamanı            |

---

## 🔄 Oturum Yöneticileri Arasında Geçiş (Session Handler Migration)

Eğer uygulamanız oturumları saklama biçimini değiştiriyorsa (örneğin dosyadan Redis’e geçiş),

veri kaybı olmadan geçiş yapmak için `MigratingSessionHandler` kullanabilirsiniz.

### Önerilen Adımlar:

#### 1️⃣ Yeni Handler’ı “Yazma Modunda” Etkinleştirin

Eski handler normal şekilde çalışır, veriler aynı anda yeni handler’a da yazılır:

```php
$sessionStorage = new MigratingSessionHandler($oldSessionStorage, $newSessionStorage);
```

#### 2️⃣ GC Süresi Sonrası Doğrulayın

Yeni handler’daki verilerin doğru olduğundan emin olun.

#### 3️⃣ Okuma Kaynağını Değiştirin

Şimdi oturumlar yeni handler’dan okunacak, ancak eskiye de yazılacaktır (geri dönüş kolaylığı sağlar):

```php
$sessionStorage = new MigratingSessionHandler($newSessionStorage, $oldSessionStorage);
```

#### 4️⃣ Tam Geçiş

Tüm veriler doğrulandıktan sonra tamamen yeni handler’a geçin.

---

## ⏳ Oturum Ömrü (TTL) Yapılandırması

Varsayılan olarak Symfony, **PHP’nin `session.gc_maxlifetime`** ayarını oturum süresi olarak kullanır.

Ancak oturumları bir veritabanında saklıyorsanız, **TTL (time-to-live)** değerini hem yapılandırmada hem de **çalışma zamanında** (runtime) değiştirebilirsiniz.

> ⚠️ `ini` ayarlarını oturum başlatıldıktan sonra değiştirmek mümkün değildir.
>
> Bu nedenle kullanıcıya özel TTL belirlemek istiyorsanız, çalışma zamanında callback ile yapın.

---

### 🧾 Sabit TTL Tanımlama

```php
// config/services.php
use Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler;

$services->set(RedisSessionHandler::class)
    ->args([
        service('Redis'),
        ['ttl' => 600], // saniye cinsinden (örneğin 10 dakika)
    ]);
```

---

### ⚙️ Dinamik TTL Tanımlama (Runtime)

Kullanıcıya, role veya duruma göre TTL belirlemek istiyorsanız bir **callback (closure)** tanımlayabilirsiniz.

Symfony bu callback’i her oturum yazılmadan hemen önce çağırır.

```php
// config/services.php
use Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler;

$services
    ->set(RedisSessionHandler::class)
    ->args([
        service('Redis'),
        ['ttl' => closure(service('my.ttl.handler'))],
    ]);

$services
    ->set('my.ttl.handler', 'App\Service\MyDynamicTtlHandler')
    ->args([service('security')]); // TTL hesaplaması için gerekli bağımlılıklar
```

`MyDynamicTtlHandler` sınıfı bir `__invoke()` metoduna sahip olmalıdır ve bir **tam sayı (integer)** TTL değeri döndürmelidir.

---

🧩 **Özetle:**

* `MongoDbSessionHandler` NoSQL desteği sağlar.
* Alan adları (`_id`, `data`, `expires_at`) isteğe göre özelleştirilebilir.
* `MigratingSessionHandler` ile güvenli handler geçişi mümkündür.
* `ttl` seçeneği ile oturum ömrü sabit veya dinamik olarak yönetilebilir.

---

### 🌍 Kullanıcının Oturumu Boyunca Locale Değerini "Yapışkan" Hale Getirmek

Symfony’de dil ayarı ( **locale** ) `Request` nesnesinde tutulur, bu da ayarın her istek arasında **otomatik olarak korunmadığı** anlamına gelir.

Ancak locale bilgisini **oturumda (session)** saklayarak, sonraki isteklerde aynı değeri kullanmak mümkündür.

---

## 🧩 LocaleSubscriber Oluşturma

Yeni bir **event subscriber** sınıfı oluşturun.

Genellikle `_locale` parametresi, rotalarda dil belirtmek için kullanılır.

Ancak locale’i nasıl belirleyeceğiniz tamamen size kalmış:

```php
// src/EventSubscriber/LocaleSubscriber.php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private string $defaultLocale = 'en',
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->hasPreviousSession()) {
            return;
        }

        // Rota parametresi olarak _locale belirtilmişse kaydet
        if ($locale = $request->attributes->get('_locale')) {
            $request->getSession()->set('_locale', $locale);
        } else {
            // Aksi halde oturumdaki değeri kullan
            $request->setLocale(
                $request->getSession()->get('_locale', $this->defaultLocale)
            );
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // Varsayılan Locale listener’dan önce çalışmalı (yüksek öncelik)
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}
```

Varsayılan `services.yaml` yapılandırmasını kullanıyorsanız, Symfony bu aboneliği otomatik olarak algılar

ve her istek öncesinde `onKernelRequest()` metodunu çağırır.

Locale’in oturumda kalıcı hale geldiğini görmek için:

* `_locale` değerini oturumda manuel olarak ayarlayın (örneğin, bir “Dili Değiştir” rotası ile),
* veya `_locale` parametresini varsayılan olarak belirten bir rota tanımlayın.

---

### 📦 Subscriber’ı Manuel Olarak Yapılandırmak

Varsayılan dili (`default_locale`) dışarıdan parametre olarak geçirmek istiyorsanız:

```php
// config/services.php
use App\EventSubscriber\LocaleSubscriber;

$container->register(LocaleSubscriber::class)
    ->addArgument('%kernel.default_locale%')
    // Autoconfigure kullanmıyorsanız aşağıdaki satırı açın:
    // ->addTag('kernel.event_subscriber')
;
```

---

### 🌐 Kullanıcının Locale Değerine Erişim

```php
// bir controller içinde
use Symfony\Component\HttpFoundation\Request;

public function index(Request $request): void
{
    $locale = $request->getLocale();
}
```

---

## 👤 Kullanıcının Tercihlerine Göre Locale Ayarlamak

Bazı durumlarda locale’i oturumdan değil, **kullanıcının profilinden** almak isteyebilirsiniz.

Ancak `LocaleSubscriber`, `FirewallListener`’dan **önce** çalıştığı için o aşamada kullanıcı henüz kimlik doğrulamasından geçmemiş olur.

Eğer `User` varlığınızda (`User` entity) bir `locale` alanı varsa,

bunu girişten hemen sonra oturuma yazabilirsiniz.

---

### 🔐 LoginSuccessEvent Kullanarak Locale Saklamak

```php
// src/EventSubscriber/UserLocaleSubscriber.php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/**
 * Kullanıcı giriş yaptıktan sonra locale bilgisini oturuma kaydeder.
 * Bu bilgi daha sonra LocaleSubscriber tarafından kullanılabilir.
 */
class UserLocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (null !== $user->getLocale()) {
            $this->requestStack->getSession()->set('_locale', $user->getLocale());
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }
}
```

> 💡 Kullanıcı dil tercihlerini değiştirdikten hemen sonra etkili olması için
>
> `User` nesnesi güncellendiğinde oturumu da aynı şekilde güncelleyin.

---

## 🧰 Session Proxy Mekanizması

Symfony’nin **Session Proxy** sistemi, özel oturum davranışlarını uygulamak için kullanılır.

Örneğin:

* Oturum verilerini şifrelemek,
* Yalnızca “read-only” (salt okunur) misafir oturumları oluşturmak gibi.

Bu mekanizma için, `SessionHandlerProxy` sınıfını genişleten özel bir sınıf tanımlayabilirsiniz.

Sonra servisi kaydedip `framework.session.handler_id` üzerinden Symfony’ye tanıtmanız yeterlidir:

```php
// config/packages/framework.php
use App\Session\CustomSessionHandler;
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->session()
        ->handlerId(CustomSessionHandler::class);
};
```

---

## 🔒 Oturum Verilerini Şifrelemek

Aşağıdaki örnek, [php-encryption](https://github.com/defuse/php-encryption) kütüphanesini kullanır

(ancak başka bir şifreleme kütüphanesi de tercih edebilirsiniz):

```php
// src/Session/EncryptedSessionProxy.php
namespace App\Session;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Symfony\Component\HttpFoundation\Session\Storage\Proxy\SessionHandlerProxy;

class EncryptedSessionProxy extends SessionHandlerProxy
{
    public function __construct(
        private \SessionHandlerInterface $handler,
        private Key $key
    ) {
        parent::__construct($handler);
    }

    public function read($id): string
    {
        $data = parent::read($id);
        return Crypto::decrypt($data, $this->key);
    }

    public function write($id, $data): string
    {
        $data = Crypto::encrypt($data, $this->key);
        return parent::write($id, $data);
    }
}
```

---

### 🔐 Alternatif: SodiumMarshaller ile Şifreleme

Symfony’nin `session.marshaller` servisini süsleyerek (decorate) şifreleme uygulayabilirsiniz.

Önce güvenli bir anahtar üretin:

```bash
php -r 'echo base64_encode(sodium_crypto_box_keypair());'
```

Sonra bu anahtarı gizli dosyalarınıza `SESSION_DECRYPTION_FILE` olarak ekleyin

ve `SodiumMarshaller` servisini kaydedin:

```php
// config/services.php
use Symfony\Component\Cache\Marshaller\SodiumMarshaller;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function(ContainerConfigurator $container) {
    $services = $container->services();

    $services->set(SodiumMarshaller::class)
        ->decorate('session.marshaller')
        ->args([
            [env('file:resolve:SESSION_DECRYPTION_FILE')],
            service('.inner'),
        ]);
};
```

> ⚠️ Bu yöntem yalnızca **oturum değerlerini** şifreler, **anahtar isimlerini** değil.
>
> Anahtarlarda hassas bilgi bulundurmayın.

---

## 👥 Salt Okunur Misafir Oturumları (Read-Only Guest Sessions)

Bazı uygulamalarda, oturumun sadece **misafir kullanıcılar** için oluşturulması gerekir.

Ancak bu oturumların diske yazılmasına gerek yoktur.

Bu durumda `write()` metodunu engelleyebilirsiniz:

```php
// src/Session/ReadOnlySessionProxy.php
namespace App\Session;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Session\Storage\Proxy\SessionHandlerProxy;

class ReadOnlySessionProxy extends SessionHandlerProxy
{
    public function __construct(
        private \SessionHandlerInterface $handler,
        private Security $security
    ) {
        parent::__construct($handler);
    }

    public function write($id, $data): string
    {
        if ($this->getUser() && $this->getUser()->isGuest()) {
            return; // Misafir kullanıcılar için yazma işlemini atla
        }

        return parent::write($id, $data);
    }

    private function getUser(): ?User
    {
        $user = $this->security->getUser();
        return is_object($user) ? $user : null;
    }
}
```

---

## 🧱 Legacy (Miras) Uygulamalarla Entegrasyon

Eğer eski bir uygulama (`session_start()` kullanan) içerisine Symfony framework entegre ediyorsanız,

hala Symfony’nin oturum yönetimini kullanabilirsiniz.

### 1️⃣ PHP Bridge Storage ile

```php
// config/packages/framework.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->session()
        ->storageFactoryId('session.storage.factory.php_bridge')
        ->handlerId(null);
};
```

### 2️⃣ Symfony Handler ile `session_start()` Kullanımında

```php
// config/packages/framework.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->session()
        ->storageFactoryId('session.storage.factory.php_bridge')
        ->handlerId('session.handler.native_file');
};
```

> ⚠️ Eğer miras uygulama kendi oturum yöneticisini kullanıyorsa, `handler_id: ~` tanımlayın.
>
> Oturum başlatıldıktan sonra handler değiştirilemez, bu nedenle Symfony başlatılmadan önce
>
> `session_start()` çağrılıyorsa, mevcut handler korunmalıdır.

---
