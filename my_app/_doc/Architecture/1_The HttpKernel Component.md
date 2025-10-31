
## HttpKernel Bileşeni

**HttpKernel** bileşeni, bir **Request** (istek) nesnesini **Response** (yanıt) nesnesine dönüştürmek için yapılandırılmış bir süreç sağlar. Bu süreçte **EventDispatcher** bileşenini kullanır. HttpKernel; tam kapsamlı bir framework (örneğin  **Symfony** ) ya da gelişmiş bir içerik yönetim sistemi (örneğin  **Drupal** ) oluşturmak için yeterince esnektir.

---

### 🔧 Kurulum

```bash
composer require symfony/http-kernel
```

Bu bileşeni bir Symfony uygulamasının dışında kullanıyorsanız, **Composer** tarafından sağlanan sınıf otomatik yükleme (autoloading) mekanizmasını etkinleştirmek için kodunuzda şu dosyayı dahil etmeniz gerekir:

```php
require 'vendor/autoload.php';
```

Daha fazla bilgi için ilgili makaleyi okuyabilirsiniz.

---

### 🔁 İstek-Yanıt (Request-Response) Döngüsü

Bu makale, **HttpKernel** özelliklerini bağımsız bir PHP uygulamasında nasıl kullanabileceğinizi açıklar.

Symfony uygulamalarında ise her şey önceden yapılandırılmış ve kullanıma hazırdır.

**Controller** ve **Events and Event Listeners** makalelerini okuyarak Symfony içinde controller’ların nasıl oluşturulduğunu ve olayların nasıl tanımlandığını öğrenebilirsiniz.

---

Her HTTP etkileşimi bir **istekle başlar** ve bir  **yanıtla biter** .

Bir geliştirici olarak göreviniz, isteğin bilgilerini (örneğin URL) okuyup bir yanıt oluşturacak PHP kodunu yazmaktır (örneğin bir HTML sayfası veya JSON verisi döndürmek).

Symfony uygulamalarında bu süreç şu şekilde işler:

1. Kullanıcı tarayıcıda bir kaynak ister,
2. Tarayıcı bu isteği sunucuya gönderir,
3. Symfony uygulamasına bir **Request** nesnesi iletir,
4. Uygulama, **Request** verilerini kullanarak bir **Response** nesnesi üretir,
5. Sunucu bu yanıtı tarayıcıya geri gönderir,
6. Tarayıcı kullanıcıya kaynağı görüntüler.

---

### ⚙️ Framework’ün Rolü

Genellikle, yönlendirme (routing), güvenlik (security) gibi tekrarlayan görevleri yönetmek için bir framework veya sistem kullanılır.

Bu sayede geliştirici her sayfa için yalnızca gerekli iş mantığını yazar.

Bu sistemlerin nasıl inşa edildiği büyük ölçüde değişiklik gösterebilir, ancak  **HttpKernel bileşeni** , istekten başlayıp uygun yanıtı oluşturma sürecini standartlaştırmak için bir arayüz sağlar.

Bu bileşen, sistemin mimarisi ne kadar farklı olursa olsun, herhangi bir uygulamanın veya framework’ün **kalbi** olacak şekilde tasarlanmıştır.

---

### 🧩 HttpKernelInterface

```php
namespace Symfony\Component\HttpKernel;

use Symfony\Component\HttpFoundation\Request;

interface HttpKernelInterface
{
    /**
     * @return Response Bir Response örneği döner
     */
    public function handle(
        Request $request,
        int $type = self::MAIN_REQUEST,
        bool $catch = true
    ): Response;
}
```

---

### 🔍 İç İşleyiş

`HttpKernel::handle()` — yani `HttpKernelInterface::handle()` metodunun somut (concrete) uygulaması —

bir **Request** ile başlayıp bir **Response** ile biten bir yaşam döngüsünü (lifecycle) tanımlar.

Bu yapı, Symfony’nin temel çalışma prensibini oluşturur:

Her gelen istek, olaylar ve listener’lar aracılığıyla işlenir ve sonuçta bir yanıt döndürülür.


![1761938523220](image/1_TheHttpKernelComponent/1761938523220.png)



## HttpKernel: Olaylar Tarafından Yönlendirilen Yaşam Döngüsü

HttpKernel bileşeninin yaşam döngüsünün detayları, yalnızca Symfony çekirdeğinin değil, bu çekirdeği kullanan her kütüphane veya framework’ün (örneğin Symfony Framework veya Drupal) nasıl çalıştığını anlamanın temelidir.

---

### ⚙️ HttpKernel ve Olay Tabanlı İşleyiş

`HttpKernel::handle()` metodu dahili olarak **olayları (events)** tetikleyerek çalışır.

Bu yaklaşım, yöntemi hem **esnek** hem de **soyut** hale getirir çünkü HttpKernel üzerine kurulu bir framework’te tüm işlemler aslında **event listener** (olay dinleyicileri) tarafından yapılır.

Bu belge, bu süreci adım adım açıklarken, Symfony Framework’ün — HttpKernel’in somut bir uygulaması olarak — bu adımları nasıl ele aldığını da gösterir.

---

### 🧱 Temel Kurulum

HttpKernel kullanımı için başlangıçta çok az adım gerekir.

Bir  **event dispatcher** , bir **controller resolver** ve bir **argument resolver** oluşturursunuz.

Daha sonra, aşağıda anlatılan olaylara tepki verecek  **event listener** ’ları eklersiniz.

```php
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\HttpKernel;

// Request nesnesini oluştur
$request = Request::createFromGlobals();

$dispatcher = new EventDispatcher();
// ... burada event listener'lar eklenir

// Controller ve argument resolver'ları oluştur
$controllerResolver = new ControllerResolver();
$argumentResolver = new ArgumentResolver();

// Kernel'i başlat
$kernel = new HttpKernel($dispatcher, $controllerResolver, new RequestStack(), $argumentResolver);

// Kernel'i çalıştır: request'i response'a dönüştür
$response = $kernel->handle($request);

// Response’u gönder
$response->send();

// kernel.terminate olayını tetikle
$kernel->terminate($request, $response);
```

Tam bir örnek için “ **A full working example** ” başlığına bakabilirsiniz.

Olaylara listener ekleme hakkında daha fazla bilgi için bkz.  **Creating an Event Listener** .

💡 Ayrıca HttpKernel bileşeniyle kendi framework’ünüzü oluşturmayı adım adım anlatan mükemmel bir eğitim serisi vardır.

Bkz.  **Introduction** .

---

## 🧩 1) `kernel.request` Olayı

**Tipik Amaçlar:**

Request’e ek bilgi eklemek, sistemin bazı kısımlarını başlatmak veya mümkünse doğrudan bir Response döndürmek (örneğin erişim kontrolü yapmak).

`HttpKernel::handle()` içinde tetiklenen ilk olay `kernel.request`’tir.

Bu olay birçok farklı listener’a sahip olabilir.

### 🔍 Ne işe yarar?

Bazı listener’lar — örneğin güvenlik katmanı — bir **Response** nesnesi oluşturacak kadar bilgiye sahip olabilir.

Örneğin, kullanıcı erişim iznine sahip değilse, bu listener doğrudan bir **RedirectResponse** (örneğin giriş sayfasına yönlendirme) veya **403 Access Denied** yanıtı döndürebilir.

Eğer bu aşamada bir **Response** döndürülürse, süreç doğrudan `kernel.response` aşamasına geçer.

Diğer listener’lar ise yalnızca sistemi hazırlar veya Request’e ek bilgi ekler.

Örneğin, bir listener, Request nesnesinin yerel ayarını (locale) belirleyebilir.

En yaygın listener’lardan biri  **RouterListener** ’dır.

Bu listener, gelen isteğe karşılık gelen route’u çözümler ve hangi controller’ın çağrılacağını belirler.

Request nesnesinde bulunan **attributes** özelliği, bu ek bilgileri saklamak için mükemmel bir yerdir.

Yani, router listener bir controller belirlediğinde, bunu Request’in `attributes` çantasında saklayabilir; böylece daha sonra **controller resolver** tarafından kullanılabilir.

> `kernel.request` olayının amacı ya doğrudan bir Response döndürmek ya da Request nesnesine ek bilgi (örneğin locale veya route bilgisi) eklemektir.

Eğer `kernel.request` sırasında bir Response döndürülürse, olayın  **propagation** ’ı durur — yani düşük öncelikli listener’lar çalışmaz.

---

### 🧠 Symfony Framework’te `kernel.request`

Symfony’de bu olayın en önemli listener’ı **RouterListener** sınıfıdır.

Bu sınıf yönlendirme (routing) işlemini gerçekleştirir ve `_controller` ile birlikte route içindeki değişkenleri (örneğin `{slug}`) içeren bir dizi döndürür.

Bu dizi daha sonra `Request` nesnesinin **attributes** özelliğine kaydedilir.

Henüz bir şey yapmaz, ama bir sonraki adımda (controller çözümlemede) kullanılır.

---

## ⚙️ 2) Controller’ın Çözülmesi (Resolve the Controller)

Eğer `kernel.request` aşamasında bir Response oluşturulmadıysa, bir sonraki adım  **controller** ’ın belirlenmesidir.

Controller, belirli bir sayfa için **Response** döndürmekten sorumlu olan PHP kodudur.

Tek gereksinim: **çağrılabilir (callable)** bir fonksiyon, metot veya closure olmasıdır.

Controller’ın nasıl belirleneceği tamamen uygulamanıza bağlıdır.

Bu işlem **controller resolver** tarafından yapılır — yani `ControllerResolverInterface`’i uygulayan bir sınıf.

```php
namespace Symfony\Component\HttpKernel\Controller;

use Symfony\Component\HttpFoundation\Request;

interface ControllerResolverInterface
{
    public function getController(Request $request): callable|false;
}
```

`HttpKernel::handle()` içinden bu arayüzün `getController()` metodu çağrılır.

Bu metod, `Request` nesnesini alır ve uygun bir PHP callable (controller) döndürür.

---

### 🧩 Symfony Framework’te Controller Resolver

Symfony Framework, **ControllerResolver** sınıfını (aslında ek işlevlere sahip bir alt sınıfını) kullanır.

Bu sınıf, **RouterListener** tarafından Request nesnesine eklenen bilgileri kullanır.

`getController()` metodu şu şekilde çalışır:

1. **Request attributes** içinde `_controller` anahtarını arar.

   (Bu bilgi genellikle RouterListener tarafından eklenmiştir.)
2. `_controller` dizesini bir PHP callable’a dönüştürür:

   * Eğer dize modern PHP namespace formatında değilse (örneğin `App\Controller\DefaultController::index`), dönüştürülür.

     Örneğin eski biçimdeki `FooBundle:Default:index` ifadesi

     `Acme\FooBundle\Controller\DefaultController::indexAction` haline getirilir.
3. Controller sınıfının yeni bir örneği oluşturulur (constructor argümanı olmadan).

---

## ⚙️ 3) `kernel.controller` Olayı

**Tipik Amaçlar:**

Controller çalıştırılmadan hemen önce sistemin bazı bölümlerini başlatmak veya controller’ı değiştirmek.

Controller belirlendikten sonra `HttpKernel::handle()` metodu **kernel.controller** olayını tetikler.

Bu olay, controller belirlendikten ama çalıştırılmadan önce yapılması gereken işlemler için idealdir.

### 🧠 Symfony’de Kullanımı

Symfony Framework’te bu olaya bağlı önemli bir listener  **CacheAttributeListener** ’dır.

Bu sınıf, controller’daki `#[Cache]` özniteliklerini okur ve Response üzerinde HTTP önbellekleme (cache) ayarlarını yapılandırır.

Ayrıca, profiler etkin olduğunda veri toplayan birkaç küçük listener daha vardır.

Olay dinleyicileri ayrıca `ControllerEvent::setController()` çağrısı yaparak controller callable’ını tamamen değiştirebilir.

---

## ⚙️ 4) Controller Argümanlarının Çözülmesi

Sonraki adımda `HttpKernel::handle()`,

`ArgumentResolverInterface::getArguments()` metodunu çağırır.

Hatırlayın: Controller, bir callable’dır.

Bu metod, bu callable’a aktarılacak argüman dizisini döndürür.

Bu sürecin nasıl işleyeceği tamamen uygulamanızın tasarımına bağlıdır, ancak Symfony’nin **ArgumentResolver** sınıfı iyi bir örnektir.

Artık kernel’in elinde:

* Bir callable (controller),
* Ve bu callable’a aktarılacak argümanların listesi vardır.

---

### 🧩 Symfony Framework’te Argümanların Çözülmesi

Symfony’de  **ArgumentResolver** , reflection kullanarak controller’ın hangi parametrelere sahip olduğunu belirler.

Daha sonra her argüman için aşağıdaki kuralları uygular:

1. Eğer `Request->attributes` çantasında, argüman adıyla eşleşen bir anahtar varsa, o değeri kullanır.

   (Örneğin `$slug` argümanı için `slug` anahtarı varsa, onun değeri kullanılır. Genellikle bu bilgi `RouterListener` tarafından eklenir.)
2. Eğer argüman Symfony’nin `Request` sınıfı ile type-hint edilmişse, doğrudan Request nesnesi geçilir.
3. Eğer argüman **variadic** (örneğin `...$params`) ise ve Request attributes’te buna karşılık gelen bir dizi varsa, tüm değerler variadic argümana aktarılır.

Bu davranış, `ValueResolverInterface` arayüzünü uygulayan **value resolver** sınıfları tarafından sağlanır.

Symfony varsayılan olarak dört adet value resolver uygular.

Ancak kendi **ValueResolverInterface** sınıfınızı yazarak ve  **ArgumentResolver** ’a geçirerek bu davranışı özelleştirebilirsiniz.

---


![1761938662035](image/1_TheHttpKernelComponent/1761938662035.png)


## 9) `kernel.exception` Olayı

**Tipik Amaç:** Bir istisnayı (exception) ele almak ve uygun bir **Response** döndürmek.

`HttpKernel::handle()` çalışırken herhangi bir noktada bir istisna fırlatılırsa, **`kernel.exception`** olayı tetiklenir.

Dahili olarak `handle()` metodu bir `try-catch` bloğu içinde çalışır.

Bir hata oluştuğunda, bu olay sayesinde sistem, bu istisnaya karşı uygun bir yanıt oluşturabilir.

---

### 🔍 ExceptionEvent Nesnesi

Bu olaya gönderilen her listener, bir **`ExceptionEvent`** nesnesi alır.

Bu nesne üzerinden `getThrowable()` metodunu çağırarak orijinal istisnaya erişebilirsiniz.

Tipik bir listener, belirli bir istisna türünü kontrol eder ve buna uygun bir **hata yanıtı (error Response)** oluşturur.

Örneğin, bir **404 sayfası** oluşturmak istiyorsanız, özel bir istisna türü fırlatabilir ve bu olayı dinleyen bir listener, bu istisnayı yakalayarak **404 Response** oluşturabilir.

Aslında, HttpKernel bileşeni bu işi sizin için yapan bir listener içerir:  **ErrorListener** .

Bu listener varsayılan olarak hataları yakalar ve uygun Response üretir.

> `ExceptionEvent` nesnesi, `isKernelTerminating()` metodunu da sunar.
>
> Bu metod, istisna fırlatıldığı anda kernel’in **terminate** aşamasında olup olmadığını anlamanızı sağlar.
>
> 🆕 Bu metod Symfony **7.1** sürümünde eklenmiştir.

> `kernel.exception` için bir Response ayarlandığında propagation durur —
>
> yani düşük öncelikli listener’lar çalıştırılmaz.

---

### ⚙️ Symfony Framework’te `kernel.exception`

Symfony Framework bu olaya iki temel listener bağlar:

#### 🧩 1. HttpKernel Bileşenindeki **ErrorListener**

Bu listener, HttpKernel bileşeninin çekirdeğinde bulunur ve şu görevleri üstlenir:

1. **Fırlatılan istisnayı** bir `FlattenException` nesnesine dönüştürür.

   Bu nesne, hatayla ilgili tüm bilgileri (istek, kod, mesaj, vs.) taşır, aynı zamanda **yazdırılabilir ve serileştirilebilir** bir yapıya sahiptir.
2. Eğer orijinal istisna  **HttpExceptionInterface** ’i uygularsa:

   * `getStatusCode()` ve `getHeaders()` metotları çağrılır,
   * Bu bilgiler `FlattenException` içine aktarılır.

     Böylece hata sayfası için doğru HTTP kodu ve başlıklar hazırlanır.

     Özel HTTP başlıkları eklemek istiyorsanız, `HttpException` sınıfından türeyen istisnalarınızda `setHeaders()` metodunu kullanabilirsiniz.
3. Eğer istisna  **RequestExceptionInterface** ’i uygularsa:

   * `FlattenException` durum kodu **400** olarak ayarlanır,
   * Diğer başlıklar değiştirilmez.
4. Belirtilen controller (yapıcıda listener’a geçirilir) çalıştırılır ve `FlattenException` ona parametre olarak gönderilir.

   Bu controller, hata sayfası için  **nihai Response** ’u üretir.

---

#### 🧩 2. Security Bileşenindeki **ExceptionListener**

Bu listener’ın amacı, **güvenlik istisnelerini** ele almak ve gerekirse kullanıcıyı kimlik doğrulamaya yönlendirmektir (örneğin, giriş sayfasına yönlendirme).

---

## 🔧 Olay Dinleyicisi (Event Listener) Oluşturmak

Gördüğünüz gibi, `HttpKernel::handle()` döngüsünde tetiklenen herhangi bir olaya listener ekleyebilirsiniz.

Tipik olarak bir listener, bir sınıf içindeki metottur, ancak aslında **herhangi bir çağrılabilir (callable)** olabilir.

Daha fazla bilgi için bkz.  **The EventDispatcher Component** .

---

### 🧠 Kernel Olayları Özeti

Her “kernel” olayı, **`KernelEvents`** sınıfında bir sabit olarak tanımlanmıştır.

Her listener’a, ilgili olayın durumunu temsil eden bir **event nesnesi** (KernelEvent alt sınıfı) iletilir:

| Olay Adı                       | KernelEvents Sabiti                    | Listener’a Geçilen Nesne   |
| ------------------------------- | -------------------------------------- | ---------------------------- |
| `kernel.request`              | `KernelEvents::REQUEST`              | `RequestEvent`             |
| `kernel.controller`           | `KernelEvents::CONTROLLER`           | `ControllerEvent`          |
| `kernel.controller_arguments` | `KernelEvents::CONTROLLER_ARGUMENTS` | `ControllerArgumentsEvent` |
| `kernel.view`                 | `KernelEvents::VIEW`                 | `ViewEvent`                |
| `kernel.response`             | `KernelEvents::RESPONSE`             | `ResponseEvent`            |
| `kernel.finish_request`       | `KernelEvents::FINISH_REQUEST`       | `FinishRequestEvent`       |
| `kernel.terminate`            | `KernelEvents::TERMINATE`            | `TerminateEvent`           |
| `kernel.exception`            | `KernelEvents::EXCEPTION`            | `ExceptionEvent`           |

---

## 🧩 Tam Çalışan Örnek

Aşağıda, yalnızca HttpKernel bileşenini kullanarak **basit bir çalışan örnek** yer almaktadır.

Bu örnek, event listener’ların, controller resolver’ların ve argument resolver’ların nasıl bir arada çalıştığını gösterir:

```php
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

// Rotaları oluştur
$routes = new RouteCollection();
$routes->add('hello', new Route('/hello/{name}', [
    '_controller' => function (Request $request): Response {
        return new Response(sprintf("Hello %s", $request->get('name')));
    },
]));

// Request nesnesini oluştur
$request = Request::createFromGlobals();

// Route eşleştiriciyi (matcher) oluştur
$matcher = new UrlMatcher($routes, new RequestContext());

// Event dispatcher oluştur
$dispatcher = new EventDispatcher();
$dispatcher->addSubscriber(new RouterListener($matcher, new RequestStack()));

// Controller ve argüman çözücüler
$controllerResolver = new ControllerResolver();
$argumentResolver = new ArgumentResolver();

// Kernel oluştur
$kernel = new HttpKernel($dispatcher, $controllerResolver, new RequestStack(), $argumentResolver);

// Request'i işle ve Response döndür
$response = $kernel->handle($request);
$response->send();

// kernel.terminate olayını tetikle
$kernel->terminate($request, $response);
```

Bu örnek, HttpKernel’in temel yaşam döngüsünü uçtan uca göstermektedir.

---

## 🔁 Alt İstekler (Sub Requests)

`HttpKernel::handle()` yalnızca **ana isteği (main request)** değil, **alt istekleri (sub request)** de işleyebilir.

Bir alt istek, normal bir Request gibidir ancak genellikle bir sayfanın tamamı yerine **küçük bir kısmını** render etmek için kullanılır.

Alt istekler genellikle controller içinden veya controller’ın render ettiği bir şablon içinden oluşturulur.

> Örneğin, bir sayfa içinde başka bir controller’ın içeriğini göstermek istediğinizde alt istek kullanabilirsiniz.

![1761941170323](image/1_TheHttpKernelComponent/1761941170323.png)


## 🔄 Alt İstek (Sub Request) Çalıştırma

Bir **alt istek (sub request)** çalıştırmak için yine `HttpKernel::handle()` metodunu kullanabilirsiniz,

ancak bu kez ikinci argümanı değiştirmeniz gerekir:

```php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

// ...

// yeni bir Request nesnesi oluştur
$request = new Request();

// örneğin, _controller bilgisini manuel olarak ayarlayabilirsiniz
$request->attributes->set('_controller', '...');

// SUB_REQUEST tipinde isteği çalıştır
$response = $kernel->handle($request, HttpKernelInterface::SUB_REQUEST);

// bu yanıtla bir işlem yapabilirsiniz
```

Bu, **tam anlamıyla yeni bir istek-yanıt döngüsü** oluşturur.

Yeni `Request` nesnesi tekrar işlenir ve bir `Response` nesnesine dönüştürülür.

Aradaki tek fark, bazı  **listener** ’ların (örneğin güvenlik mekanizmaları gibi) sadece **ana istek (main request)** üzerinde çalışmasıdır.

---

### 🔍 Ana ve Alt İstek Ayırımı

Her listener, `KernelEvent` sınıfından türetilen bir olay nesnesi alır.

Bu nesnenin `isMainRequest()` metodu kullanılarak, mevcut isteğin ana mı yoksa alt istek mi olduğu kontrol edilebilir.

Aşağıda yalnızca ana istekte çalışan bir listener örneği yer almaktadır:

```php
use Symfony\Component\HttpKernel\Event\RequestEvent;

// ...

public function onKernelRequest(RequestEvent $event): void
{
    if (!$event->isMainRequest()) {
        return; // alt isteklere tepki verme
    }

    // ana istek için çalışacak işlemler
}
```

---

### 🧩 `_format` Özelliği

Varsayılan olarak, bir isteğin `_format` özelliği **`html`** değerine sahiptir.

Eğer alt isteğiniz farklı bir formatta yanıt döndürüyorsa (örneğin  **JSON** ),

bunu açıkça ayarlayabilirsiniz:

```php
$request->attributes->set('_format', 'json');
```

Bu sayede alt isteğiniz, belirtilen formata uygun içerik döndürebilir.

---

## 📦 Kaynakların Bulunması (Locating Resources)

**HttpKernel** bileşeni, Symfony uygulamalarında kullanılan **bundle mekanizmasından** sorumludur.

Bundle’ların en önemli özelliklerinden biri, dosya sistemindeki fiziksel yollar yerine

**mantıksal yollar (logical paths)** kullanarak kaynaklara (config dosyaları, şablonlar, controller’lar, çeviri dosyaları vb.) erişmenizi sağlamasıdır.

Bu sayede, bir bundle’ın sistemde **tam olarak nerede yüklü olduğunu bilmeden** kaynaklarına erişebilirsiniz.

Örneğin, `FooBundle` adlı bir bundle’ın `Resources/config/` dizininde bulunan `services.xml` dosyasına şu şekilde erişebilirsiniz:

```
@FooBundle/Resources/config/services.xml
```

Bunu yaparken dosyanın fiziksel yolunu (`__DIR__/Resources/config/services.xml`) bilmeniz gerekmez.

Bu mekanizma, kernel tarafından sağlanan **`locateResource()`** metodu sayesinde mümkündür.

Bu metod, mantıksal yolları fiziksel dosya yollarına dönüştürür:

```php
$path = $kernel->locateResource('@FooBundle/Resources/config/services.xml');
```

---

## 📘 Daha Fazla Bilgi

* Symfony çekirdeği, yukarıda anlatılan tüm **kernel olaylarını** (`kernel.request`, `kernel.view`, `kernel.response` vb.) dahili olarak yönetir.
* Her bir olayın tetiklenme sırası, Symfony’nin **istek-yanıt yaşam döngüsünü** oluşturur.
* Bu mekanizma, framework’ün modüler, genişletilebilir ve olay tabanlı yapısının temelini oluşturur.

---

📄 **Lisans Bilgisi:**

Bu çalışma — kod örnekleri dâhil — **Creative Commons BY-SA 3.0** lisansı altındadır.
