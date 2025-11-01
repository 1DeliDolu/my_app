# Contracts Bileşeni

**Contracts (Sözleşmeler)** bileşeni, Symfony bileşenlerinden çıkarılmış bir dizi soyutlama (abstraction) sağlar. Bu soyutlamalar, Symfony bileşenlerinin kanıtlanmış faydalı kavramlarını temel alır ve halihazırda sağlam, üretim ortamında test edilmiş uygulamalara sahiptir.

---

## 🚀 Kurulum

Contracts, ayrı paketler olarak sağlanır. Bu sayede, projenizin gerçekten ihtiyaç duyduğu paketleri seçerek kurabilirsiniz:

```bash
composer require symfony/cache-contracts
composer require symfony/event-dispatcher-contracts
composer require symfony/deprecation-contracts
composer require symfony/http-client-contracts
composer require symfony/service-contracts
composer require symfony/translation-contracts
```

Symfony uygulaması dışında bu bileşeni kurarsanız, Composer’ın sağladığı sınıf otomatik yükleme mekanizmasını etkinleştirmek için `vendor/autoload.php` dosyasını projenize dahil etmeniz gerekir. Daha fazla bilgi için ilgili makaleye bakın.

---

## 🧩 Kullanım

Bu paketteki soyutlamalar, **zayıf bağlılık (loose coupling)** ve **birlikte çalışabilirlik (interoperability)** elde etmek için kullanışlıdır. Sağlanan arayüzleri (interface) **type hint** olarak kullanarak, bu sözleşmelere (contracts) uyan herhangi bir uygulamayı yeniden kullanabilirsiniz.

Bu uygulama bir Symfony bileşeni olabileceği gibi, PHP topluluğu tarafından geliştirilen başka bir paket de olabilir.

Bazı arayüzler, **autowiring** ile birleştirilerek sınıflarınıza otomatik olarak servislerin enjekte edilmesini sağlar.

Diğer bazı arayüzler ise, **etiketleme (labeling)** amacıyla kullanılır. Böylece,  **autoconfiguration** , **manuel servis etiketleme** veya çerçevenizin sunduğu diğer yöntemlerle belirli davranışların etkinleştirileceğini belirtebilirsiniz.

---

## ⚙️ Tasarım İlkeleri

Contracts aşağıdaki tasarım prensiplerine göre oluşturulmuştur:

* Her biri kendi **alt ad alanında (sub-namespace)** olmak üzere, alanlara (domain) göre bölünmüştür;
* Küçük ve tutarlı PHP arayüzleri, trait’ler, açıklayıcı docblock’lar ve gerekliyse referans test setlerinden oluşur;
* Her contract, **kanıtlanmış bir uygulamaya** sahip olmalıdır;
* Symfony bileşenleriyle **geriye dönük uyumlu (backward compatible)** olmalıdır.

Belirli sözleşmeleri (contracts) uygulayan paketler, bunu `composer.json` dosyalarının `provide` bölümünde **`symfony/*-implementation`** kuralını izleyerek belirtmelidir.

Örneğin:

```json
{
    "...": "...",
    "provide": {
        "symfony/cache-implementation": "3.0"
    }
}
```

---

## ❓ Sıkça Sorulan Sorular

### 🔹 PHP-FIG PSR’larından Farkı Nedir?

Uygun olduğunda, Symfony Contracts paketleri  **PHP-FIG’in PSR standartlarını temel alır** .

Ancak, PHP-FIG farklı hedeflere ve süreçlere sahiptir.

Symfony Contracts’un amacı, Symfony uygulamalarıyla uyumlu olmanın yanı sıra, **kendi başına da faydalı soyutlamalar** sağlamaktır.

---

📄 Bu çalışma ve kod örnekleri **Creative Commons BY-SA 3.0** lisansı altında sunulmuştur.
