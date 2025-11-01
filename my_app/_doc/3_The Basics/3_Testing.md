# Test Etme (Testing)

Kodunuzda her yeni satır yazdığınızda potansiyel olarak  **yeni hatalar da eklenir** .

Bu yüzden güvenilir ve kaliteli uygulamalar geliştirmek için kodunuzu **otomatik testlerle** kontrol etmelisiniz.

Symfony, testler için güçlü bir entegrasyon sunan **PHPUnit** kütüphanesiyle birlikte gelir.

Bu bölümde Symfony’de test yazmanın temellerini, test türlerini ve PHPUnit yapılandırmasını öğreneceksiniz.

Daha ileri seviye detaylar için resmi [PHPUnit belgelerine](https://phpunit.de/documentation.html) göz atabilirsiniz.

---

## 🧪 Test Türleri

Symfony’de test kavramları aşağıdaki şekilde sınıflandırılır:

| Test Türü                                                     | Açıklama                                                                                   |
| --------------------------------------------------------------- | -------------------------------------------------------------------------------------------- |
| **Unit Tests (Birim Testleri)**                           | Belirli birimlerin (örneğin bir sınıf veya metodun) doğru çalıştığını test eder. |
| **Integration Tests (Entegrasyon Testleri)**              | Birden fazla sınıfın veya servisin birlikte doğru çalıştığını test eder.          |
| **Application Tests (Uygulama veya Fonksiyonel Testler)** | Tüm uygulamanın dıştan içe (HTTP istekleriyle) doğru çalıştığını test eder.     |

> 💡 Diğer kaynaklarda farklı tanımlar görebilirsiniz — bu, Symfony’nin test sistemine özgü bir terminolojidir.

---

## ⚙️ Kurulum

Symfony test araçlarını yüklemek için şu komutu çalıştırın:

```bash
composer require --dev symfony/test-pack
```

Bu paket, test için gereken diğer bağımlılıkları (örneğin `phpunit/phpunit`) otomatik olarak yükler.

Kurulumdan sonra testlerinizi çalıştırmak için:

```bash
php bin/phpunit
```

Symfony, varsayılan olarak testlerinizi **`tests/`** dizininde arar.

Her test sınıfının ismi `*Test` ile bitmelidir (örnek: `BlogControllerTest`).

PHPUnit yapılandırması, proje kök dizinindeki `phpunit.dist.xml` dosyasında bulunur

(eski sürümlerde `phpunit.xml.dist` olarak adlandırılır).

> Eğer bu dosya eksikse şu komutla Symfony Flex tarifini yeniden uygulayabilirsiniz:
>
> ```bash
> composer recipes:install phpunit/phpunit --force -v
> ```

---

## 🧩 Unit Test (Birim Testi)

Birim testleri, **tekil sınıfların veya metodların** beklenen şekilde çalıştığını doğrular.

Symfony’de birim testleri, standart PHPUnit testleriyle **aynı şekilde** yazılır.

### 📁 Dosya Düzeni

Test edilen sınıfın dizin yapısı korunmalıdır.

Örneğin, `src/Form/UserType.php` dosyası için test şu konumda olmalıdır:

```
tests/Form/UserTypeTest.php
```

### ▶️ Testleri Çalıştırmak

Tüm testleri çalıştırmak için:

```bash
php bin/phpunit
```

Sadece belirli bir klasörü çalıştırmak için:

```bash
php bin/phpunit tests/Form
```

Sadece tek bir test dosyasını çalıştırmak için:

```bash
php bin/phpunit tests/Form/UserTypeTest.php
```

> Büyük test setlerinde, test türlerine göre alt dizinler oluşturmak iyi bir pratiktir:
>
> `tests/Unit/`, `tests/Integration/`, `tests/Application/` vb.

---

## 🔗 Integration Test (Entegrasyon Testi)

Entegrasyon testleri, **birden fazla sınıf veya servisin birlikte nasıl çalıştığını** test eder.

Symfony bu testlerde kullanılmak üzere `KernelTestCase` sınıfını sağlar.

```php
// tests/Service/NewsletterGeneratorTest.php
namespace App\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class NewsletterGeneratorTest extends KernelTestCase
{
    public function testSomething(): void
    {
        self::bootKernel(); // Kernel’i başlatır

        // test işlemleri...
    }
}
```

`KernelTestCase`, her test için Symfony çekirdeğini (kernel) yeniden başlatarak

testlerin birbirinden **bağımsız** çalışmasını garanti eder.

---

## 🧱 Test Ortamını (Environment) Ayarlamak

Testler Symfony çekirdeğini **test ortamında (`test`)** başlatır.

Bu sayede testlere özel ayarları `config/packages/test/` dizininde tanımlayabilirsiniz.

Örneğin, Twig testlerde daha katı hale getirilmiştir:

```php
// config/packages/test/twig.php
use Symfony\Config\TwigConfig;

return static function (TwigConfig $twig): void {
    $twig->strictVariables(true);
};
```

Testleri özel bir ortamda çalıştırmak veya `debug` modunu kapatmak isterseniz:

```php
self::bootKernel([
    'environment' => 'my_test_env',
    'debug' => false,
]);
```

> 💡 CI (Continuous Integration) ortamlarında testleri **debug=false** ile çalıştırmanız önerilir.
>
> Bu, performansı artırır ve gereksiz cache temizleme işlemlerini engeller.

Eğer testler temiz bir ortamda başlamıyorsa, cache’i manuel olarak silebilirsiniz:

```php
(new \Symfony\Component\Filesystem\Filesystem())
    ->remove(__DIR__.'/../var/cache/test');
```

---

## 🌍 Ortam Değişkenlerini Özelleştirme

Testlerde farklı veritabanı bağlantıları veya ayarlar kullanmanız gerekebilir.

Bunları `.env.test` dosyasında tanımlayabilirsiniz:

```dotenv
# .env.test
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name_test?serverVersion=8.0.37"
```

Yükleme sırası şu şekildedir:

1. `.env`
2. `.env.test`
3. `.env.test.local` (makineye özel test ayarları)

> `.env.local`  **test ortamında kullanılmaz** , böylece test sonuçları tutarlılığını korur.

---

## 🧰 Servisleri Testte Kullanmak

Entegrasyon testlerinde sıklıkla Symfony servislerine erişmeniz gerekir.

Bunun için `bootKernel()` sonrasında `static::getContainer()` metodunu kullanabilirsiniz:

```php
// tests/Service/NewsletterGeneratorTest.php
namespace App\Tests\Service;

use App\Service\NewsletterGenerator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class NewsletterGeneratorTest extends KernelTestCase
{
    public function testSomething(): void
    {
        // (1) Kernel’i başlat
        self::bootKernel();

        // (2) Servis container’ına eriş
        $container = static::getContainer();

        // (3) Servisi al ve test et
        $newsletterGenerator = $container->get(NewsletterGenerator::class);
        $newsletter = $newsletterGenerator->generateMonthlyNews();

        $this->assertEquals('...', $newsletter->getContent());
    }
}
```

> `static::getContainer()` tarafından dönen container, özel bir  **test container** ’dır.
>
> Bu sayede **tüm public servisler** ve **silinmemiş private servisler** erişilebilir hale gelir.

Eğer test etmek istediğiniz servis **private** olup container’dan kaldırılmışsa,

bu servisi `config/services_test.yaml` dosyasında **public** olarak tanımlayabilirsiniz.

---

## 🧠 Özet

| Konu                         | Açıklama                                                                      |
| ---------------------------- | ------------------------------------------------------------------------------- |
| **Test Kütüphanesi** | PHPUnit (symfony/test-pack ile entegre)                                         |
| **Test Dizinleri**     | `tests/Unit/`,`tests/Integration/`,`tests/Application/`                   |
| **KernelTestCase**     | Symfony çekirdeğini test ortamında başlatır                                |
| **Servis Erişimi**    | `static::getContainer()`ile yapılır                                         |
| **Ortam Dosyaları**   | `.env.test`,`.env.test.local`test ortamına özgü değişkenleri tanımlar |
| **Cache Yönetimi**    | `debug=false`ile performans artırılır, gerekirse manuel temizlenir         |

---

Symfony’nin test yapısı, hem küçük birim testlerinden hem de tüm uygulama davranışını simüle eden **fonksiyonel testlerden** oluşur.

Bu güçlü altyapı sayesinde, kodunuzun **her katmanını güvenle test edebilir** ve hata riskini minimuma indirebilirsiniz.


# Bağımlılıkların Taklit Edilmesi (Mocking Dependencies)

Testlerde bazen, test ettiğiniz servisin bir **bağımlılığını (dependency)** taklit etmeniz gerekir.

Bu, servisin **gerçek bağımlılıklarını izole ederek** yalnızca test edilmek istenen davranışa odaklanmanızı sağlar.

Symfony, test container’ı sayesinde özel bir konfigürasyona gerek kalmadan bu işlemi kolaylaştırır.

---

## 🎭 Örnek: Servis Bağımlılığını Mock Etme

Aşağıdaki senaryoda, `NewsletterGenerator` servisi `NewsRepositoryInterface` adlı bir bağımlılığa sahiptir.

Bu arayüz, `NewsRepository` adlı private servise yönlendirilen bir **alias** olarak tanımlıdır.

Testte, bu repository’nin gerçek versiyonu yerine **mock edilmiş (taklit)** bir versiyonu kullanmak istiyoruz:

```php
// tests/Service/NewsletterGeneratorTest.php
namespace App\Tests\Service;

use App\Contracts\Repository\NewsRepositoryInterface;
use App\Entity\News;
use App\Service\NewsletterGenerator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class NewsletterGeneratorTest extends KernelTestCase
{
    public function testSomething(): void
    {
        // Kernel’i başlat
        self::bootKernel();

        // Container’a eriş
        $container = static::getContainer();

        // Repository arayüzünü mockla
        $newsRepository = $this->createMock(NewsRepositoryInterface::class);

        // Mock davranışını tanımla
        $newsRepository->expects(self::once())
            ->method('findNewsFromLastMonth')
            ->willReturn([
                new News('some news'),
                new News('some other news'),
            ]);

        // Mock objesini container’a kaydet (orijinal servisin yerine geçer)
        $container->set(NewsRepositoryInterface::class, $newsRepository);

        // Servisi al (artık mock repository ile çalışır)
        $newsletterGenerator = $container->get(NewsletterGenerator::class);

        // Test et!
        // ...
    }
}
```

> ✅ **Avantajı:**
>
> Symfony’nin test container’ı, **private servislerle ve alias’larla etkileşime izin verir.**
>
> Bu yüzden ek bir yapılandırma yapmanız gerekmez.

---

# 🧰 Testler İçin Veritabanı Yapılandırması

Veritabanıyla etkileşime giren testlerin, diğer ortamlardaki (örneğin development veya production) veritabanlarını  **etkilememesi gerekir** .

Bu nedenle, testler için **ayrı bir veritabanı** kullanılmalıdır.

---

## ⚙️ Test Veritabanını Tanımlama

`.env.test.local` dosyasını oluşturup test ortamına özel `DATABASE_URL` değeri ekleyin:

```dotenv
# .env.test.local
DATABASE_URL="mysql://USERNAME:PASSWORD@127.0.0.1:3306/DB_NAME_test?serverVersion=8.0.37"
```

> 💡 Eğer tüm geliştiriciler aynı veritabanı ayarını kullanıyorsa, `.env.test` dosyasını kullanabilir ve  **repoya ekleyebilirsiniz** .

---

## 🏗️ Test Veritabanını Oluşturma

Aşağıdaki komutlar test veritabanını ve tablolarını oluşturur:

```bash
php bin/console --env=test doctrine:database:create
php bin/console --env=test doctrine:schema:create
```

Bu komutları test bootstrap sürecinde de otomatik olarak çalıştırabilirsiniz.

> 💡 Yaygın bir uygulama, test veritabanı adının sonuna `_test` eklemektir:
>
> `project_acme` → `project_acme_test`

---

# 🔁 Her Testten Önce Veritabanını Otomatik Sıfırlama

Testler, **birbirinden tamamen bağımsız** olmalıdır.

Eğer bir test veritabanını değiştirirse (örneğin bir entity ekler/siler), bu diğer testlerin sonucunu etkileyebilir.

Bu sorunu çözmek için `DAMA\DoctrineTestBundle` kullanabilirsiniz.

---

## ⚡ Kurulum

```bash
composer require --dev dama/doctrine-test-bundle
```

Sonra PHPUnit yapılandırmasına ekleyin:

```xml
<!-- phpunit.dist.xml -->
<phpunit>
    <extensions>
        <!-- PHPUnit 10+ için -->
        <bootstrap class="DAMA\DoctrineTestBundle\PHPUnit\PHPUnitExtension"/>
        <!-- Daha eski sürümler için -->
        <extension class="DAMA\DoctrineTestBundle\PHPUnit\PHPUnitExtension"/>
    </extensions>
</phpunit>
```

---

## 🔄 Nasıl Çalışır?

Bu eklenti, her testten **önce** bir **veritabanı işlemi (transaction)** başlatır

ve test bitince **otomatik olarak rollback (geri alım)** yapar.

Böylece her test, temiz bir veritabanı durumu ile başlar.

> 🔗 Ayrıntılı bilgi: [DAMA Doctrine Test Bundle Belgeleri](https://github.com/dmaicher/doctrine-test-bundle)

---

# 🧩 Test Verisi (Fixtures) Yükleme

Gerçek verilerle test yapmak yerine genellikle **sahte veya test verisi (fixtures)** kullanılır.

Doctrine, bu amaçla kullanılabilecek bir **fixtures kütüphanesi** sunar.

---

## 🪄 Kurulum

```bash
composer require --dev doctrine/doctrine-fixtures-bundle
```

Daha sonra boş bir fixture sınıfı oluşturun:

```bash
php bin/console make:fixtures
```

Örnek olarak, `ProductFixture` adlı bir sınıf oluşturabilirsiniz:

```php
// src/DataFixtures/ProductFixture.php
namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $product = new Product();
        $product->setName('Priceless widget');
        $product->setPrice(14.50);
        $product->setDescription('Ok, I guess it *does* have a price');

        $manager->persist($product);

        // Daha fazla test verisi eklenebilir
        $manager->flush();
    }
}
```

---

## 🧹 Veritabanını Boşaltma ve Fixture’ları Yeniden Yükleme

```bash
php bin/console --env=test doctrine:fixtures:load
```

Bu komut, test veritabanını sıfırlar ve tüm fixture sınıflarını yeniden yükler.

> Ayrıntılı bilgi: [DoctrineFixturesBundle Belgeleri](https://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html)

---

# 🧠 Özet

| Konu                           | Açıklama                                                                 |
| ------------------------------ | -------------------------------------------------------------------------- |
| **Mocking**              | Servis bağımlılıklarını taklit ederek izole testler yazmayı sağlar |
| **Test Veritabanı**     | `.env.test.local`ile ayrı bir veritabanı tanımlanır                  |
| **Otomatik Sıfırlama** | `dama/doctrine-test-bundle`testler arası veritabanı izolasyonu sağlar |
| **Fixture’lar**         | DoctrineFixturesBundle ile sahte test verileri yüklenir                   |
| **Avantaj**              | Testler birbirinden bağımsız, hızlı ve tekrarlanabilir hale gelir     |

---

Symfony’nin test sistemi,  **bağımlılıkların mock edilmesi** , **veritabanı izolasyonu**

ve **fixture yönetimi** özellikleriyle birlikte, profesyonel uygulamalar için güçlü ve sürdürülebilir bir test altyapısı sağlar.


# Uygulama Testleri (Application Tests)

Uygulama testleri, bir Symfony uygulamasının **tüm katmanlarının entegrasyonunu** test eder:

routelar, controllerlar, servisler, veri tabanı, view’lar (Twig şablonları) ve güvenlik sistemi gibi.

PHPUnit açısından diğer test türlerinden farklı değildir, ancak uygulama testlerinin **kendine özgü bir akışı vardır:**

1. Bir HTTP isteği gönderilir,
2. Sayfayla etkileşim yapılır (link tıklanır, form gönderilir vs.),
3. Yanıt doğrulanır,
4. Gerekirse süreç tekrarlanır.

Symfony’nin test araçlarını yüklemediyseniz önce şu komutu çalıştırın:

```bash
composer require --dev symfony/test-pack
```

---

## ✨ İlk Uygulama Testinizi Yazın

Uygulama testleri genellikle `tests/Controller/` dizininde yer alır

ve `WebTestCase` sınıfını genişletir.

Bu sınıf, `KernelTestCase` üzerine ek özellikler ekler (örneğin tarayıcı simülasyonu).

### 🛠️ Test Sınıfı Oluşturma

SymfonyMakerBundle ile test sınıfı oluşturabilirsiniz:

```bash
php bin/console make:test
```

Çıkan sorulara şu şekilde cevap verin:

```
 Which test type would you like?:
 > WebTestCase

 The name of the test class (e.g. BlogPostTest):
 > Controller\PostControllerTest
```

Sonuç olarak aşağıdaki sınıf oluşturulur:

```php
// tests/Controller/PostControllerTest.php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PostControllerTest extends WebTestCase
{
    public function testSomething(): void
    {
        // Kernel başlatılır ve "client" (tarayıcı simülatörü) oluşturulur
        $client = static::createClient();

        // Belirli bir sayfa isteği yapılır
        $crawler = $client->request('GET', '/');

        // Başarılı yanıt ve içerik kontrolü
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Hello World');
    }
}
```

Bu test, `/` adresine GET isteği yapar, HTTP yanıtının başarılı olduğunu

ve `h1` etiketi içinde “Hello World” metninin geçtiğini doğrular.

---

## 🕸️ Crawler Kullanımı

`request()` metodu bir `Crawler` nesnesi döndürür.

Bu nesne, DOM üzerinde CSS seçicileriyle içerik aramanıza veya sayfadaki elemanları saymanıza imkân tanır:

```php
$crawler = $client->request('GET', '/post/hello-world');
$this->assertCount(4, $crawler->filter('.comment'));
```

> 📘 Daha fazla bilgi: [The DOM Crawler Component](https://symfony.com/doc/current/components/dom_crawler.html)

---

## 🌐 İstek Gönderme (Making Requests)

`WebTestCase` içinde kullanılan `client`, bir tarayıcı gibi davranarak uygulamaya HTTP istekleri gönderir:

```php
$crawler = $client->request('GET', '/post/hello-world');
```

Bu metodun imzası şöyledir:

```php
public function request(
    string $method,
    string $uri,
    array $parameters = [],
    array $files = [],
    array $server = [],
    ?string $content = null,
    bool $changeHistory = true
): Crawler
```

Yani POST verileri, dosya yüklemeleri veya özel header’lar dahil tüm HTTP senaryolarını test edebilirsiniz.

> 💡 **Öneri:** URL’leri testlerde **hardcode** etmek (yani doğrudan `/post/42` yazmak)
>
> daha güvenilirdir. Böylece rota isimleri değiştiğinde testleriniz de hata verip sizi uyarır.

---

## 🔁 Birden Fazla İstek

Bir testte art arda birden fazla istek yapılabilir.

Ancak, Symfony her yeni istekten önce kernel’i yeniden başlatır.

Bu, container’ın sıfırlanmasını sağlar ama şu yan etkilere yol açabilir:

* Security token sıfırlanır
* Doctrine entity’leri detach edilir

Bunu önlemek için kernel yeniden başlatmak yerine reset edebilirsiniz:

```php
$client->disableReboot();
```

Eğer bu da yeterli değilse, `kernel.reset` etiketi kaldırılacak servisleri belirlemek için

test ortamına özel bir **compiler pass** ekleyebilirsiniz:

```php
// src/Kernel.php
namespace App;

use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class Kernel extends BaseKernel implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ('test' === $this->environment) {
            // Güvenlik token’ının sıfırlanmasını engelle
            $container->getDefinition('security.token_storage')->clearTag('kernel.reset');

            // Doctrine entity’lerinin detach edilmesini engelle
            $container->getDefinition('doctrine')->clearTag('kernel.reset');
        }
    }
}
```

---

## 🧭 Siteyi Dolaşmak (Browsing)

`client` nesnesi bir tarayıcı gibi çalışır ve aşağıdaki işlemleri destekler:

```php
$client->back();
$client->forward();
$client->reload();
$client->restart(); // tüm cookie’leri ve geçmişi temizler
```

---

## 🚦 Yönlendirmeler (Redirects)

Varsayılan olarak `client`, yönlendirmeleri **otomatik takip etmez.**

```php
$client->request('GET', '/old-page');
$crawler = $client->followRedirect();
```

Tüm yönlendirmeleri otomatik takip etmek isterseniz:

```php
$client->followRedirects();
```

Devre dışı bırakmak için:

```php
$client->followRedirects(false);
```

---

## 🔐 Kullanıcı Girişi (Authentication)

Korumalı sayfaları test etmek için `loginUser()` metodunu kullanabilirsiniz.

Gerçek bir form doldurma işlemi yapılmaz; Symfony test sırasında kullanıcıyı **programatik olarak oturum açmış gibi gösterir.**

```php
// tests/Controller/ProfileControllerTest.php
namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProfileControllerTest extends WebTestCase
{
    public function testVisitingWhileLoggedIn(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('john.doe@example.com');

        // Kullanıcıyı oturum açmış gibi göster
        $client->loginUser($testUser);

        $client->request('GET', '/profile');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Hello John!');
    }
}
```

### 💡 Ek Bilgi

* `loginUser()` metodu bir `TestBrowserToken` oluşturur ve session’a kaydeder.
* Stateless firewall kullanıyorsanız bu yöntem çalışmaz;

  her istekte uygun `Authorization` header’ı göndermelisiniz.

### 🧱 In-Memory Kullanıcı Kullanımı

Testlerde veritabanına gerek kalmadan kullanıcı oluşturabilirsiniz:

```php
use Symfony\Component\Security\Core\User\InMemoryUser;

$client = static::createClient();
$testUser = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
$client->loginUser($testUser);
```

Test ortamında kullanıcı tanımını güvenlik ayarlarında belirtin:

```yaml
# config/packages/security.yaml
when@test:
    security:
        providers:
            users_in_memory:
                memory:
                    users:
                        admin: { password: password, roles: ROLE_ADMIN }
```

---

## ⚙️ Özel HTTP Header’ları Göndermek

İstemciyi oluştururken HTTP header’larını belirtebilirsiniz:

```php
$client = static::createClient([], [
    'HTTP_HOST' => 'en.example.com',
    'HTTP_USER_AGENT' => 'MySuperBrowser/1.0',
]);
```

Veya her istekte ayrı tanımlayabilirsiniz:

```php
$client->request('GET', '/', [], [], [
    'HTTP_X_SESSION_TOKEN' => 'abc123',
]);
```

> Header isimleri RFC 3875 standardına uygun olmalıdır:
>
> tireleri `_` ile değiştirin, büyük harfe çevirin, `HTTP_` ile başlayın.

---

## ⚡ AJAX (XMLHttpRequest) Gönderimi

AJAX istekleri için `xmlHttpRequest()` metodunu kullanabilirsiniz.

Gerekli `HTTP_X_REQUESTED_WITH` header’ı otomatik olarak eklenir:

```php
$client->xmlHttpRequest('POST', '/submit', ['name' => 'Fabien']);
```

---

## 🧱 Hataları Raporlama (Exception Catching)

Varsayılan olarak test client, hataları yakalar ve loglara yazar.

Ancak hataları PHPUnit çıktısında görmek istiyorsanız bu özelliği kapatın:

```php
$client->catchExceptions(false);
```

---

## 🔍 Dahili Nesnelere Erişim

Test client, tarayıcıya ait dahili nesnelere erişmenizi sağlar:

```php
$history = $client->getHistory();
$cookieJar = $client->getCookieJar();

$request = $client->getRequest();          // HttpKernel Request
$internalRequest = $client->getInternalRequest();  // BrowserKit Request
$response = $client->getResponse();        // HttpKernel Response
$crawler = $client->getCrawler();          // Son Crawler
```

---

## 🧮 Profiler Verisine Erişim

Symfony Profiler, her istek hakkında ayrıntılı bilgi toplar (örneğin sorgu sayısı).

Profiler’ı testte etkinleştirmek için:

```php
$client->enableProfiler();
$crawler = $client->request('GET', '/profiler');

$profile = $client->getProfile();
```

Bu sayede bir sayfada çalıştırılan sorgu sayısını veya süresini test edebilirsiniz.

Detaylı bilgi için [How to Use the Profiler in a Functional Test](https://symfony.com/doc/current/testing/profiler.html) makalesine bakın.

---

## 🧠 Özet

| Konu                          | Açıklama                                                           |
| ----------------------------- | -------------------------------------------------------------------- |
| **Sınıf**             | `WebTestCase`                                                      |
| **Araç**               | `$client`— Tarayıcı simülasyonu yapar                          |
| **DOM İşlemleri**     | `Crawler`sınıfı ile CSS seçiciler kullanılabilir              |
| **Giriş Simülasyonu** | `loginUser()`ile kullanıcı oturumu açılır                     |
| **Yönlendirmeler**     | `followRedirect()`veya `followRedirects(true)`                   |
| **AJAX**                | `xmlHttpRequest()`metodu                                           |
| **Profiler**            | `enableProfiler()`ile etkinleştirilebilir                         |
| **Hata Ayıklama**      | `catchExceptions(false)`hataları PHPUnit çıktısında gösterir |

---

Symfony’nin `WebTestCase` yapısı sayesinde, uygulamanızın tamamını gerçek kullanıcı davranışlarını taklit ederek test edebilirsiniz —

bu da hem hata riskini azaltır hem de uygulamanın uçtan uca doğruluğunu garanti altına alır.


# 🎯 Symfony’de Yanıtla Etkileşim ve Uygulama Testlerinde Doğrulamalar

*(Interacting with the Response & Testing Assertions)*

Symfony’nin **WebTestCase** sınıfı, uygulamanızı bir tarayıcı gibi test etmenizi sağlar.

`Client` (istemci) ve `Crawler` nesneleri sayesinde sayfayla etkileşime geçebilir, formları doldurabilir, linklere tıklayabilir ve gelen yanıtı test edebilirsiniz.

---

## 🧭 Sayfa ile Etkileşim

### 🔗 Linklere Tıklamak

Bir sayfadaki bağlantıya tıklamak için `clickLink()` metodunu kullanabilirsiniz.

Bu, belirtilen metni veya `alt` özniteliğini içeren **ilk linki veya tıklanabilir görseli** bulur:

```php
$client = static::createClient();
$client->request('GET', '/post/hello-world');

$client->clickLink('Click here');
```

Daha fazla kontrol için `Crawler::selectLink()` kullanarak `Link` nesnesine erişebilirsiniz:

```php
$crawler = $client->request('GET', '/post/hello-world');
$link = $crawler->selectLink('Click here')->link();

// Link nesnesi üzerinden bilgi alınabilir:
$link->getUri();    // URL
$link->getMethod(); // HTTP metodu

// Linke tıklama
$client->click($link);
```

---

### 🧾 Formları Göndermek

Form göndermek için `submitForm()` metodunu kullanabilirsiniz:

```php
$client = static::createClient();
$client->request('GET', '/post/hello-world');

$crawler = $client->submitForm('Add comment', [
    'comment_form[content]' => 'This is a great post!',
]);
```

* İlk parametre: butonun  **metni** , **id’si** veya **name** değeri
* İkinci parametre: form alanlarına gönderilecek veriler

> 📝 Symfony, **formları değil butonları** seçer, çünkü bir form birden fazla buton içerebilir.

---

### ⚙️ `Form` Nesnesi ile Çalışmak

Daha fazla kontrol için `Crawler::selectButton()` kullanarak bir `Form` nesnesi alabilirsiniz:

```php
$crawler = $client->request('GET', '/post/hello-world');
$buttonNode = $crawler->selectButton('submit');
$form = $buttonNode->form();

// Alanlara değer atama
$form['my_form[name]'] = 'Fabien';
$form['my_form[subject]'] = 'Symfony rocks!';

// Formu gönderme
$client->submit($form);
```

Alternatif olarak formu gönderirken değerleri aynı anda geçebilirsiniz:

```php
$client->submit($form, [
    'my_form[name]' => 'Fabien',
    'my_form[subject]' => 'Symfony rocks!',
]);
```

---

### 🧰 Form Alanlarını Doldurma Örnekleri

```php
// Seçim kutusu (select) veya radio butonu seçmek
$form['my_form[country]']->select('France');

// Checkbox işaretlemek
$form['my_form[like_symfony]']->tick();

// Dosya yüklemek
$form['my_form[photo]']->upload('/path/to/lucas.jpg');

// Çoklu dosya yükleme
$form['my_form[files][0]']->upload('/path/to/lucas.jpg');
$form['my_form[files][1]']->upload('/path/to/lisa.jpg');
```

Form adını dinamik almak isterseniz:

```php
$formName = $form->getName();
$form["{$formName}[subject]"] = 'Dynamic example';
```

Formun gönderileceği değerleri görmek için:

```php
$form->getValues();
$form->getFiles();
$form->getPhpValues(); // PHP dizisi formatında döner
```

---

### 🌍 Özel Header veya Parametrelerle Form Göndermek

```php
$client->submit($form, [], ['HTTP_ACCEPT_LANGUAGE' => 'es']);
$client->submitForm('Submit', [], 'POST', ['HTTP_ACCEPT_LANGUAGE' => 'es']);
```

---

## ✅ Yanıtı Test Etmek (Assertions)

Form gönderip sayfayı ziyaret ettikten sonra, yanıtı test etmenin zamanı gelir.

Symfony, PHPUnit’in tüm assertion’larını destekler, ancak ayrıca **kendi yardımcı assertion metodlarını** da sağlar.

---

### 🔹 **Response (Yanıt) Assertions**

| Metot                                                            | Açıklama                                                  |
| ---------------------------------------------------------------- | ----------------------------------------------------------- |
| `assertResponseIsSuccessful()`                                 | Yanıtın 2xx (başarılı) olduğunu doğrular.            |
| `assertResponseStatusCodeSame(200)`                            | Belirli bir HTTP durum kodunu bekler.                       |
| `assertResponseRedirects('/login')`                            | Yanıtın yönlendirme olduğunu doğrular.                 |
| `assertResponseHasHeader('content-type')`                      | Header mevcut mu kontrol eder.                              |
| `assertResponseHeaderSame('content-type', 'application/json')` | Header değeri beklenenle aynı mı kontrol eder.           |
| `assertResponseHasCookie('PHPSESSID')`                         | Belirtilen cookie’nin yanıtla gönderildiğini doğrular. |
| `assertResponseCookieValueSame('theme', 'dark')`               | Cookie değerini test eder.                                 |
| `assertResponseIsUnprocessable()`                              | Yanıtın HTTP 422 (Unprocessable) olduğunu doğrular.     |

---

### 🔹 **Request Assertions**

| Metot                                         | Açıklama                                 |
| --------------------------------------------- | ------------------------------------------ |
| `assertRequestAttributeValueSame('id', 1)`  | Request attribute değerini test eder.     |
| `assertRouteSame('post_show', ['id' => 1])` | Route ismini ve parametrelerini test eder. |

---

### 🔹 **Browser Assertions**

| Metot                                            | Açıklama                             |
| ------------------------------------------------ | -------------------------------------- |
| `assertBrowserHasCookie('token')`              | Tarayıcıda cookie var mı test eder. |
| `assertBrowserCookieValueSame('token', 'xyz')` | Cookie değerini doğrular.            |

---

### 🔹 **Crawler (DOM) Assertions**

| Metot                                           | Açıklama                                                |
| ----------------------------------------------- | --------------------------------------------------------- |
| `assertSelectorExists('h1')`                  | Belirtilen CSS seçici sayfada var mı kontrol eder.      |
| `assertSelectorCount(3, '.comment')`          | CSS seçicisine uyan element sayısını test eder.       |
| `assertSelectorTextContains('h1', 'Welcome')` | Seçicide beklenen metin geçiyor mu kontrol eder.        |
| `assertSelectorTextSame('h1', 'Homepage')`    | Seçicideki metin tam olarak eşleşiyor mu kontrol eder. |
| `assertPageTitleSame('My Blog')`              | `<title>`etiketinin içeriğini test eder.              |
| `assertInputValueSame('username', 'john')`    | Form alanı değerini kontrol eder.                       |
| `assertCheckboxChecked('agree_terms')`        | Checkbox işaretli mi kontrol eder.                       |

---

### 🔹 **Mailer Assertions**

Symfony’nin Mailer bileşenini test etmek için:

| Metot                                             | Açıklama                                  |
| ------------------------------------------------- | ------------------------------------------- |
| `assertEmailCount(1)`                           | Gönderilen e-posta sayısını doğrular.  |
| `assertEmailTextBodyContains($email, 'Hello')`  | E-postanın metin gövdesinde içerik arar. |
| `assertEmailHtmlBodyContains($email, '<h1>')`   | HTML gövdesinde içerik arar.              |
| `assertEmailSubjectContains($email, 'Welcome')` | E-posta konusunu test eder.                 |

---

### 🔹 **Notifier Assertions**

Bildirim (Notification) bileşenini test etmek için:

| Metot                                                  | Açıklama                                              |
| ------------------------------------------------------ | ------------------------------------------------------- |
| `assertNotificationCount(2)`                         | Bildirim sayısını kontrol eder.                      |
| `assertNotificationSubjectContains($notif, 'Order')` | Bildirim başlığında metin geçiyor mu kontrol eder. |

---

### 🔹 **HttpClient Assertions**

HTTP isteklerini test etmek için  **profiler** ’ı etkinleştirin:

```php
$client->enableProfiler();
```

Ardından şu assertion’ları kullanabilirsiniz:

| Metot                                                          | Açıklama                                             |
| -------------------------------------------------------------- | ------------------------------------------------------ |
| `assertHttpClientRequest('https://api.example.com', 'POST')` | Belirli bir URL’ye istek gönderildiğini doğrular.  |
| `assertHttpClientRequestCount(3)`                            | Toplam gönderilen istek sayısını kontrol eder.     |
| `assertNotHttpClientRequest('https://bad.example.com')`      | Belirtilen URL’ye istek gönderilmediğini test eder. |

---

## 🧪 Uçtan Uca Testler (End-to-End / E2E)

Symfony’nin `WebTestCase` istemcisi, PHP seviyesinde test yapar — JavaScript çalıştırmaz.

Eğer **JavaScript dahil tüm uygulamayı gerçek bir tarayıcıda** test etmek istiyorsanız,

bunun için [**Symfony Panther**](https://symfony.com/doc/current/testing.html#panther) bileşenini kullanabilirsiniz.

Panther, Chrome veya Firefox gibi gerçek tarayıcıları otomatik olarak kontrol ederek

tam anlamıyla “kullanıcı gözüyle” test yapmanızı sağlar.

---

## 🧠 Özet

| Konu                               | Açıklama                                                       |
| ---------------------------------- | ---------------------------------------------------------------- |
| **Link Etkileşimi**         | `clickLink()`veya `selectLink()->link()`                     |
| **Form Gönderimi**          | `submitForm()`veya `submit($form)`                           |
| **Header & Cookie Testleri** | `assertResponseHasHeader()`,`assertBrowserHasCookie()`       |
| **DOM Doğrulamaları**      | `assertSelector*`metodlarıyla CSS seçici bazlı test         |
| **E-posta & Bildirim**       | `assertEmailCount()`,`assertNotificationCount()`             |
| **HttpClient Testleri**      | `assertHttpClientRequest()`,`assertHttpClientRequestCount()` |
| **E2E Testler**              | Gerçek tarayıcı testleri için Panther bileşeni              |

---
