
### 🔄 Serializer Nasıl Kullanılır

Symfony, veri yapılarını bir formattan PHP nesnelerine ve tersine dönüştürmek için bir **serializer** sağlar.

Bu genellikle bir API oluştururken veya üçüncü taraf API’lerle iletişim kurarken kullanılır. Serializer, gelen bir **JSON** isteğini PHP nesnesine dönüştürebilir; uygulamanız bu nesneyi kullanır. Ardından yanıt oluşturulurken PHP nesnelerini tekrar **JSON** çıktısına dönüştürmek için serializer kullanılabilir.

Ayrıca örneğin **CSV yapılandırma verilerini PHP nesneleri olarak yüklemek** ya da formatlar arasında dönüştürme yapmak (örneğin YAML → XML) için de kullanılabilir.

---

### ⚙️ Kurulum

Symfony Flex kullanan uygulamalarda serializer’ı kullanmadan önce şu komutu çalıştırarak Symfony pack’i yükleyin:

```bash
composer require symfony/serializer-pack
```

Serializer pack, Serializer bileşeninin sık kullanılan isteğe bağlı bağımlılıklarını da kurar. Bu bileşeni Symfony framework’ü dışında kullanıyorsanız, `symfony/serializer` paketinden başlayıp gerektiğinde isteğe bağlı bağımlılıkları yükleyebilirsiniz.

Symfony Serializer bileşenine popüler bir alternatif de üçüncü taraf **JMS serializer** kütüphanesidir.

---

### 📦 Bir Nesnenin Serileştirilmesi

Bu örnek için projenizde aşağıdaki sınıfın bulunduğunu varsayalım:

```php
// src/Model/Person.php
namespace App\Model;

class Person
{
    public function __construct(
        private int $age,
        private string $name,
        private bool $sportsperson
    ) {
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isSportsperson(): bool
    {
        return $this->sportsperson;
    }
}
```

Bu tür nesneleri bir **JSON yapısına** dönüştürmek (örneğin bir API yanıtı olarak göndermek) istiyorsanız, `SerializerInterface` servisini kullanabilirsiniz:

```php
// src/Controller/PersonController.php
namespace App\Controller;

use App\Model\Person;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class PersonController extends AbstractController
{
    public function index(SerializerInterface $serializer): Response
    {
        $person = new Person('Jane Doe', 39, false);

        $jsonContent = $serializer->serialize($person, 'json');
        // $jsonContent şu değeri içerir: {"name":"Jane Doe","age":39,"sportsperson":false}

        return JsonResponse::fromJsonString($jsonContent);
    }
}
```

`serialize()` metodunun ilk parametresi serileştirilecek nesnedir; ikinci parametre ise kullanılacak encoder’ı (örneğin `JsonEncoder`) belirtir.

---

### ⚡ AbstractController ile Basitleştirilmiş JSON Yanıtı

Controller sınıfınız `AbstractController`’dan türemişse, Serializer’ı kullanarak JSON yanıtı oluşturmayı `json()` metodu ile basitleştirebilirsiniz:

```php
class PersonController extends AbstractController
{
    public function index(): Response
    {
        $person = new Person('Jane Doe', 39, false);

        // Serializer mevcut değilse json_encode() kullanılır
        return $this->json($person);
    }
}
```

---

### 🧩 Twig Şablonlarında Serializer Kullanımı

Twig şablonlarında da nesneleri `serialize` filtresiyle serileştirebilirsiniz:

```twig
{{ person|serialize(format = 'json') }}
```

Ayrıntılar için Twig referansına bakabilirsiniz.

---

### 🔁 Bir Nesnenin Deserileştirilmesi

API’ler genellikle biçimlendirilmiş bir istek gövdesini (örneğin JSON) bir PHP nesnesine dönüştürmek zorundadır. Bu işleme **deserialization** (ya da “hydration”) denir:

```php
// src/Controller/PersonController.php
namespace App\Controller;

// ...
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;

class PersonController extends AbstractController
{
    // ...

    public function create(Request $request, SerializerInterface $serializer): Response
    {
        if ('json' !== $request->getContentTypeFormat()) {
            throw new BadRequestException('Unsupported content format');
        }

        $jsonData = $request->getContent();
        $person = $serializer->deserialize($jsonData, Person::class, 'json');

        // ... $person ile işlem yap ve bir yanıt döndür
    }
}
```

`deserialize()` metodu üç parametre ister:

1. **Kod çözümlenecek veri**
2. **Bu bilginin dönüştürüleceği sınıf adı**
3. **Veriyi diziye dönüştürmek için kullanılacak encoder’ın adı** (girdi formatı)

Bu controllera şu türde bir istek gönderildiğinde:

```json
{"first_name":"John Doe","age":54,"sportsperson":true}
```

Serializer, `Person` sınıfının yeni bir örneğini oluşturur ve özellikleri JSON’daki değerlerle doldurur.

Varsayılan olarak, nesnede tanımlı olmayan ek alanlar yoksayılır. Örneğin istek şu alanı içerirse:

```json
{"city": "Paris"}
```

`city` alanı göz ardı edilir. İsterseniz serializer context’i kullanarak bu durumlarda istisna fırlatılmasını sağlayabilirsiniz.

Ayrıca, veriyi **mevcut bir nesne örneğine deserileştirmek** (örneğin güncelleme işlemleri için) de mümkündür. Bunun için “Deserializing in an Existing Object” bölümüne bakabilirsiniz.

---

### ⚙️ Serileştirme Süreci: Normalizer’lar ve Encoder’lar

Serializer, nesneleri (de)serileştirirken iki aşamalı bir süreç kullanır: **Normalizer** ve  **Encoder** .



![1761992243834](image/7_HowtoUsetheSerializer/1761992243834.png)


### 🔄 Her İki Yönde Dönüştürme Süreci

Her iki yönde de veriler önce bir **diziye (array)** dönüştürülür. Bu, süreci iki ayrı sorumluluğa böler:

---

### ⚙️ **Normalizers**

Bu sınıflar, **nesneleri dizilere** ve **dizileri nesnelere** dönüştürür.

Hangi sınıf özelliklerinin serileştirileceğini, bunların hangi değerleri tuttuğunu ve hangi isimlerle aktarılacağını belirleme işini yaparlar.

---

### 🔢 **Encoders**

Encoders, **dizileri belirli bir formata** ve tersine dönüştürür.

Her encoder, belirli bir formatı (örneğin JSON veya XML) nasıl ayrıştırıp üreteceğini tam olarak bilir.

---

Symfony içindeki `Serializer` sınıfı, bir nesneyi (de)serileştirirken dahili olarak sıralı bir **normalizer listesi** ve hedef formata uygun **tek bir encoder** kullanır.

Varsayılan serializer servisinde birkaç normalizer yapılandırılmıştır. Bunların en önemlisi  **ObjectNormalizer** ’dır.

Bu normalizer, Reflection ve **PropertyAccess** bileşenlerini kullanarak herhangi bir nesne ile dizi arasında dönüşüm yapar.

Bu ve diğer normalizer’lar hakkında daha fazla bilgiyi ilerleyen bölümlerde öğreneceksiniz.

---

### 🧰 Varsayılan Encoder’lar

Varsayılan serializer, HTTP uygulamalarında sık kullanılan formatları kapsayan bazı encoder’larla yapılandırılmıştır:

* `JsonEncoder`
* `XmlEncoder`
* `CsvEncoder`
* `YamlEncoder`

Bu encoder’lar ve yapılandırmaları hakkında daha fazla bilgi için **Serializer Encoders** bölümüne bakabilirsiniz.

---

### 🧩 Gelişmiş Formatlar (API Platform)

**API Platform** projesi, daha gelişmiş formatlar için encoder’lar sağlar:

* **JSON-LD** (Hydra Core Vocabulary ile)
* **OpenAPI v2** (eski adıyla Swagger) ve **v3**
* **GraphQL**
* **JSON:API**
* **HAL**

---

### ⚙️ Serializer Context

Serializer ve onun **normalizer** ile  **encoder** ’ları, bir **serializer context** aracılığıyla yapılandırılır.

Bu context aşağıdaki şekillerde tanımlanabilir:

1. Framework yapılandırması üzerinden **global olarak**
2. **Serileştirme / Deserileştirme sırasında**
3. **Belirli bir özellik (property)** üzerinde

Bu üç yöntemi aynı anda kullanabilirsiniz. Aynı ayar birden fazla yerde tanımlandığında, **liste sırasındaki en son** (örneğin property üzerindeki) yapılandırma diğerlerini geçersiz kılar.

---

### 🧱 Varsayılan Context Tanımlama

Global bir varsayılan context, örneğin deserileştirme sırasında fazladan alanlara izin verilmemesi için şöyle yapılandırılabilir:

```php
// config/packages/serializer.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->serializer()
        ->defaultContext([
            'allow_extra_attributes' => false,
        ])
    ;
};
```

---

### 🎚️ Serileştirme / Deserileştirme Sırasında Context Geçmek

Belirli bir `serialize()` veya `deserialize()` çağrısı için context ayarlayabilirsiniz.

Örneğin, sadece bir serileştirme işleminde `null` değerli alanları atlamak için:

```php
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

// ...
$serializer->serialize($person, 'json', [
    AbstractObjectNormalizer::SKIP_NULL_VALUES => true
]);

// Sonraki serialize() çağrılarında null değerler atlanmayacaktır
```

---

### 🧱 Context Builders Kullanımı

“Context builder”lar, (de)serileştirme context’ini tanımlamaya yardımcı olan PHP nesneleridir.

Otomatik tamamlama, doğrulama ve belgeleme avantajı sağlarlar:

```php
use Symfony\Component\Serializer\Context\Normalizer\DateTimeNormalizerContextBuilder;

$contextBuilder = (new DateTimeNormalizerContextBuilder())
    ->withFormat('Y-m-d H:i:s');

$serializer->serialize($something, 'json', $contextBuilder->toArray());
```

Her normalizer/encoder kendi context builder’ına sahiptir.

Daha karmaşık bir context oluşturmak için `withContext()` yöntemiyle zincirleme yapabilirsiniz:

```php
use Symfony\Component\Serializer\Context\Encoder\CsvEncoderContextBuilder;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;

$initialContext = [
    'custom_key' => 'custom_value',
];

$contextBuilder = (new ObjectNormalizerContextBuilder())
    ->withContext($initialContext)
    ->withGroups(['group1', 'group2']);

$contextBuilder = (new CsvEncoderContextBuilder())
    ->withContext($contextBuilder)
    ->withDelimiter(';');

$serializer->serialize($something, 'csv', $contextBuilder->toArray());
```

Ayrıca kendi context builder’larınızı da oluşturabilir, özel context değerleriniz için otomatik tamamlama ve doğrulama sağlayabilirsiniz.

---

### 🏷️ Belirli Bir Özellikte Context Tanımlama

Son olarak, context değerlerini belirli bir özellik (property) üzerinde de tanımlayabilirsiniz.

Örneğin bir tarih alanının formatını belirlemek için:

```php
// src/Model/Person.php

use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class Person
{
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    public \DateTimeImmutable $createdAt;

    // ...
}
```

**YAML** veya **XML** kullanıyorsanız, eşleme dosyaları şu konumlarda bulunmalıdır:

* `config/serializer/` dizinindeki tüm `*.yaml` ve `*.xml` dosyaları
* Bir bundle içinde `Resources/config/serialization.yaml` veya `serialization.xml`
* Veya `Resources/config/serialization/` dizinindeki tüm `*.yaml` ve `*.xml` dosyaları

---

### 🧭 Normalization ve Denormalization İçin Özel Context

Sadece normalization veya denormalization işlemleri için ayrı context de tanımlayabilirsiniz:

```php
// src/Model/Person.php

use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class Person
{
    #[Context(
        normalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'],
        denormalizationContext: [DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339],
    )]
    public \DateTimeImmutable $createdAt;

    // ...
}
```

---

### 👥 Gruplara Özel Context Kullanımı

Context kullanımını belirli gruplarla da sınırlandırabilirsiniz:

```php
// src/Model/Person.php

use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class Person
{
    #[Groups(['extended'])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339])]
    #[Context(
        context: [DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339_EXTENDED],
        groups: ['extended'],
    )]
    public \DateTimeImmutable $createdAt;

    // ...
}
```

Bir property üzerinde `[Context]` attribute’u gerektiği kadar tekrarlanabilir.

**Grupsuz context** her zaman önce uygulanır, ardından **eşleşen gruplar** için tanımlı context’ler sırayla birleştirilir.

Aynı context’i birden fazla property’de tekrarlıyorsanız, `[Context]` attribute’unu doğrudan **sınıf düzeyinde** tanımlayarak tüm özelliklere uygulayabilirsiniz:

```php
namespace App\Model;

use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[Context([DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339])]
#[Context(
    context: [DateTimeNormalizer::FORMAT_KEY => \DateTime::RFC3339_EXTENDED],
    groups: ['extended'],
)]
class Person
{
    // ...
}
```


### ⚡ Akışlar (Streams) Kullanarak JSON Serileştirme

Symfony, PHP veri yapılarını **JSON akışlarına (streams)** dönüştürebilir ve JSON akışlarını tekrar PHP veri yapılarına çözümlenmiş hale getirebilir.

Bunu yapmak için, içeriğin tamamını belleğe yüklemeden büyük JSON verilerini **adım adım (incrementally)** işleyebilen, yüksek verimlilik için tasarlanmış **JsonStreamer** bileşenini kullanır.

---

### 🔍 Serializer vs JsonStreamer

Aşağıdaki durumlarda hangi bileşeni kullanmanız gerektiğini değerlendirin:

#### 🧩 Serializer Component

* Dinamik olarak nesne yapılarıyla çalışmanız gerekiyorsa
* Birden fazla serileştirme formatını (JSON, XML vb.) desteklemeniz gerekiyorsa
* Kendi özel formatlarınızı tanımlamak istiyorsanız

> Daha fazla esneklik sağlar ve yalnızca JSON ile sınırlı değildir.

#### ⚙️ JsonStreamer Component

* Basit nesnelerle çalışıyorsanız
* Yüksek performans ve düşük bellek kullanımı gerekiyorsa
* Gerçek zamanlı veya çok büyük JSON verilerini belleğe yüklemeden işliyorsanız

> Performans ve bellek verimliliği açısından optimize edilmiştir.

Kısacası:

 **JsonStreamer → performans için** ,

**Serializer → esneklik ve çoklu formatlar için.**

Daha fazla bilgi için *streaming JSON* konusuna bakabilirsiniz.

---

### 🧮 PHP Dizilerine (Arrays) Serileştirme ve Deserileştirme

Varsayılan  **Serializer** , iki aşamalı serileştirme sürecinin yalnızca bir adımını gerçekleştirmek için kullanılabilir.

Bunun için aşağıdaki arayüzler (interface) kullanılır:

```php
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
// ...

class PersonController extends AbstractController
{
    public function index(DenormalizerInterface&NormalizerInterface $serializer): Response
    {
        $person = new Person('Jane Doe', 39, false);

        // Bir PHP nesnesini diziye dönüştürmek için normalize() kullanılır
        $personArray = $serializer->normalize($person, 'json');

        // Diziyi tekrar PHP nesnesine dönüştürmek için denormalize()
        $personCopy = $serializer->denormalize($personArray, Person::class);

        // ...
    }

    public function json(DecoderInterface&EncoderInterface $serializer): Response
    {
        $data = ['name' => 'Jane Doe'];

        // PHP dizilerini başka bir formata dönüştürmek için encode()
        $json = $serializer->encode($data, 'json');

        // Formatı sadece PHP dizisine dönüştürmek için decode()
        $data = $serializer->decode('{"name":"Charlie Doe"}', 'json');
        // $data = ['name' => 'Charlie Doe']
    }
}
```

---

### 🚫 Özellikleri (Properties) Yoksayma

 **ObjectNormalizer** , bir nesnenin tüm özelliklerini ve şu kalıplara sahip tüm metotları serileştirir:

`get*()`, `has*()`, `is*()` ve `can*()`.

Bazı özelliklerin veya metotların **asla serileştirilmemesi** gerekebilir.

Bunun için `#[Ignore]` özniteliğini (attribute) kullanabilirsiniz:

```php
// src/Model/Person.php
namespace App\Model;

use Symfony\Component\Serializer\Attribute\Ignore;

class Person
{
    // ...

    #[Ignore]
    public function isPotentiallySpamUser(): bool
    {
        // ...
    }
}
```

Bu özellik artık hiçbir zaman serileştirilmeyecektir:

```php
use App\Model\Person;

$person = new Person('Jane Doe', 32, false);
$json = $serializer->serialize($person, 'json');
// {"name":"Jane Doe","age":32,"sportsperson":false}
```

Deserileştirme sırasında gelen `"potentiallySpamUser"` alanı da yoksayılacaktır.

---

### 🧭 Context ile Özellikleri Yoksayma

Özellikleri çalışma anında (runtime) yoksaymak için `ignored_attributes` context seçeneğini kullanabilirsiniz:

```php
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

$person = new Person('Jane Doe', 32, false);
$json = $serializer->serialize($person, 'json', [
    AbstractNormalizer::IGNORED_ATTRIBUTES => ['age'],
]);
// {"name":"Jane Doe","sportsperson":false}
```

Ancak bu yöntem çok fazla kullanıldığında karmaşık hale gelir.

Daha iyi bir çözüm için **serialization groups** özelliğini kullanın.

---

### 🎯 Belirli Özellikleri Seçme (Serialization Groups)

Bazı durumlarda bir özelliği her yerde değil, yalnızca belirli yerlerde hariç tutmanız gerekebilir.

Bunun için `#[Groups]` özniteliğini kullanabilirsiniz:

```php
// src/Model/Person.php
namespace App\Model;

use Symfony\Component\Serializer\Attribute\Groups;

class Person
{
    #[Groups(["admin-view"])]
    private int $age;

    #[Groups(["public-view"])]
    private string $name;

    #[Groups(["public-view"])]
    private bool $sportsperson;
}
```

Serileştirme sırasında hangi grupların kullanılacağını belirtebilirsiniz:

```php
$json = $serializer->serialize($person, 'json', ['groups' => 'public-view']);
// {"name":"Jane Doe","sportsperson":false}

$json = $serializer->serialize($person, 'json', ['groups' => ['public-view', 'admin-view']]);
// {"name":"Jane Doe","age":32,"sportsperson":false}

$json = $serializer->serialize($person, 'json', ['groups' => '*']);
// {"name":"Jane Doe","age":32,"sportsperson":false}
```

---

### 🧱 Serialization Context Kullanımı

`attributes` context seçeneği ile hangi özelliklerin serileştirileceğini çalışma anında belirleyebilirsiniz:

```php
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

$json = $serializer->serialize($person, 'json', [
    AbstractNormalizer::ATTRIBUTES => ['name', 'company' => ['name']]
]);
// {"name":"Dunglas","company":{"name":"Les-Tilleuls.coop"}}
```

Yoksayılan veya grup dışı özellikler burada kullanılamaz.

---

### 📚 Dizilerle (Arrays) Çalışma

Serializer, nesne dizilerini de işleyebilir.

Birden fazla nesneyi serileştirmek tek bir nesneyi serileştirmekle aynıdır:

```php
use App\Model\Person;

$person1 = new Person('Jane Doe', 39, false);
$person2 = new Person('John Smith', 52, true);

$persons = [$person1, $person2];
$jsonContent = $serializer->serialize($persons, 'json');
// [{"name":"Jane Doe","age":39,"sportsman":false},{"name":"John Smith","age":52,"sportsman":true}]
```

Bir dizi nesneyi **deserileştirmek** için `[]` eklemeniz gerekir:

```php
$persons = $serializer->deserialize($jsonData, Person::class.'[]', 'json');
```

İç içe sınıflar için `@param` PHPDoc tipi belirtmelisiniz:

```php
// src/Model/UserGroup.php
namespace App\Model;

class UserGroup
{
    /**
     * @param Person[] $members
     */
    public function __construct(
        private array $members,
    ) {
    }

    /**
     * @param Person[] $members
     */
    public function setMembers(array $members): void
    {
        $this->members = $members;
    }
}
```

`list<Person>` ve `array<Person>` gibi statik analiz türleri de desteklenir.

Bunun için `phpstan/phpdoc-parser` ve `phpdocumentor/reflection-docblock` paketleri yüklü olmalıdır

(bunlar `symfony/serializer-pack` içinde gelir).

---

### 🪆 İç İçe Yapıları Deserileştirme

Bazı API’ler, PHP nesnesinde sadeleştirmek isteyebileceğiniz **iç içe (nested)** JSON yapıları döndürebilir:

```json
{
    "id": "123",
    "profile": {
        "username": "jdoe",
        "personal_information": {
            "full_name": "Jane Doe"
        }
    }
}
```

Bunu şu şekilde sade bir sınıfa dönüştürmek isteyebilirsiniz:

```php
class Person
{
    private int $id;
    private string $username;
    private string $fullName;
}
```

Bunun için `#[SerializedPath]` özniteliğini kullanın:

```php
namespace App\Model;

use Symfony\Component\Serializer\Attribute\SerializedPath;

class Person
{
    private int $id;

    #[SerializedPath('[profile][username]')]
    private string $username;

    #[SerializedPath('[profile][personal_information][full_name]')]
    private string $fullName;
}
```

> `#[SerializedPath]` aynı property’de `#[SerializedName]` ile birlikte kullanılamaz.

Serileştirme sırasında da geçerlidir:

```php
$person = new Person(123, 'jdoe', 'Jane Doe');
$jsonContent = $serializer->serialize($person, 'json');
// {"id":123,"profile":{"username":"jdoe","personal_information":{"full_name":"Jane Doe"}}}
```

---

### 🔤 Özellik İsimlerini Dönüştürme (Name Conversion)

Bazı durumlarda serileştirilen alan adları, PHP sınıfındaki property veya getter/setter isimlerinden farklı olmalıdır.

Bunun için **name converter** kullanılır.

Varsayılan olarak serializer, `MetadataAwareNameConverter` kullanır.

Bir alan adını değiştirmek için `#[SerializedName]` özniteliğini ekleyebilirsiniz:

```php
// src/Model/Person.php
namespace App\Model;

use Symfony\Component\Serializer\Attribute\SerializedName;

class Person
{
    #[SerializedName('customer_name')]
    private string $name;
}
```

Bu eşleme, serileştirme ve deserileştirme sırasında uygulanır:

```php
$json = $serializer->serialize($person, 'json');
// {"customer_name":"Jane Doe", ...}
```

Kendi **custom name converter** sınıfınızı da oluşturabilirsiniz.

---

### 🐍 CamelCase ↔ snake_case Dönüşümü

Birçok formatta kelimeler alt çizgiyle ayrılır ( **snake_case** ),

ancak Symfony projelerinde genellikle **camelCase** kullanılır.

Symfony, serileştirme ve deserileştirme sırasında `camelCase` ↔ `snake_case` dönüşümü yapmak için

yerleşik bir **name converter** sağlar.

Bu özelliği etkinleştirmek için `serializer.name_converter.camel_case_to_snake_case` ayarını yapın:

```php
// config/packages/serializer.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->serializer()
        ->nameConverter('serializer.name_converter.camel_case_to_snake_case');
};
```


### ⚡ Akışlar (Streams) Kullanarak JSON Serileştirme

Symfony, PHP veri yapılarını **JSON akışlarına (streams)** dönüştürebilir ve JSON akışlarını tekrar PHP veri yapılarına çözümlenmiş hale getirebilir.

Bunu yapmak için, içeriğin tamamını belleğe yüklemeden büyük JSON verilerini **adım adım (incrementally)** işleyebilen, yüksek verimlilik için tasarlanmış **JsonStreamer** bileşenini kullanır.

---

### 🔍 Serializer vs JsonStreamer

Aşağıdaki durumlarda hangi bileşeni kullanmanız gerektiğini değerlendirin:

#### 🧩 Serializer Component

* Dinamik olarak nesne yapılarıyla çalışmanız gerekiyorsa
* Birden fazla serileştirme formatını (JSON, XML vb.) desteklemeniz gerekiyorsa
* Kendi özel formatlarınızı tanımlamak istiyorsanız

> Daha fazla esneklik sağlar ve yalnızca JSON ile sınırlı değildir.

#### ⚙️ JsonStreamer Component

* Basit nesnelerle çalışıyorsanız
* Yüksek performans ve düşük bellek kullanımı gerekiyorsa
* Gerçek zamanlı veya çok büyük JSON verilerini belleğe yüklemeden işliyorsanız

> Performans ve bellek verimliliği açısından optimize edilmiştir.

Kısacası:

 **JsonStreamer → performans için** ,

**Serializer → esneklik ve çoklu formatlar için.**

Daha fazla bilgi için *streaming JSON* konusuna bakabilirsiniz.

---

### 🧮 PHP Dizilerine (Arrays) Serileştirme ve Deserileştirme

Varsayılan  **Serializer** , iki aşamalı serileştirme sürecinin yalnızca bir adımını gerçekleştirmek için kullanılabilir.

Bunun için aşağıdaki arayüzler (interface) kullanılır:

```php
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
// ...

class PersonController extends AbstractController
{
    public function index(DenormalizerInterface&NormalizerInterface $serializer): Response
    {
        $person = new Person('Jane Doe', 39, false);

        // Bir PHP nesnesini diziye dönüştürmek için normalize() kullanılır
        $personArray = $serializer->normalize($person, 'json');

        // Diziyi tekrar PHP nesnesine dönüştürmek için denormalize()
        $personCopy = $serializer->denormalize($personArray, Person::class);

        // ...
    }

    public function json(DecoderInterface&EncoderInterface $serializer): Response
    {
        $data = ['name' => 'Jane Doe'];

        // PHP dizilerini başka bir formata dönüştürmek için encode()
        $json = $serializer->encode($data, 'json');

        // Formatı sadece PHP dizisine dönüştürmek için decode()
        $data = $serializer->decode('{"name":"Charlie Doe"}', 'json');
        // $data = ['name' => 'Charlie Doe']
    }
}
```

---

### 🚫 Özellikleri (Properties) Yoksayma

 **ObjectNormalizer** , bir nesnenin tüm özelliklerini ve şu kalıplara sahip tüm metotları serileştirir:

`get*()`, `has*()`, `is*()` ve `can*()`.

Bazı özelliklerin veya metotların **asla serileştirilmemesi** gerekebilir.

Bunun için `#[Ignore]` özniteliğini (attribute) kullanabilirsiniz:

```php
// src/Model/Person.php
namespace App\Model;

use Symfony\Component\Serializer\Attribute\Ignore;

class Person
{
    // ...

    #[Ignore]
    public function isPotentiallySpamUser(): bool
    {
        // ...
    }
}
```

Bu özellik artık hiçbir zaman serileştirilmeyecektir:

```php
use App\Model\Person;

$person = new Person('Jane Doe', 32, false);
$json = $serializer->serialize($person, 'json');
// {"name":"Jane Doe","age":32,"sportsperson":false}
```

Deserileştirme sırasında gelen `"potentiallySpamUser"` alanı da yoksayılacaktır.

---

### 🧭 Context ile Özellikleri Yoksayma

Özellikleri çalışma anında (runtime) yoksaymak için `ignored_attributes` context seçeneğini kullanabilirsiniz:

```php
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

$person = new Person('Jane Doe', 32, false);
$json = $serializer->serialize($person, 'json', [
    AbstractNormalizer::IGNORED_ATTRIBUTES => ['age'],
]);
// {"name":"Jane Doe","sportsperson":false}
```

Ancak bu yöntem çok fazla kullanıldığında karmaşık hale gelir.

Daha iyi bir çözüm için **serialization groups** özelliğini kullanın.

---

### 🎯 Belirli Özellikleri Seçme (Serialization Groups)

Bazı durumlarda bir özelliği her yerde değil, yalnızca belirli yerlerde hariç tutmanız gerekebilir.

Bunun için `#[Groups]` özniteliğini kullanabilirsiniz:

```php
// src/Model/Person.php
namespace App\Model;

use Symfony\Component\Serializer\Attribute\Groups;

class Person
{
    #[Groups(["admin-view"])]
    private int $age;

    #[Groups(["public-view"])]
    private string $name;

    #[Groups(["public-view"])]
    private bool $sportsperson;
}
```

Serileştirme sırasında hangi grupların kullanılacağını belirtebilirsiniz:

```php
$json = $serializer->serialize($person, 'json', ['groups' => 'public-view']);
// {"name":"Jane Doe","sportsperson":false}

$json = $serializer->serialize($person, 'json', ['groups' => ['public-view', 'admin-view']]);
// {"name":"Jane Doe","age":32,"sportsperson":false}

$json = $serializer->serialize($person, 'json', ['groups' => '*']);
// {"name":"Jane Doe","age":32,"sportsperson":false}
```

---

### 🧱 Serialization Context Kullanımı

`attributes` context seçeneği ile hangi özelliklerin serileştirileceğini çalışma anında belirleyebilirsiniz:

```php
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

$json = $serializer->serialize($person, 'json', [
    AbstractNormalizer::ATTRIBUTES => ['name', 'company' => ['name']]
]);
// {"name":"Dunglas","company":{"name":"Les-Tilleuls.coop"}}
```

Yoksayılan veya grup dışı özellikler burada kullanılamaz.

---

### 📚 Dizilerle (Arrays) Çalışma

Serializer, nesne dizilerini de işleyebilir.

Birden fazla nesneyi serileştirmek tek bir nesneyi serileştirmekle aynıdır:

```php
use App\Model\Person;

$person1 = new Person('Jane Doe', 39, false);
$person2 = new Person('John Smith', 52, true);

$persons = [$person1, $person2];
$jsonContent = $serializer->serialize($persons, 'json');
// [{"name":"Jane Doe","age":39,"sportsman":false},{"name":"John Smith","age":52,"sportsman":true}]
```

Bir dizi nesneyi **deserileştirmek** için `[]` eklemeniz gerekir:

```php
$persons = $serializer->deserialize($jsonData, Person::class.'[]', 'json');
```

İç içe sınıflar için `@param` PHPDoc tipi belirtmelisiniz:

```php
// src/Model/UserGroup.php
namespace App\Model;

class UserGroup
{
    /**
     * @param Person[] $members
     */
    public function __construct(
        private array $members,
    ) {
    }

    /**
     * @param Person[] $members
     */
    public function setMembers(array $members): void
    {
        $this->members = $members;
    }
}
```

`list<Person>` ve `array<Person>` gibi statik analiz türleri de desteklenir.

Bunun için `phpstan/phpdoc-parser` ve `phpdocumentor/reflection-docblock` paketleri yüklü olmalıdır

(bunlar `symfony/serializer-pack` içinde gelir).

---

### 🪆 İç İçe Yapıları Deserileştirme

Bazı API’ler, PHP nesnesinde sadeleştirmek isteyebileceğiniz **iç içe (nested)** JSON yapıları döndürebilir:

```json
{
    "id": "123",
    "profile": {
        "username": "jdoe",
        "personal_information": {
            "full_name": "Jane Doe"
        }
    }
}
```

Bunu şu şekilde sade bir sınıfa dönüştürmek isteyebilirsiniz:

```php
class Person
{
    private int $id;
    private string $username;
    private string $fullName;
}
```

Bunun için `#[SerializedPath]` özniteliğini kullanın:

```php
namespace App\Model;

use Symfony\Component\Serializer\Attribute\SerializedPath;

class Person
{
    private int $id;

    #[SerializedPath('[profile][username]')]
    private string $username;

    #[SerializedPath('[profile][personal_information][full_name]')]
    private string $fullName;
}
```

> `#[SerializedPath]` aynı property’de `#[SerializedName]` ile birlikte kullanılamaz.

Serileştirme sırasında da geçerlidir:

```php
$person = new Person(123, 'jdoe', 'Jane Doe');
$jsonContent = $serializer->serialize($person, 'json');
// {"id":123,"profile":{"username":"jdoe","personal_information":{"full_name":"Jane Doe"}}}
```

---

### 🔤 Özellik İsimlerini Dönüştürme (Name Conversion)

Bazı durumlarda serileştirilen alan adları, PHP sınıfındaki property veya getter/setter isimlerinden farklı olmalıdır.

Bunun için **name converter** kullanılır.

Varsayılan olarak serializer, `MetadataAwareNameConverter` kullanır.

Bir alan adını değiştirmek için `#[SerializedName]` özniteliğini ekleyebilirsiniz:

```php
// src/Model/Person.php
namespace App\Model;

use Symfony\Component\Serializer\Attribute\SerializedName;

class Person
{
    #[SerializedName('customer_name')]
    private string $name;
}
```

Bu eşleme, serileştirme ve deserileştirme sırasında uygulanır:

```php
$json = $serializer->serialize($person, 'json');
// {"customer_name":"Jane Doe", ...}
```

Kendi **custom name converter** sınıfınızı da oluşturabilirsiniz.

---

### 🐍 CamelCase ↔ snake_case Dönüşümü

Birçok formatta kelimeler alt çizgiyle ayrılır ( **snake_case** ),

ancak Symfony projelerinde genellikle **camelCase** kullanılır.

Symfony, serileştirme ve deserileştirme sırasında `camelCase` ↔ `snake_case` dönüşümü yapmak için

yerleşik bir **name converter** sağlar.

Bu özelliği etkinleştirmek için `serializer.name_converter.camel_case_to_snake_case` ayarını yapın:

```php
// config/packages/serializer.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->serializer()
        ->nameConverter('serializer.name_converter.camel_case_to_snake_case');
};
```


### 🐍 snake_case → CamelCase Dönüşümü

Symfony uygulamalarında özellik (property) adlarında genellikle **camelCase** biçimi kullanılır.

Ancak bazı paketler **snake_case** biçimini tercih edebilir.

Symfony, serileştirme ve deserileştirme işlemleri sırasında **CamelCase** ve **snake_case** stilleri arasında dönüşüm yapabilen yerleşik bir **name converter** sağlar.

Bu dönüştürücüyü, metadata-aware name converter yerine kullanmak için aşağıdaki ayarı yapabilirsiniz:

```php
// config/packages/serializer.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->serializer()
        ->nameConverter('serializer.name_converter.snake_case_to_camel_case')
    ;
};
```

> 🆕 **Symfony 7.2** sürümünde **snake_case → CamelCase** dönüştürücüsü eklendi.

---

### ⚙️ Serializer Normalizer’ları

Varsayılan olarak serializer servisi aşağıdaki  **normalizer** ’larla yapılandırılmıştır (öncelik sırasına göre):

#### 🧩 UnwrappingDenormalizer

Girdinin yalnızca bir kısmını deserileştirmek için kullanılır.

Bu konu hakkında ilerleyen bölümlerde daha fazla bilgi bulabilirsiniz.

#### 🚨 ProblemNormalizer

`FlattenException` hatalarını **API Problem** (RFC 7807) standardına göre normalleştirir.

#### 🧬 UidNormalizer

`AbstractUid` sınıfını genişleten nesneleri normalleştirir.

* `Uuid` nesneleri için varsayılan format: **RFC 4122**

  (örnek: `d9e7a184-5d5b-11ea-a62a-3499710062d0`)
* `Ulid` nesneleri için varsayılan format: **Base32**

  (örnek: `01E439TP9XJZ9RPFH3T1PYBCR8`)

Formatı değiştirmek için context’te `UidNormalizer::NORMALIZATION_FORMAT_KEY` seçeneğini şu değerlerden biriyle belirtebilirsiniz:

* `UidNormalizer::NORMALIZATION_FORMAT_BASE58`
* `UidNormalizer::NORMALIZATION_FORMAT_BASE32`
* `UidNormalizer::NORMALIZATION_FORMAT_RFC4122`

Ayrıca, `uuid` veya `ulid` string’lerini uygun `Uuid` veya `Ulid` nesnelerine dönüştürebilir — format fark etmez.

---

#### 🕒 DateTimeNormalizer

`DateTimeInterface` (örneğin `DateTime` ve `DateTimeImmutable`) nesneleriyle

 **string** , **integer** veya **float** değerleri arasında dönüşüm yapar.

Varsayılan olarak **RFC 3339** biçimini kullanarak string’e dönüştürür.

* Formatı değiştirmek için: `DateTimeNormalizer::FORMAT_KEY`
* Zaman dilimini değiştirmek için: `DateTimeNormalizer::TIMEZONE_KEY`
* Sayısal biçime dönüştürmek için: `DateTimeNormalizer::CAST_KEY`

  (değer: `int` veya `float`)

> 🆕 `CAST_KEY` seçeneği **Symfony 7.1** sürümünde eklendi.

---

#### ⚖️ ConstraintViolationListNormalizer

`ConstraintViolationListInterface` nesnelerini, **RFC 7807** standardına uygun hata listesine dönüştürür.

---

#### 🌍 DateTimeZoneNormalizer

`DateTimeZone` nesneleri ile PHP zaman dilimi adlarını temsil eden string’ler arasında dönüşüm yapar.

---

#### ⏱️ DateIntervalNormalizer

`DateInterval` nesneleri ile string’ler arasında dönüşüm yapar.

Varsayılan biçim: `P%yY%mM%dDT%hH%iM%sS`

Formatı değiştirmek için: `DateIntervalNormalizer::FORMAT_KEY`

---

#### 🧾 FormErrorNormalizer

`FormInterface` implementasyonu yapan sınıflarla çalışır.

Form hatalarını **API Problem** (RFC 7807) standardına göre normalleştirir.

---

#### 🌐 TranslatableNormalizer

`TranslatableInterface` implementasyonu yapan nesneleri, çevirmen (`translator`) aracılığıyla çevrilmiş string’e dönüştürür.

Kullanılacak dili belirtmek için:

`TranslatableNormalizer::NORMALIZATION_LOCALE_KEY`

---

#### 🧱 BackedEnumNormalizer

`BackedEnum` enum’larını string veya integer’lara dönüştürür.

Varsayılan olarak geçersiz değerlerde hata fırlatır.

Bunun yerine `null` döndürmek için şu context seçeneğini ayarlayın:

`BackedEnumNormalizer::ALLOW_INVALID_VALUES => true`

---

#### 🔢 NumberNormalizer

`BcMath\Number` veya `GMP` nesnelerini string veya integer’lara dönüştürür.

> 🆕 **Symfony 7.2** sürümünde eklendi.

---

#### 📎 DataUriNormalizer

`SplFileInfo` nesneleri ile `data:` URI string’leri arasında dönüşüm yapar.

Bu sayede dosyalar serileştirilmiş verilere **gömülebilir** hale gelir.

---

#### 💠 JsonSerializableNormalizer

`JsonSerializable` arayüzünü uygulayan sınıflarla çalışır.

`jsonSerialize()` metodunu çağırır ve ardından sonucu tekrar normalleştirir.

Bu, iç içe `JsonSerializable` sınıfların da işlenmesini sağlar.

Bu normalizer, `json_encode()` kullanan eski kod tabanlarından

Symfony Serializer’a kademeli geçiş için idealdir.

> `json_encode`’den farklı olarak **dairesel referansları** da işleyebilir.

---

#### 🔁 ArrayDenormalizer

Dizi içinde dizileri, belirtilen türde **nesne dizilerine** dönüştürür.

(Detaylar için “Handling Arrays” bölümüne bakın.)

**PropertyInfoExtractor** kullanarak tür ipuçlarını sağlayabilirsiniz:

```php
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

$propertyInfo = new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()]);
$normalizers = [new ObjectNormalizer(new ClassMetadataFactory(new AttributeLoader()), null, null, $propertyInfo), new ArrayDenormalizer()];

$this->serializer = new Serializer($normalizers, [new JsonEncoder()]);
```

---

#### ⚙️ ObjectNormalizer

En güçlü varsayılan normalizer’dır ve diğerleri tarafından işlenemeyen nesnelerde kullanılır.

**PropertyAccess** bileşeninden yararlanarak nesnelerin özelliklerini doğrudan veya getter/setter metodları üzerinden okur/yazar.

Aşağıdaki türde metotları tanır:

`get`, `set`, `has`, `is`, `add`, `remove`

Örneğin:

`getFirstName()` → `firstName`

Deserileştirme sırasında hem **constructor** hem de bulunan setter metotlarını kullanabilir.

> 🧠 `DateTime` veya `DateTimeImmutable` nesnelerini serileştirirken  **DateTimeNormalizer** ’ın kayıtlı olduğundan emin olun.
>
> Aksi halde yüksek bellek kullanımı ve dahili detayların sızması yaşanabilir.

---

### 🧰 Dahili (Built-in) Normalizer’lar

Varsayılan olarak kayıtlı normalizer’ların dışında, serializer bileşeni birkaç **ek normalizer** da sunar.

Bunları servis olarak tanımlayıp `serializer.normalizer` etiketiyle kaydedebilirsiniz.

Örneğin, `CustomNormalizer` kullanmak için:

```php
// config/services.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Serializer\Normalizer\CustomNormalizer;

return function(ContainerConfigurator $container) {
    // ...

    // autoconfigure açıksa etiket otomatik olarak eklenir
    $services->set(CustomNormalizer::class)
        // yüksek öncelik (daha erken çağrılır)
        ->tag('serializer.normalizer', [
            'priority' => 500,
        ])
    ;
};
```

---

#### 🧩 CustomNormalizer

Nesne serileştirilirken PHP nesnesi üzerinde bir metodu çağırır.

Nesne `NormalizableInterface` ve/veya `DenormalizableInterface` arayüzlerinden birini uygulamalıdır.

---

#### 🧠 GetSetMethodNormalizer

Varsayılan `ObjectNormalizer`’a bir alternatiftir.

Sınıfın içeriğini `get`, `has`, `is` veya `can` ile başlayan public metotları çağırarak okur.

Deserileştirme sırasında **constructor** ve `set` metotlarını kullanır.

Metot adından `get` ön ekini kaldırır ve ilk harfi küçültür:

örnek → `getFirstName()` → `firstName`

---

#### 🪞 PropertyNormalizer

`ObjectNormalizer`’a bir başka alternatiftir.

PHP Reflection kullanarak hem public hem de private/protected özellikleri doğrudan okur/yazar.

Deserileştirme sırasında constructor kullanılabilir.

Özellik görünürlüğüne göre sınırlandırmak için:

`PropertyNormalizer::NORMALIZE_VISIBILITY` context seçeneğini kullanın.

Örnek:

```php
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;

// ...
$json = $serializer->serialize($person, 'json', [
    // sadece public özellikleri serileştir
    PropertyNormalizer::NORMALIZE_VISIBILITY => PropertyNormalizer::NORMALIZE_PUBLIC,

    // public ve protected özellikleri serileştir
    PropertyNormalizer::NORMALIZE_VISIBILITY => PropertyNormalizer::NORMALIZE_PUBLIC | PropertyNormalizer::NORMALIZE_PROTECTED,
]);
```


### 🧱 Named Serializers

> 🆕 **Symfony 7.2** sürümünde tanıtılmıştır.

Bazen aynı uygulamada birden fazla **serializer yapılandırmasına** ihtiyaç duyabilirsiniz.

Örneğin, farklı API’lerle iletişim kuran bir uygulamada, her API kendi serileştirme kurallarına sahiptir (farklı context’ler, name converter’lar, normalizer/encoder setleri vb.).

Bu durumda, `named_serializers` seçeneğiyle birden fazla **serializer örneği** tanımlayabilirsiniz:

```php
// config/packages/serializer.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->serializer()
        ->namedSerializer('api_client1')
            ->nameConverter('serializer.name_converter.camel_case_to_snake_case')
            ->defaultContext([
                'enable_max_depth' => true,
            ])
    ;
    $framework->serializer()
        ->namedSerializer('api_client2')
            ->defaultContext([
                'enable_max_depth' => false,
            ])
    ;
};
```

---

### 💉 Named Serializer’ları Enjekte Etme

Tanımladığınız serializer’ları bağımlılık enjeksiyonu ile kullanabilirsiniz:

```php
namespace App\Controller;

use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PersonController extends AbstractController
{
    public function index(
        SerializerInterface $serializer,           // varsayılan serializer
        SerializerInterface $apiClient1Serializer, // api_client1 serializer
        #[Target('apiClient2.serializer')]         // api_client2 serializer
        SerializerInterface $customName,
    ) {
        // ...
    }
}
```

---

### 🧩 Normalizer ve Encoder’ları Özelleştirme

Varsayılan olarak, named serializer’lar da **yerleşik normalizer** ve **encoder** setlerini kullanır.

Ancak belirli serializer’lar için **ek normalizer veya encoder** tanımlayabilirsiniz.

Bunun için, `serializer.normalizer` veya `serializer.encoder` etiketine `serializer` niteliğini ekleyin:

```php
// config/services.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Serializer\Normalizer\CustomNormalizer;

return function(ContainerConfigurator $container) {
    $services->set(CustomNormalizer::class)
        // varsayılan serializer’a otomatik eklenmesini engelle
        ->autoconfigure(false)

        // yalnızca belirli bir serializer’a ekle
        ->tag('serializer.normalizer', ['serializer' => 'api_client1'])
        // birden fazla serializer’a ekle
        ->tag('serializer.normalizer', ['serializer' => ['api_client1', 'api_client2']])
        // tüm serializer’lara (varsayılan dahil) ekle
        ->tag('serializer.normalizer', ['serializer' => '*'])
    ;
};
```

`serializer` niteliği belirtilmezse, servis yalnızca varsayılan serializer’a kaydedilir.

---

### 🧭 Normalizer / Encoder Önceliklerini Görüntüleme

Her normalizer veya encoder, kendi adını içeren bir etiketle kaydedilir:

`serializer.normalizer.<name>` veya `serializer.encoder.<name>`

Bunların önceliklerini görmek için:

```bash
php bin/console debug:container --tag serializer.<normalizer|encoder>.<name>
```

---

### 🚫 Dahili Normalizer / Encoder Setlerini Devre Dışı Bırakma

Varsayılan normalizer veya encoder’ların yüklenmesini istemiyorsanız,

`include_built_in_normalizers` veya `include_built_in_encoders` seçeneklerini kullanabilirsiniz:

```php
// config/packages/serializer.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->serializer()
        ->namedSerializer('api_client1')
            ->includeBuiltInNormalizers(false)
            ->includeBuiltInEncoders(true)
    ;
};
```

---

### 🧪 Serializer’ı Hata Ayıklama (Debugging)

Bir sınıfa ait serileştirme metadatasını görmek için:

```bash
php bin/console debug:serializer 'App\Entity\Book'
```

Çıktı örneği:

```
App\Entity\Book
---------------

+----------+------------------------------------------------------------+
| Property | Options                                                    |
+----------+------------------------------------------------------------+
| name     | [                                                          |
|          |   "groups" => ["book:read","book:write"],                  |
|          |   "maxDepth" => 1,                                         |
|          |   "serializedName" => "book_name",                         |
|          |   "serializedPath" => null,                                |
|          |   "ignore" => false,                                       |
|          |   "normalizationContexts" => [],                           |
|          |   "denormalizationContexts" => []                          |
|          | ]                                                          |
| isbn     | [                                                          |
|          |   "groups" => ["book:read"],                               |
|          |   "serializedPath" => "[data][isbn]",                      |
|          | ]                                                          |
+----------+------------------------------------------------------------+
```

---

## ⚙️ Gelişmiş Serileştirme (Advanced Serialization)

### 🚫 Null Değerleri Atlamak

Varsayılan olarak, `null` değerli özellikler korunur.

Bu davranışı değiştirmek için `AbstractObjectNormalizer::SKIP_NULL_VALUES` seçeneğini kullanın:

```php
class Person
{
    public string $name = 'Jane Doe';
    public ?string $gender = null;
}

$jsonContent = $serializer->serialize(new Person(), 'json', [
    AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
]);
// {"name":"Jane Doe"}
```

---

### 🧍‍♂️ Boş Nesneleri Korumak

Varsayılan olarak, boş bir dizi `[]` olarak serileştirilir.

Bunun yerine `{}` olarak kalmasını istiyorsanız

`AbstractObjectNormalizer::PRESERVE_EMPTY_OBJECTS` seçeneğini `true` yapın.

Bu özellikle `\ArrayObject()` örnekleri için geçerlidir.

---

### 🧩 Başlatılmamış Özellikleri (Uninitialized Properties) Yönetmek

PHP’de tip tanımlı özellikler, değer atanmadan önce **başlatılmamış (uninitialized)** durumdadır.

Bu özelliklere erişmek hata fırlatır.

Serializer bu durumda hata atmamak için varsayılan olarak bu tür özellikleri  **yoksayar** .

Bu davranışı devre dışı bırakmak için:

`AbstractObjectNormalizer::SKIP_UNINITIALIZED_VALUES => false`

```php
class Person {
    public string $name = 'Jane Doe';
    public string $phoneNumber; // başlatılmamış
}

$jsonContent = $normalizer->serialize(new Person(), 'json', [
    AbstractObjectNormalizer::SKIP_UNINITIALIZED_VALUES => false,
]);
// UninitializedPropertyException fırlatır
```

`PropertyNormalizer` veya `GetSetMethodNormalizer` kullanırken bu seçenek `false` ise,

nesnede başlatılmamış özellik varsa bir `\Error` hatası oluşur.

---

### 🔁 Dairesel Referansları (Circular References) Yönetmek

İlişkili nesnelerde dairesel referanslar sonsuz döngüye yol açabilir:

```php
class Organization
{
    public function __construct(
        private string $name,
        private array $members = []
    ) {}

    public function getName(): string { return $this->name; }
    public function addMember(Member $member): void { $this->members[] = $member; }
    public function getMembers(): array { return $this->members; }
}

class Member
{
    private Organization $organization;

    public function __construct(private string $name) {}

    public function getName(): string { return $this->name; }
    public function setOrganization(Organization $organization): void { $this->organization = $organization; }
    public function getOrganization(): Organization { return $this->organization; }
}
```

Bu durumda serializer bir **CircularReferenceException** fırlatır.

```php
$organization = new Organization('Les-Tilleuls.coop');
$member = new Member('Kévin');

$organization->addMember($member);
$member->setOrganization($organization);

$json = $serializer->serialize($organization, 'json');
// CircularReferenceException
```

Bu sınırı `circular_reference_limit` context anahtarıyla değiştirebilirsiniz (varsayılan = 1).

---

### 🧩 Özel Callback ile Dairesel Referansları Yönetmek

Dairesel referanslarda istisna atmak yerine özel bir fonksiyon tanımlayabilirsiniz:

```php
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

$context = [
    AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function (object $object): string {
        if (!$object instanceof Organization) {
            throw new CircularReferenceException('A circular reference has been detected for '.get_debug_type($object));
        }
        return $object->getName();
    },
];

$json = $serializer->serialize($organization, 'json', $context);
// {"name":"Les-Tilleuls.coop","members":[{"name":"Kévin","organization":"Les-Tilleuls.coop"}]}
```

---

### 🌳 Serileştirme Derinliğini (Depth) Sınırlama

Serializer, iç içe geçmiş aynı sınıf nesnelerini algılayıp serileştirme derinliğini sınırlayabilir.

Bu, ağaç yapılarında özellikle yararlıdır.

```php
class Person
{
    public function __construct(
        private string $name,
        private ?self $mother
    ) {}

    public function getName(): string { return $this->name; }
    public function getMother(): ?self { return $this->mother; }
}
```

```php
use Symfony\Component\Serializer\Attribute\MaxDepth;

class Person
{
    #[MaxDepth(1)]
    private ?self $mother;
}
```

Context’te `AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true` ayarlayın:

```php
$json = $serializer->serialize($child, null, [
    AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
]);
// {"name":"Joe","mother":{"name":"Sophie"}}
```

Derinlik sınırına ulaşıldığında özel bir fonksiyon da kullanabilirsiniz:

```php
$maxDepthHandler = function (object $inner, object $outer, string $attr): ?string {
    return $inner instanceof Person ? $inner->getName() : null;
};

$json = $serializer->serialize($child, null, [
    AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
    AbstractObjectNormalizer::MAX_DEPTH_HANDLER => $maxDepthHandler,
]);
// {"name":"Joe","mother":{"name":"Sophie","mother":"Jane"}}
```

---

### 🪄 Özelliklere Özel Callback Tanımlama

Belirli özelliklerin serileştirilmesi için özel bir callback tanımlayabilirsiniz:

```php
$person = new Person('cordoval', 34);
$person->setCreatedAt(new \DateTime('now'));

$context = [
    AbstractNormalizer::CALLBACKS => [
        'createdAt' => function ($value) {
            return $value instanceof \DateTime ? $value->format(\DateTime::ATOM) : '';
        },
    ],
];

$json = $serializer->serialize($person, 'json', $context);
// {"name":"cordoval","age":34,"createdAt":"2014-03-22T09:43:12-0500"}
```


### 🧩 Gelişmiş Deserileştirme (Advanced Deserialization)

---

### ✅ Tüm Özelliklerin Zorunlu Olması (Require all Properties)

Varsayılan olarak, serializer eksik parametreler için **nullable** özelliklere `null` atar.

Bu davranışı değiştirmek için `AbstractNormalizer::REQUIRE_ALL_PROPERTIES` seçeneğini `true` yapabilirsiniz:

```php
class Person
{
    public function __construct(
        public string $firstName,
        public ?string $lastName,
    ) {
    }
}

$data = ['firstName' => 'John'];

$person = $serializer->deserialize($data, Person::class, 'json', [
    AbstractNormalizer::REQUIRE_ALL_PROPERTIES => true,
]);
// MissingConstructorArgumentException fırlatır
```

---

### 🧾 Tür Hatalarını Toplama (Collecting Type Errors While Denormalizing)

Typed property’lere sahip nesneleri deserileştirirken, gelen verilerde tür uyuşmazlığı olursa normalde bir hata fırlatılır.

Tüm hataları aynı anda toplamak ve kısmen deserileştirilmiş bir nesne almak için

`DenormalizerInterface::COLLECT_DENORMALIZATION_ERRORS` seçeneğini kullanın:

```php
try {
    $person = $serializer->deserialize($jsonString, Person::class, 'json', [
        DenormalizerInterface::COLLECT_DENORMALIZATION_ERRORS => true,
    ]);
} catch (PartialDenormalizationException $e) {
    $violations = new ConstraintViolationList();

    foreach ($e->getErrors() as $exception) {
        $message = sprintf(
            'The type must be one of "%s" ("%s" given).',
            implode(', ', $exception->getExpectedTypes()),
            $exception->getCurrentType()
        );
        $parameters = [];
        if ($exception->canUseMessageForUser()) {
            $parameters['hint'] = $exception->getMessage();
        }
        $violations->add(new ConstraintViolation(
            $message,
            '',
            $parameters,
            null,
            $exception->getPath(),
            null
        ));
    }

    // ... violation list'i kullanıcıya döndür
}
```

---

### 🔁 Var Olan Nesneye Deserileştirme (Deserializing in an Existing Object)

Serializer, mevcut bir nesneyi güncellemek için de kullanılabilir.

Bunu `AbstractNormalizer::OBJECT_TO_POPULATE` seçeneğiyle yapabilirsiniz:

```php
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

$person = new Person('Jane Doe', 59);

$serializer->deserialize($jsonData, Person::class, 'json', [
    AbstractNormalizer::OBJECT_TO_POPULATE => $person,
]);
// Yeni nesne döndürmek yerine mevcut $person güncellenir
```

> Bu seçenek yalnızca **en üst düzey (root)** nesne için geçerlidir.
>
> Alt nesneler varsa, varsayılan olarak yeniden oluşturulurlar.

Alt nesnelerin de mevcut olanlar üzerinden güncellenmesini istiyorsanız:

`AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true`

> Bu yalnızca **tekil child nesnelerde** çalışır;
>
> nesne dizileri yine yeniden oluşturulur.

---

### 🧱 Arayüz ve Soyut Sınıfları Deserileştirme (Interfaces & Abstract Classes)

Bir özellik bir **arayüz (interface)** veya **soyut sınıf (abstract class)** referans ediyorsa,

serializer hangi somut sınıfın oluşturulacağını bilmelidir.

Bu, **Discriminator Map** kullanılarak yapılır:

```php
namespace App\Model;

use Symfony\Component\Serializer\Attribute\DiscriminatorMap;

#[DiscriminatorMap(
    typeProperty: 'type',
    mapping: [
        'product' => Product::class,
        'shipping' => Shipping::class,
    ]
)]
interface InvoiceItemInterface
{
    // ...
}
```

Serializer artık doğru sınıfı otomatik seçer:

```php
class InvoiceLine
{
    public function __construct(private InvoiceItemInterface $invoiceItem) {}

    public function getInvoiceItem(): InvoiceItemInterface
    {
        return $this->invoiceItem;
    }
}

$invoiceLine = new InvoiceLine(new Product());
$jsonString = $serializer->serialize($invoiceLine, 'json');
// {"type":"product",...}

$invoiceLine = $serializer->deserialize($jsonString, InvoiceLine::class, 'json');
// new InvoiceLine(new Product(...))
```

---

### 🧩 Varsayılan Tip Tanımlama (defaultType)

> 🆕 **Symfony 7.3** ile eklendi.

`type` alanı olmadan deserileştirmeye izin vermek için `defaultType` parametresini kullanabilirsiniz:

```php
#[DiscriminatorMap(
    typeProperty: 'type',
    mapping: [
        'product' => Product::class,
        'shipping' => Shipping::class,
    ],
    defaultType: 'product',
)]
interface InvoiceItemInterface {}
```

Artık gelen JSON’da `"type"` alanı olmasa da serializer varsayılan olarak `Product` sınıfını kullanır.

---

### 🧩 Girdiyi Kısmen Deserileştirme (Unwrapping)

Bazı API’lerden dönen JSON verilerinde, ihtiyacınız olan veri çok katmanlı bir yapıdadır.

Tüm yanıtı deserileştirmek yerine yalnızca belirli bir kısmı çözmek için

`UnwrappingDenormalizer`’ı kullanabilirsiniz:

```php
$jsonData = '{"result":"success","data":{"person":{"name": "Jane Doe","age":57}}}';
$data = $serializer->deserialize($jsonData, Person::class, 'json', [
    UnwrappingDenormalizer::UNWRAP_PATH => '[data][person]',
]);
// $data = Person(name: 'Jane Doe', age: 57)
```

`UNWRAP_PATH`, `PropertyAccess` bileşeninin sözdizimini kullanır.

---

### 🏗️ Constructor Argümanlarını Yönetmek (Handling Constructor Arguments)

Bir sınıfın constructor’ı parametreler tanımlıyorsa, serializer bunları deserileştirme verisiyle eşleştirir.

Eksik parametreler varsa `MissingConstructorArgumentsException` fırlatılır.

Bu durumu önlemek için `default_constructor_arguments` context seçeneğiyle varsayılan değerler belirtebilirsiniz:

```php
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

$jsonData = '{"age":39,"name":"Jane Doe"}';
$person = $serializer->deserialize($jsonData, Person::class, 'json', [
    AbstractNormalizer::DEFAULT_CONSTRUCTOR_ARGUMENTS => [
        Person::class => ['sportsperson' => true],
    ],
]);
// $person = Person(name: 'Jane Doe', age: 39, sportsperson: true)
```

---

### 🔁 Özyinelemeli Denormalizasyon ve Tip Güvenliği (Recursive Denormalization and Type Safety)

Bir `PropertyTypeExtractor` mevcutsa, normalizer verinin türünü özellik tipine göre kontrol eder.

Örneğin, bir string gönderilmişse ama property `int` ise `UnexpectedValueException` fırlatılır.

Bu tür denetimini devre dışı bırakmak için:

`ObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true`

---

### ⚖️ Boolean Değerleri İşleme (Handling Boolean Values)

> 🆕 **Symfony 7.1** sürümünde eklendi.

PHP, birçok farklı değeri `true` veya `false` olarak değerlendirir (`yes`, `1`, `no`, `0` vb.).

Deserileştirme sırasında bu dönüşümü otomatikleştirmek için

`AbstractNormalizer::FILTER_BOOL` seçeneğini kullanabilirsiniz:

```php
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

$person = $serializer->denormalize(['sportsperson' => 'yes'], Person::class, context: [
    AbstractNormalizer::FILTER_BOOL => true
]);
// $person->sportsperson === true
```

Bu, `filter_var(..., FILTER_VALIDATE_BOOL)` davranışına eşdeğerdir.

---

### ⚙️ Metadata Önbellekleme (Metadata Cache)

Serializer metadatası performansı artırmak için otomatik olarak önbelleğe alınır.

Varsayılan olarak, `cache.system` önbellek havuzu kullanılır.

Bu yapılandırma `cache.system` seçeneğiyle yönetilir.
