## Servis Konteyneri (Service Container)

Uygulamanız birçok faydalı nesneyle doludur: bir **Mailer** nesnesi e-posta göndermenize yardımcı olabilir, bir başka nesne veritabanına veri kaydetmenizi sağlar. Uygulamanızın yaptığı hemen her şey aslında bu nesnelerden biri tarafından yapılır. Yeni bir **bundle** yüklediğinizde ise daha da fazla nesneye erişim sağlarsınız!

Symfony’de bu faydalı nesnelere **servis (service)** denir ve her servis, **servis konteyneri (service container)** adı verilen özel bir nesne içerisinde yaşar.

Konteyner, nesnelerin nasıl oluşturulduğunu merkezi bir şekilde yönetmenizi sağlar. Bu sayede hayatınızı kolaylaştırır, güçlü bir mimari sağlar ve **son derece hızlıdır!**

---

### Servisleri Alma ve Kullanma

Bir Symfony uygulamasını başlattığınız anda, konteyneriniz zaten birçok servis içerir. Bunlar, kullanmanız için hazır bekleyen araçlar gibidir.

Bir **controller** içinde, bir servisi konteynerden almak için sadece o servisin sınıf veya arayüz adını **type-hint** olarak belirtebilirsiniz.

Örneğin, bir şeyleri **loglamak (kaydetmek)** mı istiyorsunuz? Hiç sorun değil 👇

```php
// src/Controller/ProductController.php
namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    #[Route('/products')]
    public function list(LoggerInterface $logger): Response
    {
        $logger->info('Bak, bir servisi kullandım!');

        // ...
    }
}
```

---

### Hangi Servisler Mevcut?

Hangi servislerin kullanılabilir olduğunu öğrenmek için şu komutu çalıştırın:

```bash
php bin/console debug:autowiring
```

Bu komut, **autowiring** (otomatik bağımlılık atama) için kullanılabilecek tüm sınıf ve arayüzleri listeler:

```
Autowirable Types
=================

Aşağıdaki sınıflar ve arayüzler autowiring sırasında type-hint olarak kullanılabilir:

Bir logger örneğini tanımlar.
Psr\Log\LoggerInterface - alias: logger

İsteklerin yaşam döngüsünü yöneten istek yığını.
Symfony\Component\HttpFoundation\RequestStack - alias: request_stack

Tüm Router sınıflarının uygulaması gereken arayüz.
Symfony\Component\Routing\RouterInterface - alias: router.default

[...]
```

Controller metodlarında veya kendi servislerinizin içinde bu  **type-hint** ’leri kullandığınızda, Symfony otomatik olarak uygun servis nesnesini size iletir.

---

### Servisleri Keşfetmek

Dokümantasyon boyunca, konteynerde bulunan farklı servisleri nasıl kullanabileceğinizi göreceksiniz.

Aslında konteynerde çok daha fazla servis bulunur ve her bir servisin **benzersiz bir kimliği (ID)** vardır; örneğin `request_stack` veya `router.default`.

Tüm servislerin listesini görmek için şu komutu çalıştırabilirsiniz:

```bash
php bin/console debug:container
```

Ancak çoğu zaman bu kimliklerle uğraşmanıza gerek kalmaz.

Belirli bir servisi seçmek veya konteyneri nasıl debug edeceğinizi öğrenmek için şu bölümlere göz atın:

* **[Belirli bir servisi seçme rehberi](https://chatgpt.com/g/g-p-6904ef4ae8fc81918bdb521301b0c9c6-symfony/c/69051837-3a9c-8331-91c0-09f04319d882#)**
* **[Servis konteynerini debug etme ve servis listesini görme](https://chatgpt.com/g/g-p-6904ef4ae8fc81918bdb521301b0c9c6-symfony/c/69051837-3a9c-8331-91c0-09f04319d882#)**


## Servisleri Oluşturma ve Yapılandırma (Creating/Configuring Services in the Container)

Kendi kodunuzu da **servisler** hâlinde organize edebilirsiniz.

Örneğin, kullanıcılarınıza rastgele bir **mutlu mesaj** göstermeniz gerektiğini varsayalım. Bu kodu doğrudan controller içine yazarsanız, tekrar kullanımı mümkün olmaz. Bunun yerine, yeni bir sınıf oluşturmaya karar verirsiniz:

```php
// src/Service/MessageGenerator.php
namespace App\Service;

class MessageGenerator
{
    public function getHappyMessage(): string
    {
        $messages = [
            'Harika! Sistemi başarıyla güncelledin!',
            'Bugün gördüğüm en havalı güncellemelerden biriydi!',
            'Mükemmel iş! Devam et!',
        ];

        $index = array_rand($messages);

        return $messages[$index];
    }
}
```

Tebrikler 🎉 Artık ilk **servis sınıfınızı** oluşturdunuz!

Bu servisi controller içinde hemen kullanabilirsiniz:

```php
// src/Controller/ProductController.php
use App\Service\MessageGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    #[Route('/products/new')]
    public function new(MessageGenerator $messageGenerator): Response
    {
        // type-hint sayesinde konteyner, otomatik olarak
        // yeni bir MessageGenerator nesnesi oluşturur ve size iletir!

        $message = $messageGenerator->getHappyMessage();
        $this->addFlash('success', $message);
        // ...
    }
}
```

`MessageGenerator` servisini istediğinizde, konteyner bu sınıfın bir örneğini oluşturur ve size döner.

Ama eğer servisi hiç çağırmazsanız, asla oluşturulmaz — böylece **bellek** ve **performans** tasarrufu sağlanır.

Ek olarak, bu servis yalnızca **bir kez** oluşturulur: her çağrıldığında aynı örnek (instance) size sunulur.

---

### `services.yaml` veya `services.php` Dosyasında Otomatik Servis Yükleme

Symfony dokümantasyonu, yeni bir projede varsayılan olarak aşağıdaki servis yapılandırmasının kullanıldığını varsayar:

```php
// config/services.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function(ContainerConfigurator $container): void {
    // bu dosyadaki servisler için varsayılan yapılandırma
    $services = $container->services()
        ->defaults()
            ->autowire()      // Servislerin bağımlılıklarını otomatik olarak enjekte eder.
            ->autoconfigure() // Servisleri otomatik olarak komut, event subscriber vb. olarak kaydeder.
    ;

    // src/ altındaki sınıfları servis olarak kullanılabilir hâle getirir
    // her sınıf için, sınıfın tam adını (FQCN) ID olarak kullanır
    $services->load('App\\', '../src/');

    // bu dosyada tanımlanan servislerde sıralama önemlidir
    // çünkü yeni tanımlar, eskilerini *değiştirir*
};
```

`resource` seçeneği herhangi bir geçerli **glob pattern** (dosya yolu deseni) alabilir.

Bu yapılandırma sayesinde, `src/` dizinindeki herhangi bir sınıfı **manuel tanımlamaya gerek kalmadan** servis olarak kullanabilirsiniz.

Daha sonra, birden fazla servisi tek seferde **import etmek (içe aktarmak)** için `resource` seçeneğini nasıl kullanacağınızı öğreneceksiniz.

---

### Servisleri Hariç Tutma (Exclude)

Projenizde bazı dosya veya dizinlerin servis olarak yüklenmesini istemiyorsanız, `exclude` seçeneğini kullanabilirsiniz:

```php
// config/services.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function(ContainerConfigurator $container): void {
    // ...

    $services->load('App\\', '../src/')
        ->exclude('../src/{SomeDirectory,AnotherDirectory,Kernel.php}');
};
```

Böylece belirtilen dizinler ve dosyalar servis olarak kaydedilmez.

Eğer servislerinizi **manuel olarak tanımlamak** istiyorsanız, **açık (explicit) konfigürasyon** da yapabilirsiniz.

---

### Servisleri Belirli Symfony Ortamlarıyla Sınırlama

Bir servisin yalnızca belirli bir ortamda (ör. `dev`, `test`, `prod`) kaydedilmesini istiyorsanız, `#[When]` özniteliğini kullanabilirsiniz:

```php
use Symfony\Component\DependencyInjection\Attribute\When;

// SomeClass yalnızca "dev" ortamında kaydedilir
#[When(env: 'dev')]
class SomeClass
{
    // ...
}

// Bir sınıfa birden fazla When özniteliği ekleyebilirsiniz
#[When(env: 'dev')]
#[When(env: 'test')]
class AnotherClass
{
    // ...
}
```

Bir servisin **belirli bir ortamda kaydedilmemesini** istiyorsanız, `#[WhenNot]` özniteliğini kullanabilirsiniz:

```php
use Symfony\Component\DependencyInjection\Attribute\WhenNot;

// SomeClass, "dev" dışındaki tüm ortamlarda kaydedilir
#[WhenNot(env: 'dev')]
class SomeClass
{
    // ...
}

// Birden fazla ortamı dışlamak için birden fazla WhenNot ekleyebilirsiniz
#[WhenNot(env: 'dev')]
#[WhenNot(env: 'test')]
class AnotherClass
{
    // ...
}
```

> 💡 `#[WhenNot]` özniteliği **Symfony 7.2** sürümüyle birlikte tanıtılmıştır.
>
>
> ## Servis İçine Başka Servisleri veya Konfigürasyonu Enjekte Etme
>
> (Injecting Services/Config into a Service)
>
> Diyelim ki `MessageGenerator` sınıfı içinden **logger** servisine erişmeniz gerekiyor.
>
> Hiç sorun değil! Bunun için bir `__construct()` metodu oluşturup `LoggerInterface` tipinde bir `$logger` parametresi tanımlayın.
>
> Sonra bunu bir sınıf özelliğine atayıp istediğiniz yerde kullanın 👇
>
> ```php
> // src/Service/MessageGenerator.php
> namespace App\Service;
>
> use Psr\Log\LoggerInterface;
>
> class MessageGenerator
> {
>     public function __construct(
>         private LoggerInterface $logger,
>     ) {
>     }
>
>     public function getHappyMessage(): string
>     {
>         $this->logger->info('Mutlu bir mesaj bulunmak üzere!');
>         // ...
>     }
> }
> ```
>
> Hepsi bu kadar!
>
> Konteyner, `MessageGenerator` sınıfını oluştururken **otomatik olarak** `logger` servisini size iletecektir.
>
> Peki konteyner bunu nasıl biliyor?
>
> Cevap:  **Autowiring (otomatik bağımlılık atama)** .
>
> Buradaki kilit nokta, `__construct()` metodundaki  **`LoggerInterface` type-hint** ’idir.
>
> `services.yaml` veya `services.php` içinde `autowire: true` yapılandırması açık olduğu sürece, Symfony bu type-hint’e uygun servisi otomatik olarak bulur.
>
> Eğer bulamazsa, size açıklayıcı bir hata mesajı ve çözüm önerisi verir.
>
>> 💡 Bu yöntem, bir sınıfa bağımlılıkları `__construct()` üzerinden ekleme işlemidir.
>>
>> Buna **Dependency Injection (Bağımlılık Enjeksiyonu)** denir.
>>
>
> ---
>
> ### Hangi Type-Hint’i Kullanmalısınız?
>
> Kullanmak istediğiniz özelliğe uygun  **type-hint** ’i öğrenmek için iki seçeneğiniz vardır:
>
> 1. İlgili özelliğin veya bileşenin belgelerine bakabilirsiniz,
> 2. Veya aşağıdaki komutu çalıştırarak tüm **autowire edilebilir** type-hint’leri listeleyebilirsiniz:
>
> ```bash
> php bin/console debug:autowiring
> ```
>
> Bu komut örneğin şunu gösterir:
>
> ```
> A logger instance.
> Psr\Log\LoggerInterface - alias: monolog.logger
>
> Request stack that controls the lifecycle of requests.
> Symfony\Component\HttpFoundation\RequestStack - alias: request_stack
>
> RouterInterface is the interface that all Router classes must implement.
> Symfony\Component\Routing\RouterInterface - alias: router.default
> ```
>
> ---
>
> ### Servislere Değer (Scalar) ve Koleksiyon Enjekte Etme
>
> Sadece servisleri değil,  **string** ,  **sayı** , **boolean** veya **koleksiyon** değerlerini de servislerinize parametre olarak geçirebilirsiniz.
>
> Bunu doğrudan `config/services.php` dosyasında yapabilirsiniz:
>
> ```php
> // config/services.php
> namespace Symfony\Component\DependencyInjection\Loader\Configurator;
>
> use Symfony\Component\DependencyInjection\ContainerInterface;
> use Symfony\Component\DependencyInjection\Reference;
>
> return static function (ContainerConfigurator $container) {
>     $services = $container->services();
>
>     $services->set(App\Service\SomeService::class)
>         // string, sayısal veya boolean değerleri doğrudan geçebilirsiniz
>         ->arg(0, 'Foo')
>         ->arg(1, true)
>         ->arg(2, 7)
>         ->arg(3, 3.14)
>
>         // sabitler (const): yerleşik, kullanıcı tanımlı veya Enum olabilir
>         ->arg(4, E_ALL)
>         ->arg(5, \PDO::FETCH_NUM)
>         ->arg(6, Symfony\Component\HttpKernel\Kernel::VERSION)
>         ->arg(7, App\Config\SomeEnum::SomeCase)
>
>         // autowiring kullanmıyorsanız, servis argümanlarını manuel olarak geçebilirsiniz
>         ->arg(8, service('some-service-id')) # servis yoksa hata verir
>         # servis yoksa null döner
>         ->arg(9, new Reference('some-service-id', Reference::IGNORE_ON_INVALID_REFERENCE))
>
>         // karışık tiplerde koleksiyonlar da geçebilirsiniz
>         ->arg(10, [
>             'first' => true,
>             'second' => 'Foo',
>         ]);
>
>     // ...
> };
> ```
>
> Bu sayede servislerinizi esnek bir şekilde yapılandırabilirsiniz.
>
> ---
>
> ### Birden Fazla Servisi Yönetmek (Handling Multiple Services)
>
> Diyelim ki, sitenizde her güncelleme olduğunda yöneticinize bir **bilgilendirme e-postası** göndermek istiyorsunuz.
>
> Bunun için yeni bir sınıf oluşturuyorsunuz:
>
> ```php
> // src/Service/SiteUpdateManager.php
> namespace App\Service;
>
> use App\Service\MessageGenerator;
> use Symfony\Component\Mailer\MailerInterface;
> use Symfony\Component\Mime\Email;
>
> class SiteUpdateManager
> {
>     public function __construct(
>         private MessageGenerator $messageGenerator,
>         private MailerInterface $mailer,
>     ) {
>     }
>
>     public function notifyOfSiteUpdate(): bool
>     {
>         $happyMessage = $this->messageGenerator->getHappyMessage();
>
>         $email = (new Email())
>             ->from('admin@example.com')
>             ->to('manager@example.com')
>             ->subject('Site güncellendi!')
>             ->text('Birisi siteyi güncelledi. Ona şunu söyledik: '.$happyMessage);
>
>         $this->mailer->send($email);
>
>         return true;
>     }
> }
> ```
>
> Bu sınıf hem `MessageGenerator` hem de `MailerInterface` servislerine ihtiyaç duyar.
>
> Ama endişe etmeyin — bunları `__construct()` içinde **type-hint** olarak belirtmeniz yeterlidir!
>
> Symfony konteyneri, bu sınıfı oluştururken doğru servisleri otomatik olarak iletir.
>
> Şimdi bu servisi bir controller içinde şu şekilde kullanabilirsiniz:
>
> ```php
> // src/Controller/SiteController.php
> namespace App\Controller;
>
> use App\Service\SiteUpdateManager;
> use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
> use Symfony\Component\HttpFoundation\Response;
> use Symfony\Component\Routing\Attribute\Route;
>
> class SiteController extends AbstractController
> {
>     #[Route('/site/new')]
>     public function new(SiteUpdateManager $siteUpdateManager): Response
>     {
>         if ($siteUpdateManager->notifyOfSiteUpdate()) {
>             $this->addFlash('success', 'Bilgilendirme e-postası başarıyla gönderildi.');
>         }
>
>         // ...
>     }
> }
> ```
>
> Autowiring ve `__construct()` içindeki type-hint’ler sayesinde konteyner,
>
> `SiteUpdateManager` nesnesini oluşturur ve doğru bağımlılıkları otomatik olarak geçirir.
>
>> ⚙️ Çoğu durumda, bu sistem **manuel konfigürasyon gerekmeden** mükemmel şekilde çalışır.
>>
>
>
>
> ## Servis Argümanlarını Manuel Olarak Bağlamak (Manually Wiring Arguments)
>
> Bazı durumlarda, bir servisin aldığı argümanlar **autowiring** (otomatik bağımlılık atama) ile çözümlenemez.
>
> Örneğin, yönetici e-posta adresini **konfigüre edilebilir** hâle getirmek istediğinizi varsayalım 👇
>
> ```php
> // src/Service/SiteUpdateManager.php
> namespace App\Service;
>
> use App\Service\MessageGenerator;
> use Symfony\Component\Mailer\MailerInterface;
> use Symfony\Component\Mime\Email;
>
> class SiteUpdateManager
> {
>     public function __construct(
>         private MessageGenerator $messageGenerator,
>         private MailerInterface $mailer,
>         private string $adminEmail // 👈 yeni e-posta argümanı
>     ) {
>     }
>
>     public function notifyOfSiteUpdate(): bool
>     {
>         $email = (new Email())
>             ->from('admin@example.com')
>             ->to($this->adminEmail) // 👈 sabit değer yerine değişken
>             ->subject('Site güncellendi!')
>             ->text('Birisi siteyi güncelledi!')
>         ;
>
>         $this->mailer->send($email);
>         return true;
>     }
> }
> ```
>
> Bu değişikliği yaptıktan sonra sayfayı yenilediğinizde aşağıdaki hata mesajını göreceksiniz:
>
> ```
> Cannot autowire service "App\Service\SiteUpdateManager": argument "$adminEmail" of method "__construct()" must have a type-hint or be given a value explicitly.
> ```
>
> Bu hata mantıklıdır: konteyner, `$adminEmail` için hangi değerin kullanılacağını  **bilmez** .
>
> Ama çözüm basittir — argümanı yapılandırma dosyasında **manuel olarak** belirtebilirsiniz:
>
> ```php
> // config/services.php
> namespace Symfony\Component\DependencyInjection\Loader\Configurator;
>
> use App\Service\SiteUpdateManager;
>
> return function(ContainerConfigurator $container): void {
>     // ...
>
>     $services = $container->services();
>
>     $services->load('App\\', '../src/')
>         ->exclude('../src/{DependencyInjection,Entity,Kernel.php}');
>
>     // manuel olarak e-posta argümanını ekliyoruz
>     $services->set(SiteUpdateManager::class)
>         ->arg('$adminEmail', 'manager@example.com');
> };
> ```
>
> Artık konteyner, `SiteUpdateManager` servisini oluştururken `$adminEmail` parametresine
>
> **`manager@example.com`** değerini aktaracaktır.
>
> Diğer bağımlılıklar (`MessageGenerator`, `MailerInterface` vb.) ise **autowiring** ile otomatik olarak bağlanacaktır.
>
>> 💡 Endişelenmeyin — bu yapı kırılgan değildir.
>>
>> Eğer `$adminEmail` değişkeninin adını `$mainEmail` gibi bir şeyle değiştirirseniz, Symfony size net bir hata mesajı verir.
>>
>
> ---
>
> ## Servis Parametreleri (Service Parameters)
>
> Servis konteyneri sadece **servis nesnelerini** değil, aynı zamanda **konfigürasyon değerlerini** de tutabilir.
>
> Bu değerlere **parametre (parameter)** denir.
>
> Symfony yapılandırmasında parametreler, düz anahtar–değer çiftleri hâlindedir
>
> ve farklı veri türlerini destekler:  **string** ,  **boolean** ,  **array** ,  **binary** , **PHP constant** vb.
>
> Ayrıca servislerle ilgili özel bir parametre tipi daha vardır.
>
> YAML’de `@` ile başlayan bir string, **servis kimliği (ID)** olarak yorumlanır.
>
> XML’de `type="service"` özniteliği kullanılır,
>
> PHP konfigürasyonunda ise `service()` fonksiyonu tercih edilir 👇
>
> ```php
> // config/services.php
> namespace Symfony\Component\DependencyInjection\Loader\Configurator;
>
> use App\Service\MessageGenerator;
>
> return function(ContainerConfigurator $container): void {
>     $services = $container->services();
>
>     $services->set(MessageGenerator::class)
>         ->args([service('logger')]); // 👈 logger servisini enjekte ediyoruz
> };
> ```
>
> Konteyner parametreleriyle çalışmak için aşağıdaki yardımcı metotları kullanabilirsiniz:
>
> ```php
> // Bir parametrenin tanımlı olup olmadığını kontrol eder
> $container->hasParameter('mailer.transport');
>
> // Bir parametrenin değerini alır
> $container->getParameter('mailer.transport');
>
> // Yeni bir parametre ekler
> $container->setParameter('mailer.transport', 'sendmail');
> ```
>
>> 🔹 Parametre adlarında kullanılan `.` (nokta) notasyonu bir  **okunabilirlik kolaylığıdır** ,
>>
>> parametreler hiyerarşik değildir; yani iç içe diziler şeklinde tanımlanamazlar.
>>
>> 🔹 Parametreleri sadece **konteyner derlenmeden önce** tanımlayabilirsiniz,
>>
>> çalışma zamanında (runtime) yeni parametre eklenemez.
>>
>
> Konteynerin nasıl derlendiğini öğrenmek için bkz:  **Compiling the Container** .
>
> ---
>
> ## Belirli Bir Servisi Seçmek (Choose a Specific Service)
>
> Daha önce oluşturduğumuz `MessageGenerator` servisi, bir `LoggerInterface` bekliyor:
>
> ```php
> // src/Service/MessageGenerator.php
> namespace App\Service;
>
> use Psr\Log\LoggerInterface;
>
> class MessageGenerator
> {
>     public function __construct(
>         private LoggerInterface $logger,
>     ) {
>     }
> }
> ```
>
> Ancak konteynerde `LoggerInterface`’i uygulayan birden fazla servis bulunur:
>
> örneğin `logger`, `monolog.logger.request`, `monolog.logger.php`, vb.
>
> Konteyner hangisini kullanacağını nasıl bilir?
>
> Genellikle yapılandırma, **varsayılan** olarak `logger` servisini seçer.
>
> Ama siz farklı bir logger kullanmak istiyorsanız, bunu açıkça belirtebilirsiniz:
>
> ```php
> // config/services.php
> namespace Symfony\Component\DependencyInjection\Loader\Configurator;
>
> use App\Service\MessageGenerator;
>
> return function(ContainerConfigurator $container): void {
>     $services = $container->services();
>
>     // logger yerine monolog.logger.request servisini kullanıyoruz
>     $services->set(MessageGenerator::class)
>         ->arg('$logger', service('monolog.logger.request'));
> };
> ```
>
> Bu örnekte konteyner, `MessageGenerator` oluşturulurken `$logger` argümanına
>
> **`monolog.logger.request`** ID’sine sahip servisi aktaracaktır.
>
>> 🔍 Kullanılabilecek logger servislerini görmek için:
>>
>> ```bash
>> php bin/console debug:autowiring logger
>> ```
>>
>> Tüm servislerin tam listesini görmek için:
>>
>> ```bash
>> php bin/console debug:container
>> ```
>>
>
> ---
>
> ## Servisleri Kaldırmak (Remove Services)
>
> Bazı durumlarda bir servisin belirli bir ortamda **konteynerden kaldırılması** gerekebilir.
>
> Örneğin, test ortamında kullanılmasını istemediğiniz bir servis olabilir.
>
> ```php
> // config/services_test.php
> namespace Symfony\Component\DependencyInjection\Loader\Configurator;
>
> use App\RemovedService;
>
> return function(ContainerConfigurator $containerConfigurator) {
>     $services = $containerConfigurator->services();
>
>     // test ortamında RemovedService servisini kaldır
>     $services->remove(RemovedService::class);
> };
> ```
>
> Bu sayede, **test ortamında** konteyner artık `App\RemovedService` servisini içermez.
>
>
> ## Servislere Closure (Kapanış Fonksiyonu) Enjekte Etme
>
> (Injecting a Closure as an Argument)
>
> Bir servise **callable (çağrılabilir)** bir argüman, yani bir **closure (anonim fonksiyon)** enjekte etmek mümkündür.
>
> Örneğin `MessageGenerator` servisine bir fonksiyon argümanı ekleyelim 👇
>
> ```php
> // src/Service/MessageGenerator.php
> namespace App\Service;
>
> use Psr\Log\LoggerInterface;
>
> class MessageGenerator
> {
>     private string $messageHash;
>
>     public function __construct(
>         private LoggerInterface $logger,
>         callable $generateMessageHash,
>     ) {
>         $this->messageHash = $generateMessageHash();
>     }
>
>     // ...
> }
> ```
>
> Şimdi, bu hash değerini üretecek yeni bir **çağrılabilir (invokable)** servis oluşturalım:
>
> ```php
> // src/Hash/MessageHashGenerator.php
> namespace App\Hash;
>
> class MessageHashGenerator
> {
>     public function __invoke(): string
>     {
>         // Mesaj hash'ini hesapla ve döndür
>         return hash('sha256', uniqid((string) mt_rand(), true));
>     }
> }
> ```
>
> Servis yapılandırması ise şu şekilde olur:
>
> ```php
> // config/services.php
> namespace Symfony\Component\DependencyInjection\Loader\Configurator;
>
> use App\Service\MessageGenerator;
>
> return function(ContainerConfigurator $containerConfigurator): void {
>     $services = $containerConfigurator->services();
>
>     $services->set(MessageGenerator::class)
>         ->arg('$logger', service('monolog.logger.request'))
>         ->arg('$generateMessageHash', closure('App\Hash\MessageHashGenerator'));
> };
> ```
>
> Bu şekilde `MessageGenerator` oluşturulurken, `MessageHashGenerator` sınıfı
>
> bir **closure** olarak enjekte edilir ve çağrıldığında `__invoke()` metodu tetiklenir.
>
>> 💡 Closure’lar ayrıca **autowiring** ve özel  **attribute** ’lar (öznitelikler) aracılığıyla da enjekte edilebilir.
>>
>
> ---
>
> ## Argümanları İsme veya Tipe Göre Bağlama
>
> (Binding Arguments by Name or Type)
>
> Bazı durumlarda belirli argümanlara veya tiplere **varsayılan değer** atamak isteyebilirsiniz.
>
> Bunu `bind()` metodu ile yapabilirsiniz 👇
>
> ```php
> // config/services.php
> namespace Symfony\Component\DependencyInjection\Loader\Configurator;
>
> use Psr\Log\LoggerInterface;
>
> return function(ContainerConfigurator $container): void {
>     $services = $container->services()
>         ->defaults()
>             // Bu dosyada tanımlanan tüm servislerdeki $adminEmail argümanına bu değeri ata
>             ->bind('$adminEmail', 'manager@example.com')
>
>             // $requestLogger isminde bir argüman varsa, bu servisi ata
>             ->bind('$requestLogger', service('monolog.logger.request'))
>
>             // LoggerInterface tipine sahip tüm argümanlara bu servisi ata
>             ->bind(LoggerInterface::class, service('monolog.logger.request'))
>
>             // Hem isim hem de tip kombinasyonuna göre eşleşme
>             ->bind('string $adminEmail', 'manager@example.com')
>             ->bind(LoggerInterface::class.' $requestLogger', service('monolog.logger.request'))
>
>             // iterable türündeki $rules argümanına "app.foo.rule" etiketiyle işaretlenmiş servisleri ata
>             ->bind('iterable $rules', tagged_iterator('app.foo.rule'));
> };
> ```
>
>> `bind()` anahtarı `_defaults` altında tanımlandığında, o dosyada tanımlanan **tüm servisler için** geçerli olur.
>>
>> Argümanlar şu şekilde eşleştirilebilir:
>>
>> * **İsme göre** : örn. `$adminEmail`
>> * **Tipe göre** : örn. `Psr\Log\LoggerInterface`
>> * **İkisine birden** : örn. `Psr\Log\LoggerInterface $requestLogger`
>>
>
> Ayrıca `bind()` yapılandırmasını sadece belirli bir servise veya toplu servis yüklemelerinde de kullanabilirsiniz.
>
> ---
>
> ## Soyut (Abstract) Servis Argümanları
>
> (Abstract Service Arguments)
>
> Bazen bir servisin bazı argümanlarının değeri, **çalışma zamanında** belirlenir.
>
> Bu durumda konfigürasyon dosyasında kesin bir değer veremezsiniz.
>
> Bu gibi durumlarda `abstract_arg()` fonksiyonunu kullanarak argümanı tanımlayabilir ve açıklama ekleyebilirsiniz 👇
>
> ```php
> // config/services.php
> namespace Symfony\Component\DependencyInjection\Loader\Configurator;
>
> use App\Service\MyService;
>
> return function(ContainerConfigurator $container) {
>     $services = $container->services();
>
>     $services->set(MyService::class)
>         ->arg('$rootNamespace', abstract_arg('Bu değer Compiler Pass tarafından tanımlanmalı'));
> };
> ```
>
> Eğer çalışma zamanında bu soyut argüman bir değerle değiştirilmezse, Symfony aşağıdaki gibi bir hata fırlatır:
>
> ```
> RuntimeException:
> Argument "$rootNamespace" of service "App\Service\MyService" is abstract: should be defined by Pass.
> ```
>
> Bu, doğru zamanda gerekli değeri sağlamadığınız anlamına gelir.
>
> ---
>
> ## `autowire` Seçeneği
>
> (The autowire Option)
>
> Yukarıdaki örneklerde gördüğünüz gibi, `services.yaml` veya `services.php` dosyasında genellikle şu yapı bulunur:
>
> ```php
> $services->defaults()->autowire()->autoconfigure();
> ```
>
> `autowire: true` ayarı, bu dosyada tanımlanan tüm servislerin **bağımlılıklarının otomatik olarak çözülmesini** sağlar.
>
> Yani `__construct()` metodunda type-hint tanımlamanız yeterlidir; Symfony uygun servisi otomatik olarak geçirir.
>
> Tüm bu döküman aslında **autowiring** özelliği etrafında şekillenir.
>
> Daha fazla bilgi için: **Defining Service Dependencies Automatically (Autowiring)**
>
> ---
>
> ## `autoconfigure` Seçeneği
>
> (The autoconfigure Option)
>
> Aynı şekilde `autoconfigure: true`, konteynerin sınıf tipine göre otomatik konfigürasyon uygulamasını sağlar.
>
> Bu özellik genellikle  **service tag** ’lerini otomatik eklemek için kullanılır.
>
> Örneğin, bir **Twig extension** oluşturmak için normalde:
>
> * Sınıfı yazmanız,
> * Servis olarak kaydetmeniz,
> * Ve `twig.extension` etiketiyle işaretlemeniz gerekir.
>
> Ancak `autoconfigure: true` sayesinde bu adımlara gerek kalmaz.
>
> Eğer sınıfınız `Twig\Extension\ExtensionInterface` arayüzünü uygularsa, Symfony bu etiketi otomatik ekler.
>
> Ayrıca **autowiring** aktifse, constructor’daki argümanları da otomatik olarak çözer.
>
> Autoconfiguration aynı zamanda bazı **özniteliklerle (attributes)** de çalışır.
>
> Örneğin şu öznitelikler, otomatik olarak uygun servis etiketlerini uygular:
>
> * `#[AsMessageHandler]`
> * `#[AsEventListener]`
> * `#[AsCommand]`
>
> ---
>
> ## Servis Tanımlarını Doğrulama (Linting Service Definitions)
>
> Servis tanımlarını doğrulamak için şu komutu kullanabilirsiniz:
>
> ```bash
> php bin/console lint:container
> ```
>
> Veya ortam değişkenlerini de çözümleyerek kontrol etmek için (Symfony 7.2+):
>
> ```bash
> php bin/console lint:container --resolve-env-vars
> ```
>
> Bu komut, **container’ın doğru yapılandırıldığından emin olmak** için ek kontroller yapar.
>
> Özellikle **CI/CD (sürekli entegrasyon)** ortamlarında çalıştırılması önerilir.
>
>> ⚙️ Bu kontroller `CheckTypeDeclarationsPass` ve `CheckAliasValidityPass` adlı compiler pass’lerde uygulanır.
>>
>> Normalde performans kaybını önlemek için devre dışı bırakılmışlardır.
>>
>> Ancak `lint:container` komutu çalıştırıldığında etkinleştirilir.
>>
>
> * `CheckAliasValidityPass` **Symfony 7.1** sürümünde eklendi.
> * `--resolve-env-vars` seçeneği **Symfony 7.2** ile geldi.
>
> ---
>
> ## Public ve Private Servisler
>
> (Public Versus Private Services)
>
> Symfony’de tanımlanan tüm servisler varsayılan olarak  **private** ’tır.
>
> Yani `$container->get()` metodu ile doğrudan erişemezsiniz.
>
> En iyi uygulama (best practice) olarak:
>
> * Servisleri **private** bırakın,
> * Onlara doğrudan erişmek yerine **dependency injection** kullanın.
>
> Eğer servisleri tembel yüklemeyle (lazy load) almak istiyorsanız, **public** yapmak yerine
>
> **service locator** kullanmanız önerilir.
>
> Ama gerçekten public yapmak gerekiyorsa 👇
>
> ```php
> // config/services.php
> namespace Symfony\Component\DependencyInjection\Loader\Configurator;
>
> use App\Service\PublicService;
>
> return function(ContainerConfigurator $container): void {
>     $services = $container->services();
>
>     $services->set(PublicService::class)
>         ->public(); // 👈 servisi public yapar
> };
> ```
>
> Alternatif olarak, sınıfın kendisine `#[Autoconfigure]` özniteliği ekleyerek de public yapabilirsiniz:
>
> ```php
> // src/Service/PublicService.php
> namespace App\Service;
>
> use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
>
> #[Autoconfigure(public: true)]
> class PublicService
> {
>     // ...
> }
> ```
>
>
> ## Birçok Servisi Aynı Anda İçe Aktarma (Importing Many Services at Once with `resource`)
>
> Symfony’de, birden fazla servisi tek seferde tanımlamak için **`resource`** anahtarını kullanabilirsiniz.
>
> Varsayılan Symfony yapılandırması da bu yöntemi kullanır 👇
>
> ```php
> // config/services.php
> namespace Symfony\Component\DependencyInjection\Loader\Configurator;
>
> return function(ContainerConfigurator $container): void {
>     // ...
>
>     // src/ altındaki tüm sınıfları servis olarak kullanılabilir hale getirir
>     // her sınıf, tam sınıf adını (FQCN) servis kimliği olarak kullanır
>     $services->load('App\\', '../src/')
>         ->exclude('../src/{DependencyInjection,Entity,Kernel.php}');
> };
> ```
>
> Burada:
>
> * `resource` ve `exclude` değerleri **geçerli glob pattern** (dosya yolu şablonu) alabilir,
> * `exclude` seçeneğiyle belirli dosya veya klasörleri hariç tutabilirsiniz,
> * Ayrıca bir sınıfın  **Exclude attribute** ’u ile (`#[Exclude]`) o sınıfı özel olarak hariç tutmak da mümkündür.
>
> Bu yöntem, çok sayıda sınıfı hızla servis olarak **kullanılabilir** hale getirmenizi sağlar.
>
> Her servisin  **ID’si** , sınıfın tam adı (ör. `App\Service\MyService`) olur.
>
> Eğer içe aktardığınız bir servisi özelleştirmek isterseniz, onu aynı isimle yeniden tanımlayabilirsiniz:
>
> Örneğin, özel argümanlar vermek için “manuel bağlantı (manual wiring)” uygulayabilirsiniz.
>
> Ancak unutmayın: yeniden tanımlanan servis, `import`’tan gelen ayarları (ör. `public`, `tags`)  **devralmaz** ,
>
> yalnızca `_defaults` bölümündekileri devralır.
>
>> ⚙️ `exclude` ile hariç tutulan yollar, **geliştirme ortamında performansı artırır**
>>
>> çünkü bu dosyalar değiştirildiğinde konteyner yeniden oluşturulmaz.
>>
>
> ---
>
> ### Her Sınıf Servis mi Oluyor?
>
> Hayır!
>
> `src/` altındaki her sınıf otomatik olarak konteynere eklenmez.
>
> Bu yapı, yalnızca tüm sınıfları **“servis olarak kullanılabilir”** hale getirir.
>
> Eğer bir sınıf projede hiçbir yerde servis olarak kullanılmazsa, Symfony onu  **derlenmiş konteynere dahil etmez** .
>
> Bu, performans ve bellek açısından son derece verimlidir.
>
> ---
>
> ## Aynı Namespace Altında Birden Fazla Servis Tanımı
>
> (Multiple Service Definitions Using the Same Namespace)
>
> YAML formatı kullandığınızda, PHP namespace’i **konfigürasyonun anahtarı** olarak kullanılır.
>
> Bu nedenle aynı namespace altındaki sınıflar için birden fazla servis yapılandırması tanımlayamazsınız.
>
> ```php
> // config/services.php
> use Symfony\Component\DependencyInjection\Definition;
>
> $defaults = new Definition();
>
> // `$this`, geçerli loader nesnesine bir referanstır
> $this->registerClasses(
>     $defaults,
>     'App\\Domain\\',
>     '../src/App/Domain/*'
> );
> ```
>
> Birden fazla tanım yapmanız gerekiyorsa, **namespace** seçeneğini ve benzersiz bir anahtar adını kullanabilirsiniz:
>
> ```yaml
> # config/services.yaml
> services:
>     command_handlers:
>         namespace: App\Domain\
>         resource: '../src/Domain/*/CommandHandler'
>         tags: [command_handler]
>
>     event_subscribers:
>         namespace: App\Domain\
>         resource: '../src/Domain/*/EventSubscriber'
>         tags: [event_subscriber]
> ```
>
> Bu yöntem, aynı namespace içinde farklı amaçlara hizmet eden servisleri (örneğin komut işleyiciler ve olay dinleyiciler)
>
> ayrı gruplar hâlinde tanımlamanıza olanak tanır.
>
> ---
>
> ## Servisleri ve Argümanları Manuel Olarak Tanımlama
>
> (Explicitly Configuring Services and Arguments)
>
> `resource` ve `autowiring` özellikleri güçlüdür,
>
> ancak bazen servisleri **manuel olarak yapılandırmanız** gerekebilir.
>
> Örneğin, aynı sınıfı (`SiteUpdateManager`) iki farklı e-posta adresiyle kullanmak istiyorsunuz:
>
> ```php
> // config/services.php
> namespace Symfony\Component\DependencyInjection\Loader\Configurator;
>
> use App\Service\MessageGenerator;
> use App\Service\SiteUpdateManager;
>
> return function(ContainerConfigurator $container): void {
>     $services = $container->services();
>
>     // site_update_manager.superadmin servisi
>     $services->set('site_update_manager.superadmin', SiteUpdateManager::class)
>         ->autowire(false) // autowiring'i devre dışı bırakıyoruz
>         ->args([
>             service(MessageGenerator::class),
>             service('mailer'),
>             'superadmin@example.com',
>         ]);
>
>     // site_update_manager.normal_users servisi
>     $services->set('site_update_manager.normal_users', SiteUpdateManager::class)
>         ->autowire(false)
>         ->args([
>             service(MessageGenerator::class),
>             service('mailer'),
>             'contact@example.com',
>         ]);
>
>     // SiteUpdateManager type-hint edildiğinde varsayılan olarak "superadmin" versiyonu kullanılsın
>     $services->alias(SiteUpdateManager::class, 'site_update_manager.superadmin');
> };
> ```
>
> Bu örnekte:
>
> * İki ayrı servis (`site_update_manager.superadmin` ve `site_update_manager.normal_users`) tanımlanır.
> * `alias` sayesinde, bir controller’da `SiteUpdateManager` type-hint edildiğinde **superadmin** servisi varsayılan olarak enjekte edilir.
>
> Eğer alias oluşturmazsanız ve `src/` dizininden tüm servisler otomatik yükleniyorsa,
>
> Symfony üç servis oluşturur:
>
> 1. otomatik yüklenen `App\Service\SiteUpdateManager`,
> 2. `site_update_manager.superadmin`,
> 3. `site_update_manager.normal_users`.
>
> Bu durumda, type-hint edildiğinde otomatik yüklenen versiyon kullanılacağı için
>
> **alias tanımlamak** önerilir ✅
>
> ---
>
> ## Ortama (Environment) Göre Konfigürasyon
>
> (Injecting the Current Environment into Service Config)
>
> PHP konfigürasyon dosyaları (`services.php` veya `packages/*.php`) closure (kapanış) yapısıyla tanımlandığında,
>
> Symfony otomatik olarak bulunduğunuz ortamı (`dev`, `prod`, `test`) closure’a aktarabilir.
>
> ```php
> // config/packages/my_config.php
> namespace Symfony\Component\DependencyInjection\Loader\Configurator;
>
> return function(ContainerConfigurator $containerConfigurator, string $env): void {
>     // `$env` otomatik olarak doldurulur
>     // ortamınıza göre özel konfigürasyon yapabilirsiniz
>     if ($env === 'dev') {
>         // yalnızca geliştirme ortamı için servis ekle
>     }
> };
> ```
>
> Bu özellik, **farklı ortamlar için dinamik servis yapılandırması** oluşturmanıza olanak tanır.
>
> ---
>
> ### 🧩 Özet
>
> | Özellik                     | Açıklama                                                                                  |
> | ---------------------------- | ------------------------------------------------------------------------------------------- |
> | **`resource`**       | Belirtilen klasör altındaki tüm sınıfları servis olarak kullanılabilir hale getirir. |
> | **`exclude`**        | Belirli yolları veya dosyaları hariç tutar.                                              |
> | **`namespace`**      | Aynı namespace altında birden fazla servis grubu tanımlamak için kullanılır.          |
> | **`alias()`**        | Type-hint edildiğinde hangi servisin kullanılacağını belirler.                         |
> | **`$env`argümanı** | Ortam bazlı konfigürasyonlara erişim sağlar.                                            |
>
>
> ## Fonksiyonel Arayüzler (Functional Interfaces) için Adaptör Oluşturma
>
> (Generating Adapters for Functional Interfaces)
>
> Symfony’de, yalnızca **tek bir metodu** olan arayüzlere **fonksiyonel arayüz (functional interface)** denir.
>
> Bu arayüzler, bir **closure (anonim fonksiyon)** gibi davranır ama bir farkla:
>
> tek bir metot içerirler ve bu metot bir **isim** taşır.
>
> Bu sayede, kodunuzda type-hint olarak kullanabileceğiniz güçlü, tip güvenli yapılardır.
>
> ---
>
> ### 1️⃣ Bir Fonksiyonel Arayüz Tanımlama
>
> Aşağıda, basit bir `MessageFormatterInterface` örneği verilmiştir 👇
>
> ```php
> // src/Service/MessageFormatterInterface.php
> namespace App\Service;
>
> interface MessageFormatterInterface
> {
>     public function format(string $message, array $parameters): string;
> }
> ```
>
> ---
>
> ### 2️⃣ Aynı Metodu İçeren Bir Servis
>
> Diyelim ki `MessageUtils` adlı bir servisiniz var ve bu serviste `format()` adında bir metot bulunuyor:
>
> ```php
> // src/Service/MessageUtils.php
> namespace App\Service;
>
> class MessageUtils
> {
>     // diğer metotlar...
>
>     public function format(string $message, array $parameters): string
>     {
>         // mesajı biçimlendir ve döndür
>         return strtr($message, $parameters);
>     }
> }
> ```
>
> ---
>
> ### 3️⃣ `#[AutowireCallable]` ile Adaptör Otomatik Oluşturma
>
> Artık `MessageUtils` servisini, `MessageFormatterInterface` arayüzünü uygulayan bir adaptör gibi enjekte edebilirsiniz.
>
> Bunu yapmak için `#[AutowireCallable]` özniteliğini kullanıyoruz 👇
>
> ```php
> // src/Service/Mail/Mailer.php
> namespace App\Service\Mail;
>
> use App\Service\MessageFormatterInterface;
> use App\Service\MessageUtils;
> use Symfony\Component\DependencyInjection\Attribute\AutowireCallable;
>
> class Mailer
> {
>     public function __construct(
>         #[AutowireCallable(service: MessageUtils::class, method: 'format')]
>         private MessageFormatterInterface $formatter
>     ) {
>     }
>
>     public function sendMail(string $message, array $parameters): string
>     {
>         $formattedMessage = $this->formatter->format($message, $parameters);
>
>         // ... e-postayı gönder
>         return $formattedMessage;
>     }
> }
> ```
>
> Burada Symfony, sizin için **otomatik olarak** bir adaptör sınıfı oluşturur.
>
> Bu adaptör sınıf, `MessageFormatterInterface`’i uygular ve `format()` çağrısını
>
> `MessageUtils::format()` metoduna yönlendirir.
>
>> ⚙️ Yani `$this->formatter->format()` aslında `MessageUtils::format()` metodunu çağırır.
>>
>
> ---
>
> ### 4️⃣ Konfigürasyon Üzerinden Adaptör Oluşturma
>
> Aynı işlemi PHP yapılandırma dosyası aracılığıyla da yapabilirsiniz.
>
> Yani `#[AutowireCallable]` yerine doğrudan konfigürasyonla adaptör tanımlayabilirsiniz 👇
>
> ```php
> // config/services.php
> namespace Symfony\Component\DependencyInjection\Loader\Configurator;
>
> use App\Service\MessageFormatterInterface;
> use App\Service\MessageUtils;
>
> return function(ContainerConfigurator $container) {
>     $services = $container->services();
>
>     $services
>         ->set('app.message_formatter', MessageFormatterInterface::class)
>         ->fromCallable([inline_service(MessageUtils::class), 'format'])
>         ->alias(MessageFormatterInterface::class, 'app.message_formatter');
> };
> ```
>
> Bu yapılandırma, Symfony’ye şu talimatı verir:
>
> * `MessageFormatterInterface` arayüzü için bir adaptör sınıfı oluştur,
> * Bu adaptör, çağrılan her `format()` metodunu `MessageUtils::format()` metoduna yönlendirsin,
> * `MessageFormatterInterface` type-hint edildiğinde, `app.message_formatter` servisini kullan.
>
> ---
>
> ### 💡 Ne Oluyor?
>
> Symfony, sizin için bir **adapter (uyarlayıcı)** sınıf üretir:
>
> Bu sınıf `MessageFormatterInterface`’i uygular ve `format()` çağrılarını
>
> `MessageUtils` servisine yönlendirir.
>
> Bu sayede:
>
> * Kodunuz **daha sade** ve **bağımlılıklardan arınmış** olur,
> * Closure kullanmadan, **type-safe (tip güvenli)** bir çözüm elde edersiniz,
> * Autowiring ve Dependency Injection özellikleriyle tamamen uyumludur.
>
> ---
>
> ### 🔍 Özet
>
> | Özellik                          | Açıklama                                                                      |
> | --------------------------------- | ------------------------------------------------------------------------------- |
> | **Fonksiyonel Arayüz**     | Tek metodu olan arayüz. Closure gibi davranır.                                |
> | **`#[AutowireCallable]`** | Bir servisin metodunu arayüze otomatik olarak bağlar.                         |
> | **`fromCallable()`**      | Aynı işlemi yapılandırma dosyasıyla yapmanızı sağlar.                   |
> | **Avantaj**                 | Arayüzler üzerinden tip güvenli “callable” bağımlılıklar oluşturulur. |
