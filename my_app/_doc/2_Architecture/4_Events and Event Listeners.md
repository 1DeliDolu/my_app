# Olaylar ve Olay Dinleyicileri

Symfony uygulamanız çalışırken, birçok olay bildirimi (event notification) tetiklenir. Uygulamanız bu bildirimleri dinleyebilir ve bunlara istediğiniz herhangi bir kodu çalıştırarak tepki verebilir.

Symfony, bir HTTP isteğini işlerken çekirdek (kernel) ile ilgili çeşitli olaylar üretir. Üçüncü taraf paketler de kendi olaylarını yayınlayabilir. Ayrıca, kendi kodunuz içinde de özel olaylar (custom events) yayınlayabilirsiniz.

Bu makaledeki tüm örneklerde tutarlılık sağlamak için aynı olay olan `KernelEvents::EXCEPTION` kullanılmaktadır. Kendi uygulamanızda farklı olaylar kullanabilir veya birkaç olayı aynı sınıf içinde karıştırabilirsiniz.

---

## 🧩 Olay Dinleyicisi (Event Listener) Oluşturma

Bir olayı dinlemenin en yaygın yolu, bir **event listener** sınıfı kaydetmektir:

```php
// src/EventListener/ExceptionListener.php
namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        // Exception nesnesini olaydan al
        $exception = $event->getThrowable();
        $message = sprintf(
            'Hata mesajı: %s (kod: %s)',
            $exception->getMessage(),
            $exception->getCode()
        );

        // Özel bir Response nesnesi oluştur
        $response = new Response();
        $response->setContent($message);
        // XSS saldırılarını önlemek için içerik türünü text olarak ayarla
        $response->headers->set('Content-Type', 'text/plain; charset=utf-8');

        // HttpExceptionInterface özel durumları için durum kodu ve başlıkları uygula
        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Olay nesnesine yeni response’u ayarla
        $event->setResponse($response);
    }
}
```

Bu sınıfı oluşturduktan sonra, Symfony’e bunun bir event listener olduğunu belirtmek için servis olarak kaydedip özel bir **tag** eklemeniz gerekir:

```php
// config/services.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\EventListener\ExceptionListener;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(ExceptionListener::class)
        ->tag('kernel.event_listener');
};
```

Symfony şu mantığı izleyerek hangi metodun çağrılacağına karar verir:

1. Eğer `kernel.event_listener` etiketi `method` özelliğini tanımlıyorsa, o metod çağrılır;
2. Eğer tanımlanmadıysa, `__invoke()` metodu aranır;
3. Eğer `__invoke()` da yoksa, bir istisna fırlatılır.

---

### 🔢 Öncelik (Priority)

`kernel.event_listener` etiketi için isteğe bağlı bir `priority` (öncelik) parametresi vardır. Bu parametre, dinleyicilerin çalıştırılma sırasını kontrol eder (varsayılan: `0`).

Değer ne kadar yüksekse, dinleyici o kadar erken çalıştırılır. Symfony’nin dahili dinleyicileri genellikle `-256` ile `256` arasında değerler kullanır, ancak siz herhangi bir pozitif veya negatif tam sayı kullanabilirsiniz.

---

### 🧭 Olay Belirtme (event attribute)

Bir başka isteğe bağlı öznitelik `event`’tir. Bu, `$event` parametresi türü açıkça belirtilmediğinde kullanılır.

Örneğin, `kernel.exception` olayı için `$event` nesnesi `ExceptionEvent` türündedir.

Symfony bu durumda şu sıralamayı takip eder:

1. Eğer `method` özniteliği tanımlıysa, o metod çağrılır.
2. Değilse, `on + PascalCase event adı` (ör. `onKernelException()`) aranır.
3. Eğer o da yoksa `__invoke()` aranır.
4. Hiçbiri yoksa bir istisna atılır.

---

## 🧱 PHP Attributes ile Event Listener Tanımlama

Bir event listener tanımlamanın alternatif yolu **PHP attribute** kullanmaktır: `#[AsEventListener]`.

Bu sayede yapılandırmayı dış dosyalara yazmak yerine doğrudan sınıf içinde yapabilirsiniz:

```php
namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
final class MyListener
{
    public function __invoke(CustomEvent $event): void
    {
        // ...
    }
}
```

Aynı sınıf içinde birden fazla attribute kullanarak birden fazla olay dinleyebilirsiniz:

```php
namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: CustomEvent::class, method: 'onCustomEvent')]
#[AsEventListener(event: 'foo', priority: 42)]
#[AsEventListener(event: 'bar', method: 'onBarEvent')]
final class MyMultiListener
{
    public function onCustomEvent(CustomEvent $event): void {}
    public function onFoo(): void {}
    public function onBarEvent(): void {}
}
```

Ayrıca attribute’ları doğrudan metodların üzerine de ekleyebilirsiniz:

```php
namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class MyMultiListener
{
    #[AsEventListener]
    public function onCustomEvent(CustomEvent $event): void {}

    #[AsEventListener(event: 'foo', priority: 42)]
    public function onFoo(): void {}

    #[AsEventListener(event: 'bar')]
    public function onBarEvent(): void {}
}
```

> 💡 `event` parametresi, metod parametresinde olay türü açıkça belirtilmişse zorunlu değildir.

---

## 🎧 Olay Abonesi (Event Subscriber) Oluşturma

Bir diğer yöntem ise **Event Subscriber** tanımlamaktır.

Bu sınıflar bir veya daha fazla olayı dinleyen birden fazla metot içerir.

Listener’lardan farkı, abone sınıfının hangi olayları dinlediğini **kendisi** belirtmesidir.

Aşağıda, aynı `kernel.exception` olayını dinleyen bir Event Subscriber örneği yer alır:

```php
// src/EventSubscriber/ExceptionSubscriber.php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ExceptionEvent::class => [
                ['processException', 10],
                ['logException', 0],
                ['notifyException', -10],
            ],
        ];
    }

    public function processException(ExceptionEvent $event): void {}
    public function logException(ExceptionEvent $event): void {}
    public function notifyException(ExceptionEvent $event): void {}
}
```

Symfony, `EventSubscriber` dizinindeki tüm sınıfları otomatik olarak servis olarak yükler.

Genellikle `services.yaml` dosyasında `autoconfigure: true` olarak ayarlandığından, ek yapılandırma gerekmez.

> Eğer metotlarınız çağrılmıyorsa, `EventSubscriber` dizininden servislerin yüklendiğini ve `autoconfigure` seçeneğinin aktif olduğunu kontrol edin.
>
> Gerekirse `kernel.event_subscriber` etiketi manuel olarak eklenebilir.

---

### 🧭 Özet

| Özellik                 | Event Listener                | Event Subscriber                          |
| ------------------------ | ----------------------------- | ----------------------------------------- |
| Tanım Yeri              | Servis veya attribute         | Sınıf içinde `getSubscribedEvents()` |
| Çoklu Olay Desteği     | Evet (birden fazla attribute) | Evet                                      |
| Olay Adlarını Belirtme | Tag veya attribute ile        | Sınıf içinde dönen diziyle            |
| Otomatik Kayıt          | `autoconfigure: true`ile    | Evet                                      |
| Kullanım Kolaylığı   | Basit olaylar için           | Kompleks, çoklu olaylar için            |

---


### İstek Olayları ve Tür Kontrolü

Bir sayfa, bir ana istek (main request) ve genellikle gömülü denetleyiciler (embedded controllers) kullanıldığında birden fazla alt istek (sub-request) gönderebilir. Symfony çekirdeğine ait olaylarla (core events) çalışırken, olayın bir **ana istek** mi yoksa **alt istek** mi olduğunu kontrol etmeniz gerekebilir:

```php
// src/EventListener/RequestListener.php
namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;

class RequestListener
{
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            // Ana istek değilse bir şey yapma
            return;
        }

        // ...
    }
}
```

Bazı işlemler (örneğin gerçek istek bilgilerini kontrol etmek gibi) yalnızca **ana istek** üzerinde yapılmalıdır; bu nedenle alt isteklerde çalıştırılmaları gerekmez.

---

### Dinleyiciler (Listeners) ve Aboneler (Subscribers)

Dinleyiciler ve aboneler aynı uygulamada bir arada kullanılabilir. Hangisini kullanacağınız genellikle kişisel tercihe bağlıdır. Ancak bazı küçük avantajları vardır:

* **Aboneler** , olay bilgilerini sınıf içinde tanımladıkları için daha kolay  **yeniden kullanılabilirler** . Symfony’nin kendi içinde aboneleri tercih etmesinin nedeni budur.
* **Dinleyiciler** ise daha  **esnektir** ; çünkü paketler (bundles), yapılandırma değerlerine göre her birini koşullu olarak etkinleştirebilir veya devre dışı bırakabilir.

---

### Olay Takma Adları (Event Aliases)

Bağımlılık enjeksiyonu aracılığıyla olay dinleyicileri ve aboneleri yapılandırırken, Symfony çekirdek olaylarına **ilgili olay sınıfının tam sınıf adı (FQCN)** ile de başvurabilirsiniz:

```php
// src/EventSubscriber/RequestSubscriber.php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class RequestSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // ...
    }
}
```

Symfony, bu FQCN değerlerini **orijinal olay adlarının takma adları** olarak ele alır. Bu eşleştirme hizmet konteyneri derlenirken yapılır, dolayısıyla FQCN kullanan dinleyiciler/aboneler, olay dağıtıcısını (`event dispatcher`) incelerken orijinal olay adı altında görünür.

Bu eşleştirme, özel olaylar için de genişletilebilir. Bunun için `AddEventAliasesPass` derleyici geçişini kaydedin:

```php
// src/Kernel.php
namespace App;

use App\Event\MyCustomEvent;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\DependencyInjection\AddEventAliasesPass;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddEventAliasesPass([
            MyCustomEvent::class => 'my_custom_event',
        ]));
    }
}
```

Bu derleyici geçişi mevcut takma ad listesini  **genişletir** , yani birden fazla `AddEventAliasesPass` örneğini farklı yapılandırmalarla güvenle kaydedebilirsiniz.

---

### Olay Dinleyicilerini Hata Ayıklama

Kayıtlı olay dinleyicilerini görmek için konsolu kullanabilirsiniz. Tüm olayları ve dinleyicilerini listelemek için:

```bash
php bin/console debug:event-dispatcher
```

Belirli bir olayın dinleyicilerini görmek için olay adını belirtin:

```bash
php bin/console debug:event-dispatcher kernel.exception
```

Belirli bir kısmi ada göre arama yapmak için:

```bash
php bin/console debug:event-dispatcher kernel   # kernel.exception, kernel.response vb.
php bin/console debug:event-dispatcher Security # örn. Symfony\Component\Security\Http\Event\CheckPassportEvent
```

Güvenlik sistemi her **firewall** için kendi olay dağıtıcısını kullanır. Belirli bir dağıtıcıyı görmek için `--dispatcher` seçeneğini kullanın:

```bash
php bin/console debug:event-dispatcher --dispatcher=security.event_dispatcher.main
```

---

### Denetleyici Öncesi ve Sonrası Filtreler

Web uygulamalarında, denetleyici eylemleri çalıştırılmadan **önce** veya **hemen sonra** bazı işlemlerin yapılması gerekebilir.

Bazı framework’lerde `preExecute()` ve `postExecute()` gibi yöntemler vardır, fakat Symfony’de bu yoktur.

Bunun yerine, **EventDispatcher** bileşeni sayesinde bu sürece çok daha güçlü şekilde müdahale edebilirsiniz.

---

### Örnek: Token Doğrulama

Diyelim ki bir API geliştiriyorsunuz. Bazı denetleyiciler (controllers) herkese açık, bazıları ise yalnızca belirli istemciler tarafından kullanılabiliyor.

Bu özel istemcilere kimlik doğrulama için bir **token** veriyorsunuz.

Controller çalışmadan önce, eylemin korumalı olup olmadığını kontrol etmeniz gerekir. Eğer korumalıysa, verilen token’ı doğrulamanız gerekir.

> Bu örnekte basitlik adına token’lar `config` dosyasında tanımlanmıştır. Veritabanı veya Security bileşeni kullanılmamaktadır.

---

#### Token Parametrelerini Tanımlayın

```php
// config/services.php
$container->setParameter('tokens', [
    'client1' => 'pass1',
    'client2' => 'pass2',
]);
```

---

#### Denetleyicileri Etiketleyin

`kernel.controller` (veya `KernelEvents::CONTROLLER`) olayı, her istek öncesinde, controller çalıştırılmadan hemen önce tetiklenir.

Hangi denetleyicilerin token doğrulamasına tabi tutulacağını belirlemek için boş bir arayüz oluşturabilirsiniz:

```php
namespace App\Controller;

interface TokenAuthenticatedController
{
    // ...
}
```

Bu arayüzü uygulayan bir denetleyici şu şekilde görünür:

```php
namespace App\Controller;

use App\Controller\TokenAuthenticatedController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class FooController extends AbstractController implements TokenAuthenticatedController
{
    public function bar(): Response
    {
        // ...
    }
}
```

---

#### Olay Abonesi (Subscriber) Oluşturun

Şimdi, controller çalışmadan önce çalışacak olan token kontrol mantığını bir **event subscriber** içinde tanımlayın:

```php
// src/EventSubscriber/TokenSubscriber.php
namespace App\EventSubscriber;

use App\Controller\TokenAuthenticatedController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class TokenSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private array $tokens
    ) {
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (is_array($controller)) {
            $controller = $controller[0];
        }

        if ($controller instanceof TokenAuthenticatedController) {
            $token = $event->getRequest()->query->get('token');
            if (!in_array($token, $this->tokens)) {
                throw new AccessDeniedHttpException('Bu işlem geçerli bir token gerektirir!');
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
```

---

Symfony, `EventSubscriber` dizinini otomatik olarak yükleyecek şekilde ayarlandığından, `TokenSubscriber` otomatik olarak çalışır.

`onKernelController()` yöntemi her istekte çağrılacaktır.

Eğer controller `TokenAuthenticatedController` arayüzünü uyguluyorsa, token doğrulaması yapılacaktır.

Bu şekilde, istediğiniz controller’lar için **ön filtre (before filter)** tanımlamış olursunuz.

> Eğer abonelik metodu çağrılmıyorsa, `EventSubscriber` dizininin yüklendiğinden ve `autoconfigure` seçeneğinin etkin olduğundan emin olun. Gerekirse manuel olarak `kernel.event_subscriber` etiketi ekleyebilirsiniz.



### Sonrası Filtreler (`kernel.response` Olayı ile)

Denetleyicinizden önce bir "hook" çalıştırabildiğiniz gibi, **denetleyici çalıştıktan sonra** da bir “hook” ekleyebilirsiniz.

Bu örnekte, token doğrulamasından geçmiş tüm yanıtların üzerine, token kullanılarak oluşturulmuş bir **SHA1 hash** eklemek isteyelim.

Symfony’nin çekirdek olaylarından biri olan **`kernel.response`** (veya `KernelEvents::RESPONSE`), denetleyici bir `Response` nesnesi döndürdükten sonra tetiklenir.

Bu olaya bir **“after listener”** eklemek için yeni bir dinleyici sınıfı oluşturup bu olayı dinleyecek şekilde kaydedebilirsiniz.

---

#### 1. İsteğe `auth_token` Bilgisi Ekleme

Önceki örnekteki `TokenSubscriber` sınıfında, doğrulama yapılan istekleri işaretlemek için `Request` nesnesinin `attributes` kısmına token’ı kaydedelim:

```php
public function onKernelController(ControllerEvent $event): void
{
    // ...

    if ($controller instanceof TokenAuthenticatedController) {
        $token = $event->getRequest()->query->get('token');
        if (!in_array($token, $this->tokens)) {
            throw new AccessDeniedHttpException('Bu işlem geçerli bir token gerektirir!');
        }

        // Token doğrulamasından geçmiş isteği işaretle
        $event->getRequest()->attributes->set('auth_token', $token);
    }
}
```

---

#### 2. `kernel.response` Olayını Dinleme

Şimdi aynı sınıfı, **`KernelEvents::RESPONSE`** olayını da dinleyecek şekilde genişletelim.

`onKernelResponse()` metodu, istekte `auth_token` işareti varsa yanıt başlıklarına özel bir `X-CONTENT-HASH` ekleyecek:

```php
// En üste şu use ifadesini ekleyin
use Symfony\Component\HttpKernel\Event\ResponseEvent;

public function onKernelResponse(ResponseEvent $event): void
{
    // onKernelController, isteği “token doğrulanmış” olarak işaretlemiş mi kontrol et
    if (!$token = $event->getRequest()->attributes->get('auth_token')) {
        return;
    }

    $response = $event->getResponse();

    // Yanıt içeriğini ve token’ı kullanarak bir hash oluştur
    $hash = sha1($response->getContent() . $token);
    $response->headers->set('X-CONTENT-HASH', $hash);
}

public static function getSubscribedEvents(): array
{
    return [
        KernelEvents::CONTROLLER => 'onKernelController',
        KernelEvents::RESPONSE => 'onKernelResponse',
    ];
}
```

Artık `TokenSubscriber`, her istekte hem **denetleyici öncesinde (`onKernelController`)**

hem de **denetleyici sonrasında (`onKernelResponse`)** çağrılacak.

`TokenAuthenticatedController` arayüzünü uygulayan controller’lar için:

* İstek başlatıldığında token doğrulaması yapılır.
* Yanıt oluşturulduğunda, ek olarak özel bir hash başlığı (`X-CONTENT-HASH`) eklenir.

Bu mekanizma, `Request` nesnesinin `attributes` özelliğini kullanarak olaylar arası veri paylaşmayı sağlar.

---

### Kalıtım Kullanmadan Bir Metodun Davranışını Özelleştirmek

Bazen bir metot çağrılmadan hemen **önce** veya tamamlandıktan **sonra** ek işlem yapmak isteyebilirsiniz.

Kalıtım (inheritance) kullanmak yerine, bu işlemleri metot içinde **olaylar (events)** aracılığıyla yapabilirsiniz:

```php
class CustomMailer
{
    // ...

    public function send(string $subject, string $message): mixed
    {
        // Metot çağrılmadan önce bir olay tetikle
        $event = new BeforeSendMailEvent($subject, $message);
        $this->dispatcher->dispatch($event, 'mailer.pre_send');

        // Olay dinleyicileri içeriği değiştirmiş olabilir
        $subject = $event->getSubject();
        $message = $event->getMessage();

        // Asıl metot işlemi
        $returnValue = ...;

        // Metot tamamlandıktan sonra bir olay tetikle
        $event = new AfterSendMailEvent($returnValue);
        $this->dispatcher->dispatch($event, 'mailer.post_send');

        return $event->getReturnValue();
    }
}
```

Bu örnekte iki olay tetiklenir:

* **`mailer.pre_send`** → metot çağrılmadan önce
* **`mailer.post_send`** → metot tamamlandıktan sonra

Her iki olay da özel `Event` sınıfları kullanarak dinleyicilere bilgi aktarır.

---

#### `BeforeSendMailEvent` Sınıfı

```php
// src/Event/BeforeSendMailEvent.php
namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class BeforeSendMailEvent extends Event
{
    public function __construct(
        private string $subject,
        private string $message,
    ) {
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}
```

---

#### `AfterSendMailEvent` Sınıfı

```php
// src/Event/AfterSendMailEvent.php
namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class AfterSendMailEvent extends Event
{
    public function __construct(
        private mixed $returnValue,
    ) {
    }

    public function getReturnValue(): mixed
    {
        return $this->returnValue;
    }

    public function setReturnValue(mixed $returnValue): void
    {
        $this->returnValue = $returnValue;
    }
}
```

Bu olay sınıfları, hem bilgi almayı (örneğin `getMessage()`),

hem de bilgiyi değiştirmeyi (örneğin `setMessage()`) mümkün kılar.

---

#### Olay Abonesi Örneği

Son olarak, `mailer.post_send` olayını dinleyip metot dönüş değerini değiştirebilen bir abone oluşturalım:

```php
// src/EventSubscriber/MailPostSendSubscriber.php
namespace App\EventSubscriber;

use App\Event\AfterSendMailEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MailPostSendSubscriber implements EventSubscriberInterface
{
    public function onMailerPostSend(AfterSendMailEvent $event): void
    {
        $returnValue = $event->getReturnValue();
        // Orijinal dönüş değerini değiştir
        // $returnValue = ...

        $event->setReturnValue($returnValue);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'mailer.post_send' => 'onMailerPostSend',
        ];
    }
}
```

---

🎉 Artık özel `CustomMailer` sınıfınızda bir metot çağrıldığında,

öncesinde veya sonrasında dinamik olarak davranış ekleyebilir ya da değiştirebilirsiniz.

Symfony’nin **EventDispatcher** sistemi sayesinde, bunu kalıtım olmadan, temiz ve genişletilebilir bir şekilde yapabilirsiniz.
