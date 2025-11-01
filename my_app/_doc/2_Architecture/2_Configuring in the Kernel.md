## ⚙️ Kernel İçinde Yapılandırma

Symfony uygulamaları, varsayılan olarak **`src/Kernel.php`** konumunda bulunan bir **kernel sınıfı** tanımlar.

Bu sınıf, uygulamanın çekirdeğini oluşturur ve birçok yapılandırılabilir seçeneği içerir.

Bu bölümde, bu seçeneklerin nasıl yapılandırılacağını ve Symfony’nin bu yapılandırmalar temelinde oluşturduğu **container parametrelerini** inceleyeceğiz.

---

### 🧱 `kernel.build_dir`

**Tür:** `string`

**Varsayılan:** `$this->getCacheDir()`

Bu parametre, Symfony uygulamanızın **derleme (build) dizininin** tam yolunu (absolute path) tutar.

Bu dizin, **salt-okunur önbellek (ör. derlenmiş container)** ile **okunabilir-yazılabilir önbellek (ör. cache havuzları)** arasında ayrım yapmak için kullanılabilir.

Örneğin, uygulamanızı **Docker** veya **AWS Lambda** gibi salt-okunur dosya sistemi olan bir ortamda çalıştırıyorsanız, bu değeri değiştirmeniz gerekebilir.

Ayrıca bu değere, kernel sınıfının **`getBuildDir()`** metodu aracılığıyla erişebilirsiniz.

Bu metodu override ederek farklı bir değer döndürebilirsiniz.

Ek olarak, build dizinini ortam değişkeniyle (environment variable) de ayarlayabilirsiniz:

```bash
APP_BUILD_DIR=/path/to/build/folder
```

---

### 🧩 `kernel.bundles`

**Tür:** `array`

**Varsayılan:** `[]`

Bu parametre, uygulamada kayıtlı tüm **bundle’ların listesini** ve her birinin tam sınıf adını (FQCN) saklar:

```php
[
    'FrameworkBundle' => 'Symfony\Bundle\FrameworkBundle\FrameworkBundle',
    'TwigBundle' => 'Symfony\Bundle\TwigBundle\TwigBundle',
    // ...
]
```

Bu değer, kernel sınıfının **`getBundles()`** metodu aracılığıyla da erişilebilir.

---

### 🧠 `kernel.bundles_metadata`

**Tür:** `array`

**Varsayılan:** `[]`

Bu parametre, kayıtlı tüm bundle’ların listesini ve her biri hakkında ek **meta bilgileri** tutar:

```php
[
    'FrameworkBundle' => [
        'path' => '/<proje-dizini>/vendor/symfony/framework-bundle',
        'namespace' => 'Symfony\Bundle\FrameworkBundle',
    ],
    'TwigBundle' => [
        'path' => '/<proje-dizini>/vendor/symfony/twig-bundle',
        'namespace' => 'Symfony\Bundle\TwigBundle',
    ],
    // ...
]
```

Bu bilgi  **kernel sınıfı metotlarıyla erişilebilir değildir** ; yalnızca **container parametresi** üzerinden kullanılabilir.

---

### 🗃️ `kernel.cache_dir`

**Tür:** `string`

**Varsayılan:** `$this->getProjectDir()/var/cache/$this->environment`

Bu parametre, Symfony uygulamanızın **önbellek (cache)** dizininin tam yolunu tutar.

Symfony bu değeri, mevcut  **environment** ’a (ör. `dev`, `prod`) göre otomatik olarak oluşturur.

Uygulamanız çalışma zamanında bu dizine veri yazabilir.

Bu değer, kernel sınıfındaki **`getCacheDir()`** metodu ile de erişilebilir.

Dilerseniz bu metodu override ederek farklı bir cache dizini belirleyebilirsiniz.

---

### ✴️ `kernel.charset`

**Tür:** `string`

**Varsayılan:** `UTF-8`

Bu parametre, uygulamada kullanılan **karakter kodlaması (charset)** türünü belirtir.

Ayrıca, bu değere kernel’in **`getCharset()`** metodu üzerinden erişebilirsiniz.

Dilerseniz bu metodu override ederek farklı bir charset döndürebilirsiniz:

```php
// src/Kernel.php
namespace App;

use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    public function getCharset(): string
    {
        return 'ISO-8859-1';
    }
}
```

---

### 🕒 `kernel.container_build_time`

**Tür:** `string`

**Varsayılan:** `time()` (PHP’nin time() fonksiyonunun sonucu)

Symfony, **yeniden üretilebilir derlemeler (reproducible builds)** felsefesini benimser.

Bu sayede,  **aynı kaynak kodun derlenmesi her zaman aynı sonucu üretir** .

Bu yaklaşım, derlenen kodun güvenilir bir kaynaktan geldiğini doğrulamak için önemlidir.

Uygulamanızın  **derlenmiş servis container’ı** , kaynak kodunu değiştirmediğiniz sürece **her zaman aynı** olacaktır.

Bu yapı şu parametrelerle temsil edilir:

* **`container.build_hash`** → tüm kaynak dosyaların içeriğinden oluşturulan hash değeri
* **`container.build_time`** → container derlendiği andaki zaman damgası (PHP `time()`)
* **`container.build_id`** → yukarıdaki iki değerin birleştirilip CRC32 ile kodlanmış hali

Ancak `container.build_time` değeri her derlemede değiştiği için, sonuç tam olarak “reproducible” değildir.

Bunu önlemek için **`kernel.container_build_time`** parametresini sabit bir değere ayarlayabilirsiniz:

```php
// config/services.php

// ...
$container->setParameter('kernel.container_build_time', '1234567890');
```

Bu sayede derleme zamanı her seferinde aynı kalır ve **tam olarak yeniden üretilebilir bir build** elde edilir.

---

### 📘 Özet

| Parametre                       | Tür   | Varsayılan              | Açıklama                            |
| ------------------------------- | ------ | ------------------------ | ------------------------------------- |
| `kernel.build_dir`            | string | `$this->getCacheDir()` | Derleme dizininin yolu                |
| `kernel.bundles`              | array  | `[]`                   | Kayıtlı bundle listesi              |
| `kernel.bundles_metadata`     | array  | `[]`                   | Bundle meta bilgileri                 |
| `kernel.cache_dir`            | string | `var/cache/$env`       | Önbellek dizini                      |
| `kernel.charset`              | string | `UTF-8`                | Karakter kodlaması                   |
| `kernel.container_build_time` | string | `time()`               | Derleme zamanı (override edilebilir) |


## ⚙️ Kernel İçinde Yapılandırma (Devamı)

Symfony’nin **Kernel** sınıfı, uygulamanın çekirdeğini yönetirken birçok önemli parametreyi barındırır.

Aşağıda bu parametrelerin işlevleri, varsayılan değerleri ve nasıl özelleştirilebilecekleri anlatılmaktadır.

---

### 🧩 `kernel.container_class`

**Tür:** `string`

**Varsayılan:** Symfony tarafından otomatik oluşturulur

Bu parametre, **container sınıfı için benzersiz bir tanımlayıcı (unique identifier)** saklar.

Bu, birden fazla kernel kullanılan uygulamalarda her kernel’in farklı bir tanımlayıcıya sahip olmasını sağlar.

Varsayılan değer, Symfony tarafından mevcut **environment (ortam)** ve **debug modu** bilgilerine göre oluşturulur.

Örneğin, kernel `App` namespace’i altında tanımlıysa, `dev` ortamında ve debug modu açıkken değeri şu şekilde olur:

```
App_KernelDevDebugContainer
```

Bu değer, kernel sınıfındaki **`getContainerClass()`** metodu aracılığıyla da erişilebilir.

Dilerseniz bu metodu override ederek benzersiz bir sınıf adı üretebilirsiniz:

```php
// src/Kernel.php
namespace App;

use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    public function getContainerClass(): string
    {
        return sprintf('AcmeKernel%s', random_int(10_000, 99_999));
    }
}
```

---

### 🪲 `kernel.debug`

**Tür:** `boolean`

**Varsayılan:** Kernel başlatılırken (boot edilirken) argüman olarak geçilir

Bu parametre, uygulamanın **debug (hata ayıklama)** modunun açık veya kapalı olduğunu belirtir.

---

### 🌍 `kernel.default_locale`

Bu parametre, `framework.default_locale` yapılandırma değerini saklar.

Uygulamanın **varsayılan dil/yerel ayarını** belirtir.

---

### 🌐 `kernel.enabled_locales`

Bu parametre, `framework.enabled_locales` değerini saklar.

Yani, uygulamanızda **desteklenen dillerin** listesidir.

---

### ⚙️ `kernel.environment`

**Tür:** `string`

**Varsayılan:** Kernel başlatılırken belirlenir

Bu parametre, uygulamanın **çalıştığı konfigürasyon ortamının** adını tutar.

Örneğin: `dev`, `test`, `prod` gibi.

* `kernel.environment`: uygulamanın **çalışma konfigürasyonunu** belirler
* `kernel.runtime_environment`: uygulamanın **dağıtıldığı ortamı** tanımlar

Bu ayrım sayesinde, örneğin `prod` konfigürasyonunu hem staging hem de production ortamlarında kullanabilirsiniz.

---

### 🚨 `kernel.error_controller`

Bu parametre, `framework.error_controller` yapılandırma değerini saklar.

Uygulamanın hata sayfalarını oluşturan controller’ı belirler.

---

### 🧾 `kernel.http_method_override`

Bu parametre, `framework.http_method_override` değerini saklar.

HTTP metodunun (ör. `PUT`, `DELETE`) tarayıcı veya istemci tarafından override edilip edilemeyeceğini belirler.

---

### 🪵 `kernel.logs_dir`

**Tür:** `string`

**Varsayılan:** `$this->getProjectDir()/var/log`

Bu parametre, Symfony uygulamanızın **log (günlük) dizininin** tam yolunu tutar.

Değer, mevcut  **environment** ’a göre otomatik olarak hesaplanır.

Bu bilgiye kernel sınıfındaki **`getLogDir()`** metodu aracılığıyla erişebilirsiniz.

Dilerseniz bu metodu override ederek özel bir log dizini tanımlayabilirsiniz.

---

### 🏗️ `kernel.project_dir`

**Tür:** `string`

**Varsayılan:** `composer.json` dosyasının bulunduğu dizin

Bu parametre, Symfony uygulamanızın **proje kök dizininin** tam yolunu saklar.

Uygulamada dosya yollarını bu dizine göre oluşturmak için kullanılır.

Varsayılan olarak Symfony, proje kökünü `composer.json` dosyasının bulunduğu yerden hesaplar.

Eğer  **Composer kullanmıyorsanız** , `composer.json` dosyasını taşımışsanız veya silmişseniz,

`getProjectDir()` metodunu override ederek özel bir yol belirleyebilirsiniz:

```php
// src/Kernel.php
namespace App;

use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    public function getProjectDir(): string
    {
        // Sabit dizin belirlerken, yolun sonuna '/' eklemeyin
        return \dirname(__DIR__);
    }
}
```

---

### 🧩 `kernel.runtime_environment`

**Tür:** `string`

**Varsayılan:** `%env(default:kernel.environment:APP_RUNTIME_ENV)%`

Bu parametre, uygulamanın **çalıştığı (runtime) ortamın** adını saklar.

Bu değer, uygulamanın **nerede dağıtıldığını** belirtir

(`production`, `staging`, `test` vb.).

> `kernel.environment` → hangi yapılandırma kullanıldığını belirtir.
>
> `kernel.runtime_environment` → uygulamanın **hangi platformda** çalıştığını belirtir.

Örneğin:

Uygulama `prod` yapılandırmasıyla (`kernel.environment=prod`) çalışabilir ama hem staging hem production ortamlarında (`kernel.runtime_environment`) dağıtılmış olabilir.

---

### ⚡ `kernel.runtime_mode`

**Tür:** `string`

**Varsayılan:** `%env(query_string:default:container.runtime_mode:APP_RUNTIME_MODE)%`

Bu parametre, uygulamanın **çalışma modunu** belirtir.

Örneğin:

* `web=1&worker=0` → normal web modu
* `web=1&worker=1` → uzun süreli çalışan web sunucusu modu (ör.  **FrankenPHP** )

Bu değer, `APP_RUNTIME_MODE` ortam değişkeni ile ayarlanabilir.

---

#### 🖥️ `kernel.runtime_mode.web`

**Tür:** `boolean`

**Varsayılan:** `%env(bool:default::key:web:default:kernel.runtime_mode:)%`

Uygulamanın **web ortamında** çalışıp çalışmadığını belirtir.

---

#### 💻 `kernel.runtime_mode.cli`

**Tür:** `boolean`

**Varsayılan:** `%env(not:default:kernel.runtime_mode.web:)%`

Uygulamanın **CLI (komut satırı)** ortamında çalışıp çalışmadığını belirtir.

Varsayılan olarak, bu değer `kernel.runtime_mode.web`’in tersidir.

---

#### ⚙️ `kernel.runtime_mode.worker`

**Tür:** `boolean`

**Varsayılan:** `%env(bool:default::key:worker:default:kernel.runtime_mode:)%`

Uygulamanın bir **worker** (uzun süre çalışan işlem) ortamında çalışıp çalışmadığını belirtir.

Bu özellik, **FrankenPHP** gibi uzun süreli çalışan sunucular için geçerlidir.

---

### 🔐 `kernel.secret`

**Tür:** `string`

**Varsayılan:** `%env(APP_SECRET)%`

Bu parametre, `framework.secret` yapılandırma değerini saklar.

Uygulamanın güvenlik anahtarını temsil eder ve CSRF token’ları, şifreleme vb. işlemler için kullanılır.

---

### 📦 `kernel.trust_x_sendfile_type_header`

Bu parametre, `framework.trust_x_sendfile_type_header` değerini saklar.

Sunucunun **X-Sendfile** başlıklarını nasıl işleyeceğini belirtir.

---

### 🌐 `kernel.trusted_hosts`

Bu parametre, `framework.trusted_hosts` yapılandırmasını saklar.

Uygulamanın hangi domain’lerden gelen isteklere güveneceğini belirtir.

---

### 🧱 `kernel.trusted_proxies`

Bu parametre, `framework.trusted_proxies` değerini saklar.

Uygulamanın hangi **proxy sunucularına** güveneceğini belirtir.

---
