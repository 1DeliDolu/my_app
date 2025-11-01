autoescape

Otomatik kaçışın etkinleştirilip etkinleştirilmediğine bakılmaksızın, bir şablonun belirli bir bölümünün kaçış uygulanarak veya uygulanmadan işlenmesini autoescape etiketiyle belirtebilirsiniz:

{% autoescape %}

Bu blok içindeki her şey

HTML stratejisi kullanılarak otomatik olarak kaçışa uğratılacaktır

{% endautoescape %}

{% autoescape 'html' %}

Bu blok içindeki her şey

HTML stratejisi kullanılarak otomatik olarak kaçışa uğratılacaktır

{% endautoescape %}

{% autoescape 'js' %}

Bu blok içindeki her şey

js kaçış stratejisi kullanılarak otomatik olarak kaçışa uğratılacaktır

{% endautoescape %}

{% autoescape false %}

Bu blok içindeki her şey olduğu gibi çıktılanacaktır

{% endautoescape %}

Otomatik kaçış etkinleştirildiğinde, açıkça güvenli olarak işaretlenmiş değerler dışında her şey varsayılan olarak kaçışa uğratılır. Bu değerler şablonda raw filtresi kullanılarak güvenli olarak işaretlenebilir:

{% autoescape %}

{{ safe_value|raw }}

{% endautoescape %}

Şablon verisi döndüren fonksiyonlar (örneğin macros ve parent) her zaman güvenli biçimlendirme döndürür.

Not

Twig, otomatik kaçış stratejisi escape filtresiyle uygulanan stratejiyle aynı olduğunda, escape filtresiyle zaten kaçışa uğratılmış bir değeri tekrar kaçışa uğratmayacak kadar akıllıdır.

Not

Twig statik ifadeleri kaçışa uğratmaz:

{% set hello = "Hello" %}

{{ hello }}

{{ "world" }}

"Hello world" olarak işlenecektir.

Not

Twig for Developers bölümü, otomatik kaçışın ne zaman ve nasıl uygulandığı hakkında daha fazla bilgi verir.
