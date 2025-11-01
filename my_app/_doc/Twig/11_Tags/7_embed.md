

**embed** etiketi, **include** ve **extends** etiketlerinin davranışlarını birleştirir. **include** etiketi gibi başka bir şablonun içeriğini dahil etmenizi sağlar, ancak aynı zamanda dahil edilen şablon içinde tanımlanan herhangi bir bloğu **extends** kullanırken olduğu gibi geçersiz kılmanıza da olanak tanır.

Bir gömülü (embedded) şablonu “mikro yerleşim iskeleti” olarak düşünebilirsiniz.

```twig
{% embed "teasers_skeleton.html.twig" %}
    {# Bu bloklar "teasers_skeleton.html.twig" içinde tanımlanmıştır #}
    {# ve burada geçersiz kılınmaktadır:                            #}
    {% block left_teaser %}
        Sol tanıtım kutusu için bazı içerikler
    {% endblock %}
    {% block right_teaser %}
        Sağ tanıtım kutusu için bazı içerikler
    {% endblock %}
{% endembed %}
```

**embed** etiketi, şablon kalıtımı fikrini içerik parçaları düzeyine taşır.

Şablon kalıtımı, alt şablonlar tarafından doldurulan “belge iskeletleri” oluşturmanızı sağlarken, **embed** etiketi daha küçük içerik birimleri için “iskeletler” oluşturmanıza, bunları istediğiniz yerde yeniden kullanmanıza ve doldurmanıza olanak tanır.

Kullanım durumu hemen anlaşılmayabileceği için basitleştirilmiş bir örnek inceleyelim.

Birden çok HTML sayfası tarafından paylaşılan, yalnızca bir adet `"content"` bloğu tanımlayan bir temel şablon hayal edin:

```
┌─── sayfa düzeni ─────────────────────┐
│                                     │
│           ┌── blok "content" ──┐   │
│           │                     │   │
│           │ (alt şablon içerik) │   │
│           └─────────────────────┘   │
│                                     │
└─────────────────────────────────────┘
```

Bazı sayfalar (`page_1` ve `page_2`) aynı içerik yapısını paylaşır — dikey olarak yığılmış iki kutu:

```
┌─── sayfa düzeni ─────────────────────┐
│                                     │
│           ┌── blok "content" ──┐   │
│           │ ┌─ blok "top" ───┐ │   │
│           │ └─────────────────┘ │   │
│           │ ┌─ blok "bottom" ┐ │   │
│           │ └─────────────────┘ │   │
│           └─────────────────────┘   │
│                                     │
└─────────────────────────────────────┘
```

Diğer sayfalar (`page_3` ve `page_4`) ise yan yana duran iki kutudan oluşan farklı bir yapı paylaşır:

```
┌─── sayfa düzeni ─────────────────────┐
│                                     │
│           ┌── blok "content" ──┐   │
│           │ ┌ blok "left" ┐    │   │
│           │ ┌ blok "right"┐    │   │
│           └────────────────────┘   │
└─────────────────────────────────────┘
```

**embed** etiketi olmadan bu şablonları iki şekilde oluşturabilirsiniz:

1. Ana düzen şablonunu genişleten iki “ara” temel şablon yaratmak:
   * Biri dikey kutular (page_1 ve page_2 için)
   * Diğeri yan yana kutular (page_3 ve page_4 için)
2. Üst/alt veya sol/sağ kutuların HTML işaretlemesini her sayfa şablonuna doğrudan gömmek.

Bu iki çözüm iyi ölçeklenmez çünkü her birinin büyük dezavantajları vardır:

* **İlk çözüm** , bu basit örnek için işe yarayabilir. Ancak bir kenar çubuğu (sidebar) eklediğinizi düşünün; bu da farklı, tekrar eden içerik yapıları içerebilir. Bu durumda tüm kombinasyonlar için ayrı ara şablonlar oluşturmanız gerekir.
* **İkinci çözüm** , ortak kodun kopyalanmasına yol açar. Bu, bakım zorluğu, senkronizasyon sorunları ve hatalara neden olur.

Bu tür bir durumda **embed** etiketi çok işe yarar. Ortak düzen kodu tek bir temel şablonda bulunabilir ve iki farklı içerik yapısı (“mikro yerleşimler”) ayrı şablonlara alınarak gerektiğinde gömülebilir.

`page_1.html.twig` şablonu örneği:

```twig
{% extends "layout_skeleton.html.twig" %}

{% block content %}
    {% embed "vertical_boxes_skeleton.html.twig" %}
        {% block top %}
            Üst kutu için içerik
        {% endblock %}

        {% block bottom %}
            Alt kutu için içerik
        {% endblock %}
    {% endembed %}
{% endblock %}
```

`vertical_boxes_skeleton.html.twig` şablonu:

```twig
<div class="top_box">
    {% block top %}
        Üst kutu varsayılan içeriği
    {% endblock %}
</div>

<div class="bottom_box">
    {% block bottom %}
        Alt kutu varsayılan içeriği
    {% endblock %}
</div>
```

`vertical_boxes_skeleton.html.twig` şablonunun amacı, kutuların HTML işaretlemesini soyutlamak ve tekrar kullanılabilir hale getirmektir.

**embed** etiketi, **include** etiketiyle tamamen aynı argümanları alır:

```twig
{% embed "base" with {'name': 'Fabien'} %}
    ...
{% endembed %}

{% embed "base" with {'name': 'Fabien'} only %}
    ...
{% endembed %}

{% embed "base" ignore missing %}
    ...
{% endembed %}
```

⚠️ **Uyarı**

Gömülü şablonların “isimleri” olmadığından, şablon adına dayalı otomatik kaçış (auto-escaping) stratejileri beklenildiği gibi çalışmayabilir.

Örneğin, bir HTML şablonunun içine CSS veya JavaScript şablonu gömüyorsanız, varsayılan otomatik kaçış stratejisini açıkça belirtmek için **autoescape** etiketini kullanın.
