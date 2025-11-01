Elbette — aşağıda her **Twig fonksiyonu**nun kısa ve anlaşılır açıklamasını bulabilirsin 👇

---

### 🧩 **attribute**

Bir değişkenin özniteliğine veya anahtarına dinamik olarak erişmek için kullanılır (örnek: `attribute(user, 'name')`).

### 🧱 **block**

Belirli bir `block` içeriğini başka bir yerden çağırır veya görüntüler.

### 🧭 **constant**

Bir PHP sabitinin (constant) değerini döndürür (örnek: `constant('PHP_VERSION')`).

### 🌍 **country_names**

Tüm ülke kodlarını ve bunlara karşılık gelen ülke adlarını döndürür.

### 🕐 **country_timezones**

Belirtilen ülkenin kullanılabilir zaman dilimlerini listeler.

### 💰 **currency_names**

Tüm para birimlerinin kod–isim eşleşmelerini döndürür (örn. USD → US Dollar).

### 🔁 **cycle**

Bir liste içinde döngüsel olarak değer döndürür; genellikle `for` döngülerinde renk veya stil değişimi için kullanılır.

### 🕓 **date**

Bir tarih nesnesi oluşturur veya biçimlendirir; `now`, `"+1 day"` gibi ifadeleri destekler.

### 🧠 **dump**

Bir değişkenin içeriğini hata ayıklama (debugging) amaçlı olarak gösterir.

### ⚙️ **enum**

Bir PHP `enum` sınıfına erişim sağlar.

### 📜 **enum_cases**

Belirli bir `enum` içindeki tüm olası durumları (cases) döndürür.

### 🏷️ **html_classes**

HTML `class` öznitelikleri oluşturur; boş veya `false` değerleri otomatik olarak atlar.

### 🎨 **html_cva**

Koşullu olarak HTML class'larını birleştiren yardımcı işlevdir (özellikle component varyantlarında).

### 📄 **include**

Belirtilen Twig şablonunu çağırır ve çıktısını döndürür.

### 🗣️ **language_names**

Tüm dil kodlarını ve bunların isim karşılıklarını döndürür.

### 🌐 **locale_names**

Tüm locale (yerel ayar) kodlarını ve adlarını döndürür.

### 🔼 **max**

Bir dizideki en büyük değeri döndürür.

### 🔽 **min**

Bir dizideki en küçük değeri döndürür.

### 🧬 **parent**

Bir alt şablondan üst şablondaki `block` içeriğini çağırır (`{{ parent() }}` gibi).

### 🎲 **random**

Bir diziden rastgele bir eleman veya bir aralıktan rastgele sayı döndürür.

### 📈 **range**

Belirtilen başlangıç ve bitiş değerleri arasında bir sayı dizisi oluşturur.

### 🧾 **script_names**

Kullanılabilir script (dil veya alfabe) adlarını listeler (örn. Latin, Cyrillic).

### 🧮 **source**

Bir şablon dosyasının kaynak içeriğini döndürür (örnek: `source('base.html.twig')`).

### 🧷 **template_from_string**

Dize (string) biçiminde verilen Twig kodundan geçici bir şablon oluşturur.

### 🕰️ **timezone_names**

Tüm zaman dilimlerinin adlarını döndürür (örn. “Europe/Paris”, “America/New_York”).
