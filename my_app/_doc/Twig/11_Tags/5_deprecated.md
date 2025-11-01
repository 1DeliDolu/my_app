Twig, bir şablonda **deprecated** etiketi kullanıldığında (PHP’nin `trigger_error()` fonksiyonu aracılığıyla) bir kullanım dışı uyarısı (deprecation notice) üretir:

```twig
{# base.html.twig #}
{% deprecated 'The "base.html.twig" template is deprecated, use "layout.html.twig" instead.' %}
{% extends 'layout.html.twig' %}
```

Bir makroyu da aşağıdaki şekilde kullanım dışı ilan edebilirsiniz:

```twig
{% macro welcome(name) %}
    {% deprecated 'The "welcome" macro is deprecated, use "hello" instead.' %}

    ...
{% endmacro %}
```

Varsayılan olarak, kullanım dışı uyarıları sessize alınır ve ne görüntülenir ne de günlüklenir. Bunları nasıl ele alacağınızı öğrenmek için **Recipes** bölümüne bakın.

### 3.11

`package` ve `version` seçenekleri Twig 3.11 sürümünde eklenmiştir.

Kullanım dışı bildiriminin ait olduğu paketi ve bu bildirimin eklendiği sürümü isteğe bağlı olarak belirtebilirsiniz:

```twig
{% deprecated 'The "base.html.twig" template is deprecated, use "layout.html.twig" instead.' package='twig/twig' %}
{% deprecated 'The "base.html.twig" template is deprecated, use "layout.html.twig" instead.' package='twig/twig' version='3.11' %}
```

**Not**

Bir bloğu kullanım dışı ilan etmek için **deprecated** etiketini kullanmayın, çünkü bu durumda uyarı her zaman doğru şekilde tetiklenmeyebilir.
