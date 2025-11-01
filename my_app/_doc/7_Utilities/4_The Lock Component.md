# 🔒 Lock Bileşeni

Lock Bileşeni, paylaşılan bir kaynağa özel erişim sağlamak için kilitler oluşturan ve yöneten bir mekanizma sağlar.

Symfony Framework kullanıyorsanız, Symfony Framework Lock dokümantasyonunu okuyun.

## ⚙️ Kurulum

```bash
composer require symfony/lock
```

Symfony uygulaması dışında bu bileşeni kurarsanız, Composer tarafından sağlanan sınıf otomatik yükleme mekanizmasını etkinleştirmek için kodunuzda `vendor/autoload.php` dosyasını dahil etmeniz gerekir. Daha fazla ayrıntı için bu makaleyi okuyun.

## 🧩 Kullanım

Kilitler, paylaşılan bir kaynağa özel erişimi garanti etmek için kullanılır. Symfony uygulamalarında, örneğin bir komutun aynı anda birden fazla kez (aynı veya farklı sunucularda) çalıştırılmadığından emin olmak için kilitleri kullanabilirsiniz.

Kilitler, bir `LockFactory` sınıfı kullanılarak oluşturulur. Bu sınıfın, kilitlerin depolanmasını yöneten başka bir sınıfa ihtiyacı vardır:

```php
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\SemaphoreStore;

$store = new SemaphoreStore();
$factory = new LockFactory($store);
```

Kilit, `createLock()` metodu çağrılarak oluşturulur. İlk parametre, kilitlenen kaynağı temsil eden rastgele bir dizedir. Ardından `acquire()` metodu çağrılarak kilit alınmaya çalışılır:

```php
// ...
$lock = $factory->createLock('pdf-creation');

if ($lock->acquire()) {
    // "pdf-creation" kaynağı kilitlendi.
    // Faturayı burada güvenle oluşturabilirsiniz.

    $lock->release();
}
```

Kilit alınamazsa, metod `false` döndürür. `acquire()` metodu güvenle tekrarlanabilir, kilit zaten alınmış olsa bile çağrılabilir.

Diğer uygulamalardan farklı olarak, Lock Bileşeni aynı kaynak için oluşturulan kilit örneklerini ayırt eder. Bu, belirli bir kapsam ve kaynak için bir kilit örneğinin birden fazla kez alınabileceği anlamına gelir. Eğer bir kilit birden fazla servis tarafından kullanılacaksa, bu servislerin `LockFactory::createLock` metodunun döndürdüğü aynı Lock örneğini paylaşmaları gerekir.

Kilit açıkça serbest bırakılmazsa, örnek yok edildiğinde otomatik olarak serbest bırakılır. Bazı durumlarda, bir kaynağı birden fazla istek boyunca kilitlemek isteyebilirsiniz. Otomatik serbest bırakma davranışını devre dışı bırakmak için `createLock()` metodunun üçüncü parametresini `false` olarak ayarlayın.

## 🧱 Kilitlerin Serileştirilmesi

Key, Lock’un durumunu içerir ve serileştirilebilir. Bu, kullanıcının bir süreçte kilidi alarak uzun bir işe başlamasına ve aynı kilidi kullanarak başka bir süreçte işe devam etmesine olanak tanır.

Öncelikle, kaynağı ve kilidin anahtarını içeren serileştirilebilir bir sınıf oluşturabilirsiniz:

```php
// src/Lock/RefreshTaxonomy.php
namespace App\Lock;

use Symfony\Component\Lock\Key;

class RefreshTaxonomy
{
    public function __construct(
        private object $article,
        private Key $key,
    ) {
    }

    public function getArticle(): object
    {
        return $this->article;
    }

    public function getKey(): Key
    {
        return $this->key;
    }
}
```

Daha sonra, işin geri kalanını başka bir sürecin yürütmesi için gerekli olan her şeyi göndermek üzere bu sınıfı kullanabilirsiniz:

```php
use App\Lock\RefreshTaxonomy;
use Symfony\Component\Lock\Key;

$key = new Key('article.'.$article->getId());
$lock = $factory->createLockFromKey(
    $key,
    300,  // ttl
    false // autoRelease
);
$lock->acquire(true);

$this->bus->dispatch(new RefreshTaxonomy($article, $key));
```

Yıkıcı çağrıldığında kilidin serbest bırakılmasını önlemek için `autoRelease` parametresini `false` olarak ayarlamayı unutmayın.

Tüm store’lar serileştirme ve süreçler arası kilitleme ile uyumlu değildir: örneğin, `SemaphoreStore` tarafından alınan semaforlar çekirdek tarafından otomatik olarak serbest bırakılır. Uyumsuz bir store kullanırsanız (desteklenen store’lar için kilit depolarına bakın), uygulama anahtarı serileştirmeye çalıştığında bir istisna atılacaktır.

## ⏳ Engelleyen (Blocking) Kilitler

Varsayılan olarak, bir kilit alınamadığında `acquire` metodu hemen `false` döndürür. Kilit oluşturulana kadar (süresiz olarak) beklemek için, `acquire()` metoduna `true` argümanını geçin. Buna “blocking lock” denir çünkü uygulamanızın yürütülmesi kilit alınana kadar durur:

```php
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;

$store = new FlockStore('/var/stores');
$factory = new LockFactory($store);

$lock = $factory->createLock('pdf-creation');
$lock->acquire(true);
```

Store `BlockingStoreInterface` arayüzünü uygulayarak blocking lock’ları desteklemiyorsa (desteklenen store’lar için kilit depolarına bakın), Lock sınıfı kilidi alıncaya kadar non-blocking şekilde denemeye devam eder.

## ⏰ Süresi Dolan (Expiring) Kilitler

Uzakta oluşturulan kilitlerin yönetimi zordur çünkü uzak `Store`, kilidi alan işlemin hâlâ çalışıp çalışmadığını bilemez. Hatalar, çökmeler veya segmentasyon hataları nedeniyle `release()` metodunun çağrılacağı garanti edilemez, bu da kaynağın sonsuza kadar kilitli kalmasına neden olabilir.

Bu durumlarda en iyi çözüm, belirli bir süre sonra (TTL — Time To Live) otomatik olarak serbest bırakılan **expiring locks** oluşturmaktır. Bu süre, saniye cinsinden, `createLock()` metodunun ikinci parametresi olarak ayarlanır. Gerekirse, bu kilitler `release()` metodu ile erken serbest bırakılabilir.

Expiring lock’larla çalışırken en zor kısım doğru TTL değerini seçmektir. Çok kısa olursa, diğer süreçler iş bitmeden kilidi alabilir; çok uzun olursa ve işlem çökmeden önce `release()` çağrılmazsa, kaynak zaman aşımına kadar kilitli kalır:

```php
// ...
// 30 saniye süren (varsayılan 300.0) bir expiring lock oluştur
$lock = $factory->createLock('pdf-creation', ttl: 30);

if (!$lock->acquire()) {
    return;
}
try {
    // 30 saniyeden kısa süren bir iş yap
} finally {
    $lock->release();
}
```

Kilitin kilitli durumda kalmasını önlemek için işi her zaman `try/catch/finally` bloğu içine almanız önerilir.

Uzun süreli görevlerde, çok uzun olmayan bir TTL ile başlamak ve ardından `refresh()` metodunu kullanarak TTL’yi orijinal değerine sıfırlamak daha iyidir:

```php
// ...
$lock = $factory->createLock('pdf-creation', ttl: 30);

if (!$lock->acquire()) {
    return;
}
try {
    while (!$finished) {
        // İşin küçük bir kısmını gerçekleştir.

        // Kilidi 30 saniye daha yenile.
        $lock->refresh();
    }
} finally {
    $lock->release();
}
```

Uzun süreli görevler için başka yararlı bir teknik, varsayılan kilit TTL’sini değiştirmek amacıyla `refresh()` metoduna özel bir TTL parametresi geçirmektir:

```php
$lock = $factory->createLock('pdf-creation', ttl: 30);
// ...
// Kilidi 30 saniye yenile
$lock->refresh();
// ...
// Kilidi 600 saniye yenile (bir sonraki refresh() çağrısı tekrar 30 saniye olacaktır)
$lock->refresh(600);
```

Bu bileşen ayrıca expiring lock’larla ilgili iki yararlı metod sağlar:

`getRemainingLifetime()` (null veya saniye cinsinden float döndürür) ve

`isExpired()` (boolean döndürür).

# 🔄 Kilidin Otomatik Olarak Serbest Bırakılması

Kilitler, `Lock` nesneleri yok edildiğinde otomatik olarak serbest bırakılır. Bu, kilitlerin süreçler arasında paylaşıldığı durumlarda önemli olan bir uygulama detayıdır. Aşağıdaki örnekte, `pcntl_fork()` iki süreç oluşturur ve süreçlerden biri bittiğinde kilit otomatik olarak serbest bırakılır:

```php
// ...
$lock = $factory->createLock('pdf-creation');
if (!$lock->acquire()) {
    return;
}

$pid = pcntl_fork();
if (-1 === $pid) {
    // Çatallanma başarısız
    exit(1);
} elseif ($pid) {
    // Ana süreç
    sleep(30);
} else {
    // Alt süreç
    echo 'Kilit şimdi serbest bırakılacak.';
    exit(0);
}
// ...
```

Yukarıdaki örneğin çalışması için **PCNTL** uzantısının yüklü olması gerekir.

Bu davranışı devre dışı bırakmak için, `LockFactory::createLock()` metodunun `autoRelease` parametresini `false` olarak ayarlayın. Bu durumda kilit 3600 saniye boyunca veya `Lock::release()` çağrılana kadar tutulur:

```php
$lock = $factory->createLock(
    'pdf-creation',
    3600, // ttl
    false // autoRelease
);
```

---

# 🤝 Paylaşımlı Kilitler (Shared Locks)

Paylaşımlı veya **readers-writer lock** , yalnızca okuma işlemleri için eşzamanlı erişime izin verirken, yazma işlemleri için özel erişim gerektiren bir senkronizasyon ilkelidir. Bu, birden fazla iş parçacığının veriyi paralel olarak okuyabileceği ancak veriyi güncellemek veya değiştirmek için özel bir kilidin gerekli olduğu anlamına gelir. Bu tür kilitler genellikle atomik olarak güncellenemeyen ve güncelleme tamamlanana kadar geçersiz olan veri yapıları için kullanılır.

Sadece okuma kilidi almak için `acquireRead()` metodunu, yazma kilidi almak için ise `acquire()` metodunu kullanın:

```php
$lock = $factory->createLock('user-'.$user->id);
if (!$lock->acquireRead()) {
    return;
}
```

`acquire()` metoduna benzer şekilde, kilidi engelleme modunda almak için `acquireRead()` metoduna `true` argümanını geçin:

```php
$lock = $factory->createLock('user-'.$user->id);
$lock->acquireRead(true);
```

Symfony’nin paylaşımlı kilitlerinin öncelik politikası, kullanılan store’a bağlıdır (örneğin Redis store okuyucuları yazarlara göre önceliklendirir).

Bir okuma kilidi `acquireRead()` metodu ile alındığında, bu kilidi yükselterek (promote) yazma kilidine dönüştürmek mümkündür. Bunun için `acquire()` metodunu çağırın:

```php
$lock = $factory->createLock('user-'.$userId);
$lock->acquireRead(true);

if (!$this->shouldUpdate($userId)) {
    return;
}

$lock->acquire(true); // Kilidi yazma kilidine yükselt
$this->update($userId);
```

Aynı şekilde, bir yazma kilidi de `acquireRead()` metodunu çağırarak okuma kilidine düşürülebilir (demote).

Sağlanan store `SharedLockStoreInterface` arayüzünü uygulamıyorsa (desteklenen store’lar için kilit depolarına bakın), `Lock` sınıfı `acquire()` metodunu çağırarak yazma kilidine geri döner.

---

# 👑 Kilidin Sahibi

Bir kilit ilk kez alındığında, o kilidi alan `Lock` örneği tarafından sahiplenilir. Mevcut `Lock` örneğinin (halen) kilidin sahibi olup olmadığını kontrol etmek için `isAcquired()` metodunu kullanabilirsiniz:

```php
if ($lock->isAcquired()) {
    // Hâlâ kilidin sahibiyiz
}
```

Bazı kilit depoları süresi dolan (expiring) kilitlere sahip olduğundan, bir örnek aldığı kilidi otomatik olarak kaybedebilir:

```php
// Eğer kendimiz kilidi alamıyorsak, başka bir süreç zaten çalışıyor demektir
if (!$lock->acquire()) {
    return;
}

$this->beginTransaction();

// TTL’yi aşabilecek kadar uzun süren bir işlem yap

if ($lock->isAcquired()) {
    // Hâlâ bizde, başka bir süreç araya girmedi, güvenli
    $this->commit();
} else {
    // Kilidimiz muhtemelen TTL’yi aştı ve başka bir süreç başladı,
    // bu yüzden güvenli değil.
    $this->rollback();
    throw new \Exception('Process failed');
}
```

Yaygın bir hata, bir kilidin **herhangi bir süreç** tarafından zaten alınmış olup olmadığını kontrol etmek için `isAcquired()` metodunu kullanmaktır. Bu yanlış bir kullanımdır — bu amaçla `acquire()` metodunu kullanmalısınız.

`isAcquired()` metodu yalnızca **mevcut sürecin** kilidin sahibi olup olmadığını kontrol eder.

Teknik olarak, kilidin gerçek sahipleri `Lock` değil, aynı `Key` örneğini paylaşanlardır. Ancak kullanıcı açısından `Key` dahili bir kavram olduğundan, genellikle yalnızca `Lock` örneğiyle çalışırsınız ve bu örneği kilidin sahibi olarak düşünmek daha kolaydır.

---

# 🗄️ Kullanılabilir Store Türleri

Kilitler, `PersistingStoreInterface` (ve isteğe bağlı olarak `BlockingStoreInterface`) arayüzlerini uygulayan **store** sınıflarında oluşturulur ve yönetilir.

Bileşen, aşağıdaki yerleşik store türlerini içerir:

| Store                           | Kapsam | Engelleme | Süresi Dolma | Paylaşım | Serileştirme |
| ------------------------------- | ------ | --------- | ------------ | -------- | ------------ |
| **FlockStore**                  | local  | yes       | no           | yes      | no           |
| **MemcachedStore**              | remote | no        | yes          | no       | yes          |
| **MongoDbStore**                | remote | no        | yes          | no       | yes          |
| **PdoStore**                    | remote | no        | yes          | no       | yes          |
| **DoctrineDbalStore**           | remote | no        | yes          | no       | yes          |
| **PostgreSqlStore**             | remote | yes       | no           | yes      | no           |
| **DoctrineDbalPostgreSqlStore** | remote | yes       | no           | yes      | no           |
| **RedisStore**                  | remote | no        | yes          | yes      | yes          |
| **SemaphoreStore**              | local  | yes       | no           | no       | no           |
| **ZookeeperStore**              | remote | no        | no           | no       | no           |

Symfony ayrıca test amaçlı iki özel store türü içerir:

-   **InMemoryStore** (`LOCK_DSN=in-memory`): Kilitleri bir işlem süresince bellekte saklar.
-   **NullStore** (`LOCK_DSN=null`): Hiçbir şeyi kalıcı hale getirmez.

> 🆕 **NullStore** , Symfony 7.2 sürümünde tanıtılmıştır.

---

## 📁 FlockStore

`FlockStore`, kilitleri yerel bilgisayardaki dosya sistemi üzerinde oluşturur. Süre dolmasını desteklemez, ancak kilit nesnesi kapsam dışına çıktığında ve çöp toplayıcı (garbage collector) tarafından serbest bırakıldığında kilit otomatik olarak kaldırılır (örneğin, PHP süreci sona erdiğinde):

```php
use Symfony\Component\Lock\Store\FlockStore;

// Argüman, kilitlerin oluşturulacağı dizinin yoludur.
// Eğer belirtilmezse, sys_get_temp_dir() dahili olarak kullanılır.
$store = new FlockStore('/var/stores');
```

Dikkat: Bazı dosya sistemleri (örneğin bazı NFS türleri) kilitlemeyi desteklemez. Bu durumlarda, yerel bir disk sürücüsünde veya uzak bir store’da bir dizin kullanmak daha iyidir.

---

## 💾 MemcachedStore

`MemcachedStore`, kilitleri bir **Memcached** sunucusunda saklar ve `\Memcached` sınıfını uygulayan bir bağlantı gerektirir. Bu store engellemeyi desteklemez ve donan kilitleri önlemek için bir TTL bekler:

```php
use Symfony\Component\Lock\Store\MemcachedStore;

$memcached = new \Memcached();
$memcached->addServer('localhost', 11211);

$store = new MemcachedStore($memcached);
```

> ⚠️ Memcached, 1 saniyeden kısa TTL değerlerini desteklemez.

---

## 🧬 MongoDbStore

`MongoDbStore`, MongoDB ≥ 2.2 sürümlerinde çalışır ve `mongodb/mongodb` paketinden bir `\MongoDB\Collection` veya `\MongoDB\Client` ya da bir MongoDB Connection String gerektirir. Engellemeyi desteklemez ve donan kilitleri önlemek için bir TTL bekler:

```php
use Symfony\Component\Lock\Store\MongoDbStore;

$mongo = 'mongodb://localhost/database?collection=lock';
$options = [
    'gcProbability' => 0.001,
    'database' => 'myapp',
    'collection' => 'lock',
    'uriOptions' => [],
    'driverOptions' => [],
];
$store = new MongoDbStore($mongo, $options);
```

### `$options` Parametreleri

| Seçenek           | Açıklama                                                                                                        |
| ----------------- | --------------------------------------------------------------------------------------------------------------- |
| **gcProbability** | TTL Index’in oluşturulup oluşturulmayacağını belirler; 0.0–1.0 arasında bir olasılık değeri (varsayılan: 0.001) |
| **database**      | Veritabanı adı                                                                                                  |
| **collection**    | Koleksiyon adı                                                                                                  |
| **uriOptions**    | `MongoDBClient::__construct`için URI seçenekleri dizisi                                                         |
| **driverOptions** | `MongoDBClient::__construct`için sürücü seçenekleri dizisi                                                      |

### İlk Parametrenin Türüne Göre Davranış

-   **MongoDB\Collection** :

    `options['database']` ve `options['collection']` yok sayılır.

-   **MongoDB\Client** :

    `options['database']` ve `options['collection']` zorunludur.

-   **MongoDB Connection String** :

    `options['database']` yoksa DSN’in `/path` kısmı kullanılır.

    `options['collection']` yoksa DSN’deki `?collection=` parametresi kullanılır. En az biri zorunludur.

> `collection` query string parametresi MongoDB Connection String tanımının bir parçası değildir.
>
> Bu, `$options` olmadan bir **Data Source Name (DSN)** kullanarak bir `MongoDbStore` oluşturmayı sağlamak için kullanılır.
>
> # 💾 PdoStore
>
> `PdoStore`, kilitleri bir SQL veritabanında saklar. Bir **PDO bağlantısı** veya **Data Source Name (DSN)** gerektirir. Bu store **blocking** özelliğini desteklemez ve donmuş kilitleri önlemek için bir **TTL** bekler:
>
> ```php
> use Symfony\Component\Lock\Store\PdoStore;
>
> // PDO örneği veya PDO üzerinden tembel bağlantı için bir DSN
> $databaseConnectionOrDSN = 'mysql:host=127.0.0.1;dbname=app';
> $store = new PdoStore($databaseConnectionOrDSN, ['db_username' => 'myuser', 'db_password' => 'mypassword']);
> ```
>
> Bu store, **1 saniyeden kısa TTL değerlerini desteklemez.**
>
> Kilitlerin saklandığı tablo, `save()` metoduna yapılan ilk çağrıda otomatik olarak oluşturulur.
>
> Ayrıca bu tabloyu manuel olarak oluşturmak isterseniz, `createTable()` metodunu kodunuzda çağırabilirsiniz.
>
> ---
>
> # 🧱 DoctrineDbalStore
>
> `DoctrineDbalStore`, kilitleri bir SQL veritabanında saklar. `PdoStore` ile aynıdır, ancak **Doctrine DBAL Connection** veya **Doctrine DBAL URL** gerektirir.
>
> Bu store **blocking** özelliğini desteklemez ve bir **TTL** bekler:
>
> ```php
> use Symfony\Component\Lock\Store\DoctrineDbalStore;
>
> // Doctrine DBAL bağlantısı veya DSN
> $connectionOrURL = 'mysql://myuser:mypassword@127.0.0.1/app';
> $store = new DoctrineDbalStore($connectionOrURL);
> ```
>
> Bu store da **1 saniyeden kısa TTL** değerlerini desteklemez.
>
> Kilitlerin saklandığı tablo, aşağıdaki komut çalıştırıldığında otomatik olarak oluşturulur:
>
> ```bash
> php bin/console make:migration
> ```
>
> Tabloyu kendiniz oluşturmak isterseniz ve henüz oluşturulmadıysa, `createTable()` metodunu çağırabilirsiniz.
>
> Ayrıca bu tabloyu şemanıza eklemek için `configureSchema()` metodunu da çağırabilirsiniz.
>
> Tablo önceden oluşturulmamışsa, `save()` metoduna yapılan ilk çağrıda otomatik olarak oluşturulur.
>
> ---
>
> # 🐘 PostgreSqlStore
>
> `PostgreSqlStore`, PostgreSQL tarafından sağlanan **Advisory Lock** ’ları kullanır.
>
> Bir **PDO bağlantısı** veya **DSN** gerektirir. **Yerel engelleme (blocking)** ve **paylaşımlı kilitleri (shared locks)** destekler:
>
> ```php
> use Symfony\Component\Lock\Store\PostgreSqlStore;
>
> // PDO örneği veya PDO üzerinden tembel bağlantı için DSN
> $databaseConnectionOrDSN = 'pgsql:host=localhost;port=5634;dbname=app';
> $store = new PostgreSqlStore($databaseConnectionOrDSN, ['db_username' => 'myuser', 'db_password' => 'mypassword']);
> ```
>
> `PdoStore`’dan farklı olarak, `PostgreSqlStore` kilitleri saklamak için tabloya ihtiyaç duymaz ve **süresi dolmaz** .
>
> ---
>
> # 🧩 DoctrineDbalPostgreSqlStore
>
> `DoctrineDbalPostgreSqlStore`, PostgreSQL’in Advisory Lock’larını kullanır.
>
> `PostgreSqlStore` ile aynıdır, ancak **Doctrine DBAL Connection** veya **Doctrine DBAL URL** gerektirir.
>
> **Yerel blocking** ve **shared locks** destekler:
>
> ```php
> use Symfony\Component\Lock\Store\DoctrineDbalPostgreSqlStore;
>
> // Doctrine bağlantısı veya DSN
> $databaseConnectionOrDSN = 'postgresql+advisory://myuser:mypassword@127.0.0.1:5634/lock';
> $store = new DoctrineDbalPostgreSqlStore($databaseConnectionOrDSN);
> ```
>
> `DoctrineDbalStore`’dan farklı olarak, tabloya ihtiyaç duymaz ve **süresi dolmaz** .
>
> ---
>
> # 🚀 RedisStore
>
> `RedisStore`, kilitleri bir **Redis** sunucusunda saklar.
>
> `\Redis`, `\RedisArray`, `\RedisCluster`, `\Relay\Relay`, `\Relay\Cluster` veya `\Predis` sınıflarından birini uygulayan bir Redis bağlantısı gerektirir.
>
> Bu store **blocking** özelliğini desteklemez ve bir **TTL** bekler:
>
> ```php
> use Symfony\Component\Lock\Store\RedisStore;
>
> $redis = new \Redis();
> $redis->connect('localhost');
>
> $store = new RedisStore($redis);
> ```
>
> > 🆕 `Relay\Cluster` desteği Symfony **7.3** sürümünde eklendi.
>
> ---
>
> # 🧮 SemaphoreStore
>
> `SemaphoreStore`, kilitleri oluşturmak için PHP’nin **semafor fonksiyonlarını** kullanır:
>
> ```php
> use Symfony\Component\Lock\Store\SemaphoreStore;
>
> $store = new SemaphoreStore();
> ```
>
> ---
>
> # 🌐 CombinedStore
>
> `CombinedStore`, **yüksek erişilebilirlik (High Availability)** gerektiren uygulamalar için tasarlanmıştır.
>
> Birden fazla store’u senkronize şekilde yönetir (örneğin, birkaç Redis sunucusu).
>
> Bir kilit alındığında, çağrıyı tüm store’lara iletir ve yanıtlarını toplar.
>
> Eğer **store’ların basit çoğunluğu** kilidi almışsa, kilit alınmış sayılır:
>
> ```php
> use Symfony\Component\Lock\Store\CombinedStore;
> use Symfony\Component\Lock\Store\RedisStore;
> use Symfony\Component\Lock\Strategy\ConsensusStrategy;
>
> $stores = [];
> foreach (['server1', 'server2', 'server3'] as $server) {
>     $redis = new \Redis();
>     $redis->connect($server);
>
>     $stores[] = new RedisStore($redis);
> }
>
> $store = new CombinedStore($stores, new ConsensusStrategy());
> ```
>
> Basit çoğunluk stratejisi (`ConsensusStrategy`) yerine, tüm store’larda kilidin alınmasını zorunlu kılmak için `UnanimousStrategy` kullanılabilir:
>
> ```php
> use Symfony\Component\Lock\Store\CombinedStore;
> use Symfony\Component\Lock\Strategy\UnanimousStrategy;
>
> $store = new CombinedStore($stores, new UnanimousStrategy());
> ```
>
> > 🧠 **ConsensusStrategy** kullanıldığında, yüksek erişilebilirlik için minimum küme boyutu **üç sunucu** olmalıdır.
> >
> > Bu, bir sunucu başarısız olduğunda kümenin çalışmaya devam etmesini sağlar.
>
> ---
>
> # 🐾 ZookeeperStore
>
> `ZookeeperStore`, kilitleri bir **ZooKeeper** sunucusunda saklar.
>
> `\Zookeeper` sınıfını uygulayan bir bağlantı gerektirir.
>
> Bu store **blocking** ve **expiration** özelliklerini desteklemez, ancak PHP süreci sona erdiğinde kilit otomatik olarak serbest bırakılır:
>
> ```php
> use Symfony\Component\Lock\Store\ZookeeperStore;
>
> $zookeeper = new \Zookeeper('localhost:2181');
> // Yüksek erişilebilirlik kümesi için şu şekilde tanımlayabilirsiniz:
> // $zookeeper = new \Zookeeper('localhost1:2181,localhost2:2181,localhost3:2181');
>
> $store = new ZookeeperStore($zookeeper);
> ```
>
> ZooKeeper, kilitleme için kullanılan düğümler **geçici (ephemeral)** olduğundan TTL gerektirmez; PHP süreci sona erdiğinde bu düğümler otomatik olarak silinir.
>
> ---
>
> # 🔐 Güvenilirlik (Reliability)
>
> Bileşen, aynı kaynak iki kez kilitlenemeyecek şekilde tasarlanmıştır — ancak yalnızca aşağıdaki şekilde kullanıldığında.
>
> ## 🌍 Uzak Store’lar (Remote Stores)
>
> Uzak store’lar (`MemcachedStore`, `MongoDbStore`, `PdoStore`, `PostgreSqlStore`, `RedisStore`, `ZookeeperStore`), kilidin gerçek sahibini tanımak için benzersiz bir **token** kullanır.
>
> Bu token `Key` nesnesinde saklanır ve `Lock` tarafından dahili olarak kullanılır.
>
> Her eşzamanlı süreç **aynı sunucuda** kilidi saklamalıdır. Aksi halde, iki farklı makine aynı kilidi iki farklı süreç için verebilir.
>
> > ⚠️ **Memcached** kullanıyorsanız, **LoadBalancer** , **cluster** veya **round-robin DNS** arkasında çalıştırmayın.
> >
> > Ana sunucu kapansa bile çağrılar yedek sunucuya yönlendirilmemelidir.
>
> ---
>
> ## ⏰ Süresi Dolan Store’lar (Expiring Stores)
>
> Süresi dolan store’lar (`MemcachedStore`, `MongoDbStore`, `PdoStore`, `RedisStore`), kilidin yalnızca tanımlanan süre boyunca alınmasını garanti eder.
>
> Görev daha uzun sürerse, store kilidi serbest bırakabilir ve başka bir süreç kilidi alabilir.
>
> `Lock`, kilidin sağlığını kontrol etmek için birkaç metod sağlar:
>
> -   `isExpired()` → kilidin süresinin dolup dolmadığını kontrol eder.
> -   `getRemainingLifetime()` → kilidin kalan ömrünü (TTL) saniye cinsinden döndürür.
>
> Bu metodları kullanarak dayanıklı bir kod örneği şu şekildedir:
>
> ```php
> // ...
> $lock = $factory->createLock('pdf-creation', 30);
>
> if (!$lock->acquire()) {
>     return;
> }
> while (!$finished) {
>     if ($lock->getRemainingLifetime() <= 5) {
>         if ($lock->isExpired()) {
>             // Kilit kaybedildi, rollback yap veya bildirim gönder
>             throw new \RuntimeException('Lock lost during the overall process');
>         }
>
>         $lock->refresh();
>     }
>
>     // Süresi 5 saniyeden az olan işi gerçekleştir
> }
> ```
>
> Kilitin ömrünü dikkatli seçin ve kalan süresinin işi tamamlamak için yeterli olup olmadığını kontrol edin.
>
> Kilitin saklanması genellikle birkaç milisaniye sürer, ancak ağ koşulları bu süreyi saniyelere çıkarabilir. TTL seçerken bunu dikkate alın.
>
> Kilitler, belirli bir yaşam süresiyle sunucularda saklanır.
>
> Eğer makinenin tarihi veya saati değişirse, kilit beklenenden daha erken serbest bırakılabilir.
>
> > Bu riski önlemek için **NTP servisini devre dışı bırakın** ve zamanı yalnızca servis durdurulduğunda güncelleyin.
>
> ---
>
> # 📂 FlockStore Güvenilirliği
>
> Dosya sistemini kullandığı için `FlockStore`, eşzamanlı süreçler kilitleri **aynı fiziksel dizinde** sakladığı sürece güvenilirdir.
>
> -   Süreçler **aynı makinede** , **sanal makinede** veya **container** içinde çalışmalıdır.
> -   Kubernetes veya Swarm servislerini güncellerken dikkatli olun; kısa süreli paralel container çalışabilir.
> -   Dizinin **mutlak yolu** aynı kalmalıdır.
>     -   Capistrano veya **blue/green deployment** gibi tekniklerde sembolik bağlantılar (symlink) değişebilir.
>     -   İki dağıtım arasında dizin yolu değişirse kilitler geçersiz olur.
>
> Bazı dosya sistemleri (örneğin belirli **NFS** türleri) kilitlemeyi desteklemez.
>
> Tüm süreçler aynı fiziksel dosya sistemini ve aynı **mutlak yol**u kullanmalıdır.
>
> Birden fazla **front server** varsa, `FlockStore`’u HTTP bağlamında kullanmak uyumsuzdur.
>
> Ancak, aynı kaynağın her zaman aynı makinede kilitleneceğinden emin olunuyorsa veya iyi yapılandırılmış paylaşılan bir dosya sistemi kullanılıyorsa mümkündür.
>
> Bakım işlemleri sırasında dosya sistemi üzerindeki dosyalar silinebilir (örneğin `/tmp` klasörünün temizlenmesi veya `tmpfs` kullanılan bir dizin).
>
> Bu, işlem sonunda kilit serbest bırakılıyorsa sorun değildir; ancak **istekler arasında yeniden kullanılacak kilitler** için ciddi bir risktir.
>
> > ⚠️ **Bir kilit birden fazla istekte yeniden kullanılacaksa** , onu **geçici (volatile)** bir dosya sisteminde saklamayın.
>
> # 🧠 MemcachedStore
>
> Memcached çalışma şekli gereği öğeleri bellekte saklar. Bu nedenle, **MemcachedStore** kullanıldığında kilitler kalıcı değildir ve yanlışlıkla herhangi bir anda kaybolabilir.
>
> Eğer **Memcached servisi** veya onu barındıran makine yeniden başlatılırsa, çalışan süreçlere haber verilmeden tüm kilitler kaybolur.
>
> Yeniden başlatmadan sonra bir başkasının kilidi almasını önlemek için, servis başlatmayı geciktirmeniz ve en azından en uzun kilit TTL süresi kadar beklemeniz önerilir.
>
> Varsayılan olarak Memcached, yeni öğelere yer açmak için eski girdileri kaldırmak üzere bir **LRU (Least Recently Used)** mekanizması kullanır.
>
> Saklanan öğe sayısı kontrol altında tutulmalıdır. Bu mümkün değilse, **LRU devre dışı bırakılmalı** ve kilitler, önbellekten ayrı olarak yalnızca kilitler için ayrılmış bir **özel Memcached servisi** içinde saklanmalıdır.
>
> Memcached servisi birden fazla amaçla paylaşıldığında, kilitler yanlışlıkla silinebilir.
>
> Örneğin, bazı **PSR-6** uygulamaları `clear()` metodunu çağırdığında, bu `Memcached`’in `flush()` metodunu çalıştırır ve tüm öğeleri temizler.
>
> > `flush()` metodu **asla çağrılmamalıdır** veya kilitler, önbellekten ayrı olarak özel bir Memcached servisi içinde saklanmalıdır.
>
> ---
>
> # 🍃 MongoDbStore
>
> Kilitli kaynak adı, kilit koleksiyonundaki `_id` alanında indekslenir.
>
> Dikkat: MongoDB’de indekslenmiş bir alanın değeri, yapısal üstbilgi dâhil en fazla **1024 bayt** uzunluğunda olabilir.
>
> Süresi dolan kilitleri otomatik olarak temizlemek için bir **TTL index** kullanılmalıdır. Bu indeks manuel olarak şu şekilde oluşturulabilir:
>
> ```js
> db.lock.createIndex({ expires_at: 1 }, { expireAfterSeconds: 0 });
> ```
>
> Alternatif olarak, veritabanı kurulumunda `MongoDbStore::createTtlIndex(int $expireAfterSeconds = 0)` metodu bir kez çağrılarak TTL index oluşturulabilir.
>
> Daha fazla bilgi için: _Expire Data from Collections by Setting TTL in MongoDB._
>
> `MongoDbStore` TTL indeksini otomatik olarak oluşturmayı dener. Eğer TTL indeksini manuel olarak oluşturduysanız, `gcProbability` yapılandırma seçeneğini `0.0` olarak ayarlayarak bu davranışı devre dışı bırakmanız önerilir.
>
> Bu store’un doğru şekilde çalışması için tüm PHP uygulama ve veritabanı düğümlerinin saatlerinin senkronize olması gerekir.
>
> Kilitlerin erken süresinin dolmaması için, **kilit TTL değeri** `expireAfterSeconds` içinde, olası saat farklarını (clock drift) karşılayacak şekilde yeterli payla ayarlanmalıdır.
>
> `writeConcern` ve `readConcern` değerleri `MongoDbStore` tarafından belirtilmez; koleksiyonun varsayılan ayarları geçerlidir.
>
> Tüm sorgular için `readPreference` varsayılan olarak **primary** ’dir.
>
> Daha fazla bilgi için: _Replica Set Read and Write Semantics in MongoDB._
>
> ---
>
> # 🗃️ PdoStore
>
> `PdoStore`, SQL motorunun **ACID** özelliklerine dayanır.
>
> Birden fazla birincil (primary) ile yapılandırılmış kümelerde, yazma işlemlerinin tüm düğümlere senkron olarak yayılmasını sağlayın veya her zaman aynı düğümü kullanın.
>
> Bazı SQL motorları (örneğin MySQL), **benzersiz kısıtlama (unique constraint)** kontrolünü devre dışı bırakmaya izin verir.
>
> Bu özelliğin kapalı olmadığından emin olun:
>
> ```sql
> SET unique_checks=1;
> ```
>
> Eski kilitleri temizlemek için bu store, geçerli tarih/saat değerine dayanarak bir son kullanma referansı tanımlar.
>
> Bu mekanizma, tüm sunucu düğümlerinin saatlerinin senkronize olmasına bağlıdır.
>
> Kilitlerin erken süresinin dolmaması için, TTL değerleri düğümler arasındaki olası saat farkını (clock drift) karşılayacak kadar yüksek ayarlanmalıdır.
>
> ---
>
> # 🐘 PostgreSqlStore
>
> `PostgreSqlStore`, PostgreSQL veritabanının **Advisory Lock** özelliklerine dayanır.
>
> Bu, `PostgreSqlStore` kullanıldığında, istemci herhangi bir nedenle kilidi serbest bırakamazsa, kilitlerin oturum sonunda otomatik olarak serbest bırakılacağı anlamına gelir.
>
> Eğer **PostgreSQL servisi** veya barındırıldığı makine yeniden başlatılırsa, çalışan süreçlere haber verilmeden tüm kilitler kaybolur.
>
> TCP bağlantısı kesilirse, PostgreSQL kilitleri uygulamaya haber vermeden serbest bırakabilir.
>
> ---
>
> # 🔴 RedisStore
>
> Redis, verileri bellekte saklar. Bu nedenle, `RedisStore` kullanıldığında kilitler kalıcı değildir ve yanlışlıkla kaybolabilir.
>
> Eğer **Redis servisi** veya onu barındıran makine yeniden başlatılırsa, çalışan süreçlere haber verilmeden tüm kilitler kaybolur.
>
> Yeniden başlatmadan sonra bir başkasının kilidi almasını önlemek için, servis başlatmayı geciktirmeniz ve en azından en uzun kilit TTL süresi kadar beklemeniz önerilir.
>
> Redis, verileri diske kalıcı hale getirecek şekilde yapılandırılabilir, ancak bu işlem yazma hızını düşürür ve sunucunun diğer kullanım alanlarıyla çelişebilir.
>
> Redis servisi birden fazla amaç için paylaşılıyorsa, kilitler yanlışlıkla kaldırılabilir.
>
> > `FLUSHDB` komutu **asla çağrılmamalıdır** , ya da kilitler önbellekten ayrı, yalnızca kilitlere özel bir Redis sunucusunda saklanmalıdır.
>
> ---
>
> # ⚙️ CombinedStore
>
> `CombinedStore`, kilitleri birden fazla backend üzerinde saklamayı sağlar.
>
> Ancak yaygın bir yanılgı, bunun kilitleme mekanizmasını **daha güvenilir** hâle getireceğini düşünmektir.
>
> Bu yanlıştır — `CombinedStore` en fazla, yönetilen store’lar arasındaki **en az güvenilir olan kadar** güvenilir olacaktır.
>
> Yönetilen store’lardan biri hatalı bilgi döndürdüğünde, `CombinedStore` artık güvenilir olmayacaktır.
>
> Tüm eşzamanlı süreçlerin aynı yapılandırmayı, aynı sayıda store’u ve aynı uç noktayı (endpoint) kullanması gerekir.
>
> Birden fazla Redis veya Memcached sunucusu kümesi kullanmak yerine, her store’un tek bir sunucu yönettiği bir `CombinedStore` kullanmak daha iyidir.
>
> ---
>
> # 🧮 SemaphoreStore
>
> Semaforlar, **işletim sistemi çekirdeği (Kernel)** seviyesinde yönetilir.
>
> Güvenilir olmaları için süreçlerin aynı makinede, sanal makinede veya container içinde çalışması gerekir.
>
> Kubernetes veya Swarm servislerini güncellerken dikkatli olun; kısa bir süre boyunca paralel çalışan iki container olabilir.
>
> Tüm eşzamanlı süreçler aynı makinede çalışmalıdır.
>
> Yeni bir makinede eşzamanlı süreç başlatmadan önce, eski makinedeki süreçlerin durduğundan emin olun.
>
> `systemd` üzerinde, **sistem kullanıcısı olmayan** bir kullanıcı ile çalıştırıldığında ve `RemoveIPC=yes` (varsayılan değer) ayarı etkinse, kullanıcı oturumu kapandığında kilitler **systemd** tarafından silinir.
>
> Bunu önlemek için:
>
> -   Süreci **sistem kullanıcısı** (UID ≤ `SYS_UID_MAX`) altında çalıştırın.
> -   Veya `/etc/systemd/logind.conf` dosyasında `RemoveIPC=off` ayarını yapın.
>
> ---
>
> # 🦓 ZookeeperStore
>
> `ZookeeperStore`, sunucuda kilitleri **ephemeral node** (geçici düğümler) olarak tutar.
>
> Bu, istemci herhangi bir nedenle kilidi serbest bırakamazsa, oturum sonunda kilitlerin otomatik olarak kaldırılacağı anlamına gelir.
>
> Eğer **ZooKeeper servisi** veya barındırıldığı makine yeniden başlatılırsa, çalışan süreçlere haber verilmeden tüm kilitler kaybolur.
>
> ZooKeeper’ın yüksek erişilebilirlik (HA) özelliğini kullanmak için, birden fazla sunucudan oluşan bir **küme** yapılandırabilirsiniz.
>
> Böylece bir sunucu çökerse bile çoğunluk aktif kalır ve istekleri işlemeye devam eder.
>
> Kümedeki tüm sunucular aynı durumu paylaşır.
>
> Bu store, **çok seviyeli node kilitlerini** desteklemez çünkü ara düğümlerin temizliği ek yük oluşturur; bu nedenle tüm kilitler **kök (root)** seviyesinde tutulur.
>
> ---
>
> # ⚠️ Genel Uyarı (Overall)
>
> Store yapılandırmalarını değiştirmek çok dikkatli yapılmalıdır — örneğin yeni bir sürüm dağıtımı sırasında.
>
> Yeni yapılandırmaya sahip süreçler, eski yapılandırmaya sahip süreçler hâlâ çalışırken başlatılmamalıdır.
>
> ---
>
> 📄 \*\*
