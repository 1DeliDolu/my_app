### 🧭 With

`with` etiketi, yeni bir **iç kapsam (inner scope)** oluşturmak için kullanılır.

Bu kapsam içinde tanımlanan değişkenler, kapsam dışından görülemez:

```twig
{% with %}
    {% set value = 42 %}
    {{ value }} {# value burada 42'dir #}
{% endwith %}
```

`value` artık bu bloğun dışında görünmez.

---

Kapsamın başında değişkenleri tanımlamak yerine, `with` etiketinde tanımlamak istediğiniz değişkenleri bir eşleme (mapping) olarak geçebilirsiniz.

Önceki örnek aşağıdakine eşdeğerdir:

```twig
{% with {value: 42} %}
    {{ value }} {# value burada 42'dir #}
{% endwith %}
```

`value` bu bloğun dışında artık görünmez.

---

Aşağıdaki gibi, bir eşlemeye çözümlenen herhangi bir ifadeyle de çalışır:

```twig
{% set vars = {value: 42} %}
{% with vars %}
    ...
{% endwith %}
```

---

Varsayılan olarak, iç kapsam dış kapsamın bağlamına erişebilir.

Bu davranışı devre dışı bırakmak için `only` anahtar kelimesini ekleyebilirsiniz:

```twig
{% set zero = 0 %}
{% with {value: 42} only %}
    {# yalnızca value tanımlıdır #}
    {# zero tanımlı değildir #}
{% endwith %}
```
