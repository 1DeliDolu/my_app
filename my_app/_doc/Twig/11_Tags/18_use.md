### ♻️ Use

📝 **Not**

Yatay yeniden kullanım (horizontal reuse), Twig’in gelişmiş bir özelliğidir ve normal şablonlarda nadiren ihtiyaç duyulur. Genellikle, kalıtım kullanmadan şablon bloklarını yeniden kullanılabilir hale getirmesi gereken projelerde kullanılır.

Şablon kalıtımı (template inheritance), Twig’in en güçlü özelliklerinden biridir, ancak **tekli kalıtımla** sınırlıdır; yani bir şablon yalnızca başka bir şablonu genişletebilir. Bu sınırlama, şablon kalıtımını anlamayı ve hata ayıklamayı kolaylaştırır:

```twig
{% extends "base.html.twig" %}

{% block title %}{% endblock %}
{% block content %}{% endblock %}
```

 **Yatay yeniden kullanım (horizontal reuse)** , çoklu kalıtımın (multiple inheritance) amacına ulaşmanın, ancak karmaşıklığını yaşamadan bir yoludur:

```twig
{% extends "base.html.twig" %}

{% use "blocks.html.twig" %}

{% block title %}{% endblock %}
{% block content %}{% endblock %}
```

`use` ifadesi, Twig’e `blocks.html.twig` dosyasında tanımlanan blokları mevcut şablona aktarmasını söyler (bu işlem makrolara benzer, ancak bloklar için kullanılır):

```twig
{# blocks.html.twig #}
{% block sidebar %}{% endblock %}
```

Bu örnekte, `use` ifadesi `sidebar` bloğunu ana şablona aktarır. Kod temelde aşağıdakine eşdeğerdir (aktarılan bloklar otomatik olarak çıktılanmaz):

```twig
{% extends "base.html.twig" %}

{% block sidebar %}{% endblock %}
{% block title %}{% endblock %}
{% block content %}{% endblock %}
```

---

🧩 **Not**

`use` etiketi yalnızca şu durumlarda bir şablonu içe aktarır:

* Şablon başka bir şablonu genişletmiyorsa (`extends` kullanmıyorsa),
* Makro tanımlamıyorsa,
* Ve gövdesi (body) boşsa.

  Ancak bu şablon başka şablonları kullanabilir.

🧩 **Not**

`use` ifadeleri, şablona aktarılan bağlamdan bağımsız olarak çözümlenir; bu nedenle, şablon referansı bir ifade (expression) olamaz.

---

### 🧱 Blokları Ezmek ve Yeniden Adlandırmak

Ana şablon, içe aktarılan herhangi bir bloğu da geçersiz kılabilir. Eğer şablon zaten bir `sidebar` bloğu tanımlıyorsa, `blocks.html.twig` içindeki aynı isimli blok yok sayılır.

İsim çakışmalarını önlemek için içe aktarılan blokları yeniden adlandırabilirsiniz:

```twig
{% extends "base.html.twig" %}

{% use "blocks.html.twig" with sidebar as base_sidebar, title as base_title %}

{% block sidebar %}{% endblock %}
{% block title %}{% endblock %}
{% block content %}{% endblock %}
```

---

### 🧭 `parent()` Fonksiyonu ile Üst Bloklara Erişim

`parent()` fonksiyonu, doğru kalıtım ağacını otomatik olarak belirler. Bu sayede, içe aktarılan bir şablonda tanımlanmış bloğu geçersiz kılarken kullanılabilir:

```twig
{% extends "base.html.twig" %}

{% use "blocks.html.twig" %}

{% block sidebar %}
    {{ parent() }}
{% endblock %}

{% block title %}{% endblock %}
{% block content %}{% endblock %}
```

Bu örnekte, `parent()` fonksiyonu `blocks.html.twig` şablonundaki `sidebar` bloğunu doğru şekilde çağırır.

---

💡 **İpucu**

Yeniden adlandırma, “ebeveyn” bloğu çağırarak kalıtımı taklit etmenize olanak tanır:

```twig
{% extends "base.html.twig" %}

{% use "blocks.html.twig" with sidebar as parent_sidebar %}

{% block sidebar %}
    {{ block('parent_sidebar') }}
{% endblock %}
```

---

🧩 **Not**

Bir şablonda istediğiniz kadar `use` ifadesi kullanabilirsiniz.

Eğer iki içe aktarılan şablon aynı bloğu tanımlarsa, **sonuncu** tanım geçerli olur.
