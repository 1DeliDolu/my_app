### 🍳 Tarifler

#### ⚠️ Uyarı Bildirimlerini Görüntüleme

Kullanımdan kaldırılmış (deprecated) özellikler, `trigger_error()` PHP fonksiyonu aracılığıyla uyarı bildirimleri oluşturur. Varsayılan olarak bu uyarılar sessize alınır, görüntülenmez ve günlüklenmez.

Şablonlarınızdan tüm kullanımdan kaldırılmış özellikleri kaldırmak için aşağıdaki gibi bir betik yazıp çalıştırın:

```php
require_once __DIR__.'/vendor/autoload.php';

$twig = create_your_twig_env();

$deprecations = new \Twig\Util\DeprecationCollector($twig);

print_r($deprecations->collectDir(__DIR__.'/templates'));
```

`collectDir()` metodu bir dizindeki tüm şablonları derler, uyarı bildirimlerini yakalar ve geri döndürür.

💡 **İpucu:**

Şablonlarınız dosya sisteminde değilse `collect()` metodunu kullanın. `collect()` metodu, anahtar olarak şablon adlarını ve değer olarak şablon içeriklerini döndüren bir `Traversable` alır (örneğin `\Twig\Util\TemplateDirIterator` gibi).

Ancak, bu kod tüm uyarıları bulmayacaktır (örneğin bazı Twig sınıflarının kullanımdan kaldırılması gibi). Tüm bildirimleri yakalamak için aşağıdaki gibi özel bir hata işleyicisi kaydedebilirsiniz:

```php
$deprecations = [];
set_error_handler(function ($type, $msg) use (&$deprecations) {
    if (E_USER_DEPRECATED === $type) {
        $deprecations[] = $msg;
    }
});

// uygulamanızı çalıştırın

print_r($deprecations);
```

Çoğu uyarı bildiriminin derleme sırasında tetiklendiğini unutmayın; bu nedenle şablonlar önbelleğe alınmışsa üretilmezler.

💡 **İpucu:**

Uyarı bildirimlerini PHPUnit testlerinden yönetmek istiyorsanız, süreci kolaylaştıran `symfony/phpunit-bridge` paketine göz atın.

---

#### 🧩 Koşullu Bir Layout Oluşturma

Ajax ile çalışmak, aynı içeriğin bazen doğrudan, bazen bir layout ile birlikte görüntüleneceği anlamına gelir. Twig layout şablon adları herhangi bir geçerli ifade olabileceğinden, isteğin Ajax olup olmadığını belirten bir değişken kullanabilir ve buna göre layout seçebilirsiniz:

```twig
{% extends request.ajax ? "base_ajax.html.twig" : "base.html.twig" %}

{% block content %}
    This is the content to be displayed.
{% endblock %}
```

---

#### 🔁 Dinamik Include Yapma

Bir şablon dahil ederken, adı sabit bir string olmak zorunda değildir. Örneğin, adı bir değişkenin değerine bağlı olabilir:

```twig
{% include var ~ '_foo.html.twig' %}
```

Eğer `var` değeri `index` ise, `index_foo.html.twig` şablonu render edilir.

Aslında şablon adı şu gibi herhangi bir geçerli ifade olabilir:

```twig
{% include var|default('index') ~ '_foo.html.twig' %}
```

---

#### 🧱 Kendisini Genişleten Bir Şablonu Geçersiz Kılma

Bir şablon iki şekilde özelleştirilebilir:

1. **Kalıtım:** Bir şablon başka bir şablonu genişletir ve bazı blokları geçersiz kılar.
2. **Yerine Koyma:** Eğer filesystem loader kullanıyorsanız, Twig yapılandırılmış dizinler listesindeki ilk bulunan şablonu yükler; bu, daha sonraki dizinlerdeki aynı isimli şablonların yerine geçer.

Peki, hem kendisini genişleten hem de yer değiştiren bir şablonu nasıl birleştirebilirsiniz?

Diyelim ki şablonlarınız şu dizinlerden yükleniyor:

`.../templates/mysite` ve `.../templates/default` (bu sırayla).

`.../templates/default` içindeki `page.html.twig` şu şekilde olsun:

```twig
{# page.html.twig #}
{% extends "layout.html.twig" %}

{% block content %}
{% endblock %}
```

Bu şablonu `.../templates/mysite` dizinine aynı adla koyarak değiştirebilirsiniz.

Ancak orijinal şablonu genişletmek isterseniz şöyle yazmak isteyebilirsiniz:

```twig
{# page.html.twig in .../templates/mysite #}
{% extends "page.html.twig" %} {# from .../templates/default #}
```

Bu çalışmaz çünkü Twig her zaman `.../templates/mysite` içindekini yükler.

Bunun yerine, tüm diğer dizinlerin üstünde bir dizin (örneğin `.../templates`) ekleyebilirsiniz.

Bu, sistemdeki her şablonu benzersiz şekilde adreslenebilir hale getirir.

Normalde “normal” yolları kullanırsınız, ancak bir şablonun kendi ebeveynini genişletmek isterseniz şu şekilde referans verebilirsiniz:

```twig
{# page.html.twig in .../templates/mysite #}
{% extends "default/page.html.twig" %} {# from .../templates #}
```

📝 **Not:**

Bu tarif, şu Django wiki sayfasından esinlenmiştir:

[https://code.djangoproject.com/wiki/ExtendingTemplates](https://code.djangoproject.com/wiki/ExtendingTemplates)

---

#### ⚙️ Sözdizimini Özelleştirme

Twig, blok ayraçları için bazı sözdizimi özelleştirmelerine izin verir. Bu özellik önerilmez, çünkü şablonlar özel sözdiziminize bağımlı hale gelir. Ancak bazı özel projelerde anlamlı olabilir.

Blok ayraçlarını değiştirmek için kendi lexer nesnenizi oluşturun:

```php
$twig = new \Twig\Environment(...);

$lexer = new \Twig\Lexer($twig, [
    'tag_comment'   => ['{#', '#}'],
    'tag_block'     => ['{%', '%}'],
    'tag_variable'  => ['{{', '}}'],
    'interpolation' => ['#{', '}'],
]);
$twig->setLexer($lexer);
```

Diğer şablon motorlarının sözdizimini taklit eden bazı örnek yapılandırmalar:

```php
// Ruby erb syntax
$lexer = new \Twig\Lexer($twig, [
    'tag_comment'  => ['<%#', '%>'],
    'tag_block'    => ['<%', '%>'],
    'tag_variable' => ['<%=', '%>'],
]);

// SGML Comment Syntax
$lexer = new \Twig\Lexer($twig, [
    'tag_comment'  => ['<!--#', '-->'],
    'tag_block'    => ['<!--', '-->'],
    'tag_variable' => ['${', '}'],
]);

// Smarty like
$lexer = new \Twig\Lexer($twig, [
    'tag_comment'  => ['{*', '*}'],
    'tag_block'    => ['{', '}'],
    'tag_variable' => ['{$', '}'],
]);
```

---

#### 🧠 Dinamik Nesne Özelliklerini Kullanma

Twig, `article.title` gibi bir değişkenle karşılaştığında `article` nesnesinde `title` adlı bir **public property** arar.

Bu özellik var olmasa da, `__get()` sihirli metodu sayesinde dinamik olarak tanımlanabilir.

Aşağıdaki gibi `__isset()` metodunu da uygulamanız gerekir:

```php
class Article
{
    public function __get($name)
    {
        if ('title' == $name) {
            return 'The title';
        }

        // bir hata fırlat
    }

    public function __isset($name)
    {
        if ('title' == $name) {
            return true;
        }

        return false;
    }
}
```


# 🗃️ Şablonları Veritabanında Saklama

Bir **CMS** geliştiriyorsanız, şablonlar genellikle veritabanında saklanır.

Bu örnek, kendi projenize uyarlayabileceğiniz basit bir **PDO tabanlı Twig Loader** örneğidir.

---

## 🧩 1. Geçici SQLite Veritabanı Oluşturma

```php
$dbh = new PDO('sqlite::memory:');
$dbh->exec('CREATE TABLE templates (name STRING, source STRING, last_modified INTEGER)');

$base = '{% block content %}{% endblock %}';
$index = '
{% extends "base.html.twig" %}
{% block content %}Hello {{ name }}{% endblock %}
';

$now = time();
$dbh->prepare('INSERT INTO templates (name, source, last_modified) VALUES (?, ?, ?)')->execute(['base.html.twig', $base, $now]);
$dbh->prepare('INSERT INTO templates (name, source, last_modified) VALUES (?, ?, ?)')->execute(['index.html.twig', $index, $now]);
```

Bu örnekte `templates` adında bir tablo oluşturulmuş ve iki şablon eklenmiştir:

**base.html.twig** ve **index.html.twig**

---

## ⚙️ 2. Veritabanını Kullanan Twig Loader Tanımlama

```php
class DatabaseTwigLoader implements \Twig\Loader\LoaderInterface
{
    protected $dbh;

    public function __construct(PDO $dbh)
    {
        $this->dbh = $dbh;
    }

    public function getSourceContext(string $name): Source
    {
        if (false === $source = $this->getValue('source', $name)) {
            throw new \Twig\Error\LoaderError(sprintf('Template "%s" does not exist.', $name));
        }

        return new \Twig\Source($source, $name);
    }

    public function exists(string $name)
    {
        return $name === $this->getValue('name', $name);
    }

    public function getCacheKey(string $name): string
    {
        return $name;
    }

    public function isFresh(string $name, int $time): bool
    {
        if (false === $lastModified = $this->getValue('last_modified', $name)) {
            return false;
        }

        return $lastModified <= $time;
    }

    protected function getValue($column, $name)
    {
        $sth = $this->dbh->prepare('SELECT '.$column.' FROM templates WHERE name = :name');
        $sth->execute([':name' => (string) $name]);

        return $sth->fetchColumn();
    }
}
```

---

## 🧾 3. Kullanım Örneği

```php
$loader = new DatabaseTwigLoader($dbh);
$twig = new \Twig\Environment($loader);

echo $twig->render('index.html.twig', ['name' => 'Fabien']);
```

Bu şekilde Twig, şablonları veritabanından okuyarak render eder.

---

# 🔗 Farklı Şablon Kaynaklarını Kullanma

Bir önceki örneğin devamı olarak, bazı şablonları veritabanında, bazılarını dosya sisteminde saklamak isteyebilirsiniz.

Bunun için **`\Twig\Loader\ChainLoader`** sınıfını kullanabilirsiniz.

```php
$loader1 = new DatabaseTwigLoader($dbh);
$loader2 = new \Twig\Loader\ArrayLoader([
    'base.html.twig' => '{% block content %}{% endblock %}',
]);

$loader = new \Twig\Loader\ChainLoader([$loader1, $loader2]);

$twig = new \Twig\Environment($loader);

echo $twig->render('index.html.twig', ['name' => 'Fabien']);
```

Artık `base.html.twig` şablonunu **ArrayLoader** üzerinden sağlayabilirsiniz.

Bu durumda, veritabanından bu şablonu kaldırabilirsiniz; sistem aynı şekilde çalışmaya devam edecektir.

---

# 🧵 Bir String’ten Şablon Yükleme

Bir şablon içinde, **string olarak tanımlanmış bir Twig şablonunu** yüklemek mümkündür.

### 🔹 Twig İçinden:

```twig
{{ include(template_from_string("Hello {{ name }}")) }}
```

### 🔹 PHP İçinden:

```php
$template = $twig->createTemplate('hello {{ name }}');
echo $template->render(['name' => 'Fabien']);
```

---

# 🧠 Twig ve AngularJS’i Aynı Şablonda Kullanma

Twig ve AngularJS aynı süslü parantezleri (`{{ }}`) kullandığı için

aynı dosyada birlikte kullanmak  **önerilmez** , ancak gerekirse iki yöntem vardır:

### 1. AngularJS Kısımlarını Kaçışla Sarmak

```twig
{% verbatim %}
    <div>{{ angular_variable }}</div>
{% endverbatim %}
```

ya da:

```twig
{{ '{{' }} angular_variable {{ '}}' }}
```

### 2. Ayrıştırıcı (Delimiter) Sembollerini Değiştirmek

#### 🔸 AngularJS için:

```javascript
angular.module('myApp', []).config(function($interpolateProvider) {
    $interpolateProvider.startSymbol('{[').endSymbol(']}');
});
```

#### 🔸 Twig için:

```php
$env->setLexer(new \Twig\Lexer($env, [
    'tag_variable' => ['{[', ']}'],
]));
```

---

# 🛡️ Bir Node’u Güvenli Olarak İşaretleme

**Escaper extension** kullanırken, bazı düğümleri (node) güvenli olarak işaretleyip

otomatik kaçıştan (escaping) muaf tutmak isteyebilirsiniz.

Bunu **RawFilter** düğümüyle yapabilirsiniz:

```php
use Twig\Node\Expression\Filter\RawFilter;

$safeExpr = new RawFilter(new YourSafeNode());
```

Bu sayede Twig, bu düğümün çıktısını güvenli olarak kabul eder ve ek bir kaçış işlemi uygulamaz.

# 🔁 İç İçe Döngülerde Üst Bağlama (Parent Context) Erişim

Bazen iç içe döngüler ( **nested loops** ) kullanırken, üst döngüdeki bağlama ( **parent context** ) erişmeniz gerekebilir.

Üst bağlama her zaman **`loop.parent`** değişkeni aracılığıyla erişilebilir.

Örneğin, aşağıdaki şablon verilerini ele alalım:

```php
$data = [
    'topics' => [
        'topic1' => ['Message 1 of topic 1', 'Message 2 of topic 1'],
        'topic2' => ['Message 1 of topic 2', 'Message 2 of topic 2'],
    ],
];
```

Tüm konulardaki mesajları göstermek için şu Twig şablonunu kullanabiliriz:

```twig
{% for topic, messages in topics %}
    * {{ loop.index }}: {{ topic }}
  {% for message in messages %}
      - {{ loop.parent.loop.index }}.{{ loop.index }}: {{ message }}
  {% endfor %}
{% endfor %}
```

Çıktı şöyle olur:

```
* 1: topic1
  - 1.1: The message 1 of topic 1
  - 1.2: The message 2 of topic 1
* 2: topic2
  - 2.1: The message 1 of topic 2
  - 2.2: The message 2 of topic 2
```

İç döngüde, **`loop.parent`** değişkeni dış bağlama erişmek için kullanılır.

Böylece, dış döngüde tanımlanan geçerli  **topic** ’in indeksine **`loop.parent.loop.index`** aracılığıyla erişilebilir.

---

# ⚙️ Tanımlanmamış Fonksiyon, Filtre ve Tag’leri Dinamik Olarak Tanımlama

### 🧩 Twig 3.2 ve 3.22’de Eklenenler

* Twig 3.2 ile **`registerUndefinedTokenParserCallback()`** metodu eklendi.
* Twig 3.22 ile **`registerUndefinedTestCallback()`** metodu eklendi.

Bir  **function** ,  **filter** , **test** veya **tag** tanımlı değilse Twig varsayılan olarak bir

`\Twig\Error\SyntaxError` hatası fırlatır.

Ancak, bu durumda Twig bir **callback** (geçerli bir PHP callable) çağırabilir.

Bu callback, uygun bir function/filter/test/tag döndürmelidir.

Tag’ler için: `registerUndefinedTokenParserCallback()`

Filtreler için: `registerUndefinedFilterCallback()`

Fonksiyonlar için: `registerUndefinedFunctionCallback()`

Testler için: `registerUndefinedTestCallback()`

Örneğin:

```php
// Tüm yerel PHP fonksiyonlarını Twig fonksiyonu olarak otomatik kaydeder
// BUNU GERÇEK BİR PROJEDE ASLA YAPMAYIN — GÜVENLİ DEĞİLDİR
$twig->registerUndefinedFunctionCallback(function ($name) {
    if (function_exists($name)) {
        return new \Twig\TwigFunction($name, $name);
    }

    return false;
});
```

Callback geçerli bir function/filter/test/tag döndüremiyorsa **false** döndürmelidir.

Birden fazla callback kaydederseniz, Twig bunları sırayla çağırır ve **false döndürmeyen ilkini** kullanır.

💡 **İpucu:**

Fonksiyon/filtre/test/tag çözümlemesi derleme (compilation) aşamasında yapıldığından, bu callback’lerin kaydedilmesi herhangi bir ek yük oluşturmaz.

⚠️ **Uyarı:**

Tag ayrıştırma işlemi her tag’e özgü olduğundan (`syntax` serbest biçimlidir),

`registerUndefinedTokenParserCallback()` **tüm bilinmeyen tag’lar** için varsayılan bir uygulama tanımlamakta kullanılamaz.

Bu yöntem, belirli tag’lar için **TokenParser** örneklerini dinamik olarak kaydetmek veya

varsayılan hatayı değiştirmek için yararlıdır.

---

# 🧩 Şablon Sözdizimini Doğrulama

Üçüncü taraflardan gelen şablon kodlarını (örneğin bir web arayüzü aracılığıyla)

kaydetmeden önce sözdizimini doğrulamak faydalı olabilir.

Şablon kodu `$template` değişkeninde tutuluyorsa, şu şekilde doğrulanabilir:

```php
try {
    $twig->parse($twig->tokenize(new \Twig\Source($template)));

    // $template geçerlidir
} catch (\Twig\Error\SyntaxError $e) {
    // $template sözdizimi hataları içeriyor
}
```

Eğer bir dosya kümesi üzerinde dönüyorsanız, **tokenize()** metoduna dosya adını geçirerek

hata mesajında dosya adının görünmesini sağlayabilirsiniz:

```php
foreach ($files as $file) {
    try {
        $twig->parse($twig->tokenize(new \Twig\Source($template, $file->getFilename(), $file)));

        // $template geçerlidir
    } catch (\Twig\Error\SyntaxError $e) {
        // $template sözdizimi hataları içeriyor
    }
}
```

> **Not:**
>
> Bu yöntem sandbox politika ihlallerini yakalamaz çünkü sandbox denetimi
>
> yalnızca şablon **render edilirken** yapılır (örneğin nesnelerde izin verilen metotlar gibi bağlama bağlı denetimler için).

---

# 🔄 OPcache Etkin Olduğunda Değiştirilen Şablonları Yenileme

OPcache etkinleştirilmiş ve `opcache.validate_timestamps = 0` olarak ayarlanmışsa,

Twig önbelleği etkin olup **auto_reload** devre dışıysa, şablon önbelleğini temizlemek  **cache’i güncellemez** .

Bu durumu aşmak için Twig’in bytecode önbelleğini geçersiz kılmasını sağlayın:

```php
$twig = new \Twig\Environment($loader, [
    'cache' => new \Twig\Cache\FilesystemCache(
        '/some/cache/path',
        \Twig\Cache\FilesystemCache::FORCE_BYTECODE_INVALIDATION
    ),
    // ...
]);
```

---

# 🔁 Durum Tutan (Stateful) Node Visitor’ı Yeniden Kullanma

Bir  **visitor** ’ı bir `\Twig\Environment` örneğine eklediğinizde, Twig bu visitor’ı derlediği tüm şablonlar için kullanır.

Eğer visitor içerisinde bazı **durum bilgilerini** (state) tutuyorsanız, yeni bir şablona geçildiğinde

bu bilgiyi sıfırlamak isteyebilirsiniz.

Bunu aşağıdaki gibi yapabilirsiniz:

```php
protected $someTemplateState = [];

public function enterNode(\Twig\Node\Node $node, \Twig\Environment $env)
{
    if ($node instanceof \Twig\Node\ModuleNode) {
        // Yeni bir şablona girildiğinde state sıfırlanır
        $this->someTemplateState = [];
    }

    // ...

    return $node;
}
```

Bu şekilde, visitor her yeni şablon derlendiğinde kendi durumunu temiz bir şekilde sıfırlamış olur.
