### 🧭 Kodlama Standartları

📝 **Not:**

Twig CS fixer aracı, şablonlarınızı otomatik olarak düzeltmek için bu belgede açıklanan kodlama standartlarını kullanır.

Twig şablonları yazarken aşağıdaki resmi standartlara uymanız önerilir:

---

#### 🧩 Ayraçlar (Delimiters)

Boş olmayan içeriklerde ayraçların başlangıcından ( **{{** ,  **{%** ,  **{#** ) sonra ve bitişinden ( **}}** ,  **%}** ,  **#}** ) önce **tam olarak bir boşluk** bırakın:

```twig
{{ user }}
{# comment #} {##}
{% if user %}{% endif %}
```

---

#### ⚙️ Boşluk Kontrol Karakteri (Whitespace Control)

Boşluk kontrol karakterini (`-`) kullanırken, bu karakter ile ayraç arasında  **hiç boşluk bırakmayın** :

```twig
{{- user -}}
{#- comment -#} {#--#}
{%- if user -%}{%- endif -%}
```

---

#### ➕ Operatörler

Aşağıdaki operatörlerin **öncesinde ve sonrasında tam olarak bir boşluk** bırakın:

* Karşılaştırma operatörleri: `==`, `!=`, `<`, `>`, `>=`, `<=`
* Matematik operatörleri: `+`, `-`, `/`, `*`, `%`, `//`, `**`
* Mantıksal operatörler: `not`, `and`, `or`
* Diğerleri: `~`, `is`, `in`, `?:`

```twig
{{ 1 + 2 }}
{{ first_name ~ ' ' ~ last_name }}
{{ is_correct ? true : false }}
```

---

#### 🧱 Mappings ve Sequences

Mapping’lerde `:` karakterinden **sonra** ve sequence/mapping öğeleri arasında `,` karakterinden **sonra tam olarak bir boşluk** bırakın:

```twig
[1, 2, 3]
{'name': 'Fabien'}
```

---

#### 🧮 Parantezler

İfadelerde açılış parantezinden **sonra** ve kapanış parantezinden **önce** boşluk bırakmayın:

```twig
{{ 1 + (2 * 3) }}
```

---

#### 🧵 Stringler

String sınırlayıcılarının ( **'** ,  **"** ) **öncesinde ve sonrasında** boşluk bırakmayın:

```twig
{{ 'Twig' }}
{{ "Twig" }}
```

---

#### 🔗 Operatörler (|, ., .., [])

Aşağıdaki operatörlerin  **öncesinde ve sonrasında boşluk bırakmayın** :

```twig
{{ name|upper|lower }}
{{ user.name }}
{{ user[name] }}
{% for i in 1..12 %}{% endfor %}
```

---

#### 📞 Fonksiyon ve Filtre Çağrıları

Filtre ve fonksiyon çağrılarında kullanılan parantezlerin **öncesinde ve sonrasında** boşluk bırakmayın:

```twig
{{ name|default('Fabien') }}
{{ range(1..10) }}
```

---

#### 🧰 Dizi ve Mapping Tanımları

Sequence ve mapping tanımlarken köşeli veya süslü parantezlerin **öncesinde ve sonrasında** boşluk bırakmayın:

```twig
[1, 2, 3]
{'name': 'Fabien'}
```

---

#### 🧑‍💻 Makro Argümanları

Makro argüman tanımlarında `=` işaretinin **öncesinde ve sonrasında tam olarak bir boşluk** bırakın:

```twig
{% macro html_input(class = "input") %}
```

---

#### 🪶 İsimli Argümanlar

İsimli argümanlarda `:` işaretinden **sonra** tam olarak bir boşluk bırakın:

```twig
{{ html_input(class: "input") }}
```

---

#### 🐍 Değişken ve Fonksiyon İsimleri

Uygulama tarafından sağlanan veya şablonda oluşturulan tüm değişken adlarında, fonksiyon/filtre/test adlarında, argüman adlarında ve isimli argümanlarda **snake_case** kullanın:

```twig
{% set name = 'Fabien' %}
{% set first_name = 'Fabien' %}

{{ 'Fabien Potencier'|to_lower_case }}
{{ generate_random_number() }}

{% macro html_input(class_name) %}

{{ html_input(class_name: 'pwd') }}
```

---

#### ⬇️ Girintileme (Indentation)

Kodunuzu etiketler içinde uygun şekilde girintileyin.

Hedef dilin (örneğin HTML, PHP, vs.) girintileme standardını kullanın:

```twig
{% block content %}
    {% if true %}
        true
    {% endif %}
{% endblock %}
```

---

#### 🧷 Argüman Ayırma

Argüman isimleri ve değerlerini ayırmak için `=` yerine `:` kullanın:

```twig
{{ data|convert_encoding(from: 'iso-2022-jp', to: 'UTF-8') }}
```
