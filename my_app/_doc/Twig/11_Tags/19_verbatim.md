### 📝 Verbatim

`verbatim` etiketi, Twig tarafından ayrıştırılmaması (parse edilmemesi) gereken ham metin bölümlerini işaretler.

Örneğin, bir şablonda Twig sözdizimini örnek olarak göstermek istiyorsanız şu kodu kullanabilirsiniz:

```twig
{% verbatim %}
    <ul>
    {% for item in seq %}
        <li>{{ item }}</li>
    {% endfor %}
    </ul>
{% endverbatim %}
```
