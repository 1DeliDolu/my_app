### 🌱 Twig’i Genişletme

Twig birçok şekilde genişletilebilir; ek etiketler, filtreler, testler, operatörler, global değişkenler ve fonksiyonlar ekleyebilirsiniz. Hatta node visitor’lar ile ayrıştırıcıyı (parser) bile genişletebilirsiniz.

> **Not**
>
> Bu bölümün ilk kısmı Twig’in nasıl genişletileceğini açıklar. Değişikliklerinizi farklı projelerde yeniden kullanmak veya başkalarıyla paylaşmak istiyorsanız, sonraki bölümde açıklandığı gibi bir extension oluşturmalısınız.

> **Dikkat**
>
> Bir extension oluşturmadan Twig’i genişletirseniz, PHP kodu güncellendiğinde Twig şablonlarınızı yeniden derleyemez. Değişikliklerinizi anında görmek için şablon önbelleğini devre dışı bırakın veya kodunuzu bir extension olarak paketleyin (bu bölümün ilerleyen kısmına bakın).

Twig’i genişletmeden önce, mevcut genişletme noktaları arasındaki farkları ve ne zaman hangisinin kullanılacağını anlamalısınız.

Twig’in iki ana dil yapısı olduğunu unutmayın:

* `{{ }}`: bir ifadeyi değerlendirip sonucunu yazdırmak için kullanılır;
* `{% %}`: komutları yürütmek için kullanılır.

Twig’in neden bu kadar çok genişletme noktası sunduğunu anlamak için, bir Lorem ipsum üreticisini nasıl uygulayacağımıza bakalım (kaç kelime üretileceğini bilmesi gerekir).

#### 🏷️ Etiket (Tag) Kullanımı

```twig
{% lipsum 40 %}
```

Bu çalışır, ancak **lipsum** için bir etiket kullanmak en az üç sebeple iyi bir fikir değildir:

* lipsum bir dil yapısı değildir;
* etiket bir çıktı üretir;
* etiketi bir ifade içinde kullanamazsınız:

```twig
{{ 'some text' ~ {% lipsum 40 %} ~ 'some more text' }}
```

Aslında, etiket oluşturmanız nadiren gerekir; ve bu iyi bir haber çünkü etiketler en karmaşık genişletme noktasıdır.

#### 🔄 Filtre Kullanımı

```twig
{{ 40|lipsum }}
```

Bu da çalışır. Ancak bir filtre, kendisine verilen değeri başka bir şeye dönüştürmelidir. Burada 40 sayısını dönüştürmüyoruz, yalnızca kaç kelime oluşturulacağını belirtiyoruz.

#### ⚙️ Fonksiyon Kullanımı

```twig
{{ lipsum(40) }}
```

İşte bu! Bu örnek için bir fonksiyon oluşturmak doğru yaklaşımdır. Bunu bir ifadenin kabul edildiği her yerde kullanabilirsiniz:

```twig
{{ 'some text' ~ lipsum(40) ~ 'some more text' }}

{% set lipsum = lipsum(40) %}
```

#### 🌍 Global Nesne Kullanımı

Son olarak, Lorem ipsum metni üretebilen bir metoda sahip global bir nesne de kullanabilirsiniz:

```twig
{{ text.lipsum(40) }}
```

📘 **Kural:** Sık kullanılan özellikler için fonksiyonları, diğer her şey için global nesneleri kullanın.

| Ne?      | Zorluk     | Sıklık | Ne Zaman?            |
| -------- | ---------- | -------- | -------------------- |
| macro    | basit      | sık     | İçerik üretimi    |
| global   | basit      | sık     | Yardımcı nesne     |
| function | basit      | sık     | İçerik üretimi    |
| filter   | basit      | sık     | Değer dönüşümü |
| tag      | karmaşık | nadir    | DSL dil yapısı     |
| test     | basit      | nadir    | Mantıksal karar     |
| operator | basit      | nadir    | Değer dönüşümü |

---

### 🌐 Globals (Global Değişkenler)

Global değişkenler tüm şablonlarda ve macro’larda kullanılabilir. Twig ortamına `addGlobal()` ile global bir değişken ekleyin:

```php
$twig = new \Twig\Environment($loader);
$twig->addGlobal('text', new Text());
```

Şablonlarda artık şu şekilde kullanabilirsiniz:

```twig
{{ text.lipsum(40) }}
```

---

### 🧩 Filters (Filtreler)

Bir filtre oluşturmak, bir isim ile bir PHP callable’ını ilişkilendirmekten ibarettir:

```php
// anonim fonksiyon
$filter = new \Twig\TwigFilter('rot13', function ($string) {
    return str_rot13($string);
});

// basit PHP fonksiyonu
$filter = new \Twig\TwigFilter('rot13', 'str_rot13');

// sınıf static metodu
$filter = new \Twig\TwigFilter('rot13', ['SomeClass', 'rot13Filter']);
$filter = new \Twig\TwigFilter('rot13', 'SomeClass::rot13Filter');

// sınıf metodu
$filter = new \Twig\TwigFilter('rot13', [$this, 'rot13Filter']);
```

Sonra filtreyi Twig ortamına ekleyin:

```php
$twig = new \Twig\Environment($loader);
$twig->addFilter($filter);
```

Ve şablonda kullanın:

```twig
{{ 'Twig'|rot13 }}
{# çıktısı: Gjvt #}
```

Twig çağırıldığında, PHP callable’ı boru (`|`) operatörünün solundaki değeri ilk argüman olarak alır, parantez içindeki diğer argümanları ise ek parametre olarak alır.

#### 🔠 Charset-aware Filters

Varsayılan karakter kümesine erişmek istiyorsanız `needs_charset` seçeneğini `true` yapın:

```php
$filter = new \Twig\TwigFilter('rot13', function (string $charset, $string) {
    return str_rot13($string);
}, ['needs_charset' => true]);
```

#### 🌍 Environment-aware Filters

Geçerli ortam örneğine erişmek için `needs_environment` seçeneğini `true` yapın:

```php
$filter = new \Twig\TwigFilter('rot13', function (\Twig\Environment $env, $string) {
    $charset = $env->getCharset();
    return str_rot13($string);
}, ['needs_environment' => true]);
```

#### 🧠 Context-aware Filters

Geçerli bağlama (context) erişmek için `needs_context` seçeneğini `true` yapın:

```php
$filter = new \Twig\TwigFilter('rot13', function ($context, $string) {
    // ...
}, ['needs_context' => true]);
```

---

### 🔒 Otomatik Kaçış (Automatic Escaping)

Otomatik kaçış etkinse, filtre çıktısı yazdırılmadan önce kaçırılabilir. Eğer filtreniz HTML/JavaScript üretiyorsa `is_safe` seçeneğini kullanın:

```php
$filter = new \Twig\TwigFilter('nl2br', 'nl2br', ['is_safe' => ['html']]);
```

Girdi zaten güvenliyse ancak siz HTML etiketleri eklemek istiyorsanız, `pre_escape` seçeneğini kullanın:

```php
$filter = new \Twig\TwigFilter('somefilter', 'somefilter', ['pre_escape' => 'html', 'is_safe' => ['html']]);
```

---

### ⚖️ Variadic Filters (Değişken Argümanlı Filtreler)

Belirsiz sayıda argüman kabul eden filtreler için `is_variadic` seçeneğini `true` yapın:

```php
$filter = new \Twig\TwigFilter('thumbnail', function ($file, array $options = []) {
    // ...
}, ['is_variadic' => true]);
```

---

### 🔁 Dynamic Filters (Dinamik Filtreler)

İsmi `*` karakteri içeren filtreler dinamiktir:

```php
$filter = new \Twig\TwigFilter('*_path', function ($name, $arguments) {
    // ...
});
```

Bu durumda `product_path` ve `category_path` filtreleri eşleşir.

Birden fazla dinamik bölüm de olabilir:

```php
$filter = new \Twig\TwigFilter('*_path_*', function ($name, $suffix, $arguments) {
    // ...
});
```

---

### 🕰️ Deprecated Filters (Kullanımdan Kaldırılmış Filtreler)

Twig 3.15 ile `deprecation_info` seçeneği eklendi:

```php
$filter = new \Twig\TwigFilter('obsolete', function () {
    // ...
}, ['deprecation_info' => new DeprecatedCallableInfo('twig/twig', '3.11', 'new_one')]);
```

Alternatif olarak (Twig 3.11 ve öncesinde):

```php
$filter = new \Twig\TwigFilter('obsolete', function () {
    // ...
}, ['deprecated' => true, 'alternative' => 'new_one']);
```

Twig, bu filtre kullanıldığında bir uyarı verir.

---



### ⚙️ Fonksiyonlar (Functions)

Fonksiyonlar, filtrelerle tamamen aynı şekilde tanımlanır, ancak bir `\Twig\TwigFunction` örneği oluşturmanız gerekir:

```php
$twig = new \Twig\Environment($loader);
$function = new \Twig\TwigFunction('function_name', function () {
    // ...
});
$twig->addFunction($function);
```

Fonksiyonlar, filtrelerle aynı özellikleri destekler, ancak `pre_escape` ve `preserves_safety` seçenekleri hariç.

---

### 🧪 Testler (Tests)

Testler, filtreler ve fonksiyonlarla aynı şekilde tanımlanır, ancak bir `\Twig\TwigTest` örneği oluşturmanız gerekir:

```php
$twig = new \Twig\Environment($loader);
$test = new \Twig\TwigTest('test_name', function () {
    // ...
});
$twig->addTest($test);
```

Testler, boolean koşulları değerlendirmek için özel uygulama mantıkları oluşturmanıza olanak tanır. Örneğin, bir nesnenin "kırmızı" olup olmadığını kontrol eden bir Twig testi oluşturalım:

```php
$twig = new \Twig\Environment($loader);
$test = new \Twig\TwigTest('red', function ($value) {
    if (isset($value->color) && $value->color == 'red') {
        return true;
    }
    if (isset($value->paint) && $value->paint == 'red') {
        return true;
    }
    return false;
});
$twig->addTest($test);
```

Test fonksiyonları her zaman **true** veya **false** döndürmelidir.

Test oluştururken, testiniz PHP’nin ilkel yapılarıyla derlenebiliyorsa, `node_class` seçeneğini kullanarak özel bir test derleme sınıfı sağlayabilirsiniz. Bu, Twig’in dahili testlerinin çoğunda kullanılır:

```php
namespace App;

use Twig\Environment;
use Twig\Node\Expression\TestExpression;
use Twig\TwigTest;

$twig = new Environment($loader);
$test = new TwigTest(
    'odd',
    null,
    ['node_class' => OddTestExpression::class]
);
$twig->addTest($test);

class OddTestExpression extends TestExpression
{
    public function compile(\Twig\Compiler $compiler)
    {
        $compiler
            ->raw('(')
            ->subcompile($this->getNode('node'))
            ->raw(' % 2 != 0')
            ->raw(')');
    }
}
```

Yukarıdaki örnek, **node class** kullanan bir testin nasıl oluşturulacağını gösterir. Node sınıfı, test edilen değeri içeren `node` adlı bir alt node’a erişir. Örneğin:

```twig
{% if my_value is odd %}
```

Bu durumda, `node` alt node’u `my_value` ifadesini içerir. Node tabanlı testler ayrıca `arguments` node’una da erişebilir. Bu node, teste sağlanan diğer tüm argümanları içerir.

Eğer teste değişken sayıda veya isimlendirilmiş argümanlar geçirmek istiyorsanız, `is_variadic` seçeneğini `true` yapın. Testler dinamik isimleri de destekler (sözdizimi için dinamik filtreler kısmına bakın).

---

### 🏷️ Etiketler (Tags)

Twig gibi bir şablon motorunun en heyecan verici özelliklerinden biri, yeni dil yapıları tanımlama olanağıdır. Ancak bu, Twig’in iç işleyişini anlamanızı gerektirdiği için en karmaşık özelliktir.

Çoğu durumda, bir etiket gerekmez:

* Etiketiniz bir çıktı üretiyorsa, **fonksiyon** kullanın.
* Etiket içeriği değiştirip geri döndürüyorsa, **filtre** kullanın.

Örneğin, Markdown biçimli bir metni HTML’ye dönüştürmek istiyorsanız, bir **markdown filtresi** oluşturun:

```twig
{{ '**markdown** text'|markdown }}
```

Bu filtreyi büyük metin bloklarına uygulamak istiyorsanız, `apply` etiketiyle sarın:

```twig
{% apply markdown %}
Title
=====

Much better than creating a tag as you can **compose** filters.
{% endapply %}
```

Eğer etiket hiçbir şey döndürmüyor, sadece yan etkiden dolayı var oluyorsa, bir fonksiyon oluşturun ve bunu `do` etiketiyle çağırın:

```twig
{% do log('Log some things') %}
```

Eğer yine de yeni bir dil yapısı için özel bir etiket oluşturmak istiyorsanız, harika!

---

#### 🧱 Örnek: “set” Etiketi

```twig
{% set name = "value" %}

{{ name }}

{# çıktısı: value #}
```

> **Not:**
>
> `set` etiketi Core extension’ın bir parçasıdır ve her zaman kullanılabilir. Dahili sürümü, birden fazla atamayı destekleyecek kadar güçlüdür.

Yeni bir etiket tanımlamak için üç adım gerekir:

1. **Token Parser sınıfı** tanımlamak (şablon kodunu ayrıştırmaktan sorumlu).
2. **Node sınıfı** tanımlamak (ayrıştırılan kodu PHP’ye dönüştürmekten sorumlu).
3. **Etiketi kaydetmek.**

---

#### 🔗 Etiketi Kaydetme

```php
$twig = new \Twig\Environment($loader);
$twig->addTokenParser(new CustomSetTokenParser());
```

---

#### 🧩 Token Parser Tanımlama

```php
class CustomSetTokenParser extends \Twig\TokenParser\AbstractTokenParser
{
    public function parse(\Twig\Token $token)
    {
        $parser = $this->parser;
        $lineno = $token->getLine();
        $stream = $parser->getStream();

        $name = $stream->expect(\Twig\Token::NAME_TYPE)->getValue();
        $stream->expect(\Twig\Token::OPERATOR_TYPE, '=');
        $value = $parser->getExpressionParser()->parseExpression();
        $stream->expect(\Twig\Token::BLOCK_END_TYPE);

        return new CustomSetNode($name, $value, $lineno);
    }

    public function getTag()
    {
        return 'set';
    }
}
```

`getTag()` yöntemi ayrıştırılacak etiketin adını döndürmelidir.

`parse()` yöntemi, `set` etiketiyle karşılaşıldığında çağrılır ve bir `\Twig\Node\Node` örneği döndürmelidir.

Ayrıştırma sırasında hata oluşursa, şu şekilde bir istisna atabilirsiniz:

```php
throw new SyntaxError('Some error message.', $stream->getCurrent()->getLine(), $stream->getSourceContext());
```

---

#### 🧱 Node Tanımlama

```php
class CustomSetNode extends \Twig\Node\Node
{
    public function __construct($name, \Twig\Node\Expression\AbstractExpression $value, $line)
    {
        parent::__construct(['value' => $value], ['name' => $name], $line);
    }

    public function compile(\Twig\Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write('$context[\''.$this->getAttribute('name').'\'] = ')
            ->subcompile($this->getNode('value'))
            ->raw(";\n");
    }
}
```

`compile()` metodu, Twig’in bu node’u PHP koduna çevirmesini sağlar.

`addDebugInfo()` hataları daha iyi raporlamak için önerilir.

---

### 🧩 Bir Extension Oluşturma

Bir extension yazmanın temel amacı, sık kullanılan kodları (örneğin uluslararasılaştırma desteği) yeniden kullanılabilir bir sınıfta toplamaktır.

Bir extension **etiketler, filtreler, testler, operatörler, fonksiyonlar** ve **node visitor** tanımlayabilir.

Birçok projede, tüm özel Twig eklemelerinizi tek bir extension içinde toplamak en kullanışlı yöntemdir.

> **İpucu:**
>
> Kodunuzu bir extension olarak paketlediğinizde Twig, `auto_reload` etkinse, kodda yaptığınız değişiklikleri fark edip şablonları yeniden derler.

---

#### 🧱 Extension Arayüzü

```php
interface \Twig\Extension\ExtensionInterface
{
    public function getTokenParsers();
    public function getNodeVisitors();
    public function getFilters();
    public function getTests();
    public function getFunctions();
    public function getOperators();
}
```

Tüm bu metodların boş halini sağladığı için `\Twig\Extension\AbstractExtension` sınıfını miras almak genellikle daha kolaydır:

```php
class CustomTwigExtension extends \Twig\Extension\AbstractExtension
{
}
```

Bir extension’ı Twig’e şu şekilde kaydedebilirsiniz:

```php
$twig = new \Twig\Environment($loader);
$twig->addExtension(new CustomTwigExtension());
```

---

### 🌍 Globals (Global Değişkenler)

Extension içinde `getGlobals()` metodu ile global değişkenleri kaydedebilirsiniz:

```php
class CustomTwigExtension extends \Twig\Extension\AbstractExtension implements \Twig\Extension\GlobalsInterface
{
    public function getGlobals(): array
    {
        return [
            'text' => new Text(),
        ];
    }
}
```

> **Dikkat:**
>
> Global değişkenler bir kez alınır ve Twig ortamı boyunca önbelleğe alınır. Bu nedenle değişebilecek değerleri global değişkenlerde tutmayın.

---

### ⚙️ Fonksiyonlar (Functions)

Fonksiyonlar `getFunctions()` metodu ile kaydedilir:

```php
class CustomTwigExtension extends \Twig\Extension\AbstractExtension
{
    public function getFunctions()
    {
        return [
            new \Twig\TwigFunction('lipsum', 'generate_lipsum'),
        ];
    }
}
```


### 🧩 Filtreler (Filters)

Bir extension’a filtre eklemek için `getFilters()` metodunu ezmeniz gerekir. Bu metod, Twig ortamına eklenecek filtrelerin bir dizisini döndürmelidir:

```php
class CustomTwigExtension extends \Twig\Extension\AbstractExtension
{
    public function getFilters()
    {
        return [
            new \Twig\TwigFilter('rot13', 'str_rot13'),
        ];
    }

    // ...
}
```

---

### 🏷️ Etiketler (Tags)

Bir extension’a etiket eklemek için `getTokenParsers()` metodunu ezebilirsiniz. Bu metod, Twig ortamına eklenecek etiketlerin bir dizisini döndürmelidir:

```php
class CustomTwigExtension extends \Twig\Extension\AbstractExtension
{
    public function getTokenParsers()
    {
        return [new CustomSetTokenParser()];
    }

    // ...
}
```

Yukarıdaki örnekte, `CustomSetTokenParser` sınıfı tarafından tanımlanan yeni bir etiket eklenmiştir. Bu sınıf, etiketi ayrıştırmaktan ve PHP’ye derlemekten sorumludur.

---

### ➕ Operatörler (Operators)

`getOperators()` metodu yeni operatörler eklemenizi sağlar. Yeni bir operatör uygulamak için `Twig\Extension\CoreExtension` sınıfındaki varsayılan operatör örneklerine bakabilirsiniz.

---

### 🧪 Testler (Tests)

`getTests()` metodu yeni test fonksiyonları eklemenizi sağlar:

```php
class CustomTwigExtension extends \Twig\Extension\AbstractExtension
{
    public function getTests()
    {
        return [
            new \Twig\TwigTest('even', 'twig_test_even'),
        ];
    }

    // ...
}
```

---

### 🧱 PHP Attribute’larıyla Extension Tanımlama

> 🆕 Twig 3.21 sürümüyle attribute sınıfları eklendi.

Public metotlara `#[AsTwigFilter]`, `#[AsTwigFunction]` ve `#[AsTwigTest]` attribute’larını ekleyerek filtreler, fonksiyonlar ve testler tanımlayabilirsiniz.

```php
use Twig\Attribute\AsTwigFilter;
use Twig\Attribute\AsTwigFunction;
use Twig\Attribute\AsTwigTest;

class ProjectExtension
{
    #[AsTwigFilter('rot13')]
    public static function rot13(string $string): string
    {
        // ...
    }

    #[AsTwigFunction('lipsum')]
    public static function lipsum(int $count): string
    {
        // ...
    }

    #[AsTwigTest('even')]
    public static function isEven(int $number): bool
    {
        // ...
    }
}
```

Ardından `Twig\Extension\AttributeExtension`’ı sınıf adıyla kaydedin:

```php
$twig = new \Twig\Environment($loader);
$twig->addExtension(new \Twig\Extension\AttributeExtension(ProjectExtension::class));
```

Tüm metotlar **static** ise işlem tamamdır. `ProjectExtension` sınıfı örneklenmez ve attribute taraması sadece şablon derlenirken yapılır.

---

Eğer bazı metotlar static değilse, sınıfı bir **runtime extension** olarak kaydetmeniz gerekir:

```php
use Twig\Attribute\AsTwigFunction;

class ProjectExtension
{
    public function __construct(private LipsumProvider $lipsumProvider) {}

    #[AsTwigFunction('lipsum')]
    public function lipsum(int $count): string
    {
        return $this->lipsumProvider->lipsum($count);
    }
}

$twig = new \Twig\Environment($loader);
$twig->addExtension(new \Twig\Extension\AttributeExtension(ProjectExtension::class));
$twig->addRuntimeLoader(new \Twig\RuntimeLoader\FactoryLoader([
    ProjectExtension::class => function () use ($lipsumProvider) {
        return new ProjectExtension($lipsumProvider);
    },
]));
```

---

Twig ortamına erişmek istiyorsanız, metoda `\Twig\Environment` tipinde ilk argüman ekleyin:

```php
class ProjectExtension
{
    #[AsTwigFunction('lipsum')]
    public function lipsum(\Twig\Environment $env, int $count): string
    {
        // ...
    }
}
```

---

`#[AsTwigFilter]` ve `#[AsTwigFunction]`, variadic (değişken sayıda) argümanları otomatik olarak destekler:

```php
class ProjectExtension
{
    #[AsTwigFilter('thumbnail')]
    public function thumbnail(string $file, mixed ...$options): string
    {
        // ...
    }
}
```

---

### ⚙️ Attribute Seçenekleri

Attribute’lar, Twig callable’larını yapılandırmak için çeşitli seçenekleri destekler:

| Attribute                | Desteklenen Seçenekler                                                                                           |
| ------------------------ | ----------------------------------------------------------------------------------------------------------------- |
| **AsTwigFilter**   | needsCharset, needsEnvironment, needsContext, isSafe, isSafeCallback, preEscape, preservesSafety, deprecationInfo |
| **AsTwigFunction** | needsCharset, needsEnvironment, needsContext, isSafe, isSafeCallback, deprecationInfo                             |
| **AsTwigTest**     | needsCharset, needsEnvironment, needsContext, deprecationInfo                                                     |

---

### 🧠 Tanım ve Çalışma Zamanı (Definition vs Runtime)

Twig filtreleri, fonksiyonları ve testleri herhangi bir geçerli PHP callable olarak tanımlanabilir:

* **Fonksiyonlar / static metotlar:** Uygulaması kolay ve hızlıdır, ancak dış bağımlılıklara erişim zordur.
* **Closure (anonim fonksiyonlar):** Basit ve doğrudan.
* **Nesne metotları:** Esnek ve dış bağımlılıklara ihtiyaç duyan durumlarda gereklidir.

---

#### 📦 Basit Kullanım

Metotları doğrudan extension içinde tanımlayabilirsiniz:

```php
class CustomTwigExtension extends \Twig\Extension\AbstractExtension
{
    private $rot13Provider;

    public function __construct($rot13Provider)
    {
        $this->rot13Provider = $rot13Provider;
    }

    public function getFunctions()
    {
        return [
            new \Twig\TwigFunction('rot13', [$this, 'rot13']),
        ];
    }

    public function rot13($value)
    {
        return $this->rot13Provider->rot13($value);
    }
}
```

Bu yöntem kolaydır ancak önerilmez; çünkü şablon derlemesi, çalışma zamanı bağımlılıklarına gereksiz yere bağımlı hale gelir (örneğin veritabanı bağlantısı gibi).

---

#### 🔗 Definition–Runtime Ayrımı

Extension tanımlarını runtime implementasyonlarından ayırmak için, Twig ortamına bir `\Twig\RuntimeLoader\RuntimeLoaderInterface` örneği kaydedebilirsiniz:

```php
class RuntimeLoader implements \Twig\RuntimeLoader\RuntimeLoaderInterface
{
    public function load($class)
    {
        if ('CustomTwigRuntime' === $class) {
            return new $class(new Rot13Provider());
        }
    }
}

$twig->addRuntimeLoader(new RuntimeLoader());
```

> **Not:**
>
> Twig, PSR-11 uyumlu bir runtime loader içerir: `\Twig\RuntimeLoader\ContainerRuntimeLoader`.

---

#### 🧠 Runtime Sınıfına Taşıma

```php
class CustomTwigRuntime
{
    private $rot13Provider;

    public function __construct($rot13Provider)
    {
        $this->rot13Provider = $rot13Provider;
    }

    public function rot13($value)
    {
        return $this->rot13Provider->rot13($value);
    }
}

class CustomTwigExtension extends \Twig\Extension\AbstractExtension
{
    public function getFunctions()
    {
        return [
            new \Twig\TwigFunction('rot13', ['CustomTwigRuntime', 'rot13']),
            // veya
            new \Twig\TwigFunction('rot13', 'CustomTwigRuntime::rot13'),
        ];
    }
}
```

> **Not:**
>
> Extension sınıfı, runtime sınıfı değiştiğinde şablon önbelleğini geçersiz kılmak için `Twig\Extension\LastModifiedExtensionInterface` arayüzünü uygulamalıdır.
>
> `AbstractExtension` sınıfı bu arayüzü uygular ve aynı isme sahip, ancak `Extension` yerine `Runtime` ile biten sınıfı otomatik olarak izler.

---

### 🧪 Bir Extension’ı Test Etme

#### ✅ Fonksiyonel Testler

Test dizininde aşağıdaki yapı oluşturulabilir:

```
Fixtures/
    filters/
        lower.test
        upper.test
    functions/
        date.test
        format.test
    tags/
        for.test
        if.test
IntegrationTest.php
```

`IntegrationTest.php` şöyle görünmelidir:

```php
namespace Project\Tests;

use Twig\Test\IntegrationTestCase;

class IntegrationTest extends IntegrationTestCase
{
    public function getExtensions()
    {
        return [
            new CustomTwigExtension1(),
            new CustomTwigExtension2(),
        ];
    }

    public function getFixturesDir()
    {
        return __DIR__.'/Fixtures/';
    }
}
```

`Fixtures` örnekleri Twig deposunun `tests/Twig/Fixtures` dizininde bulunabilir.

---

#### 🧱 Node Testleri

Node visitor’ları test etmek daha karmaşık olabilir; bu durumda test sınıflarınızı `\Twig\Test\NodeTestCase` sınıfından türetin.

Örnekler Twig deposundaki `tests/Twig/Node` dizininde bulunabilir.
