**include** deyimi, bir şablonu dahil eder ve o dosyanın işlenmiş içeriğini çıktılar:

```twig
{% include 'header.html.twig' %}
    Body
{% include 'footer.html.twig' %}
```

**Not**

Aynı özellikleri biraz daha esnek bir şekilde sunduğu için include fonksiyonunun kullanılması tavsiye edilir:

* **include fonksiyonu** anlamsal olarak daha “doğrudur” (bir şablonun dahil edilmesi, mevcut kapsamda işlenmiş içeriğini çıktılar; bir etiket bir şey görüntülememelidir);
* **include fonksiyonu** daha “birleştirilebilir”dir:

```twig
{# Bir şablonun işlenmiş halini bir değişkende saklama #}
{% set content %}
    {% include 'template.html.twig' %}
{% endset %}
{# vs #}
{% set content = include('template.html.twig') %}
```

```twig
{# Bir şablonun işlenmiş haline filtre uygulama #}
{% apply upper %}
    {% include 'template.html.twig' %}
{% endapply %}
{# vs #}
{{ include('template.html.twig')|upper }}
```

**include** fonksiyonu, isimlendirilmiş argümanlar sayesinde argümanlar için belirli bir sıra dayatmaz.

Dahil edilen şablonlar, aktif bağlamın değişkenlerine erişebilir.

Eğer **filesystem loader** kullanıyorsanız, şablonlar onun tarafından tanımlanan yollarda aranır.

`with` anahtar sözcüğünden sonra ek değişkenler geçirerek ekstra değişkenler ekleyebilirsiniz:

```twig
{# template.html.twig, mevcut bağlamdaki değişkenlere ve ek olarak sağlananlara erişebilir #}
{% include 'template.html.twig' with {'name': 'Fabien'} %}

{% set vars = {'name': 'Fabien'} %}
{% include 'template.html.twig' with vars %}
```

**only** anahtar sözcüğünü ekleyerek bağlama erişimi devre dışı bırakabilirsiniz:

```twig
{# sadece name değişkeni erişilebilir olur #}
{% include 'template.html.twig' with {'name': 'Fabien'} only %}
{# hiçbir değişkene erişim olmayacaktır #}
{% include 'template.html.twig' only %}
```

**İpucu**

Bir son kullanıcı tarafından oluşturulan bir şablonu dahil ederken, onu sandbox içinde çalıştırmayı düşünmelisiniz. Daha fazla bilgi için Twig Sandbox bölümüne bakın.

Şablon adı, herhangi bir geçerli Twig ifadesi olabilir:

```twig
{% include some_var %}
{% include ajax ? 'ajax.html.twig' : 'not_ajax.html.twig' %}
```

Ve eğer ifade bir **\Twig\Template** veya **\Twig\TemplateWrapper** örneğine değerlendiriliyorsa, Twig bunu doğrudan kullanır:

```php
// {% include template %}

$template = $twig->load('some_template.html.twig');

$twig->display('template.html.twig', ['template' => $template]);
```

Bir include ifadesini **ignore missing** ile işaretleyebilirsiniz; bu durumda dahil edilecek şablon mevcut değilse Twig ifadeyi yok sayar. Bu ifade, şablon adından hemen sonra yer almalıdır. Geçerli bazı örnekler:

```twig
{% include 'sidebar.html.twig' ignore missing %}
{% include 'sidebar.html.twig' ignore missing with {'name': 'Fabien'} %}
{% include 'sidebar.html.twig' ignore missing only %}
```

Ayrıca, dahil edilmeden önce varlıkları kontrol edilen bir şablon listesi de sağlayabilirsiniz. Mevcut olan ilk şablon dahil edilir:

```twig
{% include ['page_detailed.html.twig', 'page.html.twig'] %}
```

Eğer **ignore missing** verilmişse, hiçbir şablon mevcut değilse hiçbir şey işlenmeden geçilir; aksi halde bir istisna fırlatılır.
