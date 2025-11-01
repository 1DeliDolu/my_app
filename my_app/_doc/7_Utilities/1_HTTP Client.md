HTTP Client

Bu sayfayı düzenle

Kurulum

HttpClient bileşeni, hem PHP stream wrapper’ları hem de cURL için destek sağlayan düşük seviyeli bir HTTP istemcisidir. API’leri tüketmek için yardımcı araçlar sağlar ve senkron ile asenkron işlemleri destekler. Şu komutla kurulabilir:

```
composer require symfony/http-client
```

Temel Kullanım

İstek yapmak için HttpClient sınıfını kullanın. Symfony framework’ünde bu sınıf, http_client servisi olarak kullanılabilir. Bu servis, HttpClientInterface için type-hint yapıldığında otomatik olarak autowire edilir:

```php
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SymfonyDocs
{
    public function __construct(
        private HttpClientInterface $client,
    ) {
    }

    public function fetchGitHubInformation(): array
    {
        $response = $this->client->request(
            'GET',
            'https://api.github.com/repos/symfony/symfony-docs'
        );

        $statusCode = $response->getStatusCode();
        // $statusCode = 200
        $contentType = $response->getHeaders()['content-type'][0];
        // $contentType = 'application/json'
        $content = $response->getContent();
        // $content = '{"id":521583, "name":"symfony-docs", ...}'
        $content = $response->toArray();
        // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]

        return $content;
    }
}
```

HTTP client, PHP’deki birçok yaygın HTTP istemci soyutlamasıyla birlikte çalışabilir. Ayrıca, bu soyutlamalardan herhangi birini kullanarak autowiring özelliklerinden de faydalanabilirsiniz. Daha fazla bilgi için Interoperability bölümüne bakın.

Yapılandırma

HTTP client, isteğin nasıl gerçekleştirileceği üzerinde tam kontrol sağlamanıza olanak tanıyan birçok seçenek içerir; DNS ön çözümlemesi, SSL parametreleri, public key pinning gibi. Bunlar global olarak yapılandırmada (tüm isteklere uygulanır) veya her bir istek için (global yapılandırmayı geçersiz kılar) tanımlanabilir.

Global seçenekleri default_options seçeneğiyle yapılandırabilirsiniz:

```php
// config/packages/framework.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->httpClient()
        ->defaultOptions()
            ->maxRedirects(7)
    ;
};
```

Ayrıca withOptions() metodunu kullanarak yeni varsayılan seçeneklerle yeni bir client örneği elde edebilirsiniz:

```php
$this->client = $client->withOptions([
    'base_uri' => 'https://...',
    'headers' => ['header-name' => 'header-value'],
    'extra' => ['my-key' => 'my-value'],
]);
```

Alternatif olarak, HttpOptions sınıfı çoğu mevcut seçeneği type-hinted getter ve setter’larla getirir:

```php
$this->client = $client->withOptions(
    (new HttpOptions())
        ->setBaseUri('https://...')
        // *tüm* header’ları bir kerede değiştirir ve belirtmediğiniz header’ları siler
        ->setHeaders(['header-name' => 'header-value'])
        // tek bir header’ı setHeader() ile ayarlayın veya değiştirin
        ->setHeader('another-header-name', 'another-header-value')
        ->toArray()
);
```

7.1

setHeader() metodu Symfony 7.1’de tanıtılmıştır.

Bu kılavuzda bazı seçenekler açıklanmıştır:

* Authentication
* Query String Parameters
* Headers
* Redirects
* Retry Failed Requests
* HTTP Proxies
* Using URI Templates

Tüm seçenekleri öğrenmek için http_client config referansına göz atın.

HTTP client ayrıca max_host_connections adlı bir yapılandırma seçeneğine sahiptir. Bu seçenek istek bazında geçersiz kılınamaz:

```php
// config/packages/framework.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->httpClient()
        ->maxHostConnections(10)
        // ...
    ;
};
```

Scoped Client

Bazı HTTP client seçeneklerinin istek yapılan URL’ye bağlı olması yaygındır (örneğin, GitHub API’ye yapılan isteklerde bazı header’lar ayarlanmalı, ancak diğer host’lar için değil). Bu durumda, bileşen, isteğin URL’sine göre HTTP client’ı otomatik yapılandırmak için scoped client’lar (ScopingHttpClient kullanarak) sağlar:

```php
// config/packages/framework.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    // yalnızca scope ile eşleşen istekler bu seçenekleri kullanır
    $framework->httpClient()->scopedClient('github.client')
        ->scope('https://api\.github\.com')
        ->header('Accept', 'application/vnd.github.v3+json')
        ->header('Authorization', 'token %env(GITHUB_API_TOKEN)%')
        // ...
    ;

    // base_url kullanarak, göreli URL’ler (örneğin request("GET", "/repos/symfony/symfony-docs"))
    // varsayılan olarak bu seçenekleri kullanır
    $framework->httpClient()->scopedClient('github.client')
        ->baseUri('https://api.github.com')
        ->header('Accept', 'application/vnd.github.v3+json')
        ->header('Authorization', 'token %env(GITHUB_API_TOKEN)%')
        // ...
    ;
};
```

Birden fazla scope tanımlayabilirsiniz, böylece her seçenek kümesi yalnızca bir isteğin URL’si scope seçeneğiyle tanımlanan düzenli ifadelerden biriyle eşleşirse eklenir.

request() metoduna iletilen seçenekler, scoped client’ta tanımlanan varsayılan seçeneklerle birleştirilir. request()’e iletilen seçenekler önceliklidir ve varsayılanları geçersiz kılar veya genişletir.

Symfony framework’ünde scoped client kullanıyorsanız, belirli bir servisi seçmek için Symfony tarafından tanımlanan yöntemlerden birini kullanmalısınız. Her client, yapılandırmasına göre adlandırılmış benzersiz bir servise sahiptir.

Her scoped client ayrıca karşılık gelen bir adlandırılmış autowiring alias’ı tanımlar. Örneğin, bir bağımlılıkta Symfony\Contracts\HttpClient\HttpClientInterface $githubClient türünü ve adını kullanırsanız, autowiring github.client servisini sınıfınıza enjekte eder.

Göreli URI’ların scoped client’ın base URI’sine birleştirilmesinde uygulanan kuralları öğrenmek için base_uri seçeneği belgelerine bakın.

İstek Yapma

HTTP client, tüm HTTP istek türlerini gerçekleştirmek için tek bir request() metodu sağlar:

```php
$response = $client->request('GET', 'https://...');
$response = $client->request('POST', 'https://...');
$response = $client->request('PUT', 'https://...');
// ...

// isteğe seçenekler ekleyebilir (veya global olanları geçersiz kılabilirsiniz)
$response = $client->request('GET', 'https://...', [
    'headers' => [
        'Accept' => 'application/json',
    ],
]);
```

Yanıtlar her zaman asenkron olduğundan, metodun çağrılması yanıtın alınmasını beklemeden hemen döner:

```php
// kod yürütmesi hemen devam eder; yanıtın alınmasını beklemez
$response = $client->request('GET', 'http://releases.ubuntu.com/18.04.2/ubuntu-18.04.2-desktop-amd64.iso');

// yanıt header’larını almak, onların gelmesini bekler
$contentType = $response->getHeaders()['content-type'][0];

// yanıt içeriğini almaya çalışmak, tam yanıt içeriği alınana kadar yürütmeyi durdurur
$content = $response->getContent();
```

Bu bileşen ayrıca tamamen asenkron uygulamalar için akış (streaming) yanıtlarını da destekler.



Kimlik Doğrulama (Authentication)

HTTP client, farklı kimlik doğrulama mekanizmalarını destekler. Bunlar global olarak yapılandırmada (tüm isteklere uygulanır) veya her bir istek için (herhangi bir global kimlik doğrulamayı geçersiz kılar) tanımlanabilir:

```php
// config/packages/framework.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->httpClient()->scopedClient('example_api')
        ->baseUri('https://example.com/')
        // HTTP Basic authentication
        ->authBasic('the-username:the-password')

        // HTTP Bearer authentication (token authentication olarak da bilinir)
        ->authBearer('the-bearer-token')

        // Microsoft NTLM authentication
        ->authNtlm('the-username:the-password')
    ;
};

$response = $client->request('GET', 'https://...', [
    // yalnızca bu istek için farklı bir HTTP Basic authentication kullan
    'auth_basic' => ['the-username', 'the-password'],

    // ...
]);
```

Basic Authentication ayrıca kimlik bilgilerini URL’ye dahil ederek de ayarlanabilir, örneğin:

`http://the-username:the-password@example.com`

NTLM kimlik doğrulama mekanizması, cURL transport’unun kullanılmasını gerektirir. `HttpClient::createForBaseUri()` kullanarak kimlik bilgilerini yalnızca `https://example.com/` dışındaki host’lara gönderilmeyecek şekilde güvence altına alabilirsiniz.

---

### Sorgu Dizesi Parametreleri (Query String Parameters)

Bunları isteğin URL’sine manuel olarak ekleyebilir veya `query` seçeneği aracılığıyla ilişkilendirilmiş bir dizi olarak tanımlayabilirsiniz; bu, URL ile birleştirilir:

```php
// https://httpbin.org/get?token=...&name=... adresine bir HTTP GET isteği yapar
$response = $client->request('GET', 'https://httpbin.org/get', [
    // bu değerler URL’ye eklenmeden önce otomatik olarak encode edilir
    'query' => [
        'token' => '...',
        'name' => '...',
    ],
]);
```

---

### Header’lar

Tüm isteklere eklenecek varsayılan header’ları tanımlamak için `headers` seçeneğini kullanın:

```php
// config/packages/framework.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->httpClient()
        ->defaultOptions()
            ->header('User-Agent', 'My Fancy App')
    ;
};
```

Belirli istekler için yeni header’lar ekleyebilir veya varsayılan olanları geçersiz kılabilirsiniz:

```php
// bu header yalnızca bu istekte dahil edilir ve aynı header global olarak tanımlanmışsa onu geçersiz kılar
$response = $client->request('POST', 'https://...', [
    'headers' => [
        'Content-Type' => 'text/plain',
    ],
]);
```

---

### Veri Yükleme (Uploading Data)

Bu bileşen, `body` seçeneğini kullanarak veri yüklemek için çeşitli yöntemler sunar. Düz string’ler, closure’lar, iterable’lar ve resource’lar kullanılabilir; istek yapılırken bunlar otomatik olarak işlenir:

```php
$response = $client->request('POST', 'https://...', [
    // düz bir string kullanarak veri tanımlama
    'body' => 'raw data',

    // parametre dizisi kullanarak veri tanımlama
    'body' => ['parameter1' => 'value1', '...'],

    // yüklenen veriyi üretmek için bir closure kullanma
    'body' => function (int $size): string {
        // ...
    },

    // bir resource kullanarak veriyi buradan alma
    'body' => fopen('/path/to/file', 'r'),
]);
```

POST yöntemiyle veri yüklerken, `Content-Type` HTTP header’ını açıkça tanımlamazsanız Symfony, form verisi yüklediğinizi varsayar ve sizin için gerekli `'Content-Type: application/x-www-form-urlencoded'` header’ını ekler.

`body` seçeneği bir closure olarak ayarlandığında, bu closure boş bir string döndürünceye kadar birkaç kez çağrılır; bu, body’nin sonunu belirtir. Her seferinde closure, argüman olarak verilen miktardan daha küçük bir string döndürmelidir.

Bir generator veya herhangi bir `Traversable` da closure yerine kullanılabilir.

---

JSON verilerini yüklerken `body` yerine `json` seçeneğini kullanın. Sağlanan içerik otomatik olarak JSON-encode edilir ve isteğe `Content-Type: application/json` header’ı otomatik olarak eklenir:

```php
$response = $client->request('POST', 'https://...', [
    'json' => ['param1' => 'value1', '...'],
]);

$decodedPayload = $response->toArray();
```

Dosya yüklemeli bir form göndermek için dosya tanıtıcısını `body` seçeneğine iletin:

```php
$fileHandle = fopen('/path/to/the/file', 'r');
$client->request('POST', 'https://...', ['body' => ['the_file' => $fileHandle]]);
```

Varsayılan olarak bu kod, dosya adını ve içerik türünü (content-type) açılan dosyanın verilerinden doldurur, ancak her ikisini de PHP streaming yapılandırmasıyla ayarlayabilirsiniz:

```php
stream_context_set_option($fileHandle, 'http', 'filename', 'the-name.txt');
stream_context_set_option($fileHandle, 'http', 'content_type', 'my/content-type');
```


Çok Boyutlu Diziler Kullanırken

FormDataPart sınıfı, alan adının sonuna otomatik olarak `[key]` ekler:

```php
$formData = new FormDataPart([
    'array_field' => [
        'some value',
        'other value',
    ],
]);

$formData->getParts(); // İki adet TextPart örneği döndürür
                       // adları "array_field[0]" ve "array_field[1]" olur
```

Bu davranış aşağıdaki dizi yapısı kullanılarak atlanabilir:

```php
$formData = new FormDataPart([
    ['array_field' => 'some value'],
    ['array_field' => 'other value'],
]);

$formData->getParts(); // İki adet TextPart örneği döndürür
                       // her ikisinin adı da "array_field" olur
```

Her form parçasının Content-Type değeri otomatik olarak algılanır. Ancak, bunu bir `DataPart` geçirerek geçersiz kılabilirsiniz:

```php
use Symfony\Component\Mime\Part\DataPart;

$formData = new FormDataPart([
    ['json_data' => new DataPart(json_encode($json), null, 'application/json')]
]);
```

Varsayılan olarak HttpClient, body içeriğini yüklerken stream eder. Ancak bu durum, bazı sunucularda `Content-Length` header’ı olmadığından HTTP durum kodu 411 (“Length Required”) hatasına yol açabilir. Çözüm, aşağıdaki yöntemi kullanarak body’yi string’e dönüştürmektir (büyük stream’lerde bellek tüketimi artar):

```php
$client->request('POST', 'https://...', [
    // ...
    'body' => $formData->bodyToString(),
    'headers' => $formData->getPreparedHeaders()->toArray(),
]);
```

Yüklemeye özel bir HTTP header eklemeniz gerekirse şunu yapabilirsiniz:

```php
$headers = $formData->getPreparedHeaders()->toArray();
$headers[] = 'X-Foo: bar';
```

---

### Çerezler (Cookies)

Bu bileşenin sağladığı HTTP client stateless’tir, ancak çerezlerin yönetimi stateful bir depolama gerektirir (çünkü yanıtlar çerezleri güncelleyebilir ve bunlar sonraki isteklerde kullanılmalıdır). Bu nedenle bu bileşen çerezleri otomatik olarak işlemez.

Çerezleri HttpClient bileşeniyle sorunsuz bir şekilde entegre olan BrowserKit bileşeniyle gönderebilir veya manuel olarak `Cookie` HTTP header’ını ayarlayabilirsiniz:

```php
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Cookie;

$client = HttpClient::create([
    'headers' => [
        // tek bir çerezi name=value çifti olarak ayarlayın
        'Cookie' => 'flavor=chocolate',

        // birden fazla çerezi ; ile ayırarak aynı anda ayarlayabilirsiniz
        'Cookie' => 'flavor=chocolate; size=medium',

        // gerekirse, çerez değerini geçerli karakterler içerdiğinden emin olmak için encode edin
        'Cookie' => sprintf("%s=%s", 'foo', rawurlencode('...')),
    ],
]);
```

---

### Yönlendirmeler (Redirects)

Varsayılan olarak HTTP client, bir istek yapılırken en fazla 20 yönlendirmeyi takip eder. Bu davranışı `max_redirects` ayarıyla yapılandırabilirsiniz (belirtilen değerden fazla yönlendirme olursa `RedirectionException` alınır):

```php
$response = $client->request('GET', 'https://...', [
    // 0 hiçbir yönlendirmeyi takip etmemek anlamına gelir
    'max_redirects' => 0,
]);
```

---

### Başarısız İstekleri Tekrarlama (Retry Failed Requests)

Bazen istekler ağ sorunları veya geçici sunucu hataları nedeniyle başarısız olur. Symfony’nin HttpClient bileşeni, başarısız istekleri otomatik olarak yeniden denemeyi `retry_failed` seçeneğiyle sağlar.

Varsayılan olarak başarısız istekler en fazla 3 kez yeniden denenir; yeniden denemeler arasında üstel bir gecikme vardır (ilk tekrar = 1 saniye; üçüncü tekrar = 4 saniye) ve yalnızca şu HTTP durum kodları için geçerlidir:

423, 425, 429, 502 ve 503 (tüm HTTP metodları için)

500, 504, 507 ve 510 (HTTP idempotent metodlar için).

Bir isteğin kaç kez yeniden deneneceğini yapılandırmak için `max_retries` ayarını kullanabilirsiniz.

Tüm yapılandırılabilir `retry_failed` seçeneklerinin tam listesine bakarak uygulamanız için uygun şekilde ince ayar yapabilirsiniz.

Symfony uygulaması dışında HttpClient kullanırken, orijinal HTTP client’ınızı `RetryableHttpClient` sınıfıyla sarmalayın:

```php
use Symfony\Component\HttpClient\RetryableHttpClient;

$client = new RetryableHttpClient(HttpClient::create());
```

`RetryableHttpClient`, bir isteğin yeniden denenip denenmeyeceğine ve her deneme arasındaki bekleme süresine karar vermek için `RetryStrategyInterface` kullanır.

---

### Birden Fazla Base URI Üzerinde Tekrar Deneme (Retry Over Several Base URIs)

`RetryableHttpClient`, birden fazla base URI kullanacak şekilde yapılandırılabilir. Bu özellik, HTTP isteklerinde esneklik ve güvenilirliği artırır. `base_uri` seçeneğine bir dizi URI geçin:

```php
$response = $client->request('GET', 'some-page', [
    'base_uri' => [
        // ilk istek bu base URI’yi kullanır
        'https://example.com/a/',
        // ilk istek başarısız olursa, sonraki base URI kullanılır
        'https://example.com/b/',
    ],
]);
```

Yeniden deneme sayısı base URI sayısından fazla olursa, kalan denemeler için son base URI kullanılır.

Her yeniden denemede base URI’lerin sırasını karıştırmak isterseniz, karıştırmak istediğiniz URI’leri ek bir dizi içinde gruplayın:

```php
$response = $client->request('GET', 'some-page', [
    'base_uri' => [
        [
            // bu diziden rastgele bir URI ilk istek için seçilir
            'https://example.com/a/',
            'https://example.com/b/',
        ],
        // iç içe olmayan base URI’ler sırayla kullanılır
        'https://example.com/c/',
    ],
]);
```

Bu özellik, tekrar denemelerde aynı başarısız URI’ye sürekli istek göndermeyi önleyen daha rastgele bir yaklaşım sağlar.

Ayrıca bu yöntem, bir sunucu kümesinde yük dağıtımı yapmak için de kullanılabilir.

`withOptions()` metoduyla da base URI dizisini yapılandırabilirsiniz:

```php
$client = $client->withOptions(['base_uri' => [
    'https://example.com/a/',
    'https://example.com/b/',
]]);
```

---

### HTTP Proxy’leri (HTTP Proxies)

Varsayılan olarak bu bileşen, işletim sisteminizin HTTP trafiğini yerel proxy üzerinden yönlendirmek için tanımladığı standart ortam değişkenlerine uyar. Bu nedenle, proxy’ler düzgün yapılandırılmışsa genellikle ek bir ayar yapmanız gerekmez.

Yine de bu ayarları `proxy` ve `no_proxy` seçenekleriyle belirleyebilir veya geçersiz kılabilirsiniz:

* `proxy`: proxy üzerinden geçmek için `http://...` URL’si olmalıdır.
* `no_proxy`: proxy’ye ihtiyaç duymayan host’ların virgülle ayrılmış listesi.

---

### İlerleme Geri Çağrısı (Progress Callback)

`on_progress` seçeneğine bir callable sağlayarak yükleme/indirme işlemlerinin ilerlemesini takip edebilirsiniz. Bu callback; DNS çözümlemesi, header’ların alınması ve tamamlanma sırasında garanti edilir; ayrıca yeni veri yüklendiğinde veya indirildiğinde ve en az her saniyede bir çağrılır:

```php
$response = $client->request('GET', 'https://...', [
    'on_progress' => function (int $dlNow, int $dlSize, array $info): void {
        // $dlNow şu ana kadar indirilen byte sayısıdır
        // $dlSize indirilecek toplam boyuttur veya bilinmiyorsa -1’dir
        // $info, o anda $response->getInfo()’nun döndüreceği bilgidir
    },
]);
```

Callback’ten atılan istisnalar `TransportExceptionInterface` örneğine sarılır ve isteği iptal eder.

---

### HTTPS Sertifikaları (HTTPS Certificates)

HttpClient, SSL sertifikalarını doğrulamak için sistemin sertifika deposunu kullanır (tarayıcılar kendi depolarını kullanır).

Geliştirme sırasında self-signed sertifikalar kullanıyorsanız, kendi Certificate Authority (CA) sertifikanızı oluşturup sisteminize eklemeniz önerilir.

Alternatif olarak, `verify_host` ve `verify_peer` seçeneklerini devre dışı bırakabilirsiniz (bkz. http_client config reference), ancak bu yöntem üretim ortamında önerilmez.


## SSRF (Server-side request forgery) İşleme

SSRF, bir saldırganın arka uç uygulamasını rastgele bir etki alanına HTTP isteği yapmaya yönlendirmesine olanak tanır. Bu saldırılar, hedef sunucunun dahili host ve IP’lerini de hedefleyebilir.

HttpClient’i kullanıcı tarafından sağlanan URI’lerle birlikte kullanıyorsanız, bunu bir NoPrivateNetworkHttpClient ile sarmalamak iyi bir fikirdir. Bu, yerel ağların HTTP client tarafından erişilemez hale getirilmesini sağlar:

```php
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\NoPrivateNetworkHttpClient;

$client = new NoPrivateNetworkHttpClient(HttpClient::create());
// genel ağlara istek yapılırken hiçbir şey değişmez
$client->request('GET', 'https://example.com/');

// ancak, özel ağlara yapılan tüm istekler varsayılan olarak artık engellenir
$client->request('GET', 'http://localhost/');

// ikinci isteğe bağlı argüman, engellenecek ağları tanımlar
// bu örnekte, 104.26.14.0 ile 104.26.15.255 arasındaki isteklere bir istisna fırlatılır
// ancak diğer tüm istekler, diğer dahili ağlar dahil, izinli olacaktır
$client = new NoPrivateNetworkHttpClient(HttpClient::create(), ['104.26.14.0/23']);
```

Profiling

TraceableHttpClient kullanırken, yanıt içerikleri bellekte tutulur ve belleğin tükenmesine yol açabilir.

Bu davranışı, isteklerinizde `extra.trace_content` seçeneğini `false` yaparak devre dışı bırakabilirsiniz:

```php
$response = $client->request('GET', 'https://...', [
    'extra' => ['trace_content' => false],
]);
```

Bu ayar diğer client’ları etkilemez.

URI Şablonlarını Kullanma

UriTemplateHttpClient, RFC 6570’de açıklandığı şekilde URI şablonlarının kullanımını kolaylaştıran bir client sağlar:

```php
$client = new UriTemplateHttpClient();

// bu, http://example.org/users?page=1 adresine bir istek yapar
$client->request('GET', 'http://example.org/{resource}{?page}', [
    'vars' => [
        'resource' => 'users',
        'page' => 1,
    ],
]);
```

Uygulamalarınızda URI şablonlarını kullanmadan önce, bu şablonları genişletip URL’lere dönüştüren üçüncü taraf paketi kurmanız gerekir:

```
composer require league/uri
```

Bu client’ı framework bağlamında kullanırken, mevcut tüm HTTP client’lar UriTemplateHttpClient tarafından süslenir (decorate edilir). Bu, uygulamanızda kullanabileceğiniz tüm HTTP client’larda URI şablonu özelliğinin varsayılan olarak etkin olduğu anlamına gelir.

Uygulamanızdaki tüm URI şablonlarında global olarak değiştirilecek değişkenleri yapılandırabilirsiniz:

```php
// config/packages/framework.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework) {
    $framework->httpClient()
        ->defaultOptions()
            ->vars(['secret' => 'secret-token'])
    ;
};
```

URI şablonlarındaki değişkenleri ele almak için kendi mantığınızı tanımlamak isterseniz, `http_client.uri_template_expander` alias’ını yeniden tanımlayabilirsiniz. Servisinizin çağrılabilir (invokable) olması gerekir.

Performans

Bileşen, maksimum HTTP performansı için tasarlanmıştır. Tasarım gereği, HTTP/2 ile ve eşzamanlı asenkron, akış (stream) ve çoklama (multiplexed) istek/yanıtlarla uyumludur. Düzenli senkron çağrılar yapılsa bile, bu tasarım, uzak host’lara olan bağlantıların istekler arasında açık tutulmasına olanak tanır; bu da tekrar eden DNS çözümlemesi, SSL müzakeresi vb. işlemleri azaltarak performansı artırır. Bu tasarım avantajlarından tam olarak yararlanmak için cURL uzantısı gereklidir.

cURL Desteğini Etkinleştirme

Bu bileşen, yerel PHP stream’lerini ve amphp/http-client ile cURL kütüphanelerini kullanarak HTTP istekleri yapabilir. Birbirlerinin yerine kullanılabilirler ve eşzamanlı istekler de dahil olmak üzere aynı özellikleri sağlarlar; ancak HTTP/2 yalnızca cURL veya amphp/http-client kullanıldığında desteklenir.

AmpHttpClient’ı kullanmak için `amphp/http-client` paketinin kurulu olması gerekir.

`create()` metodu, cURL PHP uzantısı etkinse cURL transport’unu seçer. cURL bulunamazsa veya çok eskiyse AmpHttpClient’a geri döner (fallback). Son olarak, AmpHttpClient mevcut değilse PHP stream’lerine geri döner. Transport’u açıkça seçmeyi tercih ederseniz, client’ı oluşturmak için aşağıdaki sınıfları kullanın:

```php
use Symfony\Component\HttpClient\AmpHttpClient;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\NativeHttpClient;

// yerel PHP stream’lerini kullanır
$client = new NativeHttpClient();

// cURL PHP uzantısını kullanır
$client = new CurlHttpClient();

// `amphp/http-client` paketindeki client’ı kullanır
$client = new AmpHttpClient();
```

Bu bileşeni tam yığın bir Symfony uygulamasında kullanırken, bu davranış yapılandırılabilir değildir; cURL PHP uzantısı kurulu ve etkinse otomatik olarak cURL kullanılır ve yukarıda açıklandığı şekilde geri dönüş yapılır.

CurlHttpClient Seçeneklerini Yapılandırma

PHP, `curl_setopt` fonksiyonu aracılığıyla birçok cURL seçeneğini yapılandırmaya izin verir. Bileşenin cURL kullanılmadığında daha taşınabilir olmasını sağlamak için, CurlHttpClient bu seçeneklerin yalnızca bir kısmını kullanır (diğer client’larda bu seçenekler yok sayılır).

Bu ek seçenekleri geçirmek için yapılandırmanıza `extra.curl` seçeneğini ekleyin:

```php
use Symfony\Component\HttpClient\CurlHttpClient;

$client = new CurlHttpClient();

$client->request('POST', 'https://...', [
    // ...
    'extra' => [
        'curl' => [
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V6,
        ],
    ],
]);
```

Bazı cURL seçeneklerinin (ör. iş parçacığı güvenliği nedeniyle) üzerine yazmak imkânsızdır ve bunları değiştirmeye çalıştığınızda bir istisna alırsınız.

HTTP Sıkıştırma

`Accept-Encoding: gzip` HTTP header’ı aşağıdaki durumlarda otomatik olarak eklenir:

* cURL client’ı kullanırken: cURL, ZLib desteğiyle derlenmişse (bkz. `php --ri curl`)
* yerel HTTP client’ı kullanırken: Zlib PHP uzantısı yüklüyse

Sunucu sıkıştırılmış (gzip) bir yanıt döndürürse, bu yanıt şeffaf şekilde açılır. HTTP sıkıştırmayı devre dışı bırakmak için `Accept-Encoding: identity` header’ını gönderin.

`Chunked transfer encoding`, hem PHP çalışma zamanınız hem de uzak sunucu bunu destekliyorsa otomatik olarak etkinleştirilir.

`Accept-Encoding` değerini örneğin `gzip` olarak ayarlarsanız, sıkıştırmayı kendiniz ele almanız gerekir.

HTTP/2 Desteği

`https` bir URL istenirken, aşağıdaki araçlardan biri kuruluysa HTTP/2 varsayılan olarak etkindir:

* libcurl paket sürümü 7.36 veya üstü; PHP >= 7.2.17 / 7.3.4 ile kullanıldığında
* amphp/http-client Packagist paketi sürüm 4.2 veya üstü

`http` URL’leri için HTTP/2’yi zorlamak isterseniz, `http_version` seçeneğiyle bunu açıkça etkinleştirmeniz gerekir:

```php
// config/packages/framework.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->httpClient()
        ->defaultOptions()
            ->httpVersion('2.0')
    ;
};
```

HTTP/2 PUSH desteği, uyumlu bir client kullanırken kutudan çıktığı gibi çalışır: itilen (pushed) yanıtlar geçici bir cache’e konur ve karşılık gelen URL’ler için daha sonra bir istek tetiklendiğinde kullanılır.

Yanıtları İşleme

Tüm HTTP client’larının döndürdüğü yanıt, aşağıdaki metodları sağlayan `ResponseInterface` tipinde bir nesnedir:

```php
$response = $client->request('GET', 'https://...');

// yanıtın HTTP durum kodunu alır
$statusCode = $response->getStatusCode();

// HTTP header’ları, adları küçük harfe çevrilmiş string[][] olarak alır
$headers = $response->getHeaders();

// yanıt gövdesini string olarak alır
$content = $response->getContent();

// yanıtın JSON içeriğini PHP dizisine dönüştürür
$content = $response->toArray();

// yanıt içeriğini bir PHP stream kaynağına dönüştürür
$content = $response->toStream();

// istek/yanıtı iptal eder
$response->cancel();

// "response_headers", "redirect_count", "start_time", "redirect_url" vb. gibi
// transport katmanından gelen bilgileri döndürür
$httpInfo = $response->getInfo();

// tekil bilgileri de alabilirsiniz
$startTime = $response->getInfo('start_time');
// ör. bu, (gerekirse yönlendirmeleri çözüp) nihai yanıt URL’sini döndürür
$url = $response->getInfo('url');

// HTTP işleminin istek ve yanıtlarına ilişkin ayrıntılı log’ları döndürür
$httpLogs = $response->getInfo('debug');

// özel "pause_handler" info öğesi, isteği belirli saniye kadar geciktirmeye
// olanak tanıyan bir callable’dır; bu sayede yeniden denemeleri geciktirebilir,
// akışları sınırlayabilirsiniz vb.
$response->getInfo('pause_handler')(2);
```

`$response->toStream()`; `StreamableInterface`’in bir parçasıdır.

`$response->getInfo()` bloklamaz: yanıtla ilgili canlı bilgileri döndürür. Çağırdığınız anda bazıları henüz bilinmiyor olabilir (ör. `http_code`).

Yanıtları Akışla (Streaming) Alma

Tüm yanıtı beklemek yerine `stream()` metodunu çağırarak yanıtın parçalarını sırasıyla alın:

```php
$url = 'https://releases.ubuntu.com/18.04.1/ubuntu-18.04.1-desktop-amd64.iso';
$response = $client->request('GET', $url);

// Yanıtlar tembeldir (lazy): bu kod, header’lar alındığı anda çalışır
if (200 !== $response->getStatusCode()) {
    throw new \Exception('...');
}

// yanıt içeriğini parça parça alıp bir dosyaya kaydedin
// yanıt parçaları Symfony\Contracts\HttpClient\ChunkInterface’i uygular
$fileHandler = fopen('/ubuntu.iso', 'w');
foreach ($client->stream($response) as $chunk) {
    fwrite($fileHandler, $chunk->getContent());
}
```

Varsayılan olarak `text/*`, JSON ve XML yanıt gövdeleri yerel bir `php://temp` stream’inde arabelleğe alınır. Bu davranışı `buffer` seçeneğiyle kontrol edebilirsiniz: arabelleğe almayı etkinleştirmek/devre dışı bırakmak için `true/false` yapın veya aldığı yanıt header’larına göre aynı değeri döndürmesi gereken bir closure verin.


Yanıtları İptal Etme (Canceling Responses)

Bir isteği iptal etmek için (örneğin, belirli bir sürede tamamlanmadığı için veya yalnızca yanıtın ilk birkaç baytını almak istediğinizde) `cancel()` metodunu kullanabilirsiniz:

```php
$response->cancel();
```

Ya da bir progress callback içinden istisna fırlatabilirsiniz:

```php
$response = $client->request('GET', 'https://...', [
    'on_progress' => function (int $dlNow, int $dlSize, array $info): void {
        // ...

        throw new \MyException();
    },
]);
```

Bu istisna, `TransportExceptionInterface` örneğine sarılır ve isteği iptal eder.

Yanıt `$response->cancel()` ile iptal edilmişse, `$response->getInfo('canceled')` değeri `true` döner.

---

### İstisnaları Yönetme (Handling Exceptions)

Üç tür istisna vardır ve hepsi `ExceptionInterface`’i uygular:

* **HttpExceptionInterface** : Kodunuz 300–599 aralığındaki durum kodlarını (status code) işlemediğinde fırlatılır.
* **TransportExceptionInterface** : Alt düzey bir sorun (ör. bağlantı hatası) oluştuğunda fırlatılır.
* **DecodingExceptionInterface** : İçerik türü beklenen biçime dönüştürülemediğinde fırlatılır.

Yanıtın HTTP durum kodu 300–599 aralığındaysa (`3xx`, `4xx`, `5xx`), `getHeaders()`, `getContent()` ve `toArray()` metotları uygun bir istisna fırlatır; bunların tümü `HttpExceptionInterface`’i uygular.

Bu istisnalardan kaçınmak ve 300–599 durum kodlarını kendiniz ele almak isterseniz, bu metot çağrılarına opsiyonel argüman olarak `false` geçin:

```php
$response->getHeaders(false);
```

Bu üç metottan hiçbirini çağırmazsanız, istisna `$response` nesnesi yok edilirken yine fırlatılır.

`$response->getStatusCode()` çağrısı bu davranışı devre dışı bırakmak için yeterlidir (ancak o zaman durum kodunu kendiniz kontrol etmeyi unutmayın).

Yanıtlar “lazy” olduğundan, destructor her zaman header’ların geri gelmesini bekler. Bu, aşağıdaki isteğin tamamlanacağı anlamına gelir; örneğin bir `404` dönerse istisna fırlatılır:

```php
// dönen değer bir değişkene atanmadığı için, dönen yanıtın
// destructor’ı hemen çağrılır ve 300–599 aralığında bir durum kodu varsa istisna atılır
$client->request('POST', 'https://...');
```

Bu nedenle, değişkene atanmamış yanıtlar senkron istekler olarak davranır.

Bu istekleri eşzamanlı yapmak istiyorsanız, karşılık gelen yanıtları bir dizide depolayabilirsiniz:

```php
$responses[] = $client->request('POST', 'https://.../path1');
$responses[] = $client->request('POST', 'https://.../path2');
// ...

// Bu satır, dizide saklanan tüm yanıtların destructor’larını tetikler;
// yanıtlar eşzamanlı olarak tamamlanır ve eğer 300–599 aralığında bir durum kodu dönerse istisna atılır
unset($responses);
```

Bu davranış, bileşenin “fail-safe” tasarımının bir parçasıdır. Hatalar gözden kaçmaz: hata işleme kodu yazmazsanız istisnalar sizi uyarır; ancak `$response->getStatusCode()` çağırarak hata yönetimi yaptığınızda, destructor’da yapılacak bir işlem kalmadığından bu mekanizma devre dışı kalır.

---

### Eşzamanlı İstekler (Concurrent Requests)

Symfony’nin HTTP client’ı varsayılan olarak asenkron istekler gönderir. Bu, birden fazla isteği paralel göndermek ve verimli şekilde işlemek için özel bir yapılandırma gerekmediği anlamına gelir.

Aşağıda, Packagist API’sinden aynı anda birkaç Symfony bileşeninin meta verilerini çeken bir örnek bulunmaktadır:

```php
$packages = ['console', 'http-kernel', '...', 'routing', 'yaml'];
$responses = [];

foreach ($packages as $package) {
    $uri = sprintf('https://repo.packagist.org/p2/symfony/%s.json', $package);
    // tüm istekleri aynı anda gönder (yanıtlar okunana kadar bloklanmaz)
    $responses[$package] = $client->request('GET', $uri);
}

$results = [];
// yanıtlar üzerinde gezinip içeriklerini oku
foreach ($responses as $package => $response) {
    $results[$package] = $response->toArray();
}
```

Görüldüğü gibi, istekler ilk döngüde gönderilir, ancak yanıtlar ikinci döngüde tüketilir.

Bu, paralel çalışmanın anahtarıdır: tüm istekleri önce gönderin, sonra okuyun.

Bu sayede client, bekleyen tüm yanıtları verimli şekilde işler ve kodunuz yalnızca gerektiğinde bekler.

Açık bağlantı sayısı sistem kaynaklarınıza bağlıdır (ör. işletim sistemi aynı anda kaç bağlantı kurulabileceğini sınırlayabilir). Bu sınırlara ulaşmamak için istekleri partiler halinde işlemeniz önerilir.

Ayrıca, her host için açık olabilecek bağlantı sayısının bir üst limiti vardır (varsayılan olarak 6). Ayrıntı için `max_host_connections` seçeneğine bakın.

---

### Çoklama (Multiplexing) Yanıtlar

Önceki örnekte, yanıtlar gönderilme sırasına göre okunur. Ancak örneğin, ikinci yanıt birinciden önce gelebilir.

Bu tür durumları verimli şekilde yönetmek için, yanıtların hangi sırayla geldiğine bakmadan tamamen asenkron işlem yapabilmek gerekir.

Bunu başarmak için `stream()` metodu kullanılabilir. Bu metod, ağ üzerinden gelen yanıt parçalarını (chunks) anında döndürür.

Aşağıdaki döngü, tamamen asenkron davranışı etkinleştirir:

```php
foreach ($client->stream($responses) as $response => $chunk) {
    if ($chunk->isFirst()) {
        // $response header’ları yeni geldi
        // $response->getHeaders() artık bloklamaz
    } elseif ($chunk->isLast()) {
        // $response gövdesinin tamamı alındı
        // $response->getContent() artık bloklamaz
    } else {
        // $chunk->getContent(), yeni gelen gövde parçasını döndürür
    }
}
```

Her yanıtı tanımlamak için `user_data` seçeneğini ve `$response->getInfo('user_data')` değerini birlikte kullanabilirsiniz.

---

### Ağ Zaman Aşımı (Network Timeouts) Yönetimi

Bu bileşen, istek ve yanıt zaman aşımı durumlarını yönetmenizi sağlar.

Zaman aşımı, örneğin DNS çözümlemesi çok uzun sürdüğünde, TCP bağlantısı belirlenen sürede kurulamadığında veya yanıt içeriği uzun süre durakladığında meydana gelebilir.

Bu durum `timeout` seçeneğiyle yapılandırılabilir:

```php
// Eğer 2.5 saniye boyunca hiçbir şey olmazsa, TransportExceptionInterface fırlatılır
$response = $client->request('GET', 'https://...', ['timeout' => 2.5]);
```

Eğer seçenek belirtilmezse, `default_socket_timeout` ini ayarı kullanılır.

Bu seçenek, `stream()` metodunun ikinci argümanıyla da geçersiz kılınabilir.

Bu sayede birden fazla yanıt aynı anda izlenebilir ve hepsine grup olarak zaman aşımı uygulanabilir.

Tüm yanıtlar belirtilen süre boyunca etkin değilse, `isTimeout()` değeri `true` olan özel bir chunk döner:

```php
foreach ($client->stream($responses, 1.5) as $response => $chunk) {
    if ($chunk->isTimeout()) {
        // $response 1.5 saniyeden uzun süredir durgun (stale)
    }
}
```

Zaman aşımı her zaman bir hata değildir; yeniden stream edip kalan verileri alabilirsiniz.

`timeout` değerini `0` yapmak, yanıtları bloklamadan izlemenizi sağlar.

`max_duration` seçeneğini kullanarak, bir istek/yanıt döngüsünün tamamının süresini sınırlayabilirsiniz.

---

### Ağ Hatalarıyla Başa Çıkma (Dealing with Network Errors)

Ağ hataları (ör. broken pipe, başarısız DNS çözümlemesi vb.) `TransportExceptionInterface` örnekleri olarak fırlatılır.

Bu hatalarla özel olarak uğraşmanız gerekmez; çoğu durumda istisnaların genel hata yönetim mekanizmanıza bırakılması yeterlidir.

Ancak ele almak isterseniz:

Ağ hatalarını yakalamak için yalnızca `$client->request()` çağrısını değil, aynı zamanda dönen yanıt nesnesinin tüm metod çağrılarını da sarmalamanız gerekir.

Çünkü yanıtlar “lazy”dir; dolayısıyla `getStatusCode()` çağrısı gibi işlemler de ağ hatası üretebilir:

```php
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

try {
    // her iki satır da potansiyel olarak istisna fırlatabilir
    $response = $client->request(/* ... */);
    $headers = $response->getHeaders();
} catch (TransportExceptionInterface $e) {
    // ...
}
```

`$response->getInfo()` bloklamaz ve tasarımı gereği istisna fırlatmaz.

Çoklu yanıtları işlerken, bireysel stream hatalarını aşağıdaki gibi `TransportExceptionInterface` yakalayarak yönetebilirsiniz:

```php
foreach ($client->stream($responses) as $response => $chunk) {
    try {
        if ($chunk->isTimeout()) {
            // zaman aşımı olduğunda ne yapılacağını belirleyin
            // zaman aşımına uğrayan bir yanıtı durdurmak istiyorsanız
            // $response->cancel() çağırmayı unutmayın
        } elseif ($chunk->isFirst()) {
            // durum kodunu kontrol etmek istiyorsanız,
            // ilk chunk geldiğinde $response->getStatusCode() kullanın
        } elseif ($chunk->isLast()) {
            // ... yanıtla bir işlem yapın
        }
    } catch (TransportExceptionInterface $e) {
        // ...
    }
}
```



İstekleri ve Yanıtları Önbelleğe Alma (Caching Requests and Responses)

Bu bileşen, yanıtları önbelleğe almayı ve sonraki isteklerde bunları yerel depolamadan sunmayı sağlayan bir `CachingHttpClient` dekoratörü sunar. Uygulama, temelde `HttpCache` sınıfını kullanır, bu nedenle uygulamanızda `HttpKernel` bileşeninin kurulu olması gerekir:

```php
use Symfony\Component\HttpClient\CachingHttpClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpKernel\HttpCache\Store;

$store = new Store('/path/to/cache/storage/');
$client = HttpClient::create();
$client = new CachingHttpClient($client, $store);

// kaynak önbellekte zaten varsa ağ üzerinden istek yapılmaz
$response = $client->request('GET', 'https://example.com/cacheable-resource');
```

`CachingHttpClient`, üçüncü bir argüman olarak `HttpCache` seçeneklerini belirlemenize izin verir.

---

### İstek Sayısını Sınırlama (Limit the Number of Requests)

Bu bileşen, belirli bir süre içinde yapılabilecek istek sayısını sınırlamayı sağlayan bir `ThrottlingHttpClient` dekoratörü sunar; hız sınırlama (rate limiting) politikasına göre çağrıları geciktirebilir.

Uygulama, `LimiterInterface` sınıfını kullanır; bu nedenle uygulamanızda `RateLimiter` bileşeni kurulu olmalıdır:

```php
// config/packages/framework.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->httpClient()->scopedClient('example.client')
        ->baseUri('https://example.com')
        ->rateLimiter('http_example_limiter');
        // ...
    ;

    $framework->rateLimiter()
        // 5 saniyede 10’dan fazla istek gönderme
        ->limiter('http_example_limiter')
            ->policy('token_bucket')
            ->limit(10)
            ->rate()
                ->interval('5 seconds')
                ->amount(10)
        ;
};
```

🆕 `ThrottlingHttpClient`, Symfony 7.1’de tanıtılmıştır.

---

### Sunucudan Gönderilen Olayları Tüketme (Consuming Server-Sent Events)

Server-Sent Events (SSE), web sayfalarına veri göndermek için kullanılan bir internet standardıdır. JavaScript tarafında `EventSource` nesnesiyle dinlenir. Olaylar, aşağıdaki biçimde (`text/event-stream` MIME türüyle) veri akışı olarak sunulur:

```
data: This is the first message.

data: This is the second message, it
data: has two lines.

data: This is the third message.
```

Symfony’nin HTTP client’ı, bu olayları tüketmek için bir `EventSource` implementasyonu sağlar. `EventSourceHttpClient` kullanarak, `text/event-stream` yanıtı dönen bir sunucuya bağlanabilir ve akışı aşağıdaki gibi tüketebilirsiniz:

```php
use Symfony\Component\HttpClient\Chunk\ServerSentEvent;
use Symfony\Component\HttpClient\EventSourceHttpClient;

// ikinci opsiyonel argüman yeniden bağlanma süresidir (varsayılan = 10 saniye)
$client = new EventSourceHttpClient($client, 10);
$source = $client->connect('https://localhost:8080/events');

while ($source) {
    foreach ($client->stream($source, 2) as $r => $chunk) {
        if ($chunk->isTimeout()) {
            // ...
            continue;
        }

        if ($chunk->isLast()) {
            // ...
            return;
        }

        // gelen mesajı içeren özel ServerSentEvent chunk’ı
        if ($chunk instanceof ServerSentEvent) {
            // sunucudan gelen olayı işle...
        }
    }
}
```

Eğer `ServerSentEvent` içeriği JSON formatındaysa, doğrudan çözümlenmiş (decoded) diziyi almak için `getArrayData()` metodunu kullanabilirsiniz.

---

### Birlikte Çalışabilirlik (Interoperability)

Bu bileşen, HTTP client’lar için dört farklı soyutlama (abstraction) ile uyumludur:

* **Symfony Contracts**
* **PSR-18**
* **HTTPlug v1/v2**
* **Yerel PHP stream’leri**

Uygulamanız bu soyutlamalardan birini kullanan kütüphaneler içeriyorsa, bileşen bunlarla tamamen uyumludur. Framework Bundle kullanıldığında, tümü autowiring alias’larından da faydalanır.

Bir HTTP client implementasyonuna doğrudan bağımlı olmadan kütüphane yazmak veya bakımını yapmak istiyorsanız, **(önerilen)** Symfony Contracts, PSR-18 veya HTTPlug v2’ye göre kodlayabilirsiniz.

---

### Symfony Contracts

`symfony/http-client-contracts` paketindeki arayüzler, bu bileşen tarafından uygulanan temel soyutlamaları tanımlar. Giriş noktası `HttpClientInterface`’dir.

Bir client gerektiğinde bu arayüze göre kod yazmanız önerilir:

```php
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MyApiLayer
{
    public function __construct(
        private HttpClientInterface $client,
    ) {
    }

    // [...]
}
```

Yukarıda bahsedilen tüm istek seçenekleri (ör. timeout yönetimi), arayüzün tanımında belirtilmiştir. Böylece bu arayüzü uygulayan herhangi bir implementasyonun (örneğin bu bileşen) bunları desteklemesi garanti edilir.

Bu, taşımayla ilgili hiçbir detayı belirtmeyen diğer soyutlamalardan önemli bir farktır.

Symfony Contracts ayrıca asenkron/çoklama (async/multiplexing) özelliklerini de kapsar.

---

### PSR-18 ve PSR-17

Bu bileşen, **PSR-18 (HTTP Client)** standardını `Psr18Client` sınıfı aracılığıyla uygular.

Bu sınıf, bir `Symfony HttpClientInterface` client’ını PSR-18 `ClientInterface`’e dönüştüren bir adaptördür. Ayrıca PSR-17’deki bazı yardımcı metodları da uygular.

Kullanmak için `psr/http-client` paketini ve bir PSR-17 implementasyonunu kurun:

```
composer require psr/http-client
composer require nyholm/psr7
```

Artık PSR-18 client ile HTTP istekleri yapabilirsiniz:

```php
use Psr\Http\Client\ClientInterface;

class Symfony
{
    public function __construct(
        private ClientInterface $client,
    ) {
    }

    public function getAvailableVersions(): array
    {
        $request = $this->client->createRequest('GET', 'https://symfony.com/versions.json');
        $response = $this->client->sendRequest($request);

        return json_decode($response->getBody()->getContents(), true);
    }
}
```

Varsayılan seçenekleri ayarlamak için `Psr18Client::withOptions()` metodunu kullanabilirsiniz:

```php
use Symfony\Component\HttpClient\Psr18Client;

$client = (new Psr18Client())
    ->withOptions([
        'base_uri' => 'https://symfony.com',
        'headers' => [
            'Accept' => 'application/json',
        ],
    ]);

$request = $client->createRequest('GET', '/versions.json');
```

---

### HTTPlug

HTTPlug v1 standardı PSR-18’den önce yayımlandı ve artık onunla değiştirilmiştir. Yeni kodlarda kullanılmaması tavsiye edilir.

Yine de bu bileşen, `HttplugClient` sınıfı sayesinde HTTPlug’a ihtiyaç duyan kütüphanelerle uyumlu çalışabilir.

`HttplugClient`, ilgili `php-http/message-factory` paketindeki factory metodlarını da uygular.

```
composer require nyholm/psr7
```

Aşağıdaki gibi HTTPlug bağımlılıkları gerektiren bir sınıfınız olduğunu varsayalım:

```php
use Http\Client\HttpClient;
use Http\Message\StreamFactory;

class SomeSdk
{
    public function __construct(
        HttpClient $httpClient,
        StreamFactory $streamFactory
    ) {
        // [...]
    }
}
```

`HttplugClient` bu arayüzleri uyguladığı için şu şekilde kullanılabilir:

```php
use Symfony\Component\HttpClient\HttplugClient;

$httpClient = new HttplugClient();
$apiClient = new SomeSdk($httpClient, $httpClient);
```

Promises ile çalışmak isterseniz, `HttplugClient` aynı zamanda `HttpAsyncClient` arayüzünü de uygular.

Kullanmak için `guzzlehttp/promises` paketini kurun:

```
composer require guzzlehttp/promises
```

Sonra şu şekilde kullanabilirsiniz:

```php
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpClient\HttplugClient;

$httpClient = new HttplugClient();
$request = $httpClient->createRequest('GET', 'https://my.api.com/');
$promise = $httpClient->sendAsyncRequest($request)
    ->then(
        function (ResponseInterface $response): ResponseInterface {
            echo 'Got status ' . $response->getStatusCode();
            return $response;
        },
        function (\Throwable $exception): never {
            echo 'Error: ' . $exception->getMessage();
            throw $exception;
        }
    );

// birkaç istek gönderdikten sonra, bunların tamamlanmasını beklemeniz gerekir

// belirli bir promise’in çözülmesini beklerken hepsini izleyin
$response = $promise->wait();

// bekleyen promise’lerin çözülmesi için maksimum 1 saniye bekle
$httpClient->wait(1.0);

// kalan tüm promise’lerin çözülmesini bekle
$httpClient->wait();
```

Varsayılan seçenekleri belirlemek için `HttplugClient::withOptions()` metodunu da kullanabilirsiniz:

```php
use Symfony\Component\HttpClient\HttplugClient;

$httpClient = (new HttplugClient())
    ->withOptions([
        'base_uri' => 'https://my.api.com',
    ]);
$request = $httpClient->createRequest('GET', '/');
```



Yerel PHP Akışları (Native PHP Streams)

`ResponseInterface`’i uygulayan yanıtlar, `createResource()` metodu ile yerel PHP stream’lerine dönüştürülebilir. Bu sayede, PHP stream’lerinin gerektiği yerlerde doğrudan kullanılabilirler:

```php
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Response\StreamWrapper;

$client = HttpClient::create();
$response = $client->request('GET', 'https://symfony.com/versions.json');

$streamResource = StreamWrapper::createResource($response, $client);

// alternatif olarak, aşağıdaki yöntem önceki örneğin aksine
// seekable ve stream_select() ile kullanılabilir bir resource döndürür
$streamResource = $response->toStream();

echo stream_get_contents($streamResource); // yanıtın içeriğini ekrana yazdırır

// daha sonra gerekirse, stream’den yanıt nesnesine erişebilirsiniz
$response = stream_get_meta_data($streamResource)['wrapper_data']->getResponse();
```

---

### Genişletilebilirlik (Extensibility)

Bir temel HTTP client’ın davranışını genişletmek isterseniz, servis dekorasyonu kullanabilirsiniz:

```php
class MyExtendedHttpClient implements HttpClientInterface
{
    public function __construct(
        private ?HttpClientInterface $decoratedClient = null
    ) {
        $this->decoratedClient ??= HttpClient::create();
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        // $method, $url ve/veya $options üzerinde gerekli işlemleri yapın
        $response = $this->decoratedClient->request($method, $url, $options);

        // burada $response üzerinde herhangi bir metot çağırmak
        // HTTP isteğini asenkron olmaktan çıkarır; daha iyi bir yöntem için aşağıya bakın

        return $response;
    }

    public function stream($responses, ?float $timeout = null): ResponseStreamInterface
    {
        return $this->decoratedClient->stream($responses, $timeout);
    }
}
```

Bu tür bir dekoratör, yalnızca istek argümanlarını işlemek gerektiğinde faydalıdır.

`on_progress` seçeneğini dekore ederek temel yanıt izleme (monitoring) bile uygulanabilir.

Ancak `request()` içinde yanıt metotlarını çağırmak asenkronluğu bozacağı için önerilmez.

Bu durumu çözmek için yanıt nesnesini de dekore etmek gerekir.

`TraceableHttpClient` ve `TraceableResponse` bunun için iyi örneklerdir.

---

### AsyncDecoratorTrait Kullanımı

Daha gelişmiş yanıt işleyicileri yazmayı kolaylaştırmak için bileşen `AsyncDecoratorTrait` sağlar.

Bu trait, ağ üzerinden dönen chunk’ları (parçaları) işlerken bunlar üzerinde işlem yapmanıza olanak tanır:

```php
class MyExtendedHttpClient implements HttpClientInterface
{
    use AsyncDecoratorTrait;

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        // $method, $url veya $options üzerinde değişiklik yapın

        $passthru = function (ChunkInterface $chunk, AsyncContext $context): \Generator {
            // chunk’larla istediğinizi yapabilirsiniz — örn. parçaları
            // küçültebilir, gruplayabilir veya bazılarını atlayabilirsiniz

            yield $chunk;
        };

        return new AsyncResponse($this->client, $method, $url, $options, $passthru);
    }
}
```

Trait zaten bir kurucu (`__construct`) ve `stream()` metodunu uygular, bu nedenle bunları eklemenize gerek yoktur.

`request()` metodunu yine tanımlamalısınız; `AsyncResponse` döndürmelidir.

Chunk’ların özel işlenmesi `$passthru` içinde yapılır:

Bu generator, her chunk geldiğinde çağrılır.

Hiçbir şey yapmayan bir `$passthru` yalnızca `yield $chunk;` döndürür.

Chunk’ı değiştirebilir, bir chunk’tan birden fazla chunk üretebilir veya hiç yield etmeden tamamen atlayabilirsiniz (`return;`).

`AsyncContext`, stream akışını kontrol etmenizi sağlar.

Bu nesne, yanıtın mevcut durumunu okumak ve yeni chunk’lar oluşturmak, akışı duraklatmak, iptal etmek, yanıt bilgilerini değiştirmek, yeni istek oluşturmak veya mevcut `$passthru`’yu değiştirmek gibi işlemler için metotlar içerir.

`AsyncDecoratorTraitTest` içinde uygulanan test örneklerini incelemek, çeşitli senaryoları anlamak için iyi bir başlangıçtır.

Simüle edilen kullanım durumları:

* başarısız isteği yeniden denemek,
* kimlik doğrulama gibi işlemler için ön istek (preflight request) göndermek,
* alt istekler (subrequests) yapmak ve içeriklerini ana yanıt gövdesine dahil etmek.

`AsyncResponse` sınıfındaki mantık, chunk akışının doğru şekilde davranmasını garanti altına almak için çeşitli güvenlik kontrolleri içerir.

Örneğin, `isLast()` sonrası chunk üretilirse veya bir içerik chunk’ı `isFirst()`’ten önce yield edilirse `LogicException` fırlatılır.

---

### Test Etme (Testing)

Bu bileşen, gerçek HTTP istekleri yapılmadan testler yazabilmek için `MockHttpClient` ve `MockResponse` sınıflarını içerir.

Bu tür testler daha hızlı çalışır, dış servislere bağımlı olmadıkları için tutarlı sonuçlar üretir.

`MockHttpClient`, `HttpClientInterface`’i uygular.

Bu sayede kodunuzda gerçek client yerine test ortamında kolayca mock client kullanılabilir.

---

#### HTTP Client ve Yanıtlar

`MockHttpClient`’ı kullanmanın ilk yolu, yapıcıya bir yanıt listesi geçmektir.

Bu yanıtlar, yapılan isteklerde sırayla döndürülür:

```php
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

$responses = [
    new MockResponse($body1, $info1),
    new MockResponse($body2, $info2),
];

$client = new MockHttpClient($responses);
// yanıtlar, MockHttpClient’a geçildiği sırayla döner
$response1 = $client->request('...');
$response2 = $client->request('...');
```

Yanıtları dosyadan doğrudan oluşturmak da mümkündür — bu, yanıt snapshot’larını dosyalarda sakladığınız testler için özellikle kullanışlıdır:

```php
use Symfony\Component\HttpClient\Response\MockResponse;

$response = MockResponse::fromFile('tests/fixtures/response.xml');
```

🆕 `fromFile()` metodu Symfony 7.1’de tanıtılmıştır.

---

#### Callback ile Dinamik Yanıtlar

`MockHttpClient`’a bir callback geçerek dinamik olarak yanıt oluşturabilirsiniz:

```php
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

$callback = function ($method, $url, $options): MockResponse {
    return new MockResponse('...');
};

$client = new MockHttpClient($callback);
$response = $client->request('...'); // yanıt almak için $callback çağrılır
```

Birden fazla callback kullanarak, istekleri test etmeden önce doğrulama (assertion) da yapabilirsiniz:

```php
$expectedRequests = [
    function ($method, $url, $options): MockResponse {
        $this->assertSame('GET', $method);
        $this->assertSame('https://example.com/api/v1/customer', $url);
        return new MockResponse('...');
    },
    function ($method, $url, $options): MockResponse {
        $this->assertSame('POST', $method);
        $this->assertSame('https://example.com/api/v1/customer/1/products', $url);
        return new MockResponse('...');
    },
];

$client = new MockHttpClient($expectedRequests);
```

Ayrıca, `setResponseFactory()` metoduyla yanıtları veya callback’leri sonradan da atayabilirsiniz:

```php
$responses = [
    new MockResponse($body1, $info1),
    new MockResponse($body2, $info2),
];

$client = new MockHttpClient();
$client->setResponseFactory($responses);
```

---

#### HTTP Durum Kodlarını Test Etme

Farklı HTTP durum kodlarını test etmek için `http_code` seçeneğini tanımlayabilirsiniz:

```php
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

$client = new MockHttpClient([
    new MockResponse('...', ['http_code' => 500]),
    new MockResponse('...', ['http_code' => 404]),
]);

$response = $client->request('...');
```

Mock client’a sağlanan yanıtların `MockResponse` olması gerekmez;

`ResponseInterface`’i uygulayan herhangi bir sınıf kullanılabilir.

Ancak `MockResponse`, parçalı (chunked) yanıtları ve zaman aşımı testlerini simüle etmeye de olanak tanır:

```php
$body = function (): \Generator {
    yield 'hello';
    // boş string’ler zaman aşımı olarak değerlendirilir
    yield '';
    yield 'world';
};

$mockResponse = new MockResponse($body());
```

Son olarak, yanıtları dinamik olarak üreten bir çağrılabilir (invokable) veya iterable sınıf oluşturup fonksiyonel testlerde callback olarak da kullanabilirsiniz:

```php
namespace App\Tests;

use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

class MockClientCallback
{
    public function __invoke(string $method, string $url, array $options = []): ResponseInterface
    {
        // bir fixture yükle veya veri üret
        return new MockResponse($data);
    }
}
```

Symfony’yi bu callback’i kullanacak şekilde yapılandırın:

```php
// config/packages/framework.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->httpClient()
        ->mockResponseFactory(MockClientCallback::class)
    ;
};
```

---

#### JSON Döndüren Mock Yanıtlar

Normalde JSON döndürmek için:

```php
use Symfony\Component\HttpClient\Response\MockResponse;

$response = new MockResponse(json_encode([
    'foo' => 'bar',
]), [
    'response_headers' => [
        'content-type' => 'application/json',
    ],
]);
```

Ancak bunun yerine `JsonMockResponse` kullanabilirsiniz:

```php
use Symfony\Component\HttpClient\Response\JsonMockResponse;

$response = new JsonMockResponse([
    'foo' => 'bar',
]);
```

Ayrıca, `JsonMockResponse`’u dosyadan doğrudan oluşturabilirsiniz:

```php
use Symfony\Component\HttpClient\Response\JsonMockResponse;

$response = JsonMockResponse::fromFile('tests/fixtures/response.json');
```

🆕 `fromFile()` metodu Symfony 7.1’de tanıtılmıştır.



İstek Verilerini Test Etme (Testing Request Data)

`MockResponse` sınıfı, istekleri test etmek için bazı yardımcı metotlar sunar:

* **getRequestMethod()** – HTTP metodunu döndürür.
* **getRequestUrl()** – isteğin gönderileceği URL’yi döndürür.
* **getRequestOptions()** – başlıklar, sorgu parametreleri, gövde içeriği gibi istekle ilgili diğer bilgileri içeren bir dizi döndürür.

Kullanım örneği:

```php
$mockResponse = new MockResponse('', ['http_code' => 204]);
$httpClient = new MockHttpClient($mockResponse, 'https://example.com');

$response = $httpClient->request('DELETE', 'api/article/1337', [
    'headers' => [
        'Accept: */*',
        'Authorization: Basic YWxhZGRpbjpvcGVuc2VzYW1l',
    ],
]);

$mockResponse->getRequestMethod();
// "DELETE" döndürür

$mockResponse->getRequestUrl();
// "https://example.com/api/article/1337" döndürür

$mockResponse->getRequestOptions()['headers'];
// ["Accept: */*", "Authorization: Basic YWxhZGRpbjpvcGVuc2VzYW1l"] döndürür
```

---

### Tam Örnek (Full Example)

Aşağıdaki bağımsız örnek, HTTP client’ın bir gerçek uygulamada nasıl kullanılacağını ve test edileceğini gösterir:

```php
// ExternalArticleService.php
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ExternalArticleService
{
    public function __construct(
        private HttpClientInterface $httpClient,
    ) {
    }

    public function createArticle(array $requestData): array
    {
        $requestJson = json_encode($requestData, JSON_THROW_ON_ERROR);

        $response = $this->httpClient->request('POST', 'api/article', [
            'headers' => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            'body' => $requestJson,
        ]);

        if (201 !== $response->getStatusCode()) {
            throw new Exception('Response status code is different than expected.');
        }

        // ... diğer kontroller

        $responseJson = $response->getContent();
        $responseData = json_decode($responseJson, true, 512, JSON_THROW_ON_ERROR);

        return $responseData;
    }
}
```

```php
// ExternalArticleServiceTest.php
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ExternalArticleServiceTest extends TestCase
{
    public function testSubmitData(): void
    {
        // Arrange
        $requestData = ['title' => 'Testing with Symfony HTTP Client'];
        $expectedRequestData = json_encode($requestData, JSON_THROW_ON_ERROR);

        $expectedResponseData = ['id' => 12345];
        $mockResponseJson = json_encode($expectedResponseData, JSON_THROW_ON_ERROR);
        $mockResponse = new MockResponse($mockResponseJson, [
            'http_code' => 201,
            'response_headers' => ['Content-Type: application/json'],
        ]);

        $httpClient = new MockHttpClient($mockResponse, 'https://example.com');
        $service = new ExternalArticleService($httpClient);

        // Act
        $responseData = $service->createArticle($requestData);

        // Assert
        $this->assertSame('POST', $mockResponse->getRequestMethod());
        $this->assertSame('https://example.com/api/article', $mockResponse->getRequestUrl());
        $this->assertContains(
            'Content-Type: application/json',
            $mockResponse->getRequestOptions()['headers']
        );
        $this->assertSame($expectedRequestData, $mockResponse->getRequestOptions()['body']);

        $this->assertSame($expectedResponseData, $responseData);
    }
}
```

---

### HAR Dosyalarını Kullanarak Test Etme (Testing Using HAR Files)

Modern tarayıcılar (ağ sekmesi aracılığıyla) ve HTTP client’lar, yapılan HTTP isteklerinin bilgilerini **HAR (HTTP Archive)** formatında dışa aktarabilir.

Symfony’nin HTTP Client bileşeni, bu `.har` dosyalarını testlerde kullanmanıza olanak tanır.

Önce, test etmek istediğiniz HTTP isteğini tarayıcı veya client ile gerçekleştirin. Ardından, bu bilgiyi uygulamanızda bir `.har` dosyası olarak kaydedin:

```php
// ExternalArticleServiceTest.php
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ExternalArticleServiceTest extends KernelTestCase
{
    public function testSubmitData(): void
    {
        // Arrange
        $fixtureDir = sprintf('%s/tests/fixtures/HTTP', static::getContainer()->getParameter('kernel.project_dir'));
        $factory = new HarFileResponseFactory("$fixtureDir/example.com_archive.har");
        $httpClient = new MockHttpClient($factory, 'https://example.com');
        $service = new ExternalArticleService($httpClient);

        // Act
        $responseData = $service->createArticle($requestData);

        // Assert
        $this->assertSame('the expected response', $responseData);
    }
}
```

Eğer servisiniz birden fazla istek yapıyorsa veya `.har` dosyasında birden fazla istek/yanıt çifti varsa, `HarFileResponseFactory` ilgili yanıtı **istek metodu, URL ve gövde** temelinde bulur.

Ancak istek gövdesi veya URI rastgele değişiyorsa (örneğin tarih veya UUID içeriyorsa), bu yöntem işe yaramaz.

---

### Ağ Taşıma İstisnalarını Test Etme (Testing Network Transport Exceptions)

Ağ Hataları (Network Errors) bölümünde açıklandığı gibi, HTTP istekleri yapılırken taşıma düzeyinde hatalar oluşabilir.

Bu nedenle, uygulamanızın taşıma hatası durumunda nasıl davrandığını test etmek önemlidir.

`MockResponse`, bu durumu test etmek için birkaç yol sağlar:

* **Başlıklar alınmadan önceki hataları** test etmek için, `MockResponse` oluştururken `error` seçeneğini tanımlayın.

  (Örneğin, host çözümlenemediğinde veya erişilemediğinde.)

  `TransportException`, `getStatusCode()` veya `getHeaders()` çağrıldığında fırlatılır.
* **Başlıklar alındıktan sonra akış sırasında oluşan hataları** test etmek için, istisnayı `body` parametresine dahil edin.

  Bu istisna doğrudan `body`’ye verilebilir veya bir callback içinde yield edilebilir.

  Bu durumda `getStatusCode()` başarı (200) döndürebilir ama `getContent()` çağrısı başarısız olur.

Aşağıdaki örnek üç yöntemi de göstermektedir:

```php
// ExternalArticleServiceTest.php
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ExternalArticleServiceTest extends TestCase
{
    // ...

    public function testTransportLevelError(): void
    {
        $requestData = ['title' => 'Testing with Symfony HTTP Client'];
        $httpClient = new MockHttpClient([
            // Başlıklar alınmadan önce oluşan bir taşıma hatasını simüle et
            new MockResponse(info: ['error' => 'host unreachable']),

            // Başarı durum kodu döndüren ancak gövde alınırken hata veren yanıt
            new MockResponse([new \RuntimeException('Error at transport level')]),

            // Veya callback içinden istisna fırlatarak
            new MockResponse((static function (): \Generator {
                yield new TransportException('Error at transport level');
            })()),
        ]);

        $service = new ExternalArticleService($httpClient);

        try {
            $service->createArticle($requestData);

            // `createArticle()` içinde istisna fırlatılması gerektiğinden bu satıra ulaşılmamalı
            $this->fail();
        } catch (TransportException $e) {
            $this->assertEquals(new \RuntimeException('Error at transport level'), $e->getPrevious());
            $this->assertSame('Error at transport level', $e->getMessage());
        }
    }
}
```

---

Bu çalışma ve kod örnekleri, **Creative Commons BY-SA 3.0** lisansı altında sunulmuştur.
