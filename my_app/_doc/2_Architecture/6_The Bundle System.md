## Bundle Sistemi

Symfony’nin 4.0 sürümünden önce, uygulama kodunuzu kendi  **bundle** ’larınızla organize etmeniz önerilirdi. Ancak artık bu yaklaşım  **önerilmiyor** . Günümüzde bundle’lar yalnızca **birden fazla uygulama arasında kod ve özellik paylaşımı** yapmak için kullanılmalıdır.

🎥 **Video tercih eder misiniz?** Symfony Bundle Development adlı [screencast serisine](https://symfonycasts.com/) göz atabilirsiniz.

---

### 🧩 Bundle Nedir?

Bir bundle, diğer yazılımlardaki *plugin* (eklenti) yapısına benzer, hatta ondan daha güçlüdür.

Symfony çekirdek özellikleri bile bundle’lar olarak uygulanmıştır:

* `FrameworkBundle`
* `SecurityBundle`
* `DebugBundle`

  vb.

Ayrıca üçüncü taraf (third-party) bundle’lar aracılığıyla uygulamanıza yeni özellikler ekleyebilirsiniz.

---

### ⚙️ Bundle’ların Etkinleştirilmesi

Uygulamanızda kullandığınız bundle’lar, **ortama (environment)** göre `config/bundles.php` dosyasında etkinleştirilir:

```php
// config/bundles.php
return [
    // 'all' => true demek, bundle tüm ortamlar için aktif demektir
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    // ...

    // yalnızca 'dev' ortamında aktif
    Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true],
    // ...

    // yalnızca 'dev' ve 'test' ortamlarında aktif, 'prod'da kullanılamaz
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
    // ...
];
```

> 💡 Symfony Flex kullanan projelerde bundle’lar **kurulum veya kaldırma sırasında otomatik** olarak etkinleştirilir/devre dışı bırakılır.
>
> Bu durumda `bundles.php` dosyasını elle düzenlemenize gerek kalmaz.

---

### 🏗️ Yeni Bir Bundle Oluşturmak

Bu bölümde, yeni bir bundle oluşturmak için gerekli adımlar gösterilmiştir.

Yeni bundle’ımızın adı **AcmeBlogBundle** olacak. Buradaki `Acme` ifadesi örnek bir üretici adıdır.

Kendi adınızı veya şirketinizi temsil eden bir ad kullanabilirsiniz (örneğin `AbcBlogBundle`).

#### 1️⃣ Sınıfı oluşturun:

```php
// src/AcmeBlogBundle.php
namespace Acme\BlogBundle;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class AcmeBlogBundle extends AbstractBundle
{
}
```

> Eğer bundle’ınız eski Symfony sürümleriyle uyumlu olmalıysa `AbstractBundle` yerine `Bundle` sınıfını genişletin.

`AcmeBlogBundle` ismi, standart **Bundle adlandırma kurallarına** uyar.

Dilerseniz yalnızca `BlogBundle` adını da kullanabilirsiniz (dosya adı da `BlogBundle.php` olmalıdır).

Bu  **boş sınıf** , yeni bir bundle oluşturmak için  **yeterlidir** .

Her ne kadar genelde boş olsa da, bu sınıf bundle davranışını özelleştirmek için kullanılabilir.

#### 2️⃣ Bundle’ı etkinleştirin:

```php
// config/bundles.php
return [
    // ...
    Acme\BlogBundle\AcmeBlogBundle::class => ['all' => true],
];
```

Artık `AcmeBlogBundle` aktif hale geldi ve kullanıma hazır.

---

### 🗂️ Bundle Klasör Yapısı

Symfony, bundle’lar arasında tutarlılığı sağlamak için **önerilen bir klasör yapısı** sunar.

Bu yapı esnektir, ihtiyaçlarınıza göre uyarlanabilir:

```
assets/        → JavaScript, TypeScript, CSS/Sass, resimler vb.
config/        → Konfigürasyon dosyaları (örn. routes.php)
public/        → Derlenmiş veya kopyalanacak web varlıkları (assets:install ile bağlanır)
src/           → Bundle mantığıyla ilgili PHP sınıfları (örn. Controller/CategoryController.php)
templates/     → Twig şablonları (örn. category/show.html.twig)
tests/         → Test dosyaları
translations/  → Çeviriler (örn. AcmeBlogBundle.en.xlf)
```

> 🆕 Symfony 5 ile önerilen yapı değişti.
>
> Eski yapıyı görmek için Symfony 4.4 belgelerine göz atabilirsiniz.

---

### 🔁 Eski Dizin Yapısına Dönmek

Yeni `AbstractBundle` sınıfı varsayılan olarak **yeni yapı**yı kullanır.

Eğer **eski yapıyı** kullanmak istiyorsanız `getPath()` metodunu geçersiz kılın:

```php
class AcmeBlogBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return __DIR__;
    }
}
```

---

### 📦 PSR-4 Otomatik Yükleme (Autoloading)

Bundle’ınızın **PSR-4 standardına** uygun olmasını önerilir.

Yani namespace dizin yapısı ile eşleşmelidir.

`composer.json` dosyanızda aşağıdaki gibi tanımlayın:

```json
{
    "autoload": {
        "psr-4": {
            "Acme\\BlogBundle\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Acme\\BlogBundle\\Tests\\": "tests/"
        }
    }
}
```

---

✅ Artık `AcmeBlogBundle`, tüm ortamlarda etkinleştirilebilir, özelleştirilebilir ve Symfony projenizde kullanılabilir hale geldi.
