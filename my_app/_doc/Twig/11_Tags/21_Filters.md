Elbette — aşağıda her **Twig filtresi**nin kısa ve sade açıklamasını bulabilirsin 👇

---

### 🧮 **abs**

Bir sayının mutlak değerini döndürür. Negatif sayılar pozitif hale gelir.

### 📦 **batch**

Bir diziyi belirtilen büyüklükte alt dizilere ayırır (örneğin listeyi satırlara bölmek için).

### 🔠 **capitalize**

Metnin ilk harfini büyük, geri kalanını küçük yapar.

### 🧩 **column**

Bir dizideki her elemandan belirli bir alanın değerini çeker (örneğin `users|column('name')`).

### 🔄 **convert_encoding**

Bir metni bir karakter kodlamasından diğerine dönüştürür (örneğin UTF-8 → ISO-8859-1).

### 🌍 **country_name**

ISO ülke kodunu ülke adına çevirir (örn. “US” → “United States”).

### 💰 **currency_name**

Para birimi kodunu tam ismine çevirir (örn. “USD” → “US Dollar”).

### 💲 **currency_symbol**

Para birimi kodunu sembolüne dönüştürür (örn. “USD” → “$”).

### 🖼️ **data_uri**

Bir dosya veya metni Base64 kodlayarak veri URI’sine dönüştürür.

### 🕒 **date**

Bir tarih/saat değerini belirtilen biçime göre biçimlendirir.

### ⏰ **date_modify**

Bir tarih nesnesini verilen zaman farkına göre değiştirir (örneğin +1 day).

### ⚙️ **default**

Bir değişken tanımsız veya boşsa varsayılan bir değer döndürür.

### 🧹 **escape**

Metni HTML, JS veya başka bir bağlam için güvenli hale getirir (XSS koruması).

### 🔍 **filter**

Bir diziyi belirli bir koşulu sağlayan öğelerle sınırlar (örnek: `|filter(u => u.active)`).

### 🔎 **find**

Bir dizide veya metinde belirli bir koşulu sağlayan ilk öğeyi döndürür.

### ⬆️ **first**

Bir dizinin veya metnin ilk elemanını döndürür.

### 🧾 **format**

Bir şablon metne değişkenleri yerleştirir (örnek: `"Hello %s"|format(name)`).

### 💵 **format_currency**

Bir sayıyı yerel para biçiminde gösterir.

### 📅 **format_date**

Bir tarihi yerel biçime göre biçimlendirir.

### 🕓 **format_datetime**

Tarih ve saati yerel biçimde birleştirir.

### 🔢 **format_number**

Sayıları yerel biçimde biçimlendirir (örneğin 1,000 yerine 1 000).

### ⏱️ **format_time**

Sadece saat bilgisini yerel biçimde biçimlendirir.

### 🔁 **html_to_markdown**

HTML kodunu Markdown biçimine dönüştürür.

### 🧱 **inky_to_html**

Inky e-posta bileşenlerini standart HTML’e dönüştürür.

### 💅 **inline_css**

HTML içindeki CSS stillerini inline hale getirir (özellikle e-posta şablonlarında).

### 🔗 **join**

Bir diziyi belirtilen ayırıcıyla birleştirerek metin oluşturur.

### 📜 **json_encode**

Bir değeri JSON biçimine dönüştürür.

### 🗝️ **keys**

Bir dizinin anahtarlarını döndürür.

### 🗣️ **language_name**

Dil kodunu tam isme çevirir (örn. “en” → “English”).

### ⬇️ **last**

Bir dizinin veya metnin son elemanını döndürür.

### 📏 **length**

Bir dizinin veya metnin uzunluğunu döndürür.

### 🌐 **locale_name**

Yerel ayar kodunu tam açıklamaya dönüştürür (örn. “fr_FR” → “French (France)”).

### 🔡 **lower**

Tüm harfleri küçük harfe çevirir.

### 🧮 **map**

Bir dizideki her öğeyi dönüştürmek için işlev uygular (örneğin `users|map(u => u.name)`).

### 🪶 **markdown_to_html**

Markdown biçimini HTML’e dönüştürür.

### 🔗 **merge**

İki diziyi birleştirir.

### 📄 **nl2br**

Yeni satır karakterlerini `<br>` HTML etiketiyle değiştirir.

### 🔢 **number_format**

Bir sayıyı belirli biçimde (ondalık, binlik ayırıcıyla) biçimlendirir.

### 👥 **plural**

Metnin çoğul biçimini üretir (i18n desteğiyle).

### 🧾 **raw**

Veriyi hiçbir biçimde escape etmeden olduğu gibi gösterir.

### 🧠 **reduce**

Bir diziyi tek bir değere indirger (örnek: toplam hesaplamak).

### 🔤 **replace**

Metin içinde belirtilen karakterleri/değerleri değiştirir.

### 🔄 **reverse**

Dizinin veya metnin sırasını tersine çevirir.

### ⚪ **round**

Sayıyı belirtilen basamakta yuvarlar.

### 🎲 **shuffle**

Bir dizinin elemanlarını rastgele karıştırır.

### 👤 **singular**

Bir metnin tekil biçimini döndürür.

### ✂️ **slice**

Bir dizinin veya metnin belirli bölümünü alır.

### 🪶 **slug**

Metni URL-dostu biçime dönüştürür (boşlukları tireye çevirir, özel karakterleri temizler).

### ↕️ **sort**

Bir diziyi sıralar.

### ⚡ **spaceless**

HTML’deki gereksiz boşlukları kaldırır.

### 🪓 **split**

Bir metni belirtilen ayırıcıya göre böler.

### 🧼 **striptags**

HTML etiketlerini metinden kaldırır.

### 🌍 **timezone_name**

Zaman dilimi kodunu tam ada dönüştürür (örn. “Europe/Paris”).

### 🏷️ **title**

Her kelimenin ilk harfini büyük yapar.

### ✂️ **trim**

Metnin başındaki ve sonundaki boşlukları kaldırır.

### 🔤 **u**

Unicode karakter işlemleri yapmayı sağlayan güçlü bir yardımcı (örneğin `|u.upper`).

### 🔠 **upper**

Tüm harfleri büyük harfe çevirir.

### 🌐 **url_encode**

Metni URL içinde güvenli olacak şekilde kodlar.
