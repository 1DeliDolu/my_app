### 🧾 Types

🧩 **3.13**

`types` etiketi Twig 3.13 sürümünde eklenmiştir. Bu etiket deneyseldir ve kullanım ile geri bildirimlere bağlı olarak değişebilir.

Bir değişkenin türünü belirtmek için `types` etiketini kullanın:

```twig
{% types is_correct: 'boolean' %}
{% types score: 'number' %}
```

Veya birden fazla değişkeni tanımlayın:

```twig
{% types
    is_correct: 'boolean',
    score: 'number',
%}
```

Türleri `{}` içinde de belirtebilirsiniz:

```twig
{% types {
    is_correct: 'boolean',
    score: 'number',
} %}
```

İsteğe bağlı (optional) değişkenleri `?` soneki ekleyerek tanımlayın:

```twig
{% types {
    is_correct: 'boolean',
    score?: 'number',
} %}
```

Varsayılan olarak, bu etiket şablonun derlenmesini veya çalışma zamanı davranışını etkilemez.

Amacı, tasarımcıların ve geliştiricilerin bağlamda mevcut ve/veya gerekli değişkenleri belgelemelerini ve belirtmelerini sağlamaktır. Twig’in kendisi değişkenleri veya türlerini doğrulamaz, ancak bu etiket uzantıların bunu yapabilmesine olanak tanır.

Ayrıca, Twig uzantıları bu etiketleri analiz ederek şablonların derleme zamanı ve çalışma zamanı analizlerini gerçekleştirebilir.

📝 **Not**

Bir şablonda bildirilen türler o şablona özgüdür ve dahil edilen (`include`) şablonlara aktarılmamalıdır. Bunun nedeni, bir şablonun farklı yerlerden dahil edilebilmesi ve her birinin potansiyel olarak farklı değişken türlerine sahip olabilmesidir.

📝 **Not**

Tür dizelerinin (type strings) sözdizimi ve içeriği kasıtlı olarak kapsam dışında bırakılmıştır.
