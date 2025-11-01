### 🛡️ Sandbox

⚠️ **Uyarı**

`sandbox` etiketi Twig 3.15 itibarıyla kullanımdan kaldırılmıştır. Bunun yerine `include` fonksiyonunun `sandboxed` seçeneğini kullanın.

`sandbox` etiketi, Twig ortamında sandboxing küresel olarak etkinleştirilmediğinde, dahil edilen bir şablon için sandboxing modunu etkinleştirmek amacıyla kullanılabilir:

```twig
{% sandbox %}
    {% include 'user.html.twig' %}
{% endsandbox %}
```

⚠️ **Uyarı**

`sandbox` etiketi yalnızca sandbox uzantısı etkinleştirildiğinde kullanılabilir (bkz. Twig for Developers bölümü).

📝 **Not**

`sandbox` etiketi yalnızca bir `include` etiketini sandbox’a almak için kullanılabilir ve bir şablonun belirli bir bölümünü sandbox’a almak için kullanılamaz.

Aşağıdaki örnek çalışmayacaktır:

```twig
{% sandbox %}
    {% for i in 1..2 %}
        {{ i }}
    {% endfor %}
{% endsandbox %}
```
