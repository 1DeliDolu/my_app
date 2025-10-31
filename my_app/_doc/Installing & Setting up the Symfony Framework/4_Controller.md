İşte metnin **modern Türkçe markdown** çevirisi:

---

# 🎮 Controller (Denetleyici)

Bir  **controller** , `Request` nesnesinden bilgi okuyan ve bir `Response` nesnesi oluşturan ve döndüren bir  **PHP fonksiyonudur** .

Bu yanıt bir  **HTML sayfası** ,  **JSON** ,  **XML** ,  **dosya indirmesi** ,  **yönlendirme** , **404 hatası** veya başka herhangi bir şey olabilir.

Controller, uygulamanızın bir sayfanın içeriğini oluşturmak için ihtiyaç duyduğu **iş mantığını (logic)** çalıştırır.

> Henüz ilk çalışan sayfanızı oluşturmadıysanız, [Symfony&#39;de İlk Sayfanızı Oluşturun](https://symfony.com/doc/current/page_creation.html) bölümüne göz atın ve sonra buraya geri dönün.

---

## 🧩 Basit Bir Controller

Bir controller herhangi bir **PHP callable** olabilir (fonksiyon, nesne üzerindeki bir metod veya Closure).

Ancak genellikle bir **controller sınıfı içindeki metod** olarak yazılır:

```php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LuckyController
{
    #[Route('/lucky/number/{max}', name: 'app_lucky_number')]
    public function number(int $max): Response
    {
        $number = random_int(0, $max);

        return new Response(
            '<html><body>Lucky number: '.$number.'</body></html>'
        );
    }
}
```

### Açıklama

Bu örnekte controller, `LuckyController` sınıfındaki `number()` metodudur.

* **Satır 2:** Symfony, PHP'nin `namespace` özelliğini kullanarak sınıfı isimlendirir.
* **Satır 4:** `Response` sınıfı içe aktarılır; controller bir `Response` nesnesi döndürmelidir.
* **Satır 7:** Sınıfın ismi teknik olarak fark etmez ama **Controller** ile bitirmek bir konvansiyondur.
* **Satır 10:** `max` parametresi, rota içindeki `{max}` alanı sayesinde metoda otomatik geçer.
* **Satır 14:** Controller bir `Response` nesnesi oluşturur ve döndürür.

---

## 🌐 URL ile Controller Eşleştirme

Bir controller’ın sonucunu görüntülemek için, bir **rota (route)** ile bir  **URL** ’ye bağlamanız gerekir.

Yukarıdaki örnekte bu, `#[Route('/lucky/number/{max}')]` ile yapılmıştır.

Tarayıcıda şu adrese gidin:

```
http://localhost:8000/lucky/number/100
```

Daha fazla bilgi için: [Routing (Yönlendirme)](https://symfony.com/doc/current/routing.html)

---

## 🧱 Temel Controller Sınıfı ve Servisler

Symfony, geliştirmeyi kolaylaştırmak için isteğe bağlı bir **base controller sınıfı** sağlar:

`AbstractController`. Bu sınıfı genişleterek birçok **yardımcı metoda** erişim sağlayabilirsiniz.

Aşağıdaki gibi düzenleyin:

```php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LuckyController extends AbstractController
{
    // ...
}
```

Artık `$this->render()` gibi birçok yardımcı metodu kullanabilirsiniz.

---

## 🔗 URL Üretimi

`generateUrl()` metodu, belirli bir rota için URL oluşturur:

```php
$url = $this->generateUrl('app_lucky_number', ['max' => 10]);
```

---

## 🔄 Yönlendirme (Redirect)

Kullanıcıyı başka bir sayfaya yönlendirmek için `redirectToRoute()` veya `redirect()` kullanabilirsiniz:

```php
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

// ...
public function index(): RedirectResponse
{
    return $this->redirectToRoute('homepage'); // başka route'a yönlendirir

    // aynı şeyin uzun hali:
    // return new RedirectResponse($this->generateUrl('homepage'));

    // kalıcı (301) yönlendirme
    return $this->redirectToRoute('homepage', [], 301);
    return $this->redirectToRoute('homepage', [], Response::HTTP_MOVED_PERMANENTLY);

    // parametreli yönlendirme
    return $this->redirectToRoute('app_lucky_number', ['max' => 10]);

    // mevcut query string'i koruyarak yönlendirme
    return $this->redirectToRoute('blog_show', $request->query->all());

    // mevcut route'a yeniden yönlendirme (örneğin Post/Redirect/Get deseni)
    return $this->redirectToRoute($request->attributes->get('_route'));

    // harici yönlendirme
    return $this->redirect('http://symfony.com/doc');
}
```

> ⚠️ **Uyarı:** `redirect()` metodu hedef URL’yi kontrol etmez.
>
> Kullanıcının sağladığı URL’lere yönlendirme yaparsanız, güvenlik açığı oluşabilir (unvalidated redirect).

---

## 🎨 Şablonları Render Etmek

HTML yanıtı döndürüyorsanız, bir **Twig şablonunu** render etmeniz gerekir:

```php
// templates/lucky/number.html.twig dosyasını render eder
return $this->render('lucky/number.html.twig', ['number' => $number]);
```

Daha fazla bilgi: [Şablon Oluşturma ve Kullanımı](https://symfony.com/doc/current/templates.html)

---

## ⚙️ Servisleri Kullanmak

Symfony birçok faydalı **service (servis)** ile birlikte gelir.

Bunlar şablon render etmek, e-posta göndermek, veritabanı sorgulamak gibi işlemleri yapar.

Bir servisi controller içinde kullanmak için, **tip belirterek (type-hint)** parametre olarak ekleyin.

Symfony otomatik olarak bu servisi enjekte eder:

```php
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
// ...

#[Route('/lucky/number/{max}')]
public function number(int $max, LoggerInterface $logger): Response
{
    $logger->info('We are logging!');
    // ...
}
```

Kullanabileceğiniz tüm servisleri görmek için:

```bash
php bin/console debug:autowiring
```

---

## 🎯 #[Autowire] Özelliği

Belirli bir servis veya parametreyi doğrudan enjekte etmek için `#[Autowire]` özelliğini kullanabilirsiniz:

```php
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;

class LuckyController extends AbstractController
{
    public function number(
        int $max,

        #[Autowire(service: 'monolog.logger.request')]
        LoggerInterface $logger,

        #[Autowire('%kernel.project_dir%')]
        string $projectDir
    ): Response
    {
        $logger->info('We are logging!');
        // ...
    }
}
```

Daha fazla bilgi: [Servis Bağımlılıklarını Otomatik Tanımlama (Autowiring)](https://symfony.com/doc/current/service_container/autowiring.html)

> Tüm servislerde olduğu gibi, **constructor injection** da kullanabilirsiniz.



İşte bu bölümü modern, okunabilir bir **Türkçe Markdown** formatında çevrilmiş hâli 👇

---

# ⚙️ Controller (Denetleyici) Oluşturma

Symfony’de yeni bir controller sınıfı oluşturmak için **Symfony Maker Bundle** kullanabilirsiniz.

Bu, zamandan tasarruf sağlar ve size otomatik olarak yapılandırılmış dosyalar üretir.

### 🧱 Yeni Controller Oluşturmak

```bash
php bin/console make:controller BrandNewController
```

Bu komut aşağıdaki dosyaları oluşturur:

```
created: src/Controller/BrandNewController.php
created: templates/brandnew/index.html.twig
```

### 🧩 CRUD (Create, Read, Update, Delete) Üretmek

Bir Doctrine Entity’sinden tam bir CRUD yapısı oluşturmak istiyorsanız:

```bash
php bin/console make:crud Product
```

Oluşturulan dosyalar:

```
created: src/Controller/ProductController.php
created: src/Form/ProductType.php
created: templates/product/_delete_form.html.twig
created: templates/product/_form.html.twig
created: templates/product/edit.html.twig
created: templates/product/index.html.twig
created: templates/product/new.html.twig
created: templates/product/show.html.twig
```

---

## 🚨 Hatalar ve 404 Sayfaları Yönetimi

Bir nesne bulunamadığında **404 (Not Found)** yanıtı döndürmelisiniz.

Bunu yapmak için özel bir istisna (exception) fırlatılır:

```php
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

// ...
public function index(): Response
{
    $product = ...; // veritabanından nesne alınır
    if (!$product) {
        throw $this->createNotFoundException('The product does not exist');

        // Yukarıdaki, aşağıdaki kısayoldur:
        // throw new NotFoundHttpException('The product does not exist');
    }

    return $this->render(/* ... */);
}
```

`createNotFoundException()` metodu, `NotFoundHttpException` nesnesi oluşturmak için bir kısayoldur.

Bu istisna Symfony içinde **otomatik olarak 404 HTTP yanıtını** tetikler.

Diğer örnek:

```php
// Bu istisna 500 hata kodu üretir
throw new \Exception('Something went wrong!');
```

> Symfony, `HttpException` sınıfından türetilmiş bir hata fırlatılırsa uygun HTTP durum kodunu kullanır.
>
> Aksi hâlde varsayılan olarak **500 (Internal Server Error)** döndürülür.

* Kullanıcıya **özel hata sayfası** gösterilir.
* Geliştirici modunda (“Debug”) **ayrıntılı hata sayfası** görüntülenir.

➡️ Özelleştirme için bakınız: [How to Customize Error Pages](https://symfony.com/doc/current/controller/error_pages.html)

---

## 📥 Request Nesnesini Controller Argümanı Olarak Kullanma

Bir isteğin (request) query parametrelerini, header bilgilerini veya yüklenen dosyaları okumak istiyorsanız,

bunlara `Request` nesnesi aracılığıyla erişebilirsiniz.

```php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

public function index(Request $request): Response
{
    $page = $request->query->get('page', 1);
    // ...
}
```

---

## 🤖 Request Nesnesinin Otomatik Eşleştirilmesi (Automatic Mapping)

Symfony, **istek (request)** verilerini controller parametrelerine  **otomatik olarak eşleştirebilir** .

Bunu yapmak için çeşitli **attribute’lar (öznitelikler)** kullanılır.

---

### 🔹 Sorgu Parametrelerini (Query) Tek Tek Eşleştirmek

Örnek istek:

```
https://example.com/dashboard?firstName=John&lastName=Smith&age=27
```

```php
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;

public function dashboard(
    #[MapQueryParameter] string $firstName,
    #[MapQueryParameter] string $lastName,
    #[MapQueryParameter] int $age,
): Response
{
    // ...
}
```

#### Desteklenen veri türleri:

* `string`
* `int`
* `float`
* `bool`
* `array`
* `\BackedEnum`
* `AbstractUid` (Symfony 7.3+)

#### Filtre (Filter) Desteği:

```php
public function dashboard(
    #[MapQueryParameter(filter: \FILTER_VALIDATE_REGEXP, options: ['regexp' => '/^\w+$/'])] string $firstName,
    #[MapQueryParameter] string $lastName,
    #[MapQueryParameter(filter: \FILTER_VALIDATE_INT)] int $age,
): Response
{
    // ...
}
```

---

### 🔸 Tüm Query String’i Bir Nesneye Eşleştirmek

Bir **DTO (Data Transfer Object)** sınıfı tanımlayalım:

```php
namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class UserDto
{
    public function __construct(
        #[Assert\NotBlank]
        public string $firstName,

        #[Assert\NotBlank]
        public string $lastName,

        #[Assert\GreaterThan(18)]
        public int $age,
    ) {}
}
```

Controller’da `MapQueryString` attribute’unu kullanabiliriz:

```php
use App\Model\UserDto;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;

public function dashboard(
    #[MapQueryString] UserDto $userDto
): Response
{
    // ...
}
```

#### Ek Özelleştirme:

```php
public function dashboard(
    #[MapQueryString(
        validationGroups: ['strict', 'edit'],
        validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY
    )] UserDto $userDto
): Response
{
    // ...
}
```

> Varsayılan hata durum kodu: **404**

Ayrıca query parametrelerini belirli bir key altına eşlemek için `key` özelliğini kullanabilirsiniz

(Symfony 7.3+):

```php
#[MapQueryString(key: 'search')] SearchDto $searchDto
```

Varsayılan (boş) DTO’ya izin vermek için:

```php
#[MapQueryString] UserDto $userDto = new UserDto()
```

---

## 📦 Request Payload Eşleştirme

Bir **API** oluştururken, `POST` veya `PUT` isteklerinde veriler **query string** yerine **payload** içinde gelir:

```json
{
    "firstName": "John",
    "lastName": "Smith",
    "age": 28
}
```

Bu payload’ı doğrudan DTO’ya eşlemek mümkündür:

```php
use App\Model\UserDto;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

public function dashboard(
    #[MapRequestPayload] UserDto $userDto
): Response
{
    // ...
}
```

### Gelişmiş Seçenekler

```php
public function dashboard(
    #[MapRequestPayload(
        serializationContext: ['...'],
        resolver: App\Resolver\UserDtoResolver
    )]
    UserDto $userDto
): Response
{
    // ...
}
```

#### Özelleştirilebilir alanlar:

* `acceptFormat`: Beklenen içerik türü (`json`, `xml` vb.)
* `validationGroups`: Doğrulama grupları
* `validationFailedStatusCode`: Doğrulama başarısız olursa dönecek durum kodu

```php
#[MapRequestPayload(
    acceptFormat: 'json',
    validationGroups: ['strict', 'read'],
    validationFailedStatusCode: Response::HTTP_NOT_FOUND
)] UserDto $userDto
```

> Varsayılan hata kodu: **422 (Unprocessable Entity)**

JSON API’lerde rotanızı şu şekilde tanımlayın:

```php
#[Route('/dashboard', name: 'dashboard', format: 'json')]
```

---

## 🧩 Nested (İç İçe) DTO Listeleri

Eğer birden fazla DTO nesnesi alıyorsanız, `phpstan/phpdoc-parser` ve `phpdocumentor/type-resolver` kurmanız gerekir:

```php
public function dashboard(
    #[MapRequestPayload] EmployeesDto $employeesDto
): Response
{
    // ...
}

final class EmployeesDto
{
    /**
     * @param UserDto[] $users
     */
    public function __construct(
        public readonly array $users = []
    ) {}
}
```

Sonuç olarak şöyle bir yapı dönebilir:

```json
[
  {"firstName": "John", "lastName": "Smith", "age": 28},
  {"firstName": "Jane", "lastName": "Doe", "age": 30}
]
```

Bunu sağlamak için:

```php
#[MapRequestPayload(type: UserDto::class)] array $users
```

> `type` seçeneği Symfony 7.1 sürümüyle tanıtılmıştır.



İşte metnin **modern Türkçe ve açıklayıcı Markdown çevirisi** 👇

---

# 🗂️ Yüklenen Dosyaları Eşlemek (Mapping Uploaded Files)

Symfony, bir veya birden fazla **UploadedFile** nesnesini controller parametrelerine **otomatik olarak eşlemek** için

`#[MapUploadedFile]` adlı bir attribute sağlar:

```php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/user/picture', methods: ['PUT'])]
    public function changePicture(
        #[MapUploadedFile] UploadedFile $picture,
    ): Response {
        // ...
    }
}
```

### 🔍 Açıklama

* Symfony, `$picture` argüman adını kullanarak **ilgili UploadedFile nesnesini** otomatik bulur.
* Eğer dosya yüklenmemişse, **HttpException** fırlatılır.
* Ancak parametreyi **nullable** yaparsanız bu hata oluşmaz:

```php
#[MapUploadedFile]
?UploadedFile $document
```

---

## ✅ Yüklenen Dosyalara Doğrulama (Validation) Uygulamak

`#[MapUploadedFile]` attribute’u, yüklenen dosyaya **doğrulama kuralları (constraints)** eklemenizi de sağlar:

```php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;

class UserController extends AbstractController
{
    #[Route('/user/picture', methods: ['PUT'])]
    public function changePicture(
        #[MapUploadedFile([
            new Assert\File(mimeTypes: ['image/png', 'image/jpeg']),
            new Assert\Image(maxWidth: 3840, maxHeight: 2160),
        ])]
        UploadedFile $picture,
    ): Response {
        // ...
    }
}
```

* Symfony, **UploadedFile nesnesini controller’a enjekte etmeden önce** doğrulamayı gerçekleştirir.
* Eğer kısıtlamalardan biri ihlal edilirse, **HttpException** fırlatılır ve controller metodu çalışmaz.

---

## 📚 Birden Fazla Dosya Yükleme

Birden fazla dosya yükleniyorsa, bunları bir **dizi (array)** veya **variadic argüman (…$args)** olarak eşleyebilirsiniz:

```php
#[MapUploadedFile(new Assert\File(mimeTypes: ['application/pdf']))]
array $documents

#[MapUploadedFile(new Assert\File(mimeTypes: ['application/pdf']))]
UploadedFile ...$documents
```

Her dosya ayrı ayrı doğrulanır; bir tanesi bile başarısız olursa, `HttpException` fırlatılır.

---

## 🏷️ Dosya Adını Değiştirmek

Yüklenen dosyanın adını özel bir şekilde değiştirmek için `name` seçeneğini kullanabilirsiniz:

```php
#[MapUploadedFile(name: 'something-else')]
UploadedFile $document
```

---

## ⚠️ Hata Durum Kodunu Özelleştirmek

Doğrulama hatalarında fırlatılacak **HTTP durum kodunu** değiştirmek mümkündür:

```php
#[MapUploadedFile(
    constraints: new Assert\File(maxSize: '2M'),
    validationFailedStatusCode: Response::HTTP_REQUEST_ENTITY_TOO_LARGE
)]
UploadedFile $document
```

> `#[MapUploadedFile]` özelliği **Symfony 7.1** sürümünde tanıtılmıştır.

---

# 💾 Session Yönetimi

Symfony, oturum (session) içinde **“flash mesajları”** adı verilen özel mesajları saklamanıza izin verir.

Flash mesajları **yalnızca bir kez** kullanılır ve okunduktan sonra **otomatik olarak silinir.**

Bu özellik, kullanıcı bildirimlerini göstermek için idealdir.

### 🧩 Örnek:

```php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

public function update(Request $request): Response
{
    // ...

    if ($form->isSubmitted() && $form->isValid()) {
        // işlem yapılır
        $this->addFlash('notice', 'Your changes were saved!');

        // Yukarıdaki, aşağıdakine denktir:
        // $request->getSession()->getFlashBag()->add('notice', 'Your changes were saved!');

        return $this->redirectToRoute(/* ... */);
    }

    return $this->render(/* ... */);
}
```

Daha fazla bilgi için: [Session Kullanımı](https://symfony.com/doc/current/session.html)

---

# 📨 Request ve Response Nesneleri

Symfony, `Request` sınıfı ile type-hint edilmiş her controller parametresine  **Request nesnesini otomatik olarak geçirir** :

```php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

public function index(Request $request): Response
{
    $request->isXmlHttpRequest(); // Ajax isteği mi?

    $request->getPreferredLanguage(['en', 'fr']);

    // GET ve POST değişkenlerini alır
    $request->query->get('page');
    $request->getPayload()->get('page');

    // SERVER değişkenlerini alır
    $request->server->get('HTTP_HOST');

    // Yüklenen dosyayı (UploadedFile) alır
    $request->files->get('foo');

    // COOKIE değerini alır
    $request->cookies->get('PHPSESSID');

    // HTTP header bilgilerini alır (küçük harfli anahtarlarla)
    $request->headers->get('host');
    $request->headers->get('content-type');
}
```

> `Request` sınıfı, isteğe dair tüm bilgileri döndüren çok sayıda özellik ve metoda sahiptir.

---

## 🧾 Response Nesnesi

`Response` nesnesinin de **public `headers`** özelliği vardır.

Bu özellik, **ResponseHeaderBag** türündedir ve HTTP header’larını yönetir.

Header isimleri normalize edilir (`Content-Type`, `content-type` veya `content_type` aynı sayılır).

Controller’lar **mutlaka bir Response nesnesi döndürmelidir:**

```php
use Symfony\Component\HttpFoundation\Response;

// Basit bir 200 OK yanıtı
$response = new Response('Hello '.$name, Response::HTTP_OK);

// CSS içeriği ile yanıt döndürme
$response = new Response('<style> ... </style>');
$response->headers->set('Content-Type', 'text/css');
```

Symfony, farklı yanıt türleri için özel **Response sınıfları** da sunar (örneğin `JsonResponse`, `StreamedResponse` vb.).

Daha fazla bilgi için: [HttpFoundation Bileşeni Dokümantasyonu](https://symfony.com/doc/current/components/http_foundation.html)

> Teknik olarak controller, `Response` dışındaki bir değer de döndürebilir.
>
> Ancak bu durumda dönüş değeri, **kernel.view** eventi aracılığıyla Response’a dönüştürülmelidir.
>
> Bu, Symfony’nin gelişmiş özelliklerinden biridir.

---

# ⚙️ Konfigürasyon Değerlerine Erişim

Controller içinde tanımlanmış bir yapılandırma parametresine erişmek için `getParameter()` metodunu kullanabilirsiniz:

```php
public function index(): Response
{
    $contentsDir = $this->getParameter('kernel.project_dir').'/contents';
    // ...
}
```

Bu yöntemle `services.yaml` veya `parameters.yaml` dosyalarındaki tüm parametrelere ulaşabilirsiniz.



İşte bu bölümün tamamının **modern Türkçe Markdown** çevirisi 👇

---

# 🧾 JSON Yanıt Döndürme (Returning JSON Response)

Bir controller’dan **JSON veri döndürmek** için `json()` yardımcı metodunu (helper method) kullanabilirsiniz.

Bu metod, veriyi otomatik olarak kodlayan bir **JsonResponse** nesnesi döndürür:

```php
use Symfony\Component\HttpFoundation\JsonResponse;
// ...

public function index(): JsonResponse
{
    // '{"username":"jane.doe"}' döndürür ve doğru Content-Type header’ını ayarlar
    return $this->json(['username' => 'jane.doe']);

    // Kısayol olarak 3 isteğe bağlı parametre de alabilir:
    // return $this->json($data, $status = 200, $headers = [], $context = []);
}
```

> Eğer uygulamanızda **serializer service** aktifse, veriyi JSON’a çevirmek için bu servis kullanılır.
>
> Aksi durumda PHP’nin `json_encode()` fonksiyonu kullanılır.

---

# 📂 Dosya Yayınlamak (Streaming File Responses)

Bir controller’dan **dosya sunmak veya indirmek** için `file()` yardımcı metodunu kullanabilirsiniz:

```php
use Symfony\Component\HttpFoundation\BinaryFileResponse;
// ...

public function download(): BinaryFileResponse
{
    // Dosya içeriğini gönderir ve tarayıcıyı indirme yapmaya zorlar
    return $this->file('/path/to/some_file.pdf');
}
```

### ⚙️ `file()` Metodunun Ek Seçenekleri

```php
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
// ...

public function download(): BinaryFileResponse
{
    // Dosyayı sistemden yükle
    $file = new File('/path/to/some_file.pdf');

    return $this->file($file);

    // İndirilen dosyanın adını değiştir
    return $this->file($file, 'custom_name.pdf');

    // Dosyayı indirmek yerine tarayıcıda göster
    return $this->file(
        'invoice_3241.pdf',
        'my_invoice.pdf',
        ResponseHeaderBag::DISPOSITION_INLINE
    );
}
```

> 🧠 **Not:** `ResponseHeaderBag::DISPOSITION_INLINE`, dosyanın tarayıcıda **görüntülenmesini** sağlar.
>
> Varsayılan davranış ise **indirilebilir** hale getirmektir.

---

# 🚀 Early Hints (Erken İpuçları) Göndermek

 **Early Hints (103 HTTP durumu)** , tarayıcıya yanıt tamamen gönderilmeden önce bazı kaynakları (örneğin CSS, JS, fontlar) **önceden indirmesi için sinyal** verir.

Bu, sayfa yüklenme hızını **algısal olarak** artırır.

Destekleyen SAPI’lerden biri örneğin  **FrankenPHP** ’dir.

```php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\WebLink\Link;

class HomepageController extends AbstractController
{
    #[Route("/", name: "homepage")]
    public function index(): Response
    {
        $response = $this->sendEarlyHints([
            new Link(rel: 'preconnect', href: 'https://fonts.google.com'),
            (new Link(href: '/style.css'))->withAttribute('as', 'style'),
            (new Link(href: '/script.js'))->withAttribute('as', 'script'),
        ]);

        // yanıtın içeriğini hazırla
        return $this->render('homepage/index.html.twig', response: $response);
    }
}
```

### 🧠 Nasıl Çalışır?

* `sendEarlyHints()` metodu, **103 HTTP kodlu** bir yanıt başlığı (header) hemen gönderir.
* Bu sayede tarayıcı, yanıtın tamamını beklemeden dosyaları **önceden indirmeye başlar.**
* Yöntem bir `Response` nesnesi döndürür; bu nesne sonrasında controller tarafından kullanılarak **tam yanıt** oluşturulur.

Örnek:

Tarayıcı `style.css` ve `script.js` dosyalarını sayfa yüklenmeden indirmeye başlar.

---

# 🧭 Genel Özet (Final Thoughts)

Symfony’de bir  **controller** , genellikle bir sınıf metodu olarak tanımlanır.

Bu metod  **Request nesnesini alır** , gerekli işlemleri yapar ve bir  **Response nesnesi döndürür** .

Bir URL ile eşleştirildiğinde (route), bu controller  **erişilebilir hale gelir** .

Symfony, controller geliştirmeyi kolaylaştırmak için **AbstractController** sınıfını sağlar.

Bu sınıf şu yardımcı metodlara erişim kazandırır:

* 🧩 `render()` – Twig şablonlarını render etmek için
* 🔁 `redirectToRoute()` – yönlendirme yapmak için
* 🚫 `createNotFoundException()` – 404 yanıtı döndürmek için

Controller’larda ayrıca servisleri kullanarak şu işlemleri de yapabilirsiniz:

* Veritabanına nesneleri kaydetmek ve çekmek
* Form gönderimlerini işlemek
* Önbellekleme (cache) yapmak
* Dosya yüklemek veya hata sayfalarını özelleştirmek

---

# 🚀 Devam Et!

Bir sonraki adımda, **Twig ile şablon oluşturmayı** öğreneceksiniz:

👉 [Twig ile Şablonları Render Etmek](https://symfony.com/doc/current/templates.html)

---

## 📚 Daha Fazla Öğrenin

* 🔧 [How to Customize Error Pages](https://symfony.com/doc/current/controller/error_pages.html)
* 🔄 [How to Forward Requests to another Controller](https://symfony.com/doc/current/controller/forwarding.html)
* 🧰 [How to Define Controllers as Services](https://symfony.com/doc/current/controller/service.html)
* 📤 [How to Upload Files](https://symfony.com/doc/current/controller/upload_file.html)
* ⚙️ [Extending Action Argument Resolving](https://symfony.com/doc/current/controller/argument_value_resolver.html)

---
