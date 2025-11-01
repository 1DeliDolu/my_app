
### 🧮 Set

Kod blokları içinde değişkenlere değer atayabilirsiniz. Atamalar `set` etiketiyle yapılır ve birden fazla hedef içerebilir.

Aşağıda, `name` değişkenine `Fabien` değerinin nasıl atanacağını görebilirsiniz:

```twig
{% set name = 'Fabien' %}
```

`set` çağrısından sonra, `name` değişkeni diğer tüm değişkenler gibi şablonda kullanılabilir:

```twig
{# Fabien görüntüler #}
{{ name }}
```

Atanan değer herhangi bir geçerli Twig ifadesi olabilir:

```twig
{% set numbers = [1, 2] %}
{% set user = {'name': 'Fabien'} %}
{% set name = 'Fabien' ~ ' ' ~ 'Potencier' %}
```

Bir blok içinde birden fazla değişkene değer atanabilir:

```twig
{% set first, last = 'Fabien', 'Potencier' %}
```

Bu ifade aşağıdakine eşdeğerdir:

```twig
{% set first = 'Fabien' %}
{% set last = 'Potencier' %}
```

`set` etiketi ayrıca metin parçalarını “yakalamak” için de kullanılabilir:

```twig
{% set content %}
    <div id="pagination">
        ...
    </div>
{% endset %}
```

⚠️ **Dikkat**

Otomatik çıktı kaçışını (automatic output escaping) etkinleştirirseniz, Twig yalnızca metin parçaları yakalanırken içeriği güvenli olarak değerlendirir.

📝 **Not**

Twig’de döngüler kapsamlıdır; bu nedenle bir `for` döngüsü içinde tanımlanan bir değişken, döngü dışında erişilebilir değildir:

```twig
{% for item in items %}
    {% set value = item %}
{% endfor %}

{# value DEĞİL #}
```

Değişkene döngü dışında erişmek istiyorsanız, onu döngüden önce tanımlayın:

```twig
{% set value = "" %}
{% for item in items %}
    {% set value = item %}
{% endfor %}

{# value erişilebilir #}
```
