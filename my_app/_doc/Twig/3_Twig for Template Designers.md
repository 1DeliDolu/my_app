## 🧩 Twig Şablon Tasarımcıları İçin

Bu belge, şablon motorunun sözdizimini ve anlamını açıklar ve Twig şablonları oluşturanlar için başvuru niteliğinde bir kaynaktır.

---

### 🧱 Genel Bakış

Bir şablon, normal bir metin dosyasıdır. HTML, XML, CSV, LaTeX vb. herhangi bir metin tabanlı format üretebilir. Belirli bir uzantıya sahip olması gerekmez; `.html` veya `.xml` gibi uzantılar uygundur.

Bir şablon, değerlendirme sırasında değerlerle değiştirilen **değişkenler veya ifadeler** ile şablonun mantığını kontrol eden **etiketler (tags)** içerir.

Aşağıda temel birkaç öğeyi gösteren basit bir şablon örneği bulunmaktadır. Ayrıntılara daha sonra değineceğiz:

```twig
<!DOCTYPE html>
<html>
    <head>
        <title>My Webpage</title>
    </head>
    <body>
        <ul id="navigation">
        {% for item in navigation %}
            <li><a href="{{ item.href }}">{{ item.caption }}</a></li>
        {% endfor %}
        </ul>

        <h1>My Webpage</h1>
        {{ a_variable }}
    </body>
</html>
```

İki tür sınırlayıcı (delimiter) vardır:

* `{% ... %}` → döngü gibi ifadeleri yürütmek için kullanılır,
* `{{ ... }}` → bir ifadenin sonucunu çıktılamak için kullanılır.

💡 **İpucu:**

Twig ile denemeler yapmak için  **Twig Playground** ’u kullanabilirsiniz.

---

### 🧠 Üçüncü Taraf Entegrasyonlar

Birçok IDE, Twig için sözdizimi vurgulama ve otomatik tamamlama desteği sunar:

* **Textmate** → Twig bundle aracılığıyla
* **Vim** → vim-twig eklentisiyle
* **NetBeans** → (7.2 sürümünden itibaren yerel destek)
* **PhpStorm** → (2.1 sürümünden itibaren yerel destek)
* **Eclipse** → Twig plugin ile
* **Sublime Text** → Twig bundle ile
* **GtkSourceView** → Twig dil tanımıyla (gedit ve diğer projelerde kullanılır)
* **Coda** ve **SubEthaEdit** → Twig syntax mode ile
* **Coda 2** → farklı Twig syntax mode ile
* **Komodo / Komodo Edit** → Twig highlight/syntax check mode
* **Notepad++** → Twig Highlighter
* **Emacs** → web-mode.el
* **Atom** → PHP-twig for atom
* **Visual Studio Code** → Twig pack, Modern Twig veya Twiggy

Ayrıca ilginizi çekebilecek araçlar:

* **Twig CS Fixer:** Şablon kod stilinizi kontrol eder/düzeltir
* **Twig Language Server:** Sözdizimi vurgulama, tanılama, otomatik tamamlama gibi özellikler sağlar
* **TwigQI:** Derleme sırasında yaygın hataları analiz eden bir eklenti
* **TwigStan:** PHPStan ile güçlendirilmiş statik analiz aracı

---

### 🧮 Değişkenler

Twig şablonları, PHP uygulaması tarafından sağlanan veya şablon içinde **set** etiketiyle oluşturulan değişkenlere erişebilir. Bu değişkenler şablonda kullanılabilir, manipüle edilebilir ve görüntülenebilir.

Twig, PHP türlerini olabildiğince soyutlayarak aşağıdaki temel türlerle çalışır:

| Twig Türü         | PHP Türü                   |
| ------------------- | ---------------------------- |
| string              | String veya Stringable nesne |
| number              | Integer veya float           |
| boolean             | true veya false              |
| null                | null                         |
| iterable (mapping)  | Dizi                         |
| iterable (sequence) | Dizi                         |
| iterable (object)   | Iterable nesne               |
| object              | Nesne                        |

`iterable` ve `object` türleri, nokta (`.`) operatörü aracılığıyla erişilebilen öznitelikleri açığa çıkarır:

```twig
{{ user.name }}
```

> **Not:**
>
> Süslü parantezler (`{{ }}`) değişkenin değil, yazdırma ifadesinin bir parçasıdır. Etiketler içinde değişkenlere erişirken parantez kullanmayın.

Bir değişken veya öznitelik mevcut değilse davranış, `strict_variables` seçeneğine bağlıdır:

* **false** ise → `null` döndürür
* **true** ise → bir istisna fırlatır

Daha fazlası için **dot operatörü** hakkında bilgi edinebilirsiniz.

---

### 🌍 Global Değişkenler

Aşağıdaki değişkenler her zaman şablonlarda kullanılabilir:

* **_self:** Geçerli şablon adını referans alır
* **_context:** Geçerli bağlamı (context) referans alır
* **_charset:** Geçerli karakter setini referans alır

---

### ✏️ Değişken Tanımlama

Kod blokları içinde değişkenlere değer atayabilirsiniz. Atamalar `set` etiketiyle yapılır:

```twig
{% set name = 'Fabien' %}
{% set numbers = [1, 2] %}
{% set map = {'city': 'Paris'} %}
```


## 🔄 Filtreler

Değişkenler ve ifadeler **filtrelerle** değiştirilebilir. Filtreler, değişkenden bir boru sembolü (`|`) ile ayrılır. Birden fazla filtre zincirleme şekilde uygulanabilir — bir filtrenin çıktısı bir sonrakine aktarılır.

Aşağıdaki örnek, `name` değişkenindeki tüm HTML etiketlerini kaldırır ve başlık biçimine çevirir:

```twig
{{ name|striptags|title }}
```

Argüman kabul eden filtreler, argümanları parantez içinde alır. Bu örnek, bir listenin elemanlarını virgülle birleştirir:

```twig
{{ list|join(', ') }}
```

Bir kod bölümüne filtre uygulamak için `apply` etiketini kullanabilirsiniz:

```twig
{% apply upper %}
    This text becomes uppercase
{% endapply %}
```

Yerleşik filtreler hakkında daha fazla bilgi edinmek için **filters** sayfasına göz atın.

⚠️ **Uyarı:**

Filtre operatörünün önceliği en yüksek olduğundan, daha karmaşık ifadeleri filtrelerken parantez kullanın:

```twig
{{ (1..5)|join(', ') }}
{{ ('HELLO' ~ 'FABIEN')|lower }}
```

---

## 🧮 Fonksiyonlar

Fonksiyonlar, içerik üretmek için çağrılabilir. Fonksiyonlar isimlerinden sonra parantez `()` ile çağrılır ve argümanlar alabilirler.

Örneğin, `range` fonksiyonu bir aritmetik tam sayı dizisi döndürür:

```twig
{% for i in range(0, 3) %}
    {{ i }},
{% endfor %}
```

Yerleşik fonksiyonlar hakkında daha fazla bilgi edinmek için **functions** sayfasına bakın.

---

## 🏷️ İsimlendirilmiş Argümanlar

İsimlendirilmiş argümanlar;  **fonksiyonlar** ,  **filtreler** ,  **testler** , **makrolar** ve **dot operatörü argümanları** dahil olmak üzere argüman geçilen her yerde desteklenir.

🆕 **Twig 3.15** sürümünde makrolar ve dot operatörü için isimlendirilmiş argümanlar eklendi.

🆕 **Twig 3.12** sürümünden itibaren `:` işareti de `=` alternatifi olarak kullanılabilir.

```twig
{% for i in range(low: 1, high: 10, step: 2) %}
    {{ i }},
{% endfor %}
```

İsimlendirilmiş argümanlar, iletilen değerlerin anlamını daha açık hale getirir:

```twig
{{ data|convert_encoding('UTF-8', 'iso-2022-jp') }}

{# yerine #}

{{ data|convert_encoding(from: 'iso-2022-jp', to: 'UTF-8') }}
```

Ayrıca, varsayılan değerini değiştirmek istemediğiniz argümanları atlamanıza da olanak tanır:

```twig
{# ilk argüman tarih formatıdır; null geçilirse global tarih formatı kullanılır #}
{{ "now"|date(null, "Europe/Paris") }}

{# format değerini atlayıp zaman dilimini isimlendirilmiş argümanla belirtebilirsiniz #}
{{ "now"|date(timezone: "Europe/Paris") }}
```

Pozisyonel ve isimlendirilmiş argümanları bir arada kullanabilirsiniz; ancak pozisyonel argümanlar her zaman önce gelmelidir:

```twig
{{ "now"|date('d/m/Y H:i', timezone: "Europe/Paris") }}
```

💡 **İpucu:**

Her fonksiyon, filtre ve test dokümantasyonunda desteklenen tüm argümanların isimleri listelenmiştir.

---

## ⚙️ Kontrol Yapıları

 **Kontrol yapısı** , programın akışını yöneten tüm unsurları ifade eder — koşullar (`if/elseif/else`), döngüler (`for`) ve bloklar gibi. Kontrol yapıları `{% ... %}` blokları içinde yer alır.

Örneğin, `users` adlı bir değişkende sağlanan kullanıcı listesini göstermek için `for` etiketini kullanabilirsiniz:

```twig
<h1>Members</h1>
<ul>
    {% for user in users %}
        <li>{{ user.username|e }}</li>
    {% endfor %}
</ul>
```

Bir ifadeyi test etmek için `if` etiketi kullanılabilir:

```twig
{% if users|length > 0 %}
    <ul>
        {% for user in users %}
            <li>{{ user.username|e }}</li>
        {% endfor %}
    </ul>
{% endif %}
```

Yerleşik etiketler hakkında daha fazla bilgi için **tags** sayfasına bakın.

---

## 💬 Yorumlar

Bir şablonun bir kısmını yorum satırı haline getirmek için `{# ... #}` sözdizimini kullanın. Bu, hata ayıklama veya diğer şablon tasarımcılarına not bırakmak için yararlıdır:

```twig
{# not: bu şablon devre dışı bırakıldı çünkü artık kullanılmıyor
    {% for user in users %}
        ...
    {% endfor %}
#}
```

🆕 **Twig 3.15** sürümüyle **satır içi yorumlar (inline comments)** eklendi.

Blok, değişken veya yorum içindeyken yorum eklemek isterseniz satır içi yorumları kullanabilirsiniz. `#` ile başlar ve satırın sonuna kadar devam eder:

```twig
{{
    # bu bir satır içi yorumdur
    "Hello World"|upper
    # bu bir satır içi yorumdur
}}
```

```twig
{{
    {
        # bu bir satır içi yorumdur
        fruit: 'apple', # bu bir satır içi yorumdur
        color: 'red', # bu bir satır içi yorumdur
    }|join(', ')
}}
```

Satır içi yorumlar ifadeyle aynı satırda da bulunabilir:

```twig
{{
    "Hello World"|upper # bu bir satır içi yorumdur
}}
```

⚠️ Satır içi yorumlar, bulunduğu satırın sonuna kadar devam ettiğinden aşağıdaki kod çalışmaz; çünkü `}}` ifadesi yorumun bir parçası olurdu:

```twig
{{ "Hello World"|upper # bu bir satır içi yorumdur }}
```


## 📦 Diğer Şablonları Dahil Etme

`include` fonksiyonu, bir şablonu dahil etmek ve bu şablonun oluşturulmuş içeriğini mevcut şablona döndürmek için kullanışlıdır:

```twig
{{ include('sidebar.html.twig') }}
```

Varsayılan olarak, dahil edilen şablonlar kendilerini çağıran şablonla aynı **bağlama (context)** erişebilir. Bu, ana şablonda tanımlanan herhangi bir değişkenin dahil edilen şablonda da kullanılabileceği anlamına gelir:

```twig
{% for box in boxes %}
    {{ include('render_box.html.twig') }}
{% endfor %}
```

Burada, `render_box.html.twig` şablonu `box` değişkenine erişebilir.

Şablonun adı, **template loader** türüne bağlıdır. Örneğin, `\Twig\Loader\FilesystemLoader` sınıfı, dosya adını belirterek diğer şablonlara erişmenizi sağlar. Alt dizinlerdeki şablonlara `/` karakteriyle erişebilirsiniz:

```twig
{{ include('sections/articles/sidebar.html.twig') }}
```

Bu davranış, Twig’i barındıran uygulamaya bağlıdır.

---

## 🧬 Şablon Kalıtımı (Template Inheritance)

Twig’in en güçlü özelliği  **şablon kalıtımıdır** . Bu özellik, sitenizin tüm ortak öğelerini içeren bir “iskelet” şablon oluşturmanıza ve alt şablonların bu blokları gerektiğinde geçersiz kılmasına olanak tanır.

### 🏗️ Örnek: Temel Şablon

Öncelikle, iki sütunlu bir sayfa için kullanılabilecek bir HTML iskeletini tanımlayan `base.html.twig` adlı bir şablon oluşturalım:

```twig
<!DOCTYPE html>
<html>
    <head>
        {% block head %}
            <link rel="stylesheet" href="style.css"/>
            <title>{% block title %}{% endblock %} - My Webpage</title>
        {% endblock %}
    </head>
    <body>
        <div id="content">{% block content %}{% endblock %}</div>
        <div id="footer">
            {% block footer %}
                © Copyright 2011 by <a href="https://example.com/">you</a>.
            {% endblock %}
        </div>
    </body>
</html>
```

Bu örnekte, `block` etiketleri dört blok tanımlar. Bu bloklar, alt şablonlar tarafından doldurulabilir veya geçersiz kılınabilir.

### 🧱 Alt Şablon

Bir alt şablon şu şekilde görünebilir:

```twig
{% extends "base.html.twig" %}

{% block title %}Index{% endblock %}
{% block head %}
    {{ parent() }}
    <style type="text/css">
        .important { color: #336699; }
    </style>
{% endblock %}
{% block content %}
    <h1>Index</h1>
    <p class="important">
        Welcome to my awesome homepage.
    </p>
{% endblock %}
```

Burada kilit nokta `extends` etiketidir. Bu etiket, Twig’e bu şablonun başka bir şablonu “genişlettiğini” belirtir. Twig önce üst şablonu bulur, ardından bu alt şablonu işler.

> `extends` etiketi her zaman şablonun **ilk etiketi** olmalıdır.

Alt şablon `footer` bloğunu tanımlamadığı için, bu kısım üst şablondaki haliyle kullanılır.

### 🔁 Üst Blok İçeriğini Kullanma

Bir üst bloğun içeriğini alt şablonda yeniden göstermek için `parent()` fonksiyonunu kullanabilirsiniz:

```twig
{% block sidebar %}
    <h3>Table Of Contents</h3>
    ...
    {{ parent() }}
{% endblock %}
```

💡 **İpucu:**

`extends` etiketi dokümantasyon sayfasında,  **blok iç içe geçirme (nesting)** ,  **scope** , **dinamik kalıtım** ve **koşullu kalıtım** gibi ileri seviye özellikler açıklanır.

> **Not:**
>
> Twig ayrıca, `use` etiketi sayesinde “yatay yeniden kullanım (horizontal reuse)” ile çoklu kalıtımı da destekler.

---

## 🔒 HTML Kaçış (Escaping)

Şablonlardan HTML üretirken, değişkenlerin çıktısında istenmeyen karakterlerin HTML yapısını bozma riski vardır. İki yaklaşım kullanılabilir:

1. Her değişkeni manuel olarak kaçışlamak
2. Tüm değişkenlerin otomatik olarak kaçışlanmasını sağlamak

Twig her iki yöntemi de destekler ve **otomatik kaçış** varsayılan olarak  **etkindir** .

Otomatik kaçış stratejisi, `autoescape` seçeneği ile yapılandırılabilir ve varsayılan olarak `html` kullanır.

---

### ✋ Manuel Kaçış ile Çalışmak

Manuel kaçış etkinleştirildiyse, gerekliyse değişkenleri kendiniz kaçışlamanız gerekir.

Kaçışlanması gerekenler: Güvenilmeyen kaynaklardan gelen tüm değişkenlerdir.

Kaçışlama, `escape` veya kısaltması `e` filtresiyle yapılır:

```twig
{{ user.username|e }}
```

Varsayılan olarak `html` stratejisi kullanılır; ancak bağlama göre farklı stratejiler belirtebilirsiniz:

```twig
{{ user.username|e('js') }}
{{ user.username|e('css') }}
{{ user.username|e('url') }}
{{ user.username|e('html_attr') }}
```

---

### ⚙️ Otomatik Kaçış ile Çalışmak

Otomatik kaçış etkin veya devre dışı olsa da, bir şablonun belirli bir bölümünü `autoescape` etiketiyle açıkça kaçışlayabilir veya hariç tutabilirsiniz:

```twig
{% autoescape %}
    Everything will be automatically escaped in this block (using the HTML strategy)
{% endautoescape %}
```

Varsayılan olarak `html` stratejisi kullanılır.

Farklı bağlamlarda değişkenleri çıktılamak istiyorsanız uygun stratejiyi belirtmelisiniz:

```twig
{% autoescape 'js' %}
    Everything will be automatically escaped in this block (using the JS strategy)
{% endautoescape %}
```

---

## 🧱 Kaçışlama (Escaping) İpuçları

Bazen Twig’in değişken veya blok olarak işlemesini istemediğiniz kısımlar olabilir.

Örneğin, `{{` karakterlerini ham metin olarak göstermek istiyorsanız şu yöntemi kullanabilirsiniz:

```twig
{{ '{{' }}
```

Daha büyük bölümler için `verbatim` bloğu kullanmak daha uygundur.


## 🧩 Makrolar (Macros)

 **Makrolar** , normal programlama dillerindeki fonksiyonlara benzer.

HTML parçalarını tekrar kullanmak ve kendinizi tekrarlamamak için faydalıdırlar.

Makrolar, **macro** etiketi dokümantasyonunda detaylı şekilde açıklanmıştır.

---

## 🧮 İfadeler (Expressions)

Twig, şablonlarda her yerde ifadelerin kullanılmasına izin verir.

---

### 🔤 Literaller (Literals)

İfadelerin en basit biçimi **literal** değerlerdir. Literal’ler, PHP türlerinin (string, number, array vb.) temsilleridir.

Aşağıdaki literal türleri mevcuttur:

* `"Hello World"`: Çift veya tek tırnak içindeki her şey bir  **string** ’dir.

  (Fonksiyon çağrılarında, filtrelerde veya `include` gibi işlemlerde kullanılabilir.)
  Kaçış gerektiren karakterler:

  ```
  \f  : Form feed
  \n  : Yeni satır
  \r  : Satır başı
  \t  : Sekme
  \v  : Dikey sekme
  \x  : Hexadecimal kaçış dizisi
  \0–\377 : Oktal kaçış dizileri
  \\ : Ters eğik çizgi
  ```

  Tek tırnaklı stringlerde `'` karakteri `\'` ile,

  çift tırnaklı stringlerde `"` karakteri `\"` ile kaçışlanmalıdır.

  Örneğin:

  `'It\'s good'` veya `'c:\\Program Files'`.
* `42` / `42.23`: Nokta içeriyorsa  **float** , içermiyorsa **integer** türüdür.

  Okunabilirliği artırmak için alt çizgi kullanılabilir:

  `-3_141.592_65` ≡ `-3141.59265`.
* `["first_name", "last_name"]`: Köşeli parantez içindeki ifadeler **sequence** (liste) tanımlar.
* `{"name": "Fabien"}`: Süslü parantez içindekiler **mapping** (anahtar-değer) tanımlar:

  ```twig
  {'name': 'Fabien', 'city': 'Paris'}
  {name: 'Fabien', city: 'Paris'} {# aynı anlama gelir #}
  {2: 'Twig', 4: 'Symfony'}
  {Paris} {# eşdeğer: {'Paris': Paris} #}
  {% set key = 'name' %}
  {(key): 'Fabien', (1 + 1): 2, ('ci' ~ 'ty'): 'city'}
  ```
* `true / false`: Mantıksal değerlerdir.
* `null`: Tanımsız değer (olmayan değişkenler `null` döndürür).

  `none` ifadesi `null` için bir takma addır.

Karmaşık yapılarda listeler ve sözlükler iç içe olabilir:

```twig
{% set complex = [1, {"name": "Fabien"}] %}
```

💡 **İpucu:**

Çift veya tek tırnaklı stringlerin performans farkı yoktur; ancak **string interpolation** yalnızca çift tırnaklarda desteklenir.

---

### 🧷 String Interpolation

**String interpolation** (`#{expression}`), çift tırnaklı stringler içinde ifadeleri doğrudan yerleştirmenizi sağlar:

```twig
{{ "first #{middle} last" }}
{{ "first #{1 + 2} last" }}
```

Kaçışlamak isterseniz ters eğik çizgi kullanın:

```twig
{{ "first \#{1 + 2} last" }} {# çıktısı: first #{1 + 2} last #}
```

---

### ➕ Matematiksel İşlemler (Math)

Twig, şablonlarda temel matematiksel işlemleri destekler:

| Operatör | Açıklama            | Örnek                   |
| --------- | --------------------- | ------------------------ |
| `+`     | Toplama               | `{{ 1 + 1 }}`→`2`   |
| `-`     | Çıkarma             | `{{ 3 - 2 }}`→`1`   |
| `/`     | Bölme (float döner) | `{{ 1 / 2 }}`→`0.5` |
| `%`     | Mod alma              | `{{ 11 % 7 }}`→`4`  |
| `//`    | Tamsayı bölme       | `{{ 20 // 7 }}`→`2` |
| `*`     | Çarpma               | `{{ 2 * 2 }}`→`4`   |
| `**`    | Üs alma              | `{{ 2 ** 3 }}`→`8`  |

⚠️ `**` operatörü  **sağ birleşimlidir** , yani `{{ -1**0 }}` → `{{ -(1**0) }}` olarak değerlendirilir, `{{ (-1)**0 }}` değil.

---

### 🧠 Mantıksal Operatörler (Logic)

İfadeleri birleştirmek için şu operatörler kullanılabilir:

| Operatör  | Açıklama                            |
| ---------- | ------------------------------------- |
| `and`    | Her iki ifade de true ise true döner |
| `xor`    | Yalnızca biri true ise true döner   |
| `or`     | Herhangi biri true ise true döner    |
| `not`    | İfadeyi tersine çevirir             |
| `(expr)` | Parantez içinde grupla               |

> 💡 Twig ayrıca bit düzeyinde `b-and`, `b-xor`, `b-or` operatörlerini de destekler.
>
> ⚠️ Operatörler  **büyük/küçük harf duyarlıdır** .

---

### 🔍 Karşılaştırmalar (Comparisons)

Twig, şu karşılaştırma operatörlerini destekler:

`==`, `!=`, `<`, `>`, `>=`, `<=`.

---

### 🚀 Spaceship Operatörü

`<=>` operatörü, iki ifadeyi karşılaştırır ve:

* `-1`: soldaki < sağdaki
* `0`: eşit
* `1`: soldaki > sağdaki

değerlerini döndürür.

---

### 🔁 Iterable Operatörleri

Bir iterable’ın (liste, dizi vb.) tüm veya bazı elemanlarının koşulu sağlayıp sağlamadığını test edebilirsiniz:

```twig
{% set sizes = [34, 36, 38, 40, 42] %}

{% set hasOnlyOver38 = sizes has every v => v > 38 %} {# false #}
{% set hasOver38 = sizes has some v => v > 38 %}      {# true #}
```

Boş iterable’larda `has every` → `true`, `has some` → `false` döner.

---

### 🔎 İçerme Operatörleri (Containment)

`in` operatörü, sol operandın sağ operand içinde olup olmadığını kontrol eder:

```twig
{{ 1 in [1, 2, 3] }}     {# true #}
{{ 'cd' in 'abcde' }}    {# true #}
```

> Dizi, string, mapping veya Traversable nesnelerinde kullanılabilir.

Negatif test için `not in` kullanılır:

```twig
{% if 1 not in [1, 2, 3] %}
{% endif %}
```

veya eşdeğer şekilde:

```twig
{% if not (1 in [1, 2, 3]) %}
{% endif %}
```

Ayrıca string’ler için:

```twig
{% if 'Fabien' starts with 'F' %}
{% endif %}

{% if 'Fabien' ends with 'n' %}
{% endif %}
```

Daha karmaşık karşılaştırmalar için **regular expression** kullanabilirsiniz:

```twig
{% if phone matches '/^[\\d\\.]+$/' %}
{% endif %}
```

---

### 🧪 Test Operatörü (is)

`is` operatörü, bir değişkeni belirli bir duruma göre test eder:

```twig
{{ name is odd }}
```

Testler argüman alabilir:

```twig
{% if post.status is constant('Post::PUBLISHED') %}
{% endif %}
```

Negatif test için `is not` kullanılabilir:

```twig
{% if post.status is not constant('Post::PUBLISHED') %}
{% endif %}
```

Eşdeğeri:

```twig
{% if not (post.status is constant('Post::PUBLISHED')) %}
{% endif %}
```

Yerleşik testler hakkında daha fazla bilgi için **tests** sayfasına göz atın.


## ⚙️ Diğer Operatörler

Aşağıdaki operatörler diğer kategorilere tam olarak uymayan Twig operatörleridir:

---

### 🧩 `|` — Filtre Uygulama

Bir değişkene filtre uygular.

---

### 🔢 `..` — Dizi (Sequence) Oluşturma

İki operand arasındaki değerlere dayalı bir dizi oluşturur (bu, `range` fonksiyonunun sözdizimsel bir kısaltmasıdır):

```twig
{% for i in 1..5 %}{{ i }}{% endfor %}
```

eşdeğeri:

```twig
{% for i in range(1, 5) %}{{ i }}{% endfor %}
```

⚠️ Filtre operatörleriyle birlikte kullanırken parantez zorunludur:

```twig
{{ (1..5)|join(', ') }}
```

---

### 🔗 `~` — Birleştirme (Concatenation)

Tüm operandları string’e dönüştürür ve birleştirir:

```twig
{{ "Hello " ~ name ~ "!" }}
```

`name` = `'John'` → çıktı: `Hello John!`

---

### 🧱 `.` ve `[]` — Öznitelik (Attribute) Erişimi

Bir değişkenin özniteliğine erişmek için kullanılır.

Bu, PHP nesneleri veya dizileri üzerinde çalışabilir.

```twig
{{ user.name }}
```

* Nokta (`.`) operatöründen sonra parantez içinde herhangi bir ifade kullanılabilir:

  ```twig
  {{ user.('first-name') }}
  ```

  veya dinamik bir öznitelik için:

  ```twig
  {{ user.(name) }}
  {{ user.('get' ~ name) }}
  ```

> 🕓 Twig 3.15 öncesinde bu durumlarda `attribute()` fonksiyonu kullanılmalıydı.

**Köşeli parantez sözdizimi (`[]`)** de desteklenir:

```twig
{{ user['name'] }}
```

Metot çağırmak için `()` kullanılır:

```twig
{{ html.generate_input() }}
{{ html.generate_input('pwd', 'password') }}
{{ html.generate_input(name: 'pwd', type: 'password') }}
```

---

### 🧠 PHP Uygulama Mantığı

`user.name` çözümleme sırası:

1. `user` bir PHP dizisi veya `ArrayAccess` nesnesiyse → `name` anahtarını kontrol et
2. Aksi halde bir nesne ise → `name` özelliğini kontrol et
3. Aksi halde → sınıf sabiti kontrol et
4. Aksi halde → `name()`, `getName()`, `isName()`, `hasName()` metotlarını sırayla dene
5. `strict_variables` false ise `null` döndür
6. Aksi halde hata fırlat

`user['name']` çözümleme sırası:

1. Dizi anahtarını kontrol et
2. `strict_variables` false ise `null` döndür
3. Aksi halde hata fırlat

Metot çağrısı `user.name()` şeklindeyse:

1. `user` bir nesne ise ve yukarıdaki metotlardan birini içeriyorsa çalıştırılır
2. Aksi halde `strict_variables` false ise `null` döner
3. Değilse hata fırlatılır

---

### ❓ `?:` — Üçlü (Ternary) Operatör

```twig
{{ result ? 'yes' : 'no' }}
{{ result ?: 'no' }}    {# result ? result : 'no' #}
{{ result ? 'yes' }}    {# result ? 'yes' : '' #}
```

---

### 🪄 `??` — Null-Coalescing Operatör

Değer tanımlı ve `null` değilse onu döndürür, aksi halde alternatif değeri döndürür:

```twig
{{ result ?? 'no' }}
```

---

### 🌈 `...` — Spread Operatörü

Dizileri, mapping’leri veya fonksiyon argümanlarını genişletmek için kullanılır:

```twig
{% set numbers = [1, 2, ...moreNumbers] %}
{% set ratings = {'q1': 10, 'q2': 5, ...moreRatings} %}
{{ 'Hello %s %s!'|format(...['Fabien', 'Potencier']) }}
```

> 🆕 Twig 3.15 ile fonksiyon argümanlarında genişletme desteği eklendi.

---

### 🧭 `=>` — Arrow Function (Ok Fonksiyonu)

Kısa fonksiyon tanımlamak için kullanılır.

Bir veya daha fazla argüman alır ve tek bir ifade döndürür.

Arrow fonksiyonlar, filtrelerde, fonksiyonlarda, testlerde, makrolarda ve metot çağrılarında kullanılabilir:

```twig
{{ people|map(p => p.first_name)|join(', ') }}
```

Değişkenlerde saklanabilir:

```twig
{% set first_name_fn = (p) => p.first_name %}
{{ people|map(first_name_fn)|join(', ') }}
```

> 🆕 Twig 3.15 — fonksiyon, makro ve metot çağrılarında destek eklendi.
>
> 🆕 Twig 3.19 — `invoke` filtresi ile arrow fonksiyonlar çağrılabilir hale geldi.

---

## ⚖️ Operatör Öncelikleri

Twig operatörleri, farklı öncelik seviyelerine sahiptir.

Parantez (`()`) kullanılmadığında Twig bu tabloya göre ifadeleri işler.

### 🔢 Twig 3.x Operatör Önceliği (En düşük → En yüksek)

| Öncelik | Operatör                | Tür   | Açıklama                   |
| -------- | ------------------------ | ------ | ---------------------------- |
| 512      | `...`                  | prefix | Spread operatörü           |
| 300      | `                        | `      | infix                        |
| 300→5   | `??`                   | infix  | Null-coalescing              |
| 250      | `=>`                   | infix  | Arrow function               |
| 200      | `**`                   | infix  | Üs alma                     |
| 100      | `is`,`is not`        | infix  | Test operatörleri           |
| 60       | `*`,`/`,`//`,`%` | infix  | Çarpma, bölme              |
| 50→70   | `not`                  | prefix | Mantıksal değil            |
| 40→27   | `~`                    | infix  | String birleştirme          |
| 30       | `+`,`-`              | infix  | Toplama, çıkarma           |
| 25       | `..`                   | infix  | Dizi oluşturma              |
| 20       | Karşılaştırmalar     | infix  | `<`,`>`,`==`,`in`vb. |
| 18       | `b-and`                | infix  | Bitwise AND                  |
| 17       | `b-xor`                | infix  | Bitwise XOR                  |
| 16       | `b-or`                 | infix  | Bitwise OR                   |
| 15       | `and`                  | infix  | Mantıksal AND               |
| 12       | `xor`                  | infix  | Mantıksal XOR               |
| 10       | `or`                   | infix  | Mantıksal OR                |
| 5        | `?:`                   | infix  | Elvis operatörü            |
| 0        | `( )`                  | prefix | Grup ifadeleri               |

### ⚙️ Twig 4.0 Değişiklikleri

Twig 4.0’da bazı operatörlerin öncelikleri güncellendi (ör. `|`, `??`, `~`, `=>`).

---

### 🧮 Örnek: Öncelik Farkı

```twig
{{ 6 b-and 2 or 6 b-and 16 }}
```

PHP’ye şu şekilde çevrilir:

```php
(6 & 2) || (6 & 16)
```

Önceliği değiştirmek için parantez kullanabilirsiniz:

```twig
{% set greeting = 'Hello ' %}
{% set name = 'Fabien' %}

{{ greeting ~ name|lower }}   {# Hello fabien #}
{{ (greeting ~ name)|lower }} {# hello fabien #}
```

---

## ⚪ Boşluk Kontrolü (Whitespace Control)

Twig, PHP gibi, **etiketlerden sonraki ilk satır sonunu** otomatik olarak kaldırır.

Bunun dışında, boşluklar (boşluk, sekme, satır sonu vb.) olduğu gibi korunur.

### ✂️ Boşluk Kırpma (Trimming)

İki tür kırpma modu vardır:

1. **`-`** → Tüm boşlukları (satır sonları dahil) kaldırır
2. **`~`** → Satır sonları hariç diğer boşlukları kaldırır

Etiketlerin her iki tarafına veya tek tarafına eklenebilir:

```twig
{%- if true -%}
    {{- value -}}
{%- endif -%}
```

Bu örnek `no spaces` çıktısını üretir.

```twig
<li>
    {{ value }}    </li>
{# çıktı: <li>\n    no spaces    </li> #}

<li>
    {{- value }}    </li>
{# çıktı: <li>no spaces    </li> #}

<li>
    {{~ value }}    </li>
{# çıktı: <li>\nno spaces    </li> #}
```

---

## 🧩 Genişletilebilirlik (Extensions)

Twig genişletilebilir bir yapıya sahiptir.

Kendi eklentilerinizi (extensions) oluşturmak isterseniz, **Creating an Extension** bölümüne göz atın.
