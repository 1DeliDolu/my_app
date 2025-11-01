apply

apply etiketi, Twig filtrelerini bir şablon veri bloğuna uygulamanı sağlar:

```twig
{% apply upper %}
    Bu metin büyük harfe dönüştürülür
{% endapply %}
```

Ayrıca filtreleri zincirleyebilir ve onlara argümanlar da geçirebilirsin:

```twig
{% apply lower|escape('html') %}
    <strong>SOME TEXT</strong>
{% endapply %}
```

{# çıktısı "`<strong>`some text `</strong>`" olur #}
