### ⚡ Symfony Cache (Önbellek) Bileşeni

Bir **cache (önbellek)** kullanmak, uygulamanızın performansını ciddi biçimde artırır.

Symfony’nin **Cache** bileşeni, farklı depolama sistemleriyle çalışabilen **yüksek performanslı adaptörler** (adapters) ile birlikte gelir.

---

## 🚀 Temel Kullanım

Aşağıdaki örnek, cache bileşeninin tipik kullanımını göstermektedir:

```php
use Symfony\Contracts\Cache\ItemInterface;

// Callable yalnızca cache miss (önbellekte veri yoksa) durumunda çalışır.
$value = $pool->get('my_cache_key', function (ItemInterface $item): string {
    $item->expiresAfter(3600); // 1 saat geçerli olacak

    // ... uzun süren bir HTTP isteği veya hesaplama
    $computedValue = 'foobar';

    return $computedValue;
});

echo $value; // 'foobar'

// Cache anahtarını silmek için:
$pool->delete('my_cache_key');
```

Symfony, **Cache Contracts** (Symfony\Contracts\Cache) ve **PSR-6/PSR-16** arayüzlerini destekler.

Detaylar için [Cache Component documentation](https://symfony.com/doc/current/components/cache.html) sayfasına bakabilirsiniz.

---

## ⚙️ FrameworkBundle ile Cache Yapılandırması

Cache bileşeni yapılandırılırken üç temel kavram vardır:

| Kavram                            | Açıklama                                                                                                                        |
| --------------------------------- | --------------------------------------------------------------------------------------------------------------------------------- |
| **Pool (Havuz)**            | Cache ile etkileşime geçtiğiniz servistir. Her pool’un kendine ait bir namespace’i vardır, anahtar çakışması yaşanmaz. |
| **Adapter (Adaptör)**      | Pool oluşturmak için kullanılan şablondur (örneğin filesystem, redis vb.).                                                  |
| **Provider (Sağlayıcı)** | Bazı adaptörlerin depolama sistemine bağlanmak için kullandığı servistir. Redis veya Memcached buna örnektir.             |

---

### 🔸 Varsayılan Havuzlar

Symfony, iki varsayılan cache havuzunu otomatik olarak etkinleştirir:

* `cache.app` → uygulama önbelleği (kendi kodunuzda kullanabilirsiniz)
* `cache.system` → sistem önbelleği (örneğin annotation, serializer, validation gibi dahili işlemler)

Bu havuzlar için hangi adaptörlerin kullanılacağını belirleyebilirsiniz:

```php
// config/packages/cache.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->cache()
        ->app('cache.adapter.filesystem')
        ->system('cache.adapter.system');
};
```

> ⚠️ `cache.system` yapılandırmasını değiştirmek mümkündür,
>
> ancak genellikle **Symfony’nin varsayılan ayarlarını korumanız** önerilir.

---

## 🧱 Hazır Adaptörler

Symfony aşağıdaki önceden tanımlı adaptörleri içerir:

| Adaptör                          | Açıklama                                                             |
| --------------------------------- | ---------------------------------------------------------------------- |
| `cache.adapter.apcu`            | PHP APCu uzantısını kullanır (çok hızlı).                       |
| `cache.adapter.array`           | Sadece bellekte (RAM) çalışır, geçicidir.                         |
| `cache.adapter.doctrine_dbal`   | Doctrine DBAL üzerinden veritabanı bağlantısı.                    |
| `cache.adapter.filesystem`      | Dosya sistemi tabanlı önbellek.                                      |
| `cache.adapter.memcached`       | Memcached tabanlı önbellek.                                          |
| `cache.adapter.pdo`             | PDO tabanlı (SQL) önbellek.                                          |
| `cache.adapter.psr6`            | PSR-6 standardına uygun bir cache adaptörü.                         |
| `cache.adapter.redis`           | Redis tabanlı önbellek.                                              |
| `cache.adapter.redis_tag_aware` | Etiketlerle çalışmak için optimize edilmiş Redis adaptörü.      |
| `cache.adapter.system`          | Sistem için otomatik olarak en uygun depoyu seçer (APCu veya dosya). |

---

### 🔧 Kısa Yol Tanımlamaları

Bazı adaptörlerde provider’ları kısa yollarla ayarlayabilirsiniz:

```php
// config/packages/cache.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->cache()
        ->directory('%kernel.cache_dir%/pools') // filesystem için

        ->defaultDoctrineDbalProvider('doctrine.dbal.default_connection')
        ->defaultPsr6Provider('app.my_psr6_service')
        ->defaultRedisProvider('redis://localhost')
        ->defaultMemcachedProvider('memcached://localhost')
        ->defaultPdoProvider('pgsql:host=localhost');
};
```

> 🆕 Symfony 7.1 ile birlikte `PDO` adaptöründe DSN kullanımı desteklenmiştir.

---

## 🧩 Özel (Namespaced) Cache Pool’ları Oluşturmak

Kendi cache havuzlarınızı oluşturabilir ve farklı adaptörlerle özelleştirebilirsiniz:

```php
// config/packages/cache.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $cache = $framework->cache();
    $cache->defaultMemcachedProvider('memcached://localhost');

    // custom_thing.cache → CacheInterface $customThingCache ile otomatik bağlanır
    $cache->pool('custom_thing.cache')
        ->adapters(['cache.app']);

    // my_cache_pool → CacheInterface $myCachePool ile bağlanır
    $cache->pool('my_cache_pool')
        ->adapters(['cache.adapter.filesystem']);

    // Yukarıda tanımlanan memcached provider'ı kullanır
    $cache->pool('acme.cache')
        ->adapters(['cache.adapter.memcached']);

    // Provider bağlantısını manuel olarak belirtme
    $cache->pool('foobar.cache')
        ->adapters(['cache.adapter.memcached'])
        ->provider('memcached://user:password@example.com');

    // 60 saniyelik ömürle kısa süreli önbellek
    $cache->pool('short_cache')
        ->adapters(['foobar.cache'])
        ->defaultLifetime(60);
};
```

> 🔍 Her pool, kendi namespace’ine sahiptir.
>
> Aynı backend’i kullansalar bile anahtar çakışması yaşanmaz.
>
> Namespace, pool adı + adaptör sınıfı + proje dizinine göre hashlenir.

---

### 🧠 Otomatik Servis Oluşturma

Her custom pool bir servis haline gelir:

* Servis ID’si: `custom_thing.cache`
* Otomatik alias: `$customThingCache`

Controller veya servislerde doğrudan kullanabilirsiniz:

```php
use Symfony\Contracts\Cache\CacheInterface;

public function listProducts(CacheInterface $customThingCache)
{
    // ...
}
```

Veya constructor ile:

```php
public function __construct(private CacheInterface $customThingCache) {}
```

---

## 🔤 Namespace’i Manuel Belirleme

Eğer üçüncü taraf bir uygulama ile uyumlu namespace kullanmak istiyorsanız,

servis tanımında `cache.pool` etiketi ile belirtebilirsiniz:

```php
// config/services.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function(ContainerConfigurator $container): void {
    $container->services()
        ->set('app.cache.adapter.redis')
            ->parent('cache.adapter.redis')
            ->tag('cache.pool', ['namespace' => 'my_custom_namespace']);
};
```

---

## ⚙️ Özel Provider Ayarları

Bazı provider’lar özel bağlantı seçenekleri sunar.

Örneğin, **Redis** adapter’ında `timeout` veya `retry_interval` gibi değerleri değiştirmek için

kendi provider servisinizi oluşturabilirsiniz:

```php
// config/packages/cache.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Config\FrameworkConfig;

return static function (ContainerBuilder $container, FrameworkConfig $framework): void {
    $framework->cache()
        ->pool('cache.my_redis')
            ->adapters(['cache.adapter.redis'])
            ->provider('app.my_custom_redis_provider');

    $container->register('app.my_custom_redis_provider', \Redis::class)
        ->setFactory([RedisAdapter::class, 'createConnection'])
        ->addArgument('redis://localhost')
        ->addArgument([
            'retry_interval' => 2,
            'timeout' => 10,
        ]);
};
```

---

## 🔗 Cache Chain (Zincirli Önbellek) Kullanımı

Farklı cache adaptörleri farklı güçlü yanlara sahiptir:

* **Array** → çok hızlı ama kalıcı değil
* **Redis** → kalıcı ve büyük veriler için uygun ama daha yavaş

Her iki avantajı birleştirmek için **cache zinciri (chain)** oluşturabilirsiniz.

### 📘 Çalışma Mantığı:

* Bir öğe kaydedildiğinde tüm adaptörlere sırayla yazılır.
* Bir öğe çağrıldığında, en hızlı adaptörden başlanarak aranır.
* Bulunamazsa sıradaki adaptör denenir.
* Bulunursa, eksik adaptörlere otomatik olarak geri yazılır.

```php
// config/packages/cache.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->cache()
        ->pool('my_cache_pool')
            ->defaultLifetime(31536000) // 1 yıl
            ->adapters([
                'cache.adapter.array', // en hızlı
                'cache.adapter.apcu',
                ['name' => 'cache.adapter.redis', 'provider' => 'redis://user:password@example.com'],
            ]);
};
```

> 💡 **Tavsiye:** Adaptörleri **hızlıdan yavaşa** doğru sıralayın.

> ❗ Eğer bir adaptör hata verirse, Symfony diğerlerine yazmaya devam eder ve hata fırlatmaz.

---

### 🏷️ Cache Etiketleri (Cache Tags) Kullanımı

Bir uygulamada çok sayıda cache anahtarı (key) varsa, verileri **etiketleyerek (tag)** gruplamak,

önbelleği daha verimli temizlemenizi (invalidate) sağlar.

Bir veya birden fazla  **etiket** , bir cache öğesine eklenebilir.

Aynı etikete sahip tüm öğeler tek bir fonksiyon çağrısıyla temizlenebilir.

---

## 🚀 Temel Kullanım

```php
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class SomeClass
{
    // Otomatik bağımlılık enjeksiyonu (autowiring) ile cache havuzu eklenir
    public function __construct(
        private TagAwareCacheInterface $myCachePool,
    ) {
    }

    public function someMethod(): void
    {
        $value0 = $this->myCachePool->get('item_0', function (ItemInterface $item): string {
            $item->tag(['foo', 'bar']);
            return 'debug';
        });

        $value1 = $this->myCachePool->get('item_1', function (ItemInterface $item): string {
            $item->tag('foo');
            return 'debug';
        });

        // “bar” etiketiyle işaretlenmiş tüm cache öğelerini sil
        $this->myCachePool->invalidateTags(['bar']);
    }
}
```

---

## ⚙️ Etiket Desteğini Etkinleştirmek

Cache adaptörünüzün `TagAwareCacheInterface` arayüzünü uygulaması gerekir.

Bu desteği aşağıdaki şekilde etkinleştirebilirsiniz:

```php
// config/packages/cache.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->cache()
        ->pool('my_cache_pool')
            ->tags(true)
            ->adapters(['cache.adapter.redis_tag_aware']);
};
```

> 💡 `cache.adapter.redis_tag_aware`, Redis üzerinde etiketleri destekleyen özel bir adaptördür.

---

## 🧩 Etiketleri Farklı Bir Havuzda Saklamak

Varsayılan olarak etiketler, cache öğeleriyle aynı havuzda saklanır.

Bu çoğu senaryoda uygundur, ancak bazı durumlarda **etiketleri ayrı bir havuzda** tutmak daha verimli olabilir:

```php
// config/packages/cache.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    // Asıl cache havuzu
    $framework->cache()
        ->pool('my_cache_pool')
            ->tags('tag_pool')
            ->adapters(['cache.adapter.redis']);

    // Etiketlerin saklanacağı havuz
    $framework->cache()
        ->pool('tag_pool')
            ->adapters(['cache.adapter.apcu']);
};
```

> 🔗 `TagAwareCacheInterface` arayüzü, Symfony tarafından otomatik olarak `cache.app` servisine bağlanır.

---

## 🧹 Cache Temizleme (Clearing the Cache)

Cache’i temizlemek için aşağıdaki komutları kullanabilirsiniz:

### 🔸 Tüm cache havuzlarını listeleme

```bash
php bin/console cache:pool:list
```

### 🔸 Belirli bir havuzu temizleme

```bash
php bin/console cache:pool:clear my_cache_pool
```

### 🔸 Tüm özel (custom) havuzları temizleme

```bash
php bin/console cache:pool:clear cache.app_clearer
```

### 🔸 Tüm havuzları temizleme

```bash
php bin/console cache:pool:clear --all
```

### 🔸 Bazı havuzlar hariç tümünü temizleme

```bash
php bin/console cache:pool:clear --all --exclude=my_cache_pool --exclude=another_cache_pool
```

### 🔸 Sistemdeki tüm cache’leri temizleme

```bash
php bin/console cache:pool:clear cache.global_clearer
```

### 🔸 Etiket(ler)e göre temizleme

```bash
php bin/console cache:pool:invalidate-tags tag1
php bin/console cache:pool:invalidate-tags tag1 tag2
php bin/console cache:pool:invalidate-tags tag1 tag2 --pool=cache.app
php bin/console cache:pool:invalidate-tags tag1 tag2 -p cache1 -p cache2
```

---

### 🔧 Varsayılan Cache Clearer Servisleri

| Servis                   | Açıklama                                                       |
| ------------------------ | ---------------------------------------------------------------- |
| `cache.global_clearer` | Tüm havuzlardaki tüm öğeleri temizler.                       |
| `cache.system_clearer` | `bin/console cache:clear`komutunda sistem cache’ini temizler. |
| `cache.app_clearer`    | Uygulama cache’lerini temizler (varsayılan).                   |

---

## 🔐 Cache Şifreleme (Encrypting the Cache)

Cache verilerini **libsodium** kütüphanesiyle şifrelemek için `SodiumMarshaller` kullanılabilir.

---

### 1️⃣ Güvenli Anahtar Üretin

```bash
php -r 'echo base64_encode(sodium_crypto_box_keypair());'
```

Bu anahtarı gizli yapılandırmanıza (ör. `.env` veya Secret Store)

`CACHE_DECRYPTION_KEY` olarak ekleyin.

---

### 2️⃣ SodiumMarshaller Servisini Kaydedin

```php
// config/packages/cache.php
use Symfony\Component\Cache\Marshaller\SodiumMarshaller;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Reference;

// ...

$container->setDefinition(SodiumMarshaller::class, new ChildDefinition('cache.default_marshaller'))
    ->addArgument(['env(base64:CACHE_DECRYPTION_KEY)'])
    // Birden fazla anahtar belirterek rotasyon yapabilirsiniz
    // ->addArgument(['env(base64:CACHE_DECRYPTION_KEY)', 'env(base64:OLD_CACHE_DECRYPTION_KEY)'])
    ->addArgument(new Reference('.inner'));
```

> ⚠️ Bu yöntem **cache değerlerini** şifreler, **cache anahtarlarını (keys)** şifrelemez.
>
> Anahtarlarda hassas bilgi bulundurmayın!

---

### 🔁 Anahtar Döndürme (Key Rotation)

Birden fazla anahtar tanımladıysanız:

* İlk anahtar okuma ve yazma için kullanılır.
* Ek anahtar(lar) sadece okuma için kullanılır.

Tüm cache öğeleri eski anahtarla şifrelenmiş verilerle dolduğunda

`OLD_CACHE_DECRYPTION_KEY` kaldırılabilir.

---

🧠 **Özet:**

* Cache etiketleri, ilgili verileri grup halinde temizlemenizi sağlar.
* Redis veya APCu adaptörleriyle desteklenir.
* Symfony CLI üzerinden havuz veya etiket bazlı temizleme yapılabilir.
* `SodiumMarshaller` ile önbellek verileri güvenli şekilde şifrelenebilir.

---

### ⚙️ Cache Değerlerini Asenkron (Eşzamansız) Olarak Hesaplamak

Symfony’nin Cache bileşeni, **cache stampede** (önbellek çökmesi) problemini önlemek için

**olasılıksal erken sona erme (probabilistic early expiration)** algoritmasını kullanır.

Bu sayede bazı cache öğeleri, henüz geçerlilik süresi dolmadan **erken yenileme (early expiration)** için seçilir.

---

## 🧠 Varsayılan Davranış: Senkron Yenileme

Varsayılan olarak, süresi dolmuş (expired) cache değerleri **senkron şekilde** hesaplanır —

yani kullanıcı isteği geldiğinde değer yeniden üretilir ve yanıt, bu işlem tamamlanınca döner.

Ancak, bu işlem zaman alabilir. Bu nedenle Symfony, **asenkron cache yenileme** özelliği sunar.

---

## 🚀 Asenkron Yenileme: Messenger ile Arka Plan İşleme

Asenkron çalışmada, cache öğesinin değeri **arka plandaki bir worker tarafından** hesaplanır.

* Öğe sorgulandığında mevcut (eski) değer  **hemen döndürülür** .
* Aynı anda bir **`EarlyExpirationMessage`** mesajı **Messenger** bileşenine gönderilir.
* Worker bu mesajı işleyerek değeri arka planda yeniler.
* Sonraki isteklerde cache artık tazelenmiş (fresh) değeri döner.

---

## 🧩 1️⃣ Cache Değerini Hesaplayan Servisi Oluşturun

```php
// src/Cache/CacheComputation.php
namespace App\Cache;

use Symfony\Contracts\Cache\ItemInterface;

class CacheComputation
{
    public function compute(ItemInterface $item): string
    {
        // Bu örnekte cache 5 saniye geçerli
        $item->expiresAfter(5);

        // Burada kendi ağır işleminizi yapabilirsiniz (örnek: API çağrısı, veri hesaplama vs.)
        return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }
}
```

---

## 🧠 2️⃣ Cache Değerini Kullanan Controller

Aşağıdaki örnekte cache değeri bir denetleyici (controller) tarafından isteniyor:

```php
// src/Controller/CacheController.php
namespace App\Controller;

use App\Cache\CacheComputation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;

class CacheController extends AbstractController
{
    #[Route('/cache', name: 'cache')]
    public function index(CacheInterface $asyncCache): Response
    {
        // Cache öğesini getir — arka planda CacheComputation::compute ile yenilenecek
        $cachedValue = $asyncCache->get('my_value', [CacheComputation::class, 'compute']);

        return new Response("Cache value: $cachedValue");
    }
}
```

---

## ⚙️ 3️⃣ Asenkron Cache Pool’unu Yapılandırma

Yeni bir cache havuzu tanımlayın (örneğin `async.cache`),

ve bu havuzun değerleri **Messenger bus** üzerinden arka planda hesaplanacak şekilde yapılandırın:

```php
// config/packages/framework.php
use Symfony\Component\Cache\Messenger\EarlyExpirationMessage;
use Symfony\Config\FrameworkConfig;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

return static function (FrameworkConfig $framework): void {
    // Yeni cache havuzu
    $framework->cache()
        ->pool('async.cache')
            // Bu havuzun erken sona eren öğeleri Messenger üzerinden işlensin
            ->earlyExpirationMessageBus('messenger.default_bus');

    // Messenger yapılandırması
    $framework->messenger()
        ->transport('async_bus')
            ->dsn(env('MESSENGER_TRANSPORT_DSN'))
        ->routing(EarlyExpirationMessage::class)
            ->senders(['async_bus']);
};
```

> 💡 `MESSENGER_TRANSPORT_DSN` değerini `.env` dosyanıza eklemeyi unutmayın,
>
> örneğin:
>
> `MESSENGER_TRANSPORT_DSN=doctrine://default` veya `redis://localhost`

---

## ⚙️ 4️⃣ Worker’ı Başlatma

Artık mesajları işleyen tüketiciyi (consumer) başlatabilirsiniz:

```bash
php bin/console messenger:consume async_bus
```

---

## ✅ Artık Ne Olacak?

* Cache öğesi istendiğinde Symfony  **mevcut cache değerini hemen döndürür** .
* Eğer öğe **erken yenileme** için seçilmişse, bir `EarlyExpirationMessage` mesajı oluşturulur.
* Bu mesaj, tanımladığınız **Messenger transport’una** gönderilir.
* Worker (consumer) bu mesajı işler ve değeri  **arka planda yeniden hesaplar** .
* Sonraki istekte güncellenmiş (fresh) değer döner.

---

### 📘 Özet

| Adım | Açıklama                                                                        |
| ----- | --------------------------------------------------------------------------------- |
| 1️⃣ | `CacheComputation`sınıfı ile değeri hesaplayın.                            |
| 2️⃣ | `CacheController`içinde `CacheInterface`kullanarak değeri çağırın.      |
| 3️⃣ | `async.cache`pool’unu ve `Messenger`yapılandırmasını ekleyin.            |
| 4️⃣ | `messenger:consume`komutunu çalıştırarak arka plan işlemcisini başlatın. |

---
