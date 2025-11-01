**for** etiketi, bir dizideki veya eşlemedeki (mapping) her öğe üzerinde döngü kurmak için kullanılır.

Örneğin, `users` adlı bir değişkende verilen kullanıcı listesini göstermek için:

```twig
<h1>Members</h1>
<ul>
    {% for user in users %}
        <li>{{ user.username|e }}</li>
    {% endfor %}
</ul>
```

> **Not:**
>
> Bir dizi (sequence) veya eşleme (mapping), bir dizi (array) ya da **Traversable** arayüzünü uygulayan bir nesne olabilir.

---

### 🔹 Sayılar Üzerinde Döngü

Bir sayı dizisi üzerinde dönmek istiyorsanız, `..` operatörünü kullanabilirsiniz:

```twig
{% for i in 0..10 %}
    * {{ i }}
{% endfor %}
```

Yukarıdaki kod, 0’dan 10’a kadar tüm sayıları yazdırır.

---

### 🔹 Harfler Üzerinde Döngü

```twig
{% for letter in 'a'..'z' %}
    * {{ letter }}
{% endfor %}
```

`..` operatörü, her iki tarafında da herhangi bir ifade alabilir:

```twig
{% for letter in 'a'|upper..'z'|upper %}
    * {{ letter }}
{% endfor %}
```

💡 **İpucu:**

1’den farklı adımlar kullanmak isterseniz, `range` fonksiyonunu kullanabilirsiniz.

---

### 🔹 loop Değişkeni

Bir **for** döngüsü içinde bazı özel değişkenlere erişebilirsiniz:

| Değişken         | Açıklama                                                             |
| ------------------ | ---------------------------------------------------------------------- |
| `loop.index`     | Döngünün şu anki yinelemesi (1’den başlar)                       |
| `loop.index0`    | Döngünün şu anki yinelemesi (0’dan başlar)                       |
| `loop.revindex`  | Döngünün sonundan itibaren kalan yineleme sayısı (1’den başlar) |
| `loop.revindex0` | Döngünün sonundan itibaren kalan yineleme sayısı (0’dan başlar) |
| `loop.first`     | İlk yinelemedeyse**true**                                       |
| `loop.last`      | Son yinelemedeyse**true**                                        |
| `loop.length`    | Dizideki toplam öğe sayısı                                         |
| `loop.parent`    | Üst bağlam (parent context)                                          |

```twig
{% for user in users %}
    {{ loop.index }} - {{ user.username }}
{% endfor %}
```

> **Not:**
>
> `loop.length`, `loop.revindex`, `loop.revindex0` ve `loop.last` değişkenleri yalnızca PHP dizileri veya **Countable** arayüzünü uygulayan nesneler için kullanılabilir.

---

### 🔹 else Bloğu

Dizi boş olduğunda alternatif içerik göstermek için **else** bloğu kullanabilirsiniz:

```twig
<ul>
    {% for user in users %}
        <li>{{ user.username|e }}</li>
    {% else %}
        <li><em>no user found</em></li>
    {% endfor %}
</ul>
```

---

### 🔹 Anahtarlar Üzerinde Döngü

Varsayılan olarak döngü, dizinin **değerleri** üzerinde çalışır.

Anahtarlar üzerinde dönmek için **keys** filtresini kullanabilirsiniz:

```twig
<h1>Members</h1>
<ul>
    {% for key in users|keys %}
        <li>{{ key }}</li>
    {% endfor %}
</ul>
```

---

### 🔹 Anahtar ve Değer Üzerinde Döngü

Hem anahtara hem değere erişebilirsiniz:

```twig
<h1>Members</h1>
<ul>
    {% for key, user in users %}
        <li>{{ key }}: {{ user.username|e }}</li>
    {% endfor %}
</ul>
```

---

### 🔹 Alt Küme Üzerinde Döngü

Belirli bir alt küme üzerinde dönmek için **slice** filtresini kullanabilirsiniz:

```twig
<h1>Top Ten Members</h1>
<ul>
    {% for user in users|slice(0, 10) %}
        <li>{{ user.username|e }}</li>
    {% endfor %}
</ul>
```

---

### 🔹 Bir Dizge (String) Üzerinde Döngü

Bir dizgenin karakterleri üzerinde dönmek için **split** filtresini kullanın:

```twig
<h1>Characters</h1>
<ul>
    {% for char in "諺 / ことわざ"|split('') -%}
        <li>{{ char }}</li>
    {%- endfor %}
</ul>
```
