### 🧩 Macro

Makrolar, normal programlama dillerindeki fonksiyonlara benzer. Şablon parçalarını tekrar etmeden yeniden kullanmak için kullanışlıdırlar.

Makrolar normal şablonlarda tanımlanır.

Makrolar aracılığıyla HTML formlarının nasıl render edileceğini tanımlayan genel bir yardımcı şablon (forms.twig) olduğunu hayal edin:

```twig
{% macro input(name, value, type = "text", size = 20) %}
    <input type="{{ type }}" name="{{ name }}" value="{{ value|e }}" size="{{ size }}"/>
{% endmacro %}

{% macro textarea(name, value, rows = 10, cols = 40) %}
    <textarea name="{{ name }}" rows="{{ rows }}" cols="{{ cols }}">{{ value|e }}</textarea>
{% endmacro %}
```

Her makro argümanı varsayılan bir değere sahip olabilir (burada, `type` belirtilmezse varsayılan değer `"text"` olur).

Makrolar, yerel PHP fonksiyonlarından birkaç yönden farklıdır:

* Bir makronun argümanları her zaman isteğe bağlıdır.
* Ek pozisyonel argümanlar bir makroya aktarılırsa, bunlar özel `varargs` değişkeninde bir değer listesi olarak toplanır.
* Ancak PHP fonksiyonlarında olduğu gibi, makroların mevcut şablon değişkenlerine erişimi yoktur.

💡 **İpucu:**

Tüm bağlamı özel `_context` değişkenini kullanarak bir argüman olarak geçirebilirsiniz.

---

### 📥 Makroları İçe Aktarma

Makroları içe aktarmanın iki yolu vardır:

* Makroları içeren tüm şablonu yerel bir değişkene aktarmak (`import` etiketiyle)
* Veya yalnızca belirli makroları şablondan aktarmak (`from` etiketiyle)

Tüm makroları bir şablondan yerel bir değişkene aktarmak için `import` etiketini kullanın:

```twig
{% import "forms.html.twig" as forms %}
```

Yukarıdaki `import` çağrısı `forms.html.twig` dosyasını (yalnızca makrolar veya hem şablon hem de makrolar içerebilir) içe aktarır ve makroları `forms` adlı yerel değişkenin özellikleri olarak kullanılabilir hale getirir.

Artık makrolar mevcut şablonda istenildiği gibi çağrılabilir:

```twig
<p>{{ forms.input('username') }}</p>
<p>{{ forms.input('password', null, 'password') }}</p>

{# Named argument kullanımı #}
<p>{{ forms.input(name: 'password', type: 'password') }}</p>
```

Alternatif olarak, `from` etiketiyle şablondan isimleri doğrudan mevcut ad alanına aktarabilirsiniz:

```twig
{% from 'forms.html.twig' import input as input_field, textarea %}

<p>{{ input_field('password', '', 'password') }}</p>
<p>{{ input_field(name: 'password', type: 'password') }}</p>
<p>{{ textarea('comment') }}</p>
```

⚠️ **Dikkat:**

`from` ile içe aktarılan makrolar fonksiyon gibi çağrılır, bu yüzden mevcut fonksiyonları gölgeleyebilirler:

```twig
{% from 'forms.html.twig' import input as include %}

{# "include" artık makroya referans eder, Twig'in dahili "include" fonksiyonuna değil #}
{{ include() }}
```

💡 **İpucu:**

Makro kullanımları ve tanımları aynı şablondaysa, makroları içe aktarmanıza gerek yoktur; onlar otomatik olarak özel `_self` değişkeni altında kullanılabilir:

```twig
<p>{{ _self.input('password', '', 'password') }}</p>

{% macro input(name, value, type = "text", size = 20) %}
    <input type="{{ type }}" name="{{ name }}" value="{{ value|e }}" size="{{ size }}"/>
{% endmacro %}
```

---

### 📚 Makro Kapsamı (Scoping)

Kapsam kuralları, makroları `import` veya `from` ile içe aktarmanızdan bağımsız olarak aynıdır.

* İçe aktarılan makrolar  **her zaman geçerli şablona özeldir** .

  Yani makrolar, mevcut şablonda tanımlı tüm bloklarda ve diğer makrolarda kullanılabilir; ancak dahil edilen (`include`) veya alt (`extends`) şablonlarda kullanılmaz. Her şablonda açıkça yeniden içe aktarmanız gerekir.
* İçe aktarılan makrolar, `embed` etiketlerinin gövdesinde mevcut değildir; bu etiketin içinde açıkça yeniden içe aktarılmaları gerekir.
* Bir `import` veya `from` çağrısı bir `block` etiketi içinde yapılırsa, içe aktarılan makrolar yalnızca o blokta tanımlanır ve şablon seviyesinde aynı isimdeki makroları gölgeler.

---

### 🧾 Bir Makronun Tanımlı Olup Olmadığını Kontrol Etme

Bir makronun tanımlı olup olmadığını `defined` testiyle kontrol edebilirsiniz:

```twig
{% import "macros.html.twig" as macros %}
{% from "macros.html.twig" import hello %}

{% if macros.hello is defined -%}
    OK
{% endif %}

{% if hello is defined -%}
    OK
{% endif %}
```

---

### 🏷️ İsimlendirilmiş Makro Bitiş Etiketleri

Twig, daha iyi okunabilirlik için bitiş etiketinden sonra makronun adını yazmanıza izin verir

(`endmacro` kelimesinden sonraki isim, makro adıyla aynı olmalıdır):

```twig
{% macro input() %}
    ...
{% endmacro input %}
```
