# 🧠 ExpressionLanguage Bileşeni

ExpressionLanguage bileşeni, ifadeleri derleyebilen ve değerlendirebilen bir motor sağlar. Bir ifade, bir değer döndüren (çoğunlukla ama yalnızca Boolean ile sınırlı olmayan) tek satırlık bir ifadedir.

## ⚙️ Kurulum

```bash
composer require symfony/expression-language
```

Bu bileşeni bir Symfony uygulaması dışında kurarsanız, Composer tarafından sağlanan sınıf otomatik yükleme mekanizmasını etkinleştirmek için kodunuzda `vendor/autoload.php` dosyasını dahil etmeniz gerekir. Daha fazla bilgi için bu makaleyi okuyun.

## 💡 Expression Language Bana Nasıl Yardımcı Olabilir?

Bu bileşenin amacı, daha karmaşık mantıklar için yapılandırmalar içinde ifadelerin kullanılmasına olanak tanımaktır. Örneğin, Symfony Framework güvenlikte, doğrulama kurallarında ve route eşleştirmede ifadeleri kullanır.

Framework’ün kendisinde kullanmanın yanı sıra, ExpressionLanguage bileşeni bir iş kuralı motorunun temeli için mükemmel bir adaydır. Fikir, bir web sitesinin yöneticisinin PHP kullanmadan ve güvenlik sorunları yaratmadan şeyleri dinamik bir şekilde yapılandırmasına izin vermektir:

```yaml
# Özel fiyatı al
user.getGroup() in ['good_customers', 'collaborator']

# Makaleyi ana sayfaya çıkar
article.commentCount > 100 and article.category not in ["misc"]

# Uyarı gönder
product.stock < 15
```

İfadeler, çok sınırlı bir PHP sandbox’ı olarak görülebilir ve hangi değişkenlerin ifadede mevcut olduğunu açıkça belirtmeniz gerektiği için dış enjeksiyonlara karşı daha az savunmasızdır (ancak yine de kullanıcıdan alınan ve ifadelere geçirilen tüm verileri temizlemelisiniz).

## 🚀 Kullanım

ExpressionLanguage bileşeni ifadeleri derleyebilir ve değerlendirebilir. İfadeler genellikle bir Boolean döndüren tek satırlık ifadelerdir ve bu, ifadeyi çalıştıran kod tarafından bir `if` ifadesinde kullanılabilir. Basit bir ifade örneği `1 + 2`’dir. Daha karmaşık ifadeler de kullanılabilir, örneğin `someArray[3].someMethod('bar')`.

Bileşen, ifadelerle çalışmanın iki yolunu sunar:

-   **evaluation** : ifade PHP’ye derlenmeden değerlendirilir;
-   **compile** : ifade PHP’ye derlenir, böylece önbelleğe alınabilir ve daha sonra değerlendirilebilir.

Bileşenin ana sınıfı `ExpressionLanguage`’dir:

```php
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

$expressionLanguage = new ExpressionLanguage();

var_dump($expressionLanguage->evaluate('1 + 2')); // 3 gösterir

var_dump($expressionLanguage->compile('1 + 2')); // (1 + 2) gösterir
```

İfade sözdizimini öğrenmek için **The Expression Syntax** bölümüne bakın.

## ⚖️ Null Coalescing Operator

Bu içerik, ExpressionLanguage sözdizimi referans sayfasındaki **null coalescing operator** bölümüne taşınmıştır.

## 🧩 İfadeleri Ayrıştırma ve Denetleme

ExpressionLanguage bileşeni, ifadeleri ayrıştırma (parse) ve denetleme (lint) yöntemi sağlar. `parse()` yöntemi, ifadeyi inceleyip üzerinde işlem yapmanızı sağlayan bir `ParsedExpression` örneği döndürür. `lint()` ise ifade geçerli değilse bir `SyntaxError` fırlatır:

```php
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

$expressionLanguage = new ExpressionLanguage();

var_dump($expressionLanguage->parse('1 + 2', []));
// ifadenin incelenebilir ve değiştirilebilir AST düğümlerini gösterir

$expressionLanguage->lint('1 + 2', []); // hiçbir şey fırlatmaz

$expressionLanguage->lint('1 + a', []);
// SyntaxError istisnası fırlatır:
// "Variable "a" is not valid around position 5 for expression `1 + a`."
```

Bu yöntemlerin davranışı, `Parser` sınıfında tanımlanan bazı bayraklarla yapılandırılabilir:

-   `IGNORE_UNKNOWN_VARIABLES`: ifadede tanımlanmamış bir değişken varsa istisna fırlatma;
-   `IGNORE_UNKNOWN_FUNCTIONS`: ifadede tanımlanmamış bir fonksiyon varsa istisna fırlatma.

Bu bayraklar şu şekilde kullanılabilir:

```php
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\Parser;

$expressionLanguage = new ExpressionLanguage();

// tanımlanmamış değişkenler ve fonksiyonlar yok sayıldığı için SyntaxError fırlatmaz
$expressionLanguage->lint(
    'unknown_var + unknown_function()',
    [],
    Parser::IGNORE_UNKNOWN_VARIABLES | Parser::IGNORE_UNKNOWN_FUNCTIONS
);
```

🆕 Symfony 7.1’de, `parse()` ve `lint()` yöntemlerinde bayrak desteği eklendi.

## 🍎 Değişkenleri Aktarma

İfadeye değişkenler de geçirebilirsiniz; bu değişkenler geçerli herhangi bir PHP türünde olabilir (nesneler dahil):

```php
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

$expressionLanguage = new ExpressionLanguage();

class Apple
{
    public string $variety;
}

$apple = new Apple();
$apple->variety = 'Honeycrisp';

var_dump($expressionLanguage->evaluate(
    'fruit.variety',
    [
        'fruit' => $apple,
    ]
)); // "Honeycrisp" gösterir
```

Bu bileşen bir Symfony uygulaması içinde kullanıldığında, bazı nesneler ve değişkenler Symfony tarafından otomatik olarak enjekte edilir, böylece ifadelerinizde bunları kullanabilirsiniz (örneğin `request`, geçerli kullanıcı vb.):

-   Güvenlik ifadelerinde mevcut değişkenler;
-   Servis container ifadelerinde mevcut değişkenler;
-   Route ifadelerinde mevcut değişkenler.

# ⚡ Caching

ExpressionLanguage bileşeni, ifadeleri düz PHP içinde önbelleğe alabilmek için bir `compile()` yöntemi sağlar. Ancak dahili olarak, bileşen ayrıştırılmış ifadeleri de önbelleğe alır, bu sayede yinelenen ifadeler daha hızlı derlenip değerlendirilir.

## 🔄 İş Akışı

Hem `evaluate()` hem de `compile()`, dönen değerleri sağlayabilmeden önce bazı işlemler yapmak zorundadır. `evaluate()` için bu ek yük daha da fazladır.

Her iki yöntem de ifadeyi tokenize edip ayrıştırmak zorundadır. Bu işlem `parse()` yöntemi tarafından yapılır. Bu yöntem bir `ParsedExpression` döndürür.

`compile()` yöntemi bu nesnenin dize halini döndürürken, `evaluate()` yöntemi "düğümler" (ParsedExpression içinde saklanan ifade parçaları) üzerinde döngü kurar ve bunları anında değerlendirir.

Zaman kazanmak için ExpressionLanguage, `ParsedExpression`’ı önbelleğe alır, böylece yinelenen ifadelerde tokenize etme ve ayrıştırma adımlarını atlayabilir.

Önbellekleme, bir **PSR-6 CacheItemPoolInterface** örneği tarafından yapılır (varsayılan olarak `ArrayAdapter` kullanılır). Bunu özelleştirmek için özel bir cache pool oluşturabilir veya mevcut adaptörlerden birini kullanarak yapıcıya (constructor) enjekte edebilirsiniz:

```php
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

$cache = new RedisAdapter(...);
$expressionLanguage = new ExpressionLanguage($cache);
```

Kullanılabilir önbellek adaptörleri hakkında daha fazla bilgi için **The Cache Component** dokümantasyonuna bakın.

---

## 🧱 Parsed ve Serialized İfadeleri Kullanma

Hem `evaluate()` hem de `compile()` yöntemleri `ParsedExpression` ve `SerializedParsedExpression` nesnelerini işleyebilir:

```php
// ...

// parse() yöntemi bir ParsedExpression döndürür
$expression = $expressionLanguage->parse('1 + 4', []);

var_dump($expressionLanguage->evaluate($expression)); // 5 yazdırır

use Symfony\Component\ExpressionLanguage\SerializedParsedExpression;
// ...

$expression = new SerializedParsedExpression(
    '1 + 4',
    serialize($expressionLanguage->parse('1 + 4', [])->getNodes())
);

var_dump($expressionLanguage->evaluate($expression)); // 5 yazdırır
```

---

## 🌳 AST (Abstract Syntax Tree) Dump Etme ve Düzenleme

ExpressionLanguage bileşeniyle oluşturulan ifadeleri denetlemek veya değiştirmek zordur, çünkü ifadeler düz metin halindedir. Daha iyi bir yaklaşım, bu ifadeleri bir **AST** ’ye dönüştürmektir.

Bilgisayar biliminde AST (Abstract Syntax Tree), “bir programlama dilinde yazılmış kaynak kodun yapısının ağaç temsili”dir. Symfony’de bir ExpressionLanguage AST’si, verilen ifadeyi temsil eden PHP sınıflarından oluşan düğümler kümesidir.

### 🪵 AST Dump Etme

Herhangi bir ifadeyi ayrıştırdıktan sonra `getNodes()` metodunu çağırarak AST’sini elde edin:

```php
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

$ast = (new ExpressionLanguage())
    ->parse('1 + 2', [])
    ->getNodes()
;

// AST düğümlerini inceleme için dökümle
var_dump($ast);

// AST düğümlerini bir dize temsiline dökümle
$astAsString = $ast->dump();
```

### 🧩 AST’yi Manipüle Etme

AST’nin düğümleri, değişiklik yapılmasına olanak tanımak için bir PHP dizisine dönüştürülebilir.

AST’yi bir diziye çevirmek için `toArray()` metodunu çağırın:

```php
// ...

$astAsArray = (new ExpressionLanguage())
    ->parse('1 + 2', [])
    ->getNodes()
    ->toArray()
;
```

---

## 🔧 ExpressionLanguage’i Genişletme

ExpressionLanguage, özel fonksiyonlar ekleyerek genişletilebilir. Örneğin, Symfony Framework’te güvenlik sistemi, kullanıcının rolünü kontrol etmek için özel fonksiyonlar içerir.

İfadelerde fonksiyonların nasıl kullanılacağını öğrenmek için **"The Expression Syntax"** bölümünü okuyun.

### 🧠 Fonksiyon Kaydetme

Fonksiyonlar her bir `ExpressionLanguage` örneği için ayrı ayrı kaydedilir.

Yani bir örnek tarafından çalıştırılan herhangi bir ifadede bu fonksiyonlar kullanılabilir.

Bir fonksiyonu kaydetmek için `register()` yöntemini kullanın. Bu yöntem 3 argüman alır:

-   **name** – İfade içindeki fonksiyonun adı;
-   **compiler** – Fonksiyon derlenirken çalıştırılan fonksiyon;
-   **evaluator** – İfade değerlendirilirken çalıştırılan fonksiyon.

Örnek:

```php
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

$expressionLanguage = new ExpressionLanguage();
$expressionLanguage->register('lowercase', function ($str): string {
    return sprintf('(is_string(%1$s) ? strtolower(%1$s) : %1$s)', $str);
}, function ($arguments, $str): string {
    if (!is_string($str)) {
        return $str;
    }

    return strtolower($str);
});

var_dump($expressionLanguage->evaluate('lowercase("HELLO")'));
// çıktısı: hello
```

Özel fonksiyon argümanlarına ek olarak, `evaluator` fonksiyonu ilk argüman olarak bir `arguments` değişkeni alır; bu değişken `evaluate()` metodunun ikinci argümanına (örneğin değerlendirilen ifadedeki “values”) eşittir.

---

## 🧩 Expression Providers Kullanma

Kütüphanenizde `ExpressionLanguage` sınıfını kullanırken sıklıkla özel fonksiyonlar eklemek isteyebilirsiniz.

Bunu yapmak için, `ExpressionFunctionProviderInterface` arayüzünü uygulayan yeni bir **expression provider** sınıfı oluşturabilirsiniz.

Bu arayüz, kaydedilecek **ExpressionFunction** örneklerinden oluşan bir dizi döndüren `getFunctions()` metodunu gerektirir:

```php
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class StringExpressionLanguageProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions(): array
    {
        return [
            new ExpressionFunction('lowercase', function ($str): string {
                return sprintf('(is_string(%1$s) ? strtolower(%1$s) : %1$s)', $str);
            }, function ($arguments, $str): string {
                if (!is_string($str)) {
                    return $str;
                }

                return strtolower($str);
            }),
        ];
    }
}
```

Bir PHP fonksiyonundan `fromPhp()` statik yöntemiyle bir expression function oluşturabilirsiniz:

```php
ExpressionFunction::fromPhp('strtoupper');
```

Ad alanına (namespace) sahip fonksiyonlar da desteklenir, ancak ifade adını tanımlamak için ikinci bir argüman gerektirir:

```php
ExpressionFunction::fromPhp('My\strtoupper', 'my_strtoupper');
```

Provider’ları `registerProvider()` kullanarak veya yapıcının ikinci argümanına geçirerek kaydedebilirsiniz:

```php
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

// yapıcı ile
$expressionLanguage = new ExpressionLanguage(null, [
    new StringExpressionLanguageProvider(),
    // ...
]);

// registerProvider() ile
$expressionLanguage->registerProvider(new StringExpressionLanguageProvider());
```

---

## 🧱 Kendi ExpressionLanguage Sınıfını Oluşturma

Kütüphanenizde kendi `ExpressionLanguage` sınıfınızı oluşturmanız önerilir.

Artık genişletmeyi yapıcıyı geçersiz kılarak (override ederek) ekleyebilirsiniz:

```php
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage as BaseExpressionLanguage;

class ExpressionLanguage extends BaseExpressionLanguage
{
    public function __construct(?CacheItemPoolInterface $cache = null, array $providers = [])
    {
        // varsayılan provider’ı öne ekleyerek kullanıcıların üzerine yazmasına izin verir
        array_unshift($providers, new StringExpressionLanguageProvider());

        parent::__construct($cache, $providers);
    }
}
```

---
