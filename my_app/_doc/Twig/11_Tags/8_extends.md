**extends** etiketi, bir şablonu başka bir şablondan türetmek (extend etmek) için kullanılır.

> **Not:**
>
> PHP’de olduğu gibi, Twig de çoklu kalıtımı desteklemez. Bu nedenle, her şablon işleminde yalnızca bir tane **extends** etiketi kullanılabilir.
>
> Ancak Twig, “yatay yeniden kullanım”ı (horizontal reuse) destekler.

Basit bir HTML iskelet belgesi tanımlayan bir temel şablon ( **base.html.twig** ) oluşturalım:

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

Bu örnekteki **block** etiketleri, alt şablonların doldurabileceği dört adet blok tanımlar.

**block** etiketi, şablon motoruna “bu bölge alt şablon tarafından geçersiz kılınabilir” bilgisini verir.

---

### 🔹 Alt Şablon (Child Template)

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
        Welcome on my awesome homepage.
    </p>
{% endblock %}
```

Burada anahtar unsur **extends** etiketidir. Bu etiket, şablon motoruna bu şablonun başka bir şablonu “genişlettiğini” bildirir.

Twig, bu şablonu işlerken önce üst (parent) şablonu bulur. **extends** etiketi, şablonun ilk satırında bulunmalıdır.

Alt şablon **footer** bloğunu tanımlamadığı için, üst şablondaki değer aynen kullanılır.

---

### 🔹 Aynı İsimli Birden Fazla Blok

Aynı şablon içinde aynı isimli birden fazla **block** etiketi tanımlayamazsınız.

Bunun nedeni, **block** etiketinin iki yönlü çalışmasıdır: Hem içeriğin yerleştirileceği bir “boşluk” sağlar hem de o boşluğu dolduran içeriği tanımlar.

Eğer aynı isimli iki blok olsaydı, üst şablon hangisini kullanacağını bilemezdi.

Bir bloğu birden fazla kez yazdırmak isterseniz **block()** fonksiyonunu kullanabilirsiniz:

```twig
<title>{% block title %}{% endblock %}</title>
<h1>{{ block('title') }}</h1>
{% block body %}{% endblock %}
```

---

### 🔹 Üst Bloğu Çağırmak (Parent Blocks)

Üst bloğun içeriğini göstermek için **parent()** fonksiyonu kullanılabilir.

Bu, üst bloğun çıktısını döndürür:

```twig
{% block sidebar %}
    <h3>Table Of Contents</h3>
    ...
    {{ parent() }}
{% endblock %}
```

---

### 🔹 İsimli Blok Kapanış Etiketleri

Okunabilirliği artırmak için Twig, **endblock** etiketinden sonra blok adının yazılmasına izin verir.

(“endblock” sonrasındaki isim, açılış bloğunun ismiyle aynı olmalıdır.)

```twig
{% block sidebar %}
    {% block inner_sidebar %}
        ...
    {% endblock inner_sidebar %}
{% endblock sidebar %}
```

---

### 🔹 Blok İç İçe Geçirme ve Kapsam (Scope)

Daha karmaşık düzenler için bloklar iç içe yerleştirilebilir.

Varsayılan olarak, bloklar dış kapsamda tanımlanan değişkenlere erişebilir:

```twig
{% for item in seq %}
    <li>{% block loop_item %}{{ item }}{% endblock %}</li>
{% endfor %}
```

---

### 🔹 Kısa Blok Sözdizimi (Block Shortcuts)

Az içeriğe sahip bloklar için kısa sözdizimi kullanılabilir.

Aşağıdaki iki örnek aynı işi yapar:

```twig
{% block title %}
    {{ page_title|title }}
{% endblock %}

{% block title page_title|title %}
```

---

### 🔹 Dinamik Kalıtım (Dynamic Inheritance)

Twig, dinamik kalıtımı destekler; yani temel şablon olarak bir değişken kullanılabilir:

```twig
{% extends some_var %}
```

Eğer değişken bir `\Twig\Template` veya `\Twig\TemplateWrapper` örneği içeriyorsa, Twig onu üst şablon olarak kullanır:

```twig
// {% extends layout %}

$layout = $twig->load('some_layout_template.html.twig');
$twig->display('template.html.twig', ['layout' => $layout]);
```

Ayrıca Twig, var olan ilkini kullanmak üzere birden fazla şablon listesi de alabilir:

```twig
{% extends ['layout.html.twig', 'base_layout.html.twig'] %}
```

---

### 🔹 Koşullu Kalıtım (Conditional Inheritance)

Üst şablon adı herhangi bir Twig ifadesi olabileceği için, kalıtım koşullu hale getirilebilir:

```twig
{% extends standalone ? "minimum.html.twig" : "base.html.twig" %}
```

Bu örnekte, `standalone` değişkeni **true** ise `"minimum.html.twig"`, aksi halde `"base.html.twig"` genişletilir.

---

### 🔹 Bloklar Nasıl Çalışır?

Bir blok, bir şablonun belirli bir bölümünün nasıl işlendiğini değiştirme olanağı sağlar, ancak etrafındaki mantığı etkilemez.

Örneğin:

```twig
{# base.html.twig #}
{% for post in posts %}
    {% block post %}
        <h1>{{ post.title }}</h1>
        <p>{{ post.body }}</p>
    {% endblock %}
{% endfor %}
```

Bu şablon, **block** etiketi olmasa da aynı çıktıyı üretir.

Blok, yalnızca alt şablonun bu kısmı geçersiz kılmasına izin verir:

```twig
{# child.html.twig #}
{% extends "base.html.twig" %}

{% block post %}
    <article>
        <header>{{ post.title }}</header>
        <section>{{ post.text }}</section>
    </article>
{% endblock %}
```

Bu durumda Twig, döngüyü işlerken alt şablondaki **post** bloğunu kullanır; yani şu şablonla aynı etkiyi yaratır:

```twig
{% for post in posts %}
    <article>
        <header>{{ post.title }}</header>
        <section>{{ post.text }}</section>
    </article>
{% endfor %}
```

---

### 🔹 Koşullu Bloklar

Bir blok **if** ifadesi içinde yer alabilir:

```twig
{% if posts is empty %}
    {% block head %}
        {{ parent() }}

        <meta name="robots" content="noindex, follow">
    {% endblock head %}
{% endif %}
```

Ancak bu, bloğun koşullu olarak tanımlandığı anlamına gelmez — sadece koşul sağlandığında render edilecek kısmı geçersiz kılınabilir hale getirir.

Eğer çıktının koşullu olarak gösterilmesini istiyorsanız şu şekilde yazmalısınız:

```twig
{% block head %}
    {{ parent() }}

    {% if posts is empty %}
        <meta name="robots" content="noindex, follow">
    {% endif %}
{% endblock head %}
```
