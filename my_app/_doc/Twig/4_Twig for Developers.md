# 🌿 Twig Geliştiriciler İçin

Bu bölüm Twig'in şablon diliyle değil, API'siyle ilgilidir. Twig'i uygulamaya entegre edenler için bir referans niteliğindedir; Twig şablonlarını oluşturanlar için değildir.

---

## ⚙️ Temeller

Twig, `\Twig\Environment` sınıfından türetilmiş “environment” adında merkezi bir nesne kullanır. Bu sınıfın örnekleri yapılandırma ve uzantıları depolamak ve şablonları yüklemek için kullanılır.

Çoğu uygulama, uygulama başlatılırken tek bir `\Twig\Environment` nesnesi oluşturur ve bunu şablonları yüklemek için kullanır. Bazı durumlarda, farklı yapılandırmalarla birden fazla environment yan yana bulunabilir.

Bir uygulama için Twig’in tipik yapılandırması şu şekildedir:

```php
require_once '/path/to/vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader('/path/to/templates');
$twig = new \Twig\Environment($loader, [
    'cache' => '/path/to/compilation_cache',
]);
```

Bu, varsayılan yapılandırmaya sahip bir şablon ortamı ve `/path/to/templates/` dizininde şablon arayan bir yükleyici oluşturur. Farklı yükleyiciler mevcuttur ve dilerseniz kendi yükleyicinizi (örneğin veritabanından yükleme için) yazabilirsiniz.

> **Not:**
>
> `Environment` sınıfının ikinci parametresi, seçenekleri içeren bir dizidir. `cache` seçeneği, Twig’in derlenmiş şablonları sakladığı dizini belirtir. Bu, değerlendirilen şablonlar için kullanılabilecek önbellekten farklıdır. Bunun için herhangi bir PHP cache kütüphanesini kullanabilirsiniz.

---

## 📂 Şablon Yükleme

Bir şablonu yüklemek için Twig environment’ında `load()` metodunu çağırın. Bu, bir `\Twig\TemplateWrapper` örneği döndürür:

```php
$template = $twig->load('index.html.twig');
```

---

## 🖋️ Şablonları Render Etme

Bir şablonu değişkenlerle render etmek için `render()` metodunu çağırın:

```php
echo $template->render(['the' => 'variables', 'go' => 'here']);
```

> **Not:**
>
> `display()` metodu, render edilmiş şablonu doğrudan çıktılamak için bir kısayoldur.

Ayrıca environment üzerinden doğrudan yükleyip render edebilirsiniz:

```php
echo $twig->render('index.html.twig', ['the' => 'variables', 'go' => 'here']);
```

Eğer bir şablon bloklar tanımlıyorsa, belirli bloklar `renderBlock()` ile ayrı ayrı render edilebilir:

```php
echo $template->renderBlock('block_name', ['the' => 'variables', 'go' => 'here']);
```

---

## 🔄 Şablonları Akış (Stream) Olarak Render Etme

Bir şablonu stream olarak çalıştırmak için `stream()` metodunu çağırın:

```php
$template->stream(['the' => 'variables', 'go' => 'here']);
```

Belirli bir bloğu stream etmek için:

```php
$template->streamBlock('block_name', ['the' => 'variables', 'go' => 'here']);
```

> **Not:**
>
> `stream()` ve `streamBlock()` metotları iterable döndürür.

---

## ⚙️ Environment Seçenekleri

Yeni bir `\Twig\Environment` örneği oluştururken ikinci parametre olarak bir seçenek dizisi geçebilirsiniz:

```php
$twig = new \Twig\Environment($loader, ['debug' => true]);
```

Mevcut seçenekler:

| Seçenek                   | Tür              | Açıklama                                                                                                            |
| -------------------------- | ----------------- | --------------------------------------------------------------------------------------------------------------------- |
| **debug**            | boolean           | `true`ise, üretilen şablonlara `__toString()`metodu eklenir. (varsayılan:`false`)                            |
| **charset**          | string            | Şablonlarda kullanılan karakter seti (varsayılan:`utf-8`).                                                       |
| **cache**            | string veya false | Derlenmiş şablonların saklanacağı dizin veya `false`ile devre dışı bırakılır.                            |
| **auto_reload**      | boolean           | Şablon değiştiğinde otomatik yeniden derleme. Belirtilmezse `debug`değerine göre belirlenir.                  |
| **strict_variables** | boolean           | `true`ise tanımsız değişkenlerde hata fırlatır;`false`ise `null`döner.                                   |
| **autoescape**       | string            | Varsayılan otomatik kaçış stratejisini belirler (`html`,`js`,`css`,`url`,`html_attr`veya PHP callback). |
| **optimizations**    | integer           | Uygulanacak optimizasyonlar (-1: tümü etkin, 0: devre dışı).                                                     |
| **use_yield**        | boolean           | `true`: yalnızca `yield`kullanır (Twig 4.0’da zorunlu olacak).                                                 |

---

## 📦 Loaders (Yükleyiciler)

Yükleyiciler, şablonları dosya sistemi gibi kaynaklardan yüklemekten sorumludur.

### 🧠 Derleme Önbelleği

Tüm yükleyiciler derlenmiş şablonları disk üzerinde önbelleğe alabilir. Bu, Twig’i hızlandırır çünkü şablonlar yalnızca bir kez derlenir.

---

## 🏗️ Dahili Yükleyiciler

### 🗂️ `\Twig\Loader\FilesystemLoader`

Dosya sisteminden şablon yükler:

```php
$loader = new \Twig\Loader\FilesystemLoader($templateDir);
```

Birden fazla dizinde şablon aramak için:

```php
$loader = new \Twig\Loader\FilesystemLoader([$templateDir1, $templateDir2]);
```

Ek veya öncelikli yollar eklemek için:

```php
$loader->addPath($templateDir3);
$loader->prependPath($templateDir4);
```

**Ad alanlı (namespaced) şablonlar** da desteklenir:

```php
$loader->addPath($templateDir, 'admin');
$twig->render('@admin/index.html.twig', []);
```

Göreceli yollar önerilir, çünkü önbellek anahtarlarını proje kök dizininden bağımsız hale getirir:

```php
$loader = new \Twig\Loader\FilesystemLoader('templates', getcwd().'/..');
```

> **Not:**
>
> İkinci parametre belirtilmezse Twig, göreceli yollar için `getcwd()` kullanır.

---

### 🧩 `\Twig\Loader\ArrayLoader`

PHP dizisinden şablon yükler:

```php
$loader = new \Twig\Loader\ArrayLoader([
    'index.html.twig' => 'Hello {{ name }}!',
]);
$twig = new \Twig\Environment($loader);

echo $twig->render('index.html.twig', ['name' => 'Fabien']);
```

Bu yükleyici **birim testleri** veya küçük projeler için uygundur.

> **İpucu:**
>
> Array loader kullanırken cache anahtarı şablon içeriğine göre değişir. Cache’in büyümemesi için eski dosyaları manuel temizlemeniz gerekir.

---

### 🔗 `\Twig\Loader\ChainLoader`

Birden fazla yükleyiciyi zincirler:

```php
$loader1 = new \Twig\Loader\ArrayLoader([
    'base.html.twig' => '{% block content %}{% endblock %}',
]);
$loader2 = new \Twig\Loader\ArrayLoader([
    'index.html.twig' => '{% extends "base.html.twig" %}{% block content %}Hello {{ name }}{% endblock %}',
    'base.html.twig'  => 'Will never be loaded',
]);

$loader = new \Twig\Loader\ChainLoader([$loader1, $loader2]);
$twig = new \Twig\Environment($loader);
```

Twig, her yükleyicide sırayla şablonu arar ve bulduğu anda döner. Yukarıdaki örnekte `index.html.twig` `$loader2`’den, `base.html.twig` ise `$loader1`’den yüklenir.

> **Not:**
>
> Yeni yükleyiciler `addLoader()` metodu ile de eklenebilir.



# 🧩 Kendi Loader’ınızı Oluşturma

Tüm yükleyiciler (`loader`) `\Twig\Loader\LoaderInterface` arayüzünü uygular:

```php
interface \Twig\Loader\LoaderInterface
{
    /**
     * Verilen şablonun mantıksal adı için kaynak bağlamını döndürür.
     *
     * @param string $name Şablonun mantıksal adı
     * @return \Twig\Source
     * @throws \Twig\Error\LoaderError $name bulunamadığında
     */
    public function getSourceContext($name);

    /**
     * Verilen şablon adı için önbellek anahtarını döndürür.
     *
     * @param string $name Yüklenecek şablonun adı
     * @return string Önbellek anahtarı
     * @throws \Twig\Error\LoaderError $name bulunamadığında
     */
    public function getCacheKey($name);

    /**
     * Şablonun hâlâ güncel olup olmadığını döndürür.
     *
     * @param string    $name Şablon adı
     * @param timestamp $time Önbellekteki şablonun son değişiklik zamanı
     * @return bool Güncelse true, değilse false
     * @throws \Twig\Error\LoaderError $name bulunamadığında
     */
    public function isFresh($name, $time);

    /**
     * Belirli bir şablonun kaynak koduna sahip olup olmadığımızı kontrol eder.
     *
     * @param string $name Kontrol edilecek şablon adı
     * @return bool Bu yükleyici tarafından yönetiliyorsa true, aksi halde false
     */
    public function exists($name);
}
```

`isFresh()` metodu, önbellekteki şablonun son değiştirilme zamanına göre hâlâ güncel olup olmadığını belirler.

`getSourceContext()` metodu ise bir `\Twig\Source` örneği döndürmelidir.

---

# 🧱 Uzantıların (Extensions) Kullanımı

Twig uzantıları, Twig’e yeni özellikler ekleyen paketlerdir. Bir uzantı `addExtension()` metodu ile kaydedilir:

```php
$twig->addExtension(new \Twig\Extension\SandboxExtension());
```

Twig aşağıdaki yerleşik uzantılarla birlikte gelir:

| Uzantı                                         | Açıklama                                                                                         |
| ----------------------------------------------- | -------------------------------------------------------------------------------------------------- |
| **\Twig\Extension\CoreExtension**         | Twig’in tüm temel özelliklerini tanımlar.                                                      |
| **\Twig\Extension\DebugExtension**        | Şablon değişkenlerini hata ayıklamak için `dump`fonksiyonunu ekler.                         |
| **\Twig\Extension\EscaperExtension**      | Otomatik çıktı kaçışını ve kod bloklarını kaçış/geri açma özelliğini ekler.        |
| **\Twig\Extension\SandboxExtension**      | Twig’e sandbox modu ekler; güvensiz kodların güvenli şekilde çalıştırılmasını sağlar. |
| **\Twig\Extension\ProfilerExtension**     | Yerleşik Twig profil aracını etkinleştirir.                                                    |
| **\Twig\Extension\OptimizerExtension**    | Derleme öncesinde düğüm ağacını optimize eder.                                              |
| **\Twig\Extension\StringLoaderExtension** | Şablonlar içinde `template_from_string`fonksiyonunu tanımlar.                                 |

 **Core** , **Escaper** ve **Optimizer** uzantıları varsayılan olarak yüklüdür.

---

# ⚙️ Yerleşik Uzantılar

> 💡 **İpucu:**
>
> Kendi uzantılarınızı oluşturmak için “Twig’i Genişletme” bölümünü okuyun.

---

## 🧩 Core Extension

Core uzantısı, Twig’in temel özelliklerini tanımlar:

* Etiketler (Tags)
* Filtreler (Filters)
* Fonksiyonlar (Functions)
* Testler (Tests)

---

## 🛡️ Escaper Extension

`EscaperExtension`, Twig’e **otomatik çıktı kaçışı** ekler. Bir `autoescape` etiketi ve `raw` filtresi tanımlar.

Global çıktı kaçış stratejisini etkinleştirmek veya devre dışı bırakmak için:

```php
$escaper = new \Twig\Extension\EscaperExtension('html');
$twig->addExtension($escaper);
```

`'html'` olarak ayarlandığında, tüm değişkenler HTML için otomatik olarak kaçışa tabi tutulur. Ancak `raw` filtresi kullanılan ifadeler kaçıştan muaf olur:

```twig
{{ article.to_html|raw }}
```

Yerel olarak kaçış modunu değiştirmek için `autoescape` etiketi kullanılır:

```twig
{% autoescape 'html' %}
    {{ var }}
    {{ var|raw }}      {# var kaçıştan muaf #}
    {{ var|escape }}   {# var iki kez kaçışa uğramaz #}
{% endautoescape %}
```

> ⚠️ **Uyarı:**
>
> `autoescape` etiketi, dahil edilen dosyalar (`include`) üzerinde etkili değildir.

---

### 🔍 Kaçış Kuralları

* Şablonda doğrudan kullanılan literal değerler (sayılar, boolean, dizi vb.)  **otomatik olarak kaçışa uğramaz** :

  ```twig
  {{ "Twig<br/>" }} {# kaçış yok #}

  {% set text = "Twig<br/>" %}
  {{ text }} {# kaçış yapılır #}
  ```
* Sonucu literal olan veya güvenli (`safe`) olarak işaretlenmiş değişkenler kaçıştan muaftır:

  ```twig
  {{ any_value ? "Twig<br/>" : "<br/>Twig" }} {# kaçış yok #}
  ```
* `__toString` metoduna sahip nesneler dizeye dönüştürülür ve kaçış uygulanır.

  Ancak bazı sınıflar/arayüzler **güvenli** olarak işaretlenebilir:

  ```php
  $escaper->addSafeClass('HtmlGenerator', ['html']);
  $escaper->addSafeClass('HtmlGeneratorInterface', ['html']);
  $escaper->addSafeClass('HtmlGenerator', ['html', 'js']);
  $escaper->addSafeClass('HtmlGenerator', ['all']);
  ```
* Kaçış işlemi, yazdırmadan **önce** ve diğer filtrelerden **sonra** uygulanır:

  ```twig
  {{ var|upper }} {# eşdeğeri {{ var|upper|escape }} #}
  ```
* `raw` filtresi yalnızca zincirin **sonunda** kullanılmalıdır:

  ```twig
  {{ var|raw|upper }} {# kaçış uygulanır #}
  {{ var|upper|raw }} {# kaçış uygulanmaz #}
  ```
* Son filtre mevcut bağlam için güvenli olarak işaretlenmişse (ör. `html`, `js`) otomatik kaçış uygulanmaz.

  ```twig
  {% autoescape 'js' %}
      {{ var|escape('html') }} {# HTML ve JS için kaçış #}
      {{ var }}                {# JS için kaçış #}
      {{ var|escape('js') }}   {# çift kaçış yok #}
  {% endautoescape %}
  ```

> **Not:**
>
> `autoescape`, ifadeler değerlendirildikten sonra uygulanır.
>
> Örneğin:
>
> `{{ value|raw ~ other }}` beklenen sonucu vermez, çünkü kaçış birleştirme işlemi sonrası uygulanır.

---

## 🧱 Sandbox Extension

`SandboxExtension`, **güvenilmeyen kodları** çalıştırmak için kullanılır. Ayrıntılar için “Twig Sandbox” bölümüne bakın.

---

## 📊 Profiler Extension

`ProfilerExtension`, Twig şablonları için profil oluşturmayı sağlar.

Yalnızca geliştirme ortamında kullanılmalıdır, çünkü ek yük getirir:

```php
$profile = new \Twig\Profiler\Profile();
$twig->addExtension(new \Twig\Extension\ProfilerExtension($profile));

$dumper = new \Twig\Profiler\Dumper\TextDumper();
echo $dumper->dump($profile);
```

Profil; şablon, blok ve makro işlemleri için **zaman** ve **bellek kullanımı** bilgilerini içerir.

Verileri **Blackfire.io** uyumlu biçimde dışa aktarabilirsiniz:

```php
$dumper = new \Twig\Profiler\Dumper\BlackfireDumper();
file_put_contents('/path/to/profile.prof', $dumper->dump($profile));
```

Daha sonra profili yükleyerek görselleştirebilirsiniz:

```bash
blackfire --slot=7 upload /path/to/profile.prof
```

---

## ⚡ Optimizer Extension

`OptimizerExtension`, derleme öncesinde düğüm ağacını optimize eder:

```php
$twig->addExtension(new \Twig\Extension\OptimizerExtension());
```

Varsayılan olarak tüm optimizasyonlar açıktır.

Belirli optimizasyonları etkinleştirmek için:

```php
$optimizer = new \Twig\Extension\OptimizerExtension(
    \Twig\NodeVisitor\OptimizerNodeVisitor::OPTIMIZE_FOR
);
$twig->addExtension($optimizer);
```

Desteklenen optimizasyonlar:

* `\Twig\NodeVisitor\OptimizerNodeVisitor::OPTIMIZE_ALL` — Tüm optimizasyonlar (varsayılan).
* `\Twig\NodeVisitor\OptimizerNodeVisitor::OPTIMIZE_NONE` — Tümünü kapatır (daha kısa derleme, daha yavaş çalışma).
* `\Twig\NodeVisitor\OptimizerNodeVisitor::OPTIMIZE_FOR` — `for` etiketi için döngü değişkeni oluşturulmasını mümkün olduğunca kaldırır.

---

# 🚨 Hatalar (Exceptions)

Twig aşağıdaki istisnaları (exceptions) fırlatabilir:

| İstisna                              | Açıklama                                                                                               |
| ------------------------------------- | -------------------------------------------------------------------------------------------------------- |
| **\Twig\Error\Error**           | Tüm Twig hatalarının temel sınıfı.                                                                 |
| **\Twig\Error\SyntaxError**     | Şablon sözdiziminde hata olduğunda fırlatılır.                                                     |
| **\Twig\Error\RuntimeError**    | Çalışma zamanında (örneğin olmayan bir filtrenin çağrılması) hata oluştuğunda fırlatılır. |
| **\Twig\Error\LoaderError**     | Şablon yükleme sırasında hata oluştuğunda fırlatılır.                                           |
| **\Twig\Sandbox\SecurityError** | Sandbox modunda izin verilmeyen bir etiket, filtre veya metod çağrıldığında fırlatılır.         |
