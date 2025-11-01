# 🌐 HTTP Önbelleği

Zengin web uygulamalarının doğası gereği, bu uygulamalar dinamiktir. Uygulamanız ne kadar verimli olursa olsun, her istek her zaman statik bir dosyayı sunmaktan daha fazla ek yük içerir. Genellikle bu sorun değildir. Ancak isteklerinizin yıldırım hızında olmasını istediğinizde HTTP önbelleğine ihtiyacınız vardır.

## 🏗️ Devlerin Omuzlarında Önbellekleme

HTTP Caching ile bir sayfanın (yani yanıtın) tüm çıktısını önbelleğe alabilir ve sonraki isteklerde uygulamanızı tamamen atlayabilirsiniz. Tam yanıtların önbelleğe alınması her zaman mümkün değildir — özellikle yüksek oranda dinamik sitelerde. Ancak Edge Side Includes (ESI) ile sitenizin yalnızca belirli parçalarında HTTP önbelleklemenin gücünü kullanabilirsiniz.

Symfony önbellek sistemi farklıdır çünkü RFC 7234 - Caching standardında tanımlanan HTTP önbelleğinin basitliğine ve gücüne dayanır. Symfony, yeni bir önbellekleme metodolojisi icat etmek yerine, web üzerindeki temel iletişimi tanımlayan standardı benimser. Temel HTTP doğrulama ve süresi dolma (validation ve expiration) önbellekleme modellerini anladığınızda, Symfony önbellek sistemini anlamaya hazır olacaksınız.

HTTP önbellekleme Symfony’ye özgü olmadığı için bu konuda birçok makale zaten mevcuttur. HTTP önbelleklemede yeniyseniz, Ryan Tomayko’nun **Things Caches Do** adlı makalesi şiddetle tavsiye edilir. Diğer detaylı bir kaynak ise Mark Nottingham’ın  **Cache Tutorial** ’ıdır.

## 🚪 Gateway Cache ile Önbellekleme

HTTP önbelleklemede, önbellek tamamen uygulamanızdan ayrı bir katmanda bulunur ve uygulamanızla istemci arasında yer alır.

Önbelleğin görevi, istemciden gelen istekleri kabul etmek ve bunları uygulamanıza iletmektir. Önbellek ayrıca uygulamanızdan dönen yanıtları alır ve bunları istemciye gönderir. Bu şekilde önbellek, istemci ve uygulamanız arasındaki istek–yanıt iletişiminin “aracısı” olur.

Bu süreçte, önbellek “önbelleğe alınabilir” olarak kabul edilen her yanıtı saklar (bkz. HTTP Cache). Aynı kaynak tekrar istenirse, önbellek yanıtı doğrudan istemciye gönderir ve uygulamanızı tamamen atlar.

Bu tür önbelleğe **HTTP gateway cache** denir ve  **Varnish** , **Squid** (reverse proxy modunda) ve **Symfony reverse proxy** gibi birçok örneği vardır.

Gateway cache’ler bazen  **reverse proxy cache** , **surrogate cache** veya **HTTP accelerator** olarak da adlandırılır.

## ⚙️ Symfony Reverse Proxy

Symfony, PHP ile yazılmış bir reverse proxy (yani gateway cache) içerir. Bu, **Varnish** kadar tam özellikli bir reverse proxy cache olmasa da, başlamak için mükemmel bir yoldur.

Varnish kurulum detayları için: *How to Use Varnish to Speed up my Website* bölümüne bakın.

`framework.http_cache` seçeneğini kullanarak proxy’yi **prod** ortamında etkinleştirin:

```php
// config/packages/framework.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework, string $env): void {
    if ('prod' === $env) {
        $framework->httpCache()->enabled(true);
    }
};
```

Bu durumda kernel, hemen bir reverse proxy gibi davranacak; uygulamanızdan gelen yanıtları önbelleğe alacak ve istemciye geri döndürecektir.

Proxy makul bir varsayılan yapılandırmaya sahiptir, ancak birçok seçenekle ince ayar yapılabilir.

 **Debug modunda** , Symfony yanıtınıza otomatik olarak bir `X-Symfony-Cache` başlığı ekler. Ayrıca `trace_level` yapılandırma seçeneğini `none`, `short` veya `full` olarak ayarlayabilirsiniz.

* `short`, yalnızca ana istek için bilgi ekler ve sunucu log dosyalarına yazmak için uygundur.

  Örneğin Apache’de `%{X-Symfony-Cache}o` kullanabilirsiniz.
* Bu bilgi, rotalarınızın önbellek verimliliği hakkında genel bilgi toplamak için kullanılabilir.

`trace_header` yapılandırma seçeneğiyle izleme bilgisi için kullanılan başlığın adını da değiştirebilirsiniz.

## 🔁 Reverse Proxy Değiştirme

Symfony reverse proxy, web sitenizi geliştirirken veya yalnızca PHP yükleyebildiğiniz bir paylaşımlı sunucuya dağıtırken kullanmak için harika bir araçtır. Ancak PHP ile yazıldığı için C ile yazılmış bir proxy kadar hızlı olamaz.

Neyse ki, tüm reverse proxy’ler aynı prensiplerle çalıştığından, daha güçlü bir çözüm olan **Varnish** gibi bir sisteme geçişi sorunsuz şekilde yapabilirsiniz.

Bkz.  *How to use Varnish* .

## 🧭 HTTP Önbelleğe Alınabilir Yanıtlar Oluşturma

Reverse proxy cache (ör. Symfony reverse proxy veya Varnish) ekledikten sonra, artık yanıtlarınızı önbelleğe alabilirsiniz. Bunun için önbelleğe hangi yanıtların alınacağını ve ne kadar süreyle saklanacağını önbelleğe bildirmeniz gerekir. Bu, yanıt üzerine HTTP önbellek başlıkları ekleyerek yapılır.

HTTP dört adet yanıt önbellek başlığı tanımlar:

* `Cache-Control`
* `Expires`
* `ETag`
* `Last-Modified`

Bu başlıklar iki farklı modelde çalışır:

1. **Expiration Caching** – Yanıtı belirli bir süre için (ör. 24 saat) önbelleğe alır. Basittir, ancak önbellek geçersizleştirmesi zordur.
2. **Validation Caching** – Daha karmaşıktır, ancak içerik değiştiğinde yanıtı dinamik olarak geçersiz kılmanıza olanak tanır.

## 📚 HTTP Spesifikasyonunu Okuma

Burada bahsedilen HTTP başlıkları Symfony tarafından icat edilmemiştir! Bunlar tüm webde kullanılan bir HTTP standardının parçasıdır. Daha derin bilgi için şu belgelere göz atın:

* RFC 7234 – *Caching*
* RFC 7232 – *Conditional Requests*

Bir web geliştiricisi olarak bu spesifikasyonu okumanız şiddetle tavsiye edilir. On beş yıldan fazla süredir var olmasına rağmen hâlâ çok güçlü ve açıklayıcıdır.

## ⏳ Expiration Caching

Bir yanıtı belirli bir süre boyunca önbelleğe almak en basit yöntemdir:

```php
// src/Controller/BlogController.php
use Symfony\Component\HttpFoundation\Response;

public function index(): Response
{
    $response = $this->render('blog/index.html.twig', []);

    // yanıtı 3600 saniye boyunca herkese açık şekilde önbelleğe al
    $response->setPublic();
    $response->setMaxAge(3600);

    // (isteğe bağlı) özel bir Cache-Control yönergesi ekle
    $response->headers->addCacheControlDirective('must-revalidate', true);

    return $response;
}
```

Bu kod, HTTP yanıtınıza şu başlığı ekler:

```
Cache-Control: public, maxage=3600, must-revalidate
```

Bu, HTTP reverse proxy’ye bu yanıtı 3600 saniye boyunca önbelleğe almasını söyler. Bu süre dolmadan aynı URL tekrar istenirse, uygulamanız hiç çalıştırılmaz.

Symfony reverse proxy kullanıyorsanız, `X-Symfony-Cache` başlığı üzerinden önbellek isabetlerini ve kaçırmalarını (hits/misses) görebilirsiniz.

İsteğin URI’si, önbellek anahtarı olarak kullanılır (vary kullanılmadığı sürece).

Bu yöntem yüksek performans sağlar ve kullanımı kolaydır. Ancak, önbellek geçersizleştirmeyi desteklemez. İçeriğiniz değiştiğinde, sayfa güncellenmeden önce önbelleğin süresinin dolmasını beklemeniz gerekir.

Elbette, manuel olarak önbelleği geçersiz kılabilirsiniz, ancak bu HTTP Caching standardının bir parçası değildir.

Bkz. *Cache Invalidation.*

Birçok farklı controller için aynı önbellek başlıklarını ayarlamak istiyorsanız  **FOSHttpCacheBundle** ’ı inceleyin. Bu paket, URL desenlerine veya diğer istek özelliklerine göre önbellek başlıklarını tanımlamanızı sağlar.

Daha fazla bilgi için bkz. *HTTP Cache Expiration.*

## 🔍 Validation Caching

Expiration caching’de “3600 saniye önbelleğe al” dersiniz. Ancak içerik güncellendiğinde bu değişiklik, önbellek süresi dolana kadar görünmez.

Güncellenen içeriği anında görmek istiyorsanız, önbelleğinizi geçersiz kılmanız veya **validation caching** modelini kullanmanız gerekir.

Detaylar için bkz. *HTTP Cache Validation.*

## 🛡️ Güvenli Yöntemler: Yalnızca GET veya HEAD İsteklerinin Önbelleğe Alınması

HTTP önbellekleme yalnızca **güvenli (safe)** HTTP yöntemleri (GET ve HEAD) için geçerlidir. Bunun üç önemli sonucu vardır:

1. **PUT** veya **DELETE** isteklerini önbelleğe almaya çalışmayın. Bu, uygulamanızın durumunu değiştirmeye yönelik işlemlerdir ve önbelleğe almak doğru değildir.
2. **POST** istekleri genellikle önbelleğe alınamaz, ancak açık tazelik bilgisi içeriyorsa önbelleğe alınabilir. Yine de bu özellik yaygın olarak uygulanmadığından POST önbelleğe almaktan kaçının.
3. **GET** veya **HEAD** isteğine yanıt verirken uygulamanızın durumunu asla değiştirmeyin. Bu istekler önbelleğe alınırsa, sonraki istekler sunucunuza hiç ulaşmayabilir.


# 🧩 Daha Fazla Response Metodu

`Response` sınıfı, önbellekleme ile ilgili çok daha fazla metod sağlar. İşte en kullanışlı olanlardan bazıları:

```php
// Yanıtı "stale" olarak işaretler
$response->expire();

// İçerik olmadan uygun bir 304 yanıtı döndürmeyi zorunlu kılar
$response->setNotModified();
```

Ayrıca, çoğu önbellekleme ile ilgili HTTP başlığı tek bir `setCache()` metodu aracılığıyla ayarlanabilir:

```php
// Bu metodu birden fazla önbellek ayarını tek seferde yapmak için kullanın
// (bu örnek, mevcut tüm ayarları listeler)
$response->setCache([
    'must_revalidate'  => false,
    'no_cache'         => false,
    'no_store'         => false,
    'no_transform'     => false,
    'public'           => true,
    'private'          => false,
    'proxy_revalidate' => false,
    'max_age'          => 600,
    's_maxage'         => 600,
    'immutable'        => true,
    'last_modified'    => new \DateTime(),
    'etag'             => 'abcdef'
]);
```

Bu seçeneklerin tümü ayrıca `#[Cache]` attribute’u kullanılırken de mevcuttur.

---

## 🧹 Cache Invalidation

Önbellek geçersizleştirme (cache invalidation) HTTP spesifikasyonunun bir parçası değildir. Ancak, sitenizdeki bazı içerikler güncellendiğinde çeşitli HTTP önbellek girişlerini silmek oldukça faydalı olabilir.

Detaylar için bkz.  **Cache Invalidation** .

---

## 🧱 Edge Side Includes (ESI) Kullanımı

Sayfalar dinamik bölümler içerdiğinde, tüm sayfaları önbelleğe almak mümkün olmayabilir — yalnızca belirli parçaları önbelleğe alabilirsiniz.

Sayfanızın belirli bölümleri için farklı önbellekleme stratejilerini nasıl yapılandıracağınızı öğrenmek için **Working with Edge Side Includes** bölümünü okuyun.

---

## 🔐 HTTP Önbelleği ve Kullanıcı Oturumları

Bir istek sırasında oturum (session) başlatıldığında, Symfony yanıtı otomatik olarak **özel (private)** ve **önbelleğe alınamaz (non-cacheable)** hale getirir.

Bu varsayılan davranış, özel kullanıcı bilgilerini (örneğin alışveriş sepeti, kullanıcı profili detayları vb.) önbelleğe alarak diğer ziyaretçilere açılmasını engellemek için en güvenli yaklaşımdır.

Ancak bazı durumlarda, oturumu kullanan istekler bile önbelleğe alınabilir.

Örneğin, bir kullanıcı grubuna ait bilgiler tüm grup üyeleri için önbelleğe alınabilir. Bu tür gelişmiş önbellekleme senaryoları Symfony’nin kapsamı dışındadır, ancak **FOSHttpCacheBundle** ile çözülebilir.

Symfony’nin, oturum kullanan istekleri önbelleğe alınamaz hale getiren varsayılan davranışını devre dışı bırakmak için, yanıtınıza aşağıdaki **iç başlığı (internal header)** ekleyin; böylece Symfony bu davranışı uygulamaz:

```php
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;

$response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, 'true');
```

---

## 🧭 Özet

Symfony, webin kanıtlanmış standartlarını yani **HTTP kurallarını** takip etmek üzere tasarlanmıştır.

Önbellekleme de bu kurala istisna değildir. Symfony önbellek sisteminde ustalaşmak, HTTP önbellek modellerini öğrenmek ve bunları etkili şekilde kullanmak anlamına gelir.

Bu da, yalnızca Symfony dokümantasyonuna ve örnek kodlara güvenmek yerine, **HTTP caching** ve **gateway cache** (ör. Varnish) konularında mevcut geniş bilgi dünyasına erişebileceğiniz anlamına gelir.
