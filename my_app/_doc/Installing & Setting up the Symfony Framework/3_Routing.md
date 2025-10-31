## 📍 Routing (Yönlendirme)

Bir uygulama bir **istek (request)** aldığında, Symfony bu isteği karşılayacak bir **controller** metodunu (action) çağırır.

 **Routing yapılandırması** , her gelen URL için hangi action’ın çalıştırılacağını tanımlar.

Ayrıca, SEO dostu URL’ler oluşturma gibi faydalı özellikler de sunar:

Örneğin:

`/read/intro-to-symfony` → ✅

`index.php?article_id=57` → ❌

---

## 🧭 Route Oluşturma

Symfony’de route’lar  **YAML** ,  **XML** , **PHP** veya **attribute (özellik)** olarak tanımlanabilir.

Tüm formatlar aynı özellikleri ve performansı sunar, bu yüzden sevdiğin yöntemi kullanabilirsin.

> 💡 Symfony, route ve controller’ı aynı dosyada tanımlamanın kolaylığı nedeniyle **attribute tabanlı** tanımı önerir.

---

## 🧩 Attribute (Özellik) ile Route Oluşturma

PHP’nin **attribute** özelliği sayesinde, route’ları doğrudan controller sınıflarının yanında tanımlayabilirsin.

Bu yöntemi kullanmadan önce, küçük bir yapılandırma eklemelisin.

Eğer projen **Symfony Flex** ile oluşturulduysa bu dosya zaten hazır gelir.

Değilse, aşağıdaki dosyayı kendin oluştur:

```yaml
# config/routes/attributes.yaml
controllers:
    resource:
        path: ../../src/Controller/
        namespace: App\Controller
    type: attribute

kernel:
    resource: App\Kernel
    type: attribute
```

Bu yapılandırma Symfony’ye şunu söyler:

👉 `App\Controller` isim alanındaki (namespace) sınıflarda tanımlanan attribute tabanlı route’ları `src/Controller/` dizininde ara.

Ayrıca **Kernel** sınıfı da bir controller gibi davranabilir.

Bu özellik, Symfony’yi küçük bir mikro-framework olarak kullanan projelerde oldukça faydalıdır.

---

## 📝 Örnek: `/blog` Route’u Oluşturmak

Diyelim ki uygulamada `/blog` adresine bir route eklemek istiyorsun.

Bunun için şu controller sınıfını oluştur:

```php
// src/Controller/BlogController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends AbstractController
{
    #[Route('/blog', name: 'blog_list')]
    public function list(): Response
    {
        // ...
    }
}
```

Bu tanım, `/blog` URL’sine karşılık gelen bir **`blog_list`** adlı route oluşturur.

Kullanıcı bu URL’ye girdiğinde, uygulama `BlogController::list()` metodunu çalıştırır.

> 🔍 Query string (örneğin `?foo=bar`) route eşleşmesini etkilemez.
>
> Yani `/blog?foo=bar` veya `/blog?foo=bar&bar=foo` gibi adresler de `blog_list` route’unu eşleştirir.

> ⚠️ Aynı PHP dosyasında birden fazla sınıf tanımlarsan, Symfony sadece **ilk sınıftaki route’ları** yükler.

`name` parametresi (örneğin `blog_list`), şu anda önemli görünmese de ileride **URL oluşturma** işlemlerinde kritik bir rol oynayacak.

Her route adının **benzersiz (unique)** olması gerekir.

---

## 📄 YAML, XML veya PHP ile Route Tanımlama

Route’ları controller içinde değil de, ayrı bir **dosyada** tanımlamayı da tercih edebilirsin.

**Avantajı:** Ek bağımlılık gerekmez.

**Dezavantajı:** Route yapılandırmaları ve controller kodları farklı dosyalarda olur, bu da bazen takibi zorlaştırabilir.

Aşağıda, `/blog` URL’sini `BlogController::list()` action’ına bağlayan farklı tanım biçimleri örneklenmiştir:

```php
// config/routes.php
use App\Controller\BlogController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->add('blog_list', '/blog')
        // Controller değeri [controller_sınıfı, metod_adı] formatındadır
        ->controller([BlogController::class, 'list'])

        // Eğer controller __invoke() metoduyla tanımlanmışsa metod adını yazmana gerek yoktur:
        // ->controller(BlogController::class)
    ;
};
```

Symfony, varsayılan olarak hem **YAML** hem de **PHP** formatındaki route dosyalarını otomatik olarak yükler.

Ancak route’ları **XML** formatında tanımlarsan, `src/Kernel.php` dosyasını güncellemen gerekir.

---

## ⚙️ HTTP Metotlarını Eşleştirme

Varsayılan olarak, route’lar tüm HTTP metotlarıyla (GET, POST, PUT, vb.) eşleşir.

Ancak bazı route’ların sadece belirli metotlarla çalışmasını isteyebilirsin.

`methods` seçeneğini kullanarak bu kısıtlamayı ekleyebilirsin:

```php
// config/routes.php
use App\Controller\BlogApiController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->add('api_post_show', '/api/posts/{id}')
        ->controller([BlogApiController::class, 'show'])
        ->methods(['GET', 'HEAD'])
    ;
    $routes->add('api_post_edit', '/api/posts/{id}')
        ->controller([BlogApiController::class, 'edit'])
        ->methods(['PUT'])
    ;
};
```

> 🧠 HTML formlar yalnızca **GET** ve **POST** metodlarını destekler.
>
> Eğer farklı bir metot (örneğin `PUT`) kullanmak istiyorsan, forma şu gizli alanı ekle:

```html
<input type="hidden" name="_method" value="PUT">
```

Symfony’nin **Form bileşeni** ile oluşturulan formlarda bu işlem otomatik olarak yapılır

(`framework.http_method_override` seçeneği `true` ise).

---

### 🚀 Özet

* Route’lar URL’leri controller action’larına bağlar.
* Route tanımlamaları YAML, XML, PHP veya attribute ile yapılabilir.
* Attribute tabanlı yöntem en modern ve önerilen yaklaşımdır.
* `methods` parametresiyle HTTP istek tiplerini sınırlandırabilirsin.


## ⚙️ Ortam (Environment) Eşleştirme

Bazen belirli bir route’un yalnızca belirli bir ortamda (örneğin `dev`, `prod`, `test`) aktif olmasını istersin.

Bunu yapmak için `env` seçeneğini kullanabilirsin.

```php
// config/routes.php
use App\Controller\DefaultController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    if ('dev' === $routes->env()) {
        $routes->add('tools', '/tools')
            ->controller([DefaultController::class, 'developerTools'])
        ;
    }
};
```

Bu örnekte `/tools` rotası yalnızca uygulama **`dev` ortamında** çalıştırıldığında etkin olacaktır.

`prod` ortamında bu rota tanımlanmaz.

---

## 🧠 Mantıksal Koşullar ile Eşleştirme (Matching Expressions)

Bazı durumlarda bir route’un yalnızca **belirli bir koşul** sağlandığında eşleşmesini isteyebilirsin.

Bunun için `condition` seçeneğini kullanabilirsin.

```php
// config/routes.php
use App\Controller\DefaultController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->add('contact', '/contact')
        ->controller([DefaultController::class, 'contact'])
        ->condition('context.getMethod() in ["GET", "HEAD"] and request.headers.get("User-Agent") matches "/firefox/i"')
        // Konfigürasyon parametreleri de kullanılabilir:
        // ->condition('request.headers.get("User-Agent") matches "%app.allowed_browsers%"')
        // Ortam değişkenleri de kullanılabilir:
        // ->condition('context.getHost() == env("APP_MAIN_HOST")')
    ;

    $routes->add('post_show', '/posts/{id}')
        ->controller([DefaultController::class, 'showPost'])
        // "params" değişkeniyle route parametrelerine erişebilirsin:
        ->condition('params["id"] < 1000')
    ;
};
```

Bu örneklerde `condition` ifadesi, Symfony’nin **Expression Language** sözdizimiyle yazılır ve aşağıdaki değişkenleri kullanabilir:

| Değişken        | Açıklama                                                          |
| ----------------- | ------------------------------------------------------------------- |
| **context** | `RequestContext`nesnesi — route hakkında temel bilgileri tutar. |
| **request** | Şu anda işlenen Symfony `Request`nesnesi.                       |
| **params**  | Mevcut route ile eşleşen parametrelerin dizisi.                   |

---

### 🔧 Kullanılabilir Fonksiyonlar

| Fonksiyon                  | Açıklama                                       |
| -------------------------- | ------------------------------------------------ |
| `env(string $name)`      | Ortam değişkeninin değerini döndürür.      |
| `service(string $alias)` | Bir “routing condition” servisini döndürür. |

---

## 🧩 Routing Condition Servisleri Oluşturma

Eğer route koşullarında özel bir servis kullanmak istersen, o servise `#[AsRoutingConditionService]` attribute’unu veya `routing.condition_service` etiketini eklemelisin.

```php
use Symfony\Bundle\FrameworkBundle\Routing\Attribute\AsRoutingConditionService;
use Symfony\Component\HttpFoundation\Request;

#[AsRoutingConditionService(alias: 'route_checker')]
class RouteChecker
{
    public function check(Request $request): bool
    {
        // Özel koşul kontrolü
    }
}
```

Daha sonra bu servisi route tanımında şu şekilde kullanabilirsin:

```php
// Alias kullanarak
#[Route(condition: "service('route_checker').check(request)")]

// veya tam sınıf adını kullanarak
#[Route(condition: "service('App\\Service\\RouteChecker').check(request)")]
```

> ⚙️ Symfony, `condition` ifadelerini PHP koduna derler.
>
> Bu nedenle `condition` anahtarının performans üzerinde ek bir yükü yoktur — sadece ifade içindeki PHP’nin çalışması kadar sürede yürütülür.

> ❗ `condition` ifadeleri **URL oluşturma** sırasında dikkate alınmaz.
>
> (URL oluşturma konusuna ilerleyen bölümlerde değinilecektir.)

---

## 🪄 Route’ları Hata Ayıklama (Debugging Routes)

Uygulaman büyüdükçe onlarca, hatta yüzlerce route oluşabilir.

Symfony, route’larla ilgili sorunları teşhis etmene yardımcı olacak birkaç güçlü komut sunar.

---

### 🔍 Tüm Route’ları Listeleme

Aşağıdaki komut, uygulamadaki  **tüm route’ları** , Symfony’nin değerlendirme sırasına göre listeler:

```bash
php bin/console debug:router
```

Örnek çıktı:

```
----------------  -------  -------  -----  --------------------------------------------
Name              Method   Scheme   Host   Path
----------------  -------  -------  -----  --------------------------------------------
homepage          ANY      ANY      ANY    /
contact           GET      ANY      ANY    /contact
contact_process   POST     ANY      ANY    /contact
article_show      ANY      ANY      ANY    /articles/{_locale}/{year}/{title}.{_format}
blog              ANY      ANY      ANY    /blog/{page}
blog_show         ANY      ANY      ANY    /blog/{slug}
----------------  -------  -------  -----  --------------------------------------------
```

---

### ⚙️ Ek Filtreler

Symfony 7.3 ile birlikte gelen yeni parametreler:

| Komut                                           | Açıklama                                                         |
| ----------------------------------------------- | ------------------------------------------------------------------ |
| `php bin/console debug:router --show-aliases` | Alias (takma ad) tanımlarını gösterir.                         |
| `php bin/console debug:router --method=GET`   | Sadece**GET**isteklerini karşılayan route’ları listeler. |
| `php bin/console debug:router --method=ANY`   | Tüm HTTP metodlarını gösterir.                                 |

---

### 🔎 Belirli Bir Route’un Detaylarını Görmek

Route adını (veya adın bir kısmını) belirterek detaylarını inceleyebilirsin:

```bash
php bin/console debug:router app_lucky_number
```

Örnek çıktı:

```
+-------------+---------------------------------------------------------+
| Property    | Value                                                   |
+-------------+---------------------------------------------------------+
| Route Name  | app_lucky_number                                        |
| Path        | /lucky/number/{max}                                     |
| ...         | ...                                                     |
| Options     | compiler_class: Symfony\Component\Routing\RouteCompiler |
|             | utf8: true                                              |
+-------------+---------------------------------------------------------+
```

---

### 🧭 URL Eşleşmesini Test Etme

Belirli bir URL’nin hangi route’a denk geldiğini görmek istiyorsan şu komutu çalıştır:

```bash
php bin/console router:match /lucky/number/8
```

Çıktı:

```
[OK] Route "app_lucky_number" matches
```

> ✅ Bu komut, beklediğin controller’ın neden çalışmadığını anlamak için son derece faydalıdır.

---

### 🚀 Özet

* `env()` ile ortam bazlı route tanımlayabilirsin.
* `condition` ile özel mantıklar yazabilirsin.
* `service()` fonksiyonu ile route koşullarında servisleri kullanabilirsin.
* `debug:router` ve `router:match` komutlarıyla route yapılarını detaylıca analiz edebilirsin.


## 🔢 Route Parametreleri (Değişken URL Bölümleri)

Önceki örneklerde, URL sabitti (örneğin `/blog`).

Ancak çoğu zaman, URL’nin bazı kısımlarının **değişken** olmasını isteriz.

Örneğin bir blog gönderisini görüntülemek için URL şu şekilde olabilir:

```
/blog/my-first-post  
/blog/all-about-symfony
```

Symfony’de, değişken kısımlar `{ }` içinde tanımlanır.

Yani bir blog gönderisi içeriğini göstermek için route şöyle oluşturulur:

```php
// config/routes.php
use App\Controller\BlogController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->add('blog_show', '/blog/{slug}')
        ->controller([BlogController::class, 'show'])
    ;
};
```

Buradaki `{slug}` değişkeni, Symfony tarafından yakalanarak controller metoduna parametre olarak aktarılır.

Kullanıcı `/blog/my-first-post` adresine girdiğinde Symfony şu işlemi yapar:

* `BlogController::show()` metodunu çalıştırır
* Metoda `$slug = 'my-first-post'` argümanını gönderir

> ⚙️ Bir route içinde istediğin kadar parametre tanımlayabilirsin.
>
> Ancak her parametre **aynı route içinde yalnızca bir kez** kullanılabilir.
>
> Örneğin:
>
> `/blog/posts-about-{category}/page/{pageNumber}` ✅
>
> `/blog/{category}/{category}` ❌

---

## ✅ Parametre Doğrulama (Requirements)

Diyelim ki uygulamada iki route var:

* `/blog/{slug}` → `blog_show`
* `/blog/{page}` → `blog_list`

Varsayılan olarak Symfony tüm parametreleri **herhangi bir değer** olarak kabul eder.

Bu durumda `/blog/my-first-post` hem `blog_show` hem de `blog_list` ile eşleşebilir.

Symfony, dosyada hangisi önce tanımlandıysa onu kullanır — bu karışıklığa neden olur.

Bu sorunu çözmek için `requirements` seçeneğini kullanarak parametreye **doğrulama (validation)** ekleyebilirsin:

```php
// config/routes.php
use App\Controller\BlogController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('blog_list', '/blog/{page}')
        ->controller([BlogController::class, 'list'])
        ->requirements(['page' => '\d+']) // Sadece sayılara izin ver
    ;

    $routes->add('blog_show', '/blog/{slug}')
        ->controller([BlogController::class, 'show'])
    ;
};
```

Bu örnekte, `\d+` ifadesi **bir veya daha fazla rakam** içeren değerleri eşleştirir.

Artık Symfony URL’leri şu şekilde ayırabilir:

| URL                     | Eşleşen Route | Parametre                   |
| ----------------------- | --------------- | --------------------------- |
| `/blog/2`             | `blog_list`   | `$page = 2`               |
| `/blog/my-first-post` | `blog_show`   | `$slug = 'my-first-post'` |

---

## 🧱 `Requirement` Enum’u ile Standart Doğrulamalar

Symfony 6.2’den itibaren gelen `Requirement` enum’u, sık kullanılan regex kalıplarını hazır sabitler olarak sunar:

örneğin `digits`, `uuid`, `date` vb.

```php
// config/routes.php
use App\Controller\BlogController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\Requirement\Requirement;

return static function (RoutingConfigurator $routes): void {
    $routes->add('blog_list', '/blog/{page}')
        ->controller([BlogController::class, 'list'])
        ->requirements(['page' => Requirement::DIGITS])
    ;
};
```

> 💡 Bu şekilde, karmaşık regex ifadelerini kendin yazmak zorunda kalmazsın.
>
> Symfony’nin sunduğu sabitler kodu daha okunabilir ve güvenli hale getirir.

---

## 🧩 Konfigürasyon Parametrelerini Kullanmak

Route’larda hem `path` hem de `requirements` kısımlarında konfigürasyon parametrelerini kullanabilirsin.

Bu yöntem, karmaşık düzenli ifadeleri (regex) tek bir yerde tanımlayıp birden fazla route’ta tekrar kullanmanı sağlar.

---

## 🌍 Unicode Özelliklerini Destekler

Symfony’nin route gereksinimleri **PCRE Unicode özelliklerini** de destekler.

Yani global karakter türlerini eşleştirebilirsin:

| Regex         | Anlamı                         |
| ------------- | ------------------------------- |
| `\p{Lu}`    | Tüm dillerdeki büyük harfler |
| `\p{Greek}` | Yunan alfabesindeki karakterler |
| `\p{Han}`   | Çince karakterler              |

Bu sayede uluslararasılaştırılmış (i18n) URL yapıları kolayca oluşturabilirsin.

---

## ✏️ Inline (Satır İçi) Gereksinim Yazımı

`requirements` dizisini ayrı yazmak yerine, doğrulamayı parametrenin içine satır içi olarak da ekleyebilirsin:

`{parameter_name<regex>}` sözdizimiyle.

```php
// config/routes.php
use App\Controller\BlogController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('blog_list', '/blog/{page<\d+>}')
        ->controller([BlogController::class, 'list'])
    ;
};
```

Bu yazım, basit doğrulamalarda konfigürasyonu kısaltır.

Ancak karmaşık regex’lerde **okunabilirliği düşürebilir.**

---

### 🚀 Özet

* `{ }` içindeki bölümler route parametreleridir.
* Symfony, bu parametreleri controller metoduna otomatik olarak gönderir.
* `requirements` ile regex tabanlı doğrulama ekleyebilirsin.
* `Requirement` enum’u hazır desenler sağlar.
* Unicode özellikleri ve inline yazım desteklenir.



## ⚙️ Opsiyonel Parametreler (Optional Parameters)

Önceki örnekte `blog_list` rotasının URL’si `/blog/{page}` şeklindeydi.

Kullanıcı `/blog/1` adresini ziyaret ederse bu route eşleşir.

Ancak `/blog` adresine girerse  **eşleşmez** , çünkü parametre tanımlandığında mutlaka bir değer beklenir.

Bu sorunu çözmek için `{page}` parametresine **varsayılan (default)** bir değer tanımlayabilirsin.

Böylece `/blog` ziyaret edildiğinde de aynı route çalışır.

---

### 🎯 Varsayılan Değer Tanımlama

Attribute yerine YAML, XML veya PHP yapılandırması kullanıyorsan, varsayılan değerleri `defaults` seçeneğiyle tanımlarsın:

```php
// config/routes.php
use App\Controller\BlogController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('blog_list', '/blog/{page}')
        ->controller([BlogController::class, 'list'])
        ->defaults(['page' => 1])        // Varsayılan değer
        ->requirements(['page' => '\d+']) // Yalnızca sayılar
    ;
};
```

Bu durumda kullanıcı `/blog` adresine girdiğinde Symfony, `blog_list` rotasını eşleştirir

ve `$page` değişkenine otomatik olarak **1** değerini atar.

> 💡 Varsayılan değer, doğrulama kuralıyla (requirement) uyuşmak zorunda değildir.

---

### 🔢 Birden Fazla Opsiyonel Parametre

Birden fazla opsiyonel parametre tanımlayabilirsin:

örneğin `/blog/{slug}/{page}`.

Ancak dikkat:

Bir parametre  **opsiyonel hale geldikten sonra** , **ondan sonraki tüm parametreler de opsiyonel** olmalıdır.

Yani:

* `/blog/{slug}/{page?}` ✅
* `/{page}/blog` ❌ (`page` her zaman zorunlu olur, `/blog` eşleşmez)

---

### 🚫 Zorunlu Varsayılan Değerler

Eğer URL oluştururken her zaman varsayılan değeri dahil etmek istersen (örneğin `/blog` yerine her zaman `/blog/1` üretmek istiyorsan),

parametre adının başına **`!`** karakteri ekle:

```
/blog/{!page}
```

---

### ✏️ Inline (Satır İçi) Varsayılan Değerler

Tıpkı `requirements` gibi, varsayılan değerleri de parametre içinde tanımlayabilirsin:

`söz dizimi: {parameter_name?default_value}`

Ayrıca `requirements` ve `default` değerlerini **tek satırda** birleştirebilirsin:

```php
// config/routes.php
use App\Controller\BlogController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('blog_list', '/blog/{page<\d+>?1}')
        ->controller([BlogController::class, 'list'])
    ;
};
```

Bu örnekte:

* `\d+` → yalnızca sayılara izin verir
* `?1` → varsayılan değeri 1 olarak atar

> 🧠 Bir parametreye `null` varsayılan değeri atamak istersen,
>
> `?` işaretinden sonra hiçbir şey yazma:
>
> `/blog/{page?}`
>
> Bu durumda controller’da ilgili parametreyi **nullable** yapmayı unutma:
>
> `public function list(?int $page) { ... }`

---

## ⚖️ Route Önceliği (Priority)

Symfony, route’ları **tanımlandıkları sırayla** değerlendirir.

Eğer bir route, diğerlerinin desenini kapsıyorsa (örneğin `/blog/{slug}` rotası `/blog/list`’i de kapsar),

istenmeyen eşleşmeler olabilir.

YAML veya XML dosyalarında route’ların sırasını değiştirerek bu önceliği yönetebilirsin.

Ancak **PHP attribute** kullanıyorsan sıralama zor olduğundan,

Symfony `priority` parametresini sunar.

---

### 🧭 Örnek: Attribute ile Öncelik Kullanımı

```php
// src/Controller/BlogController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends AbstractController
{
    /**
     * Bu route oldukça genel bir desene sahiptir.
     */
    #[Route('/blog/{slug}', name: 'blog_show')]
    public function show(string $slug): Response
    {
        // ...
    }

    /**
     * Bu route’un eşleşebilmesi için daha yüksek bir öncelik verilmelidir.
     */
    #[Route('/blog/list', name: 'blog_list', priority: 2)]
    public function list(): Response
    {
        // ...
    }
}
```

`priority` değeri bir **tam sayı (integer)** alır.

Yüksek öncelikli route’lar, düşük önceliklilerin **önünde** değerlendirilir.

Varsayılan değer `0`’dır.

---

## 🔄 Parametre Dönüştürme (Parameter Conversion)

Bazı durumlarda, route parametresindeki değeri (örneğin bir `id` veya `slug`)

otomatik olarak bir **veritabanı nesnesine** dönüştürmek isteyebilirsin.

Bu özelliğe **Param Converter** denir.

Örneğin, önceki route tanımını koruyalım, ancak controller metodunu değiştirelim:

Artık `string $slug` yerine doğrudan `BlogPost $post` alacağız.

```php
// src/Controller/BlogController.php
namespace App\Controller;

use App\Entity\BlogPost;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends AbstractController
{
    #[Route('/blog/{slug:post}', name: 'blog_show')]
    public function show(BlogPost $post): Response
    {
        // $post: slug değeriyle eşleşen BlogPost nesnesidir
        // ...
    }
}
```

Symfony, parametre tipine (örneğin `BlogPost`) bakarak otomatik bir sorgu oluşturur

ve ilgili nesneyi veritabanından getirir.

Eğer nesne bulunamazsa Symfony **404 Not Found** hatası döndürür.

Buradaki `{slug:post}` ifadesi:

* Route parametresi `slug`’u, controller’daki `$post` parametresine bağlar.
* Param converter’a, `slug` değerine göre `BlogPost` nesnesi araması gerektiğini söyler.

> 🆕 Bu özellik Symfony **7.1** ile tanıtılmıştır.

---

### ⚠️ Çoklu Entity Mapping Çakışmaları

Eğer birden fazla entity’yi route parametrelerinden map ediyorsan,

parametre adlarının çakışmamasına dikkat etmelisin.

Yanlış örnek 👇

(iki parametre de aynı adı — `name` — kullanıyor)

```php
#[Route('/search-book/{name:author}/{name:category}')]
```

Doğru kullanım 👇

(her parametrenin benzersiz bir adı var)

```php
#[Route('/search-book/{authorName:author.name}/{categoryName:category.name}')]
```

Bu sayede:

* Route parametreleri (`authorName`, `categoryName`) benzersiz olur.
* Param converter, her iki entity’yi (`$author`, `$category`) doğru şekilde eşleştirir.

> 🆕 Bu gelişmiş eşleme biçimi Symfony **7.3** ile tanıtılmıştır.

---

### ⚡ Daha Gelişmiş Dönüştürme

Daha karmaşık mapping işlemleri için `#[MapEntity]` attribute’unu kullanabilirsin.

Bu attribute, Doctrine sorgularını özelleştirerek route parametresine göre nesne çekmene olanak tanır.

📚 Ayrıntılar için:

👉 [Doctrine Param Conversion Belgeleri](https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html)

---

### 🚀 Özet

* Parametrelere varsayılan değer vererek opsiyonel hale getirebilirsin.
* `priority` ile attribute route’ların önceliğini belirleyebilirsin.
* Param converter, route parametrelerini entity nesnelerine dönüştürür.
* `{slug:post}` sözdizimiyle route parametresi — controller argümanı eşlemesi yapılabilir.
* Symfony 7.3 ile daha gelişmiş entity mapping desteklenir.


## 🔁 Route Alias’ları (Yönlendirme Takma Adları)

**Route alias** özelliği, aynı route’a birden fazla isim verebilmeni sağlar.

Bu, özellikle **geriye dönük uyumluluk (backward compatibility)** sağlamak için kullanılır — örneğin bir route’un adını değiştirdiğinde, eski adıyla çalışan kodların kırılmamasını sağlamak için.

---

### 🎯 Örnek: Route Alias Oluşturma

Diyelim ki `product_show` adında bir route’un var:

```php
// config/routes.php
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('product_show', '/product/{id}')
        ->controller('App\Controller\ProductController::show');
};
```

Artık aynı route’a ikinci bir isim (örneğin `product_details`) vermek istiyorsun.

Bunun için route’u kopyalamana gerek yok — sadece bir **alias** tanımlaman yeterli:

```php
// config/routes.php
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('product_show', '/product/{id}')
        ->controller('App\Controller\ProductController::show');

    // İkinci parametre, yukarıda tanımlanan route’un adını belirtir
    $routes->alias('product_details', 'product_show');
};
```

Bu tanımdan sonra hem `product_show` hem de `product_details` route’ları

uygulamada kullanılabilir olacak ve **aynı sonucu** üretecektir.

> 🆕 **PHP attribute** ile alias tanımlama desteği Symfony **7.3** sürümünde eklendi.

---

### 🧩 Üçüncü Taraf Route’lar için Alias Tanımlama

YAML, XML ve PHP yapılandırma biçimleri, **sana ait olmayan route’lar** için alias tanımlamayı da destekler.

Ancak PHP attribute ile tanımlanmış bir route’a sonradan alias ekleyemezsin.

Bu özellik, örneğin bir üçüncü taraf bundle’daki route’u kendi isminle çağırmak için kullanışlıdır.

Alias ve orijinal route **aynı dosyada** veya **aynı formatta** olmak zorunda değildir.

---

## ⚠️ Route Alias’larını Kademeli Olarak Kaldırma (Deprecating)

Alias’lar genellikle, bir route adını değiştirirken **eski adla uyumluluğu korumak** için kullanılır.

Ama zamanla eski route’u kaldırmak da isteyebilirsin.

Örneğin, `product_show` yerine `product_details` kullanmaya karar verdin.

Bu durumda alias yönünü “ters çevirerek” `product_show`’u **deprecated** yapabilirsin:

```php
$routes->add('product_details', '/product/{id}')
    ->controller('App\Controller\ProductController::show');

$routes->alias('product_show', 'product_details')
    // Genel bir uyarı mesajı verir:
    ->deprecate('acme/package', '1.2', '')
  
    // veya özel bir uyarı mesajı tanımlayabilirsin:
    ->deprecate(
        'acme/package',
        '1.2',
        'The "%alias_id%" route alias is deprecated. Please use "product_details" instead.'
    );
```

> 🆕 `DeprecatedAlias` attribute desteği Symfony **7.3** ile eklendi.

Bu yapılandırma sayesinde:

* `product_show` alias’ı kullanıldığında Symfony bir **uyarı mesajı (deprecation warning)** gösterir.
* `%alias_id%` yer tutucusu, alias adının kendisiyle otomatik olarak değiştirilir.
* En az bir `%alias_id%` yer tutucusu kullanmak zorunludur.

---

## 🧭 Route Grupları ve Prefixler

Bir grup route’un ortak özellikleri (örneğin hepsi `/blog` ile başlıyor) varsa, Symfony bu ortak ayarları **gruplama (grouping)** özelliğiyle kolayca yönetmeni sağlar.

### 📦 Attribute’lar ile Ortak Ayar Tanımlama

Attribute tabanlı route’larda, ortak yapılandırmayı controller sınıfının üstüne ekleyebilirsin:

```php
#[Route('/blog', name: 'blog_', requirements: ['_locale' => 'en|es|fr'])]
class BlogController extends AbstractController
{
    #[Route('/{_locale}', name: 'index')]
    public function index() {}

    #[Route('/{_locale}/posts/{slug}', name: 'show')]
    public function show() {}
}
```

Bu durumda:

* `index()` → route adı `blog_index`, URL’si `/blog/{_locale}`
* `show()` → route adı `blog_show`, URL’si `/blog/{_locale}/posts/{slug}`

Her iki route da `_locale` parametresi için `en|es|fr` doğrulamasını paylaşır.

---

### 📜 Diğer Formatlarda (YAML, PHP, XML)

PHP yapılandırmasında route’ları import ederken `prefix()` ve `namePrefix()` seçenekleriyle ortak ayarlar ekleyebilirsin:

```php
// config/routes/attributes.php
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->import(
            '../../src/Controller/',
            'attribute',
            false,
            // Belirli dosyaları hariç tutmak istersen:
            '../../src/Controller/{Debug*Controller.php}'
        )
        // Tüm URL’lerin başına eklenecek kısım:
        ->prefix('/blog')

        // Route adlarının başına eklenecek önek:
        ->namePrefix('blog_')

        // Tüm route’lara ortak bir doğrulama:
        ->requirements(['_locale' => 'en|es|fr'])
    ;
};
```

> ⚠️ `exclude` seçeneği sadece `resource` değeri **glob pattern** (örneğin `*.php`) olduğunda çalışır.
>
> Normal bir dizin yolu kullanırsan (`../src/Controller`), bu değer yok sayılır.

---

### ➕ Root Slash Davranışı

Symfony, boş (empty) path içeren route’larda, prefix eklenince **otomatik olarak bir eğik çizgi (slash)** ekler.

Örneğin `/blog` prefix’i ve boş path → `/blog/` URL’si olur.

Bu davranışı devre dışı bırakmak için `trailing_slash_on_root` seçeneğini `false` yap:

```php
// config/routes/attributes.php
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->import('../../src/Controller/', 'attribute')
        ->prefix('/blog', false); // slash eklenmez
};
```

---

## 📋 Route Bilgilerine Erişim

Symfony’nin `Request` nesnesi, route ile ilgili tüm bilgileri **request attribute’ları** olarak saklar.

Bu verilere controller içinde kolayca erişebilirsin:

```php
// src/Controller/BlogController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends AbstractController
{
    #[Route('/blog', name: 'blog_list')]
    public function list(Request $request): Response
    {
        $routeName = $request->attributes->get('_route');
        $routeParameters = $request->attributes->get('_route_params');

        // Tüm attribute’lara erişmek için:
        $allAttributes = $request->attributes->all();

        // ...
    }
}
```

> 💡 Servis içinde bu bilgilere erişmek için `RequestStack` servisini enjekte edebilirsin.
>
> Twig şablonlarında ise `app` değişkeni üzerinden ulaşabilirsin:
>
> * `app.current_route`
> * `app.current_route_parameters`

---

## 🧭 Özel Route’lar (Special Routes)

Symfony, bazı özel controller’lar tanımlar:

Bunlar sayesinde **şablon render etme** veya **redirect yapma** işlemlerini route tanımından doğrudan gerçekleştirebilirsin —

ayrı bir controller action’ı oluşturman gerekmez.

---

### 🖼️ Şablon Render Etmek

Bir route’tan doğrudan Twig şablonu render etmek mümkündür.

Detaylar için “Templates” bölümündeki **“Rendering a Template from a Route”** başlığına bakabilirsin.

---

### 🔀 Route veya URL’ye Yönlendirme (RedirectController)

`RedirectController`, route veya URL’ye doğrudan yönlendirme yapmanı sağlar:

```php
// config/routes.php
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    // Başka bir route’a yönlendirme
    $routes->add('doc_shortcut', '/doc')
        ->controller(RedirectController::class)
        ->defaults([
            'route' => 'doc_page',
            'page' => 'index',
            'version' => 'current',
            'permanent' => true,         // 301 (kalıcı) yönlendirme
            'keepQueryParams' => true,   // query parametrelerini koru
            'keepRequestMethod' => true, // HTTP metodunu koru (307/308)
        ])
    ;

    // Dış URL’ye yönlendirme
    $routes->add('legacy_doc', '/legacy/doc')
        ->controller(RedirectController::class)
        ->defaults([
            'path' => 'https://legacy.example.com/doc',
            'permanent' => true,
        ])
    ;
};
```

> Symfony, ayrıca controller içinden `redirectToRoute()` veya `redirect()` metotlarını da sunar.

---

## 🔚 Slash (/) ile Biten URL’lerin Yönlendirilmesi

Tarihsel olarak URL’lerde `/` karakteri dizinleri temsil eder (`/foo/`),

olmaması ise bir dosyayı (`/foo`) temsil eder.

Modern web uygulamalarında ise bu iki URL genellikle **aynı içeriğe** yönlendirilir.

Symfony bu davranışı GET ve HEAD isteklerinde otomatik olarak yönetir:

| Route URL | İstek `/foo`                | İstek `/foo/`              |
| --------- | ------------------------------ | ----------------------------- |
| `/foo`  | Eşleşir (200 OK)             | `/foo`’ya 301 yönlendirme |
| `/foo/` | `/foo/`’ya 301 yönlendirme | Eşleşir (200 OK)            |

---

### 🚀 Özet

* `alias()` ile route’lara alternatif isimler ekleyebilirsin.
* `deprecate()` ile eski route’ları kademeli olarak kaldırabilirsin.
* `prefix()` ve `namePrefix()` ile route gruplarına ortak yapı ekleyebilirsin.
* `RedirectController` ile route veya URL’ye doğrudan yönlendirme yapabilirsin.
* Symfony otomatik olarak `/foo` ↔ `/foo/` yönlendirmelerini yönetir.



## 🌐 Alt Alan Adı Yönlendirmesi (Sub-Domain Routing)

Symfony’de route’lar, gelen isteğin **host adı (ör. example.com)** ile eşleşmesi için `host()` seçeneğiyle yapılandırılabilir.

Böylece aynı path’e sahip farklı domain veya alt alan adları için ayrı controller’lar çalıştırabilirsin.

---

### 🎯 Örnek: Mobil ve Normal Ana Sayfa

```php
// config/routes.php
use App\Controller\MainController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('mobile_homepage', '/')
        ->controller([MainController::class, 'mobileHomepage'])
        ->host('m.example.com'); // sadece mobil alt alan adı için

    $routes->add('homepage', '/')
        ->controller([MainController::class, 'homepage']); // tüm diğer domainler
};
```

Yukarıdaki örnekte `/` path’i iki route tarafından paylaşılır:

* `m.example.com` → `mobile_homepage`
* `www.example.com` veya diğerleri → `homepage`

---

### 🧩 Parametreli Alt Alan Adı (Dinamik Host)

Alt alan adlarını parametreli hale getirip, **çok kiracılı (multi-tenant)** sistemlerde kullanabilirsin:

```php
// config/routes.php
use App\Controller\MainController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('mobile_homepage', '/')
        ->controller([MainController::class, 'mobileHomepage'])
        ->host('{subdomain}.example.com')
        ->defaults([
            'subdomain' => 'm',
        ])
        ->requirements([
            'subdomain' => 'm|mobile',
        ]);
};
```

Bu örnekte:

* `{subdomain}` parametresi alt alan adını temsil eder.
* Varsayılan olarak `'m'` değeri atanır.
* `requirements` ile yalnızca `m` veya `mobile` alt alan adlarına izin verilir.

> 💡 Eğer `defaults` tanımlamazsan, URL oluştururken her seferinde alt alan adını belirtmen gerekir.

---

### 📦 Route Import ile Host Kullanımı

Route’ları import ederken de `host()` seçeneğini ekleyebilirsin;

böylece import edilen tüm route’lar aynı host kuralını paylaşır.

---

### 🧪 Testlerde Subdomain Eşleşmesi

Alt alan adı eşleştirmesi yapıyorsan, **fonksiyonel testlerde `HTTP_HOST` başlığını** ayarlamayı unutma:

```php
$crawler = $client->request(
    'GET',
    '/',
    [],
    [],
    ['HTTP_HOST' => 'm.example.com']
);
```

> Alternatif olarak domain’i konfigürasyondan alabilirsin:
>
> ```php
> ['HTTP_HOST' => 'm.' . $client->getContainer()->getParameter('domain')]
> ```

---

### ✏️ Inline Format

Host parametrelerini kısa biçimde de yazabilirsin:

```
{subdomain<m|mobile>?m}.example.com
```

Bu, hem `requirements` hem de `default` değerini tek satırda tanımlar.

---

## 🌍 Yerelleştirilmiş Rotalar (Localized Routes)

Uygulaman birden fazla dilde çalışıyorsa, her dil için farklı URL tanımlayabilirsin.

Böylece aynı controller için birden fazla route oluşturmak zorunda kalmazsın.

### 🧭 Örnek

```php
// config/routes.php
use App\Controller\CompanyController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('about_us', [
        'en' => '/about-us',
        'nl' => '/over-ons',
    ])
    ->controller([CompanyController::class, 'about']);
};
```

* `en` locale → `/about-us`
* `nl` locale → `/over-ons`

> Attribute yöntemiyle tanımlarken `path` parametresini kullanarak dizi olarak belirtmen gerekir.

Symfony, eşleşen dil için `locale` değerini otomatik olarak tüm istek boyunca kullanır.

---

### 🗺️ Locale Bazlı URL Prefixleri

Genellikle çok dilli uygulamalarda her dile özel bir prefix kullanılır.

Örneğin `/nl/...` gibi.

```php
// config/routes/attributes.php
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->import('../../src/Controller/', 'attribute')
        ->prefix([
            'en' => '',    // varsayılan dil (prefix yok)
            'nl' => '/nl', // Hollandaca için prefix
        ]);
};
```

> Eğer bir route kendi `_locale` parametresini içeriyorsa,
>
> o route yalnızca kendi dilinde import edilir.

---

### 🌐 Locale Bazlı Domain

Her dil için farklı domain kullanmak da mümkündür:

```php
// config/routes/attributes.php
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->import('../../src/Controller/', 'attribute')
        ->host([
            'en' => 'www.example.com',
            'nl' => 'www.example.nl',
        ]);
};
```

---

## 🧩 Stateless Routes

Bazı durumlarda (örneğin HTTP cache için), route’un **session kullanmaması** gerekir.

Bunu `stateless()` seçeneğiyle belirtebilirsin:

```php
// config/routes.php
use App\Controller\MainController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('homepage', '/')
        ->controller([MainController::class, 'homepage'])
        ->stateless();
};
```

Symfony’de:

* `kernel.debug = true` ise, session kullanıldığında **`UnexpectedSessionUsageException`** fırlatılır.
* `kernel.debug = false` ise, sadece bir **uyarı loglanır.**

> Bu özellik, istemeden session başlatan işlemleri tespit etmene yardımcı olur.

---

## 🔗 URL Oluşturma (Generating URLs)

Routing sistemi  **çift yönlüdür** :

1. URL → Controller (eşleşme)
2. Route → URL (oluşturma)

Yani HTML içinde `<a href="...">` adreslerini elle yazmak yerine, Symfony senin için doğru URL’yi oluşturabilir.

Eğer bir route’un path’i değişirse, sadece route tanımını güncellemen yeterlidir.

---

### ⚙️ Route Adı ve Parametreleri

Bir URL oluşturmak için:

* Route adını (`blog_show`)
* Gerekli parametreleri (`slug => my-blog-post`) belirtmen yeterlidir.

> Symfony, route adlarını **benzersiz** tutar.
>
> Eğer `name` tanımlamazsan, Symfony bunu controller ve metod adına göre otomatik üretir.

Symfony ayrıca:

* `__invoke()` metodu olan sınıflar için otomatik route alias’ı oluşturur.
* Tek route’a sahip controller metotları için otomatik alias ekler.

```php
// src/Controller/MainController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function homepage(): Response
    {
        // ...
    }
}
```

Bu durumda Symfony otomatik olarak şu alias’ı ekler:

`App\Controller\MainController::homepage`

---

## 🧭 Controller İçinde URL Üretmek

Controller, `AbstractController`’dan miras alıyorsa `generateUrl()` metodunu kullanabilirsin:

```php
// src/Controller/BlogController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BlogController extends AbstractController
{
    #[Route('/blog', name: 'blog_list')]
    public function list(): Response
    {
        // Parametresiz URL
        $signUpPage = $this->generateUrl('sign_up');

        // Parametreli URL
        $userProfile = $this->generateUrl('user_profile', [
            'username' => $user->getUserIdentifier(),
        ]);

        // Mutlak (absolute) URL oluşturmak için:
        $absoluteUrl = $this->generateUrl('sign_up', [], UrlGeneratorInterface::ABSOLUTE_URL);

        // Yerelleştirilmiş route için farklı bir locale belirleme
        $signUpPageNl = $this->generateUrl('sign_up', ['_locale' => 'nl']);
    }
}
```

---

### 🧩 Ek Parametreler

Route tanımında bulunmayan parametreler, **query string** olarak URL’ye eklenir:

```php
$this->generateUrl('blog', ['page' => 2, 'category' => 'Symfony']);
// /blog/2?category=Symfony
```

> 🧠 Route’ta tanımlı olmayan parametreler, sorgu parametresi olarak eklenir.
>
> Eğer bir nesne (ör. `Uuid`) gönderiyorsan, bunu açıkça string’e çevirmen gerekir:

```php
$this->generateUrl('blog', ['uuid' => (string) $entity->getUuid()]);
```

---

### 🧱 AbstractController Kullanmıyorsan

Eğer controller `AbstractController`’dan türetilmemişse,

URL üretmek için `router` servisini manuel olarak enjekte etmelisin:

```php
$url = $router->generate('blog_show', ['slug' => 'hello-world']);
```

---

### 🚀 Özet

* `host()` ile alt alan adı bazlı route tanımlayabilirsin.
* Route’ları dillerle eşleştirip `prefix()` veya `host()` kullanarak yerelleştirebilirsin.
* `stateless()` ile cache dostu rotalar oluşturabilirsin.
* `generateUrl()` route adına göre güvenli ve dinamik linkler üretir.


## 🔗 Servislerde URL Üretme (Generating URLs in Services)

Symfony’de servislerin içinde de URL oluşturabilirsin.

Bunun için `router` servisini (ya da `UrlGeneratorInterface`) enjekte edip `generate()` metodunu kullanırsın.

---

### 🧩 Örnek: Serviste URL Oluşturmak

```php
// src/Service/SomeService.php
namespace App\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SomeService
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function someMethod(): void
    {
        // Parametresiz URL
        $signUpPage = $this->urlGenerator->generate('sign_up');

        // Parametreli URL
        $userProfile = $this->urlGenerator->generate('user_profile', [
            'username' => $user->getUserIdentifier(),
        ]);

        // Mutlak (absolute) URL oluşturmak
        $absolute = $this->urlGenerator->generate('sign_up', [], UrlGeneratorInterface::ABSOLUTE_URL);

        // Yerelleştirilmiş (localized) route için farklı bir locale kullanmak
        $signUpNl = $this->urlGenerator->generate('sign_up', ['_locale' => 'nl']);
    }
}
```

> 💡 Servis **autowiring** aktifse, yalnızca constructor’da
>
> `UrlGeneratorInterface` türünü belirtmen yeterlidir. Symfony otomatik olarak `router` servisini enjekte eder.

---

## 🖼️ Şablonlarda URL Oluşturma (Generating URLs in Templates)

Twig şablonlarında `path()` ve `url()` fonksiyonlarını kullanabilirsin:

```twig
<script>
    const route = "{{ path('blog_show', {slug: 'my-blog-post'})|escape('js') }}";
</script>
```

* `path()` → Göreli URL oluşturur (`/blog/my-blog-post`)
* `url()` → Tam URL oluşturur (`https://example.com/blog/my-blog-post`)
* `escape('js')` → JavaScript’e güvenli şekilde aktarmayı sağlar.

> 💡 Eğer dinamik olarak (JavaScript tarafında) URL üretmen gerekiyorsa,
>
> [**FOSJsRoutingBundle**](https://github.com/FriendsOfSymfony/FOSJsRoutingBundle) kullanabilirsin.

---

## 🧰 Komutlarda URL Oluşturma (Generating URLs in Commands)

Symfony komutlarında (CLI ortamında) URL üretimi servislerdeki gibidir,

ancak HTTP isteği olmadığından “host” bilgisi varsayılan olarak `http://localhost/` olur.

Bunu düzeltmek için `default_uri` ayarını tanımlayabilirsin:

```php
// config/packages/routing.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->router()->defaultUri('https://example.org/my/path/');
};
```

Artık komutlar doğru host adıyla URL üretecektir:

```php
// src/Command/MyCommand.php
namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsCommand(name: 'app:my-command')]
class MyCommand
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function __invoke(SymfonyStyle $io): int
    {
        $signUp = $this->urlGenerator->generate('sign_up');
        $profile = $this->urlGenerator->generate('user_profile', ['username' => 'alice']);
        $absolute = $this->urlGenerator->generate('sign_up', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $signUpNl = $this->urlGenerator->generate('sign_up', ['_locale' => 'nl']);

        return 0;
    }
}
```

> 💡 Web varlıkları (CSS, JS, resim) için oluşturulan URL’ler de aynı `default_uri` ayarını kullanır.
>
> Gerekirse `asset.request_context.base_path` ve `asset.request_context.secure` parametreleriyle değiştirilebilir.

---

## 🔍 Route’un Var Olup Olmadığını Kontrol Etmek

Dinamik uygulamalarda, bir route’un varlığını kontrol etmen gerekebilir.

Bunu `getRouteCollection()` ile yapmak yavaş olduğu için önerilmez.

Bunun yerine `generate()` metodunu deneyip `RouteNotFoundException`’ı yakalayabilirsin:

```php
use Symfony\Component\Routing\Exception\RouteNotFoundException;

try {
    $url = $this->router->generate($routeName, $params);
} catch (RouteNotFoundException $e) {
    // Route tanımlı değil
}
```

---

## 🔒 HTTPS Zorunluluğu (Forcing HTTPS on Generated URLs)

Eğer sunucun **proxy** arkasında çalışıyorsa (ör. SSL terminate ediyorsa),

Symfony’yi buna göre yapılandırmalısın. Yanlış yapılandırma, redirect döngülerine neden olabilir.

### 🌍 Global HTTPS Ayarı

Varsayılan olarak Symfony, URL’leri mevcut isteğin HTTP/HTTPS durumuna göre üretir.

Ancak konsol komutlarında HTTP varsayılan olur.

Bunu değiştirmek için servis parametrelerini kullanabilirsin:

```php
// config/services.php
$container->parameters()
    ->set('router.request_context.scheme', 'https')
    ->set('asset.request_context.secure', true);
```

---

### 🔐 Route Bazında HTTPS

Belirli bir route’un yalnızca HTTPS kullanmasını istiyorsan, `schemes()` seçeneğini ekle:

```php
// config/routes.php
use App\Controller\SecurityController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('login', '/login')
        ->controller([SecurityController::class, 'login'])
        ->schemes(['https']);
};
```

#### 🔸 Twig’de Davranış

```twig
{# Mevcut istek HTTPS ise #}
{{ path('login') }} 
{# → /login #}

{# Mevcut istek HTTP ise #}
{{ path('login') }} 
{# → https://example.com/login (mutlak URL üretir) #}
```

> ⚙️ Ayrıca bu gereksinim **gelen isteklerde** de uygulanır.
>
> Yani `/login`’e HTTP ile erişilirse Symfony otomatik olarak HTTPS’ye yönlendirir.

---

### 🌐 Tüm Route’ları HTTPS’e Zorlamak

```php
// config/routes/attributes.php
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->import('../../src/Controller/', 'attribute')
        ->schemes(['https']);
};
```

> 🔒 Alternatif olarak, Security komponentinde `requires_channel: https` ayarını da kullanabilirsin.

---

## ✍️ URI İmzalama (Signing URIs)

 **İmzalı URI** , içeriğine göre hesaplanmış bir hash değerini (_hash) içerir.

Böylece URI’nin değiştirilip değiştirilmediğini doğrulayabilirsin.

Symfony, bu işlem için `UriSigner` servisini sağlar.

---

### 🧩 Örnek: URI İmzalama ve Doğrulama

```php
// src/Service/SomeService.php
namespace App\Service;

use Symfony\Component\HttpFoundation\UriSigner;

class SomeService
{
    public function __construct(private UriSigner $uriSigner) {}

    public function someMethod(): void
    {
        $url = 'https://example.com/foo/bar?sort=desc';

        // URI'yi imzala (_hash parametresi eklenir)
        $signedUrl = $this->uriSigner->sign($url);
        // https://example.com/foo/bar?sort=desc&_hash=e4a21b9

        // İmzayı kontrol et
        $isValid = $this->uriSigner->check($signedUrl); // true

        // Request nesnesi üzerinden de doğrulanabilir
        // $this->uriSigner->checkRequest($request);
    }
}
```

---

### ⏳ Süre Sınırlı (Expiration) URI’ler

Bazı durumlarda (örneğin şifre sıfırlama linkleri) imzalı URL’lerin belirli bir süre sonra geçersiz olması istenir.

Symfony 7.1 ile birlikte, imzalı URI’lere son kullanma tarihi eklenebilir:

```php
$signedUrl = $this->uriSigner->sign($url, new \DateTimeImmutable('2050-01-01'));
// → _expiration=2524608000&_hash=...

$signedUrl = $this->uriSigner->sign($url, new \DateInterval('PT10S'));
// → Şu andan itibaren 10 saniye geçerli
```

> `_expiration` parametresi, saniye cinsinden UNIX zaman damgası olarak eklenir.

---

### 🧾 Hataları Ayırt Etme

Symfony 7.3 ile gelen `verify()` metodu, imza doğrulama hatalarını özel istisnalarla ayırır:

```php
use Symfony\Component\HttpFoundation\Exception\ExpiredSignedUriException;
use Symfony\Component\HttpFoundation\Exception\UnsignedUriException;
use Symfony\Component\HttpFoundation\Exception\UnverifiedSignedUriException;

try {
    $uriSigner->verify($uri);
} catch (UnsignedUriException) {
    // URI imzalanmamış
} catch (UnverifiedSignedUriException) {
    // İmza hatalı
} catch (ExpiredSignedUriException) {
    // URI süresi dolmuş
}
```

> 🕒 Eğer `symfony/clock` paketi kuruluysa,
>
> `UriSigner` testlerde zamanı taklit etmek için Symfony Clock servisini kullanabilir.
>
> Bu özellik Symfony **7.3** ile eklendi.

---

### 🚀 Özet

* Servislerde `UrlGeneratorInterface` ile URL oluştur.
* Twig şablonlarında `path()` veya `url()` kullan.
* CLI komutlarında `default_uri` tanımla.
* HTTPS’i `schemes()` veya global parametrelerle zorunlu kıl.
* `UriSigner` ile güvenli, imzalı ve süresi dolan URI’ler oluştur.


## 🧩 Sorun Giderme (Troubleshooting)

Routing ile çalışırken karşılaşabileceğin bazı yaygın hatalar ve çözümleri aşağıda açıklanmıştır. 👇

---

### ⚠️ Hata:

```
Controller "App\Controller\BlogController::show()" requires that you
provide a value for the "$slug" argument.
```

#### 💡 Neden Olur:

Controller metodun bir parametre alıyor (örneğin `$slug`):

```php
public function show(string $slug): Response
{
    // ...
}
```

Ancak route tanımında bu parametreye karşılık gelen bir `{slug}` bölümü  **yok** .

Örneğin:

```php
#[Route('/blog/show', name: 'blog_show')]
```

Bu durumda Symfony `$slug` değerini bulamadığı için hata verir.

#### ✅ Çözüm:

Route path’ine parametre ekle veya parametreye varsayılan değer tanımla.

**Seçenek 1 — Route path’ini düzelt:**

```php
#[Route('/blog/show/{slug}', name: 'blog_show')]
```

**Seçenek 2 — Controller parametresine varsayılan değer ata:**

```php
public function show(?string $slug = null): Response
{
    // ...
}
```

---

### ⚠️ Hata:

```
Some mandatory parameters are missing ("slug") to generate a URL for route "blog_show".
```

#### 💡 Neden Olur:

`blog_show` route’u bir `{slug}` parametresi içeriyor,

ancak URL oluştururken bu parametreyi göndermiyorsun.

Örneğin:

```php
$this->generateUrl('blog_show');
```

Bu durumda Symfony şu mesajı verir:

> “Zorunlu ‘slug’ parametresi eksik.”

#### ✅ Çözüm:

Route parametresini URL oluştururken mutlaka ekle.

**PHP’de:**

```php
$this->generateUrl('blog_show', ['slug' => 'my-post']);
```

**Twig’de:**

```twig
{{ path('blog_show', {slug: 'my-post'}) }}
```

---

### 🚀 Özet

| Hata                                      | Sebep                                       | Çözüm                                                    |
| ----------------------------------------- | ------------------------------------------- | ----------------------------------------------------------- |
| `$slug`eksik argüman hatası           | Route path’inde `{slug}`tanımlı değil | Route’a `{slug}`ekle veya varsayılan değer ata         |
| “Some mandatory parameters are missing” | URL oluştururken parametre geçilmedi      | `generateUrl()`veya `path()`içinde parametreyi gönder |
