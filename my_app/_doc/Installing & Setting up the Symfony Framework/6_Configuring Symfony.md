### Symfony Yapılandırması

#### Yapılandırma Dosyaları

Symfony uygulamaları, **`config/`** dizininde bulunan dosyalarla yapılandırılır. Bu dizin varsayılan olarak şu şekilde düzenlenmiştir:

```
your-project/
├─ config/
│  ├─ packages/
│  ├─ bundles.php
│  ├─ routes.yaml
│  └─ services.yaml
```

* **`routes.yaml`** dosyası yönlendirme (routing) yapılandırmasını tanımlar.
* **`services.yaml`** dosyası servis konteynerindeki servisleri yapılandırır.
* **`bundles.php`** dosyası uygulamanızdaki paketleri (bundle’ları) etkinleştirir/devre dışı bırakır.
* **`config/packages/`** dizini, uygulamanıza kurulan her paketin yapılandırmasını içerir.

Symfony’de “package” veya “bundle” olarak adlandırılan bu bileşenler, projelerinize **hazır özellikler** ekler.

Symfony uygulamaları varsayılan olarak **Symfony Flex** kullanır. Flex etkin durumdayken, yüklenen paketler `bundles.php` dosyasını otomatik olarak günceller ve `config/packages/` dizininde yeni yapılandırma dosyaları oluşturur.

Örneğin, “API Platform” paketi kurulduğunda aşağıdaki dosya oluşturulur:

```yaml
# config/packages/api_platform.yaml
api_platform:
    mapping:
        paths: ['%kernel.project_dir%/src/Entity']
```

Yapılandırmaların birçok küçük dosyaya bölünmüş olması yeni kullanıcılar için ilk başta karışık gelebilir, ancak bu dosyalar genellikle yalnızca paket kurulumunda değiştirildiği için sonradan sık sık düzenlenmesi gerekmez.

Tüm yapılandırma seçeneklerini görmek için:

* [Symfony Configuration Reference](https://symfony.com/doc/current/reference/configuration.html) sayfasına göz atabilir veya
* `php bin/console config:dump-reference` komutunu çalıştırabilirsiniz.

---

### Yapılandırma Formatları

Symfony, uygulamanızın yapılandırma formatı konusunda sizi kısıtlamaz.  **YAML** , **XML** veya **PHP** formatlarından istediğinizi seçebilirsiniz.

Belgelerdeki tüm örnekler bu üç formatta gösterilir.

Formatlar arasında pratikte bir fark yoktur. Symfony, çalıştırmadan önce hepsini PHP’ye çevirir ve önbelleğe alır, bu yüzden performans açısından da fark bulunmaz.

#### Formatların Avantajları

| Format         | Avantajlar                                                                                            | Dezavantajlar                                                 |
| -------------- | ----------------------------------------------------------------------------------------------------- | ------------------------------------------------------------- |
| **YAML** | Basit, okunabilir ve temizdir.                                                                        | Bazı IDE’ler otomatik tamamlama/validasyon desteği sunmaz. |
| **XML**  | IDE’ler tarafından kolayca tamamlanabilir ve PHP tarafından doğal olarak ayrıştırılır.       | Uzun ve karmaşık yapılandırmalara neden olabilir.         |
| **PHP**  | Güçlüdür, diziler veya `ConfigBuilder`kullanarak dinamik yapılandırmalar oluşturabilirsiniz. | Kısmen daha teknik bir yaklaşım gerektirir.                |

Symfony varsayılan olarak **YAML** ve **PHP** yapılandırma dosyalarını yükler.

Eğer **XML** kullanmak isterseniz, `src/Kernel.php` dosyasındaki `configureContainer()` ve/veya `configureRoutes()` metodlarına `.xml` desteği eklemeniz gerekir.

---

### Yapılandırma Dosyalarını İçe Aktarma

Symfony, yapılandırma dosyalarını **Config Component** aracılığıyla yükler.

Bu bileşen, farklı formatlardaki dosyaları birbirine **import** etme gibi gelişmiş özellikler sunar:

```php
// config/services.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->import('legacy_config.php');

    // glob ifadesiyle birden fazla dosyayı yükleme
    $container->import('/etc/myapp/*.yaml');

    // Üçüncü parametre 'ignore_errors' hataları sessizce yoksayar
    $container->import('my_config_file.yaml', null, 'not_found');
    $container->import('my_config_file.yaml', null, true);
};
```

---

### Yapılandırma Parametreleri

Bazen aynı yapılandırma değeri birden fazla dosyada kullanılır.

Bu durumda değeri tekrar tekrar yazmak yerine, **parametre** olarak tanımlayabilirsiniz.

Parametreler genellikle `config/services.yaml` dosyasında **parameters** anahtarı altında tanımlanır:

```php
// config/services.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Entity\BlogPost;
use App\Enum\PostState;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('app.admin_email', 'something@example.com')
        ->set('app.enable_v2_protocol', true)
        ->set('app.supported_locales', ['en', 'es', 'fr'])
        ->set('app.some_parameter', 'This is a Bell char: \x07')
        ->set('app.some_constant', GLOBAL_CONSTANT)
        ->set('app.another_constant', BlogPost::MAX_ITEMS)
        ->set('app.some_enum', PostState::Published);
};
```

XML formatında tanımlanan parametreler, satır başı boşluklarını da içerir:

```xml
<parameter key="app.admin_email">
    something@example.com
</parameter>
```

Eğer boşlukları kaldırmak isterseniz, `trim="true"` kullanabilirsiniz:

```xml
<parameter key="app.admin_email" trim="true">
    something@example.com
</parameter>
```

#### Parametrelerin Kullanımı

Bir parametreyi başka bir yapılandırmada kullanmak için `%` sembolleriyle çevreleyin:

```php
// config/packages/some_package.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

return static function (ContainerConfigurator $container): void {
    $container->extension('some_package', [
        'email_address' => param('app.admin_email'),
        'email_address' => '%app.admin_email%',
    ]);
};
```

#### `%` Karakterini Kaçırma

Parametre içinde `%` karakteri geçiyorsa, çift `%` kullanarak kaçırmalısınız:

```php
$container->parameters()
    ->set('url_pattern', 'http://symfony.com/?foo=%%s&bar=%%d');
```

---

### Dinamik Dosya Yolları

Parametrelerle import yollarını dinamik olarak oluşturamazsınız.

Aşağıdaki örnek  **çalışmaz** :

```php
$container->import('%kernel.project_dir%/somefile.yaml');
```

---

### Parametrelerin Doğrulanması

Symfony 7.2 sürümüyle birlikte, belirli parametrelerin **boş olmaması gerektiğini** doğrulayabilirsiniz:

```php
/** @var ContainerBuilder $container */
$container->parameterCannotBeEmpty(
    'app.private_key',
    'Did you forget to set a value for the "app.private_key" parameter?'
);
```

Eğer bu parametre `null`, boş string `''` veya boş dizi `[]` ise, Symfony bir istisna fırlatır.

Bu doğrulama  **derleme anında değil** , parametreye erişildiğinde yapılır.

---

### Ek Notlar

* Parametre isimleri genellikle `app.` önekiyle başlar.
* Nokta (`.`) ile başlayan parametreler (örnek: `.mailer.transport`) yalnızca **container derlenirken** kullanılır ve uygulama çalıştığında erişilemez.
* Bazı paketler (örneğin  **Translation** ) kendi parametrelerini otomatik olarak ekler (`locale` gibi).



### Symfony Yapılandırma Ortamları (Configuration Environments)

Bir Symfony uygulamanız olabilir, ancak bu uygulamanın **farklı durumlarda farklı şekilde davranmasını** istersiniz:

* **Geliştirme aşamasında** : Her şeyi loglamak ve hata ayıklama araçlarını aktif tutmak istersiniz.
* **Canlı üretim ortamında (production)** : Uygulamanız hızlı çalışmalı ve yalnızca hataları loglamalıdır.

Symfony’de bu davranış farkını yönetmek için **“ortam” (environment)** kavramı kullanılır.

Uygulamanın hangi ortamda çalıştığına göre farklı yapılandırma dosyaları yüklenir.

---

### Varsayılan Ortamlar

Symfony uygulamaları, varsayılan olarak üç ortamla başlar:

* **`dev`** → Yerel geliştirme ortamı
* **`prod`** → Üretim (canlı sunucu) ortamı
* **`test`** → Otomatik testler için kullanılan ortam

Uygulama çalıştırıldığında Symfony yapılandırma dosyalarını şu sırayla yükler

(sonraki dosyalar önceki değerleri  **ezebilir** ):

1. `config/packages/*.<extension>`
2. `config/packages/<environment-name>/*.<extension>`
3. `config/services.<extension>`
4. `config/services_<environment-name>.<extension>`

---

### Örnek: Framework Paketi

Symfony’nin varsayılan olarak kurulu gelen **framework** paketi buna güzel bir örnektir:

1. `config/packages/framework.yaml` → Her ortamda yüklenir.
2. `prod` ortamında → `config/packages/prod/framework.yaml` **yoksa** ek bir ayar yapılmaz.
3. `dev` ortamında → `config/packages/dev/framework.yaml` **yoksa** varsayılan ayarlar geçerlidir.
4. `test` ortamında → `config/packages/test/framework.yaml` yüklenir ve framework.yaml’deki bazı ayarları  **geçersiz kılar** .

Yani ortamlar birbirinden tamamen farklı değildir; sadece **bazı ayarlar** değişir.

Bu yüzden çoğu ortak yapılandırma `config/packages/` dizinindeki dosyalarda tutulur.

---

### Tek Dosyada Ortam Bazlı Yapılandırma

Ayrı dosyalar yerine, tek bir yapılandırma dosyasında ortam bazlı ayarlar da yapabilirsiniz.

Bunun için `$container->env()` metodunu kullanabilirsiniz:

```php
// config/packages/framework.php
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Config\WebpackEncoreConfig;

return static function (WebpackEncoreConfig $webpackEncore, ContainerConfigurator $container): void {
    $webpackEncore
        ->outputPath('%kernel.project_dir%/public/build')
        ->strictMode(true)
        ->cache(false);

    // "prod" ortamında önbelleği etkinleştir
    if ('prod' === $container->env()) {
        $webpackEncore->cache(true);
    }

    // "test" ortamında strictMode devre dışı bırak
    if ('test' === $container->env()) {
        $webpackEncore->strictMode(false);
    }
};
```

> 🔍 **İpucu:**
>
> Konfigürasyon dosyalarının yüklenme sırası hakkında daha fazla bilgi için
>
> `Kernel` sınıfındaki `configureContainer()` metoduna göz atabilirsiniz.

---

### Aktif Ortamı Seçmek

Symfony projelerinde, kök dizinde bir **`.env`** dosyası bulunur.

Bu dosya, ortam değişkenlerinin değerlerini tanımlar.

Ayrıntılı olarak sonraki bölümde anlatılacak olsa da, **`APP_ENV`** değişkeni aktif ortamı belirler.

Örneğin uygulamayı üretim ortamında çalıştırmak için:

```bash
# .env veya .env.local
APP_ENV=prod
```

Bu değer hem web uygulaması hem de **konsol komutları** için geçerlidir.

Konsolda farklı bir ortamda komut çalıştırmak için geçici olarak değer belirtebilirsiniz:

```bash
php bin/console command_name
# veya
APP_ENV=prod php bin/console command_name
```

---

### Yeni Bir Ortam Oluşturmak

Varsayılan üç ortam çoğu proje için yeterlidir, ancak gerekirse kendi ortamınızı tanımlayabilirsiniz.

Örneğin **“staging”** adlı bir ortam oluşturmak istiyorsanız:

1. `config/packages/staging/` adlı bir dizin oluşturun.
2. Bu dizine yeni ortam için gerekli yapılandırma dosyalarını ekleyin.

   Symfony önce `config/packages/*.yaml` dosyalarını, sonra `config/packages/staging/*.yaml` dosyalarını yükler.

   Bu nedenle sadece **farklılık gösteren ayarları** eklemeniz yeterlidir.
3. `.env` veya `.env.local` dosyasında ortamı belirtin:

```bash
APP_ENV=staging
```

> 💡 Ortamlar genellikle birbirine benzediğinden,
>
> `config/packages/<environment-name>/` dizinleri arasında **sembolik linkler (symlink)** kullanarak aynı ayarları paylaşabilirsiniz.

---

### Ortam Değişkenleriyle Davranış Kontrolü

Yeni ortamlar tanımlamak yerine, **ortam değişkenleri (environment variables)** kullanmak da mümkündür.

Bu sayede uygulama aynı ortamda (örneğin `prod`) çalışabilir,

ancak farklı yapılandırmalarla farklı davranabilir.

Örneğin:

* **`APP_ENV=prod`** → üretim ortamı
* **`APP_STAGE=staging`** → canlıya geçmeden önce müşteri testi
* **`APP_STAGE=qa`** → kalite kontrol ortamı

Bu yöntemle tek bir ortam üzerinden **farklı senaryoları (staging, QA, review)** kolayca yönetebilirsiniz.

---


### Ortam Değişkenlerine Dayalı Yapılandırma (Configuration Based on Environment Variables)

**Ortam değişkenleri** (ya da kısaca  *env vars* ), Symfony uygulamalarında yapılandırmayı esnek hale getirmek için yaygın olarak kullanılır.

Genellikle şu iki durumda tercih edilir:

* **Uygulamanın çalıştığı ortama göre değişen ayarlar** (örneğin: veritabanı bağlantısı, API adresi, e-posta sunucusu).
* **Canlı ortamda dinamik olarak değişebilecek değerler** (örneğin: süresi dolan bir API anahtarını tüm uygulamayı yeniden dağıtmadan güncellemek).

Diğer tüm sabit yapılandırmalar için ise **konfigürasyon parametrelerini** kullanmak önerilir.

---

### Ortam Değişkeni Sözdizimi

Bir ortam değişkenine referans vermek için şu özel sözdizimini kullanırsınız:

```php
%env(ENV_VAR_NAME)%
```

Bu değerler **çalışma zamanında (runtime)** çözülür.

Yani uygulamayı yeniden dağıtmadan veya önbelleği temizlemeden ortam değişkenlerini değiştirebilirsiniz.

(Symfony performansı korumak için her istekte yalnızca bir kez çözümler.)

Örnek olarak, uygulamanın gizli anahtarını bir ortam değişkeniyle tanımlayabilirsiniz:

```php
// config/packages/framework.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('framework', [
        // env değişkenleri geleneksel olarak büyük harfle yazılır
        'secret' => '%env(APP_SECRET)%',
    ]);
};
```

---

### PHP Üzerinden Ortam Değişkenlerine Erişim

Ortam değişkenlerine doğrudan PHP’nin süper global dizileriyle de erişebilirsiniz:

```php
$databaseUrl = $_ENV['DATABASE_URL']; // mysql://db_user:db_password@127.0.0.1:3306/db_name
$env = $_SERVER['APP_ENV']; // prod
```

Ancak Symfony’de buna gerek yoktur; yapılandırma sistemi env değişkenleriyle çalışmayı **daha güvenli ve pratik** hale getirir.

---

### Env Değerlerinin Dönüştürülmesi

Ortam değişkenlerinin değeri yalnızca **string** olabilir.

Symfony, bu değerleri dönüştürmek için çeşitli **env var işlemcileri (processors)** içerir (örneğin bir değeri integer’a dönüştürmek için).

---

### Ortam Değişkeni Tanımlama Yöntemleri

Bir ortam değişkenine değer atamanın birkaç yolu vardır:

1. `.env` dosyasına ekleyin
2. Değeri gizli (encrypted secret) olarak tanımlayın
3. Sunucunuzda veya terminalinizde gerçek bir ortam değişkeni olarak tanımlayın

Eğer uygulama tanımlanmamış bir env değişkenini kullanmaya çalışırsa, Symfony bir **hata** fırlatır.

Bunu önlemek için bir **varsayılan değer** tanımlayabilirsiniz:

```php
// config/packages/framework.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Config\FrameworkConfig;

return static function (ContainerBuilder $container, FrameworkConfig $framework) {
    // SECRET env değişkeni tanımlı değilse, bu değer kullanılır
    $container->setParameter('env(SECRET)', 'some_secret');
};
```

> 💡 Bazı barındırma sağlayıcıları (örneğin  **Platform.sh** ) ortam değişkenlerini kolayca yönetmek için araçlar sunar.

---

### Dikkat Edilmesi Gerekenler

* Env değişkenleriyle bazı yapılandırma özellikleri  **uyumsuzdur** .

  Örneğin, bir yapılandırma parametresini başka bir ayarın varlığına göre koşullu tanımlamak mümkün değildir, çünkü env değişkenleri tanımlı olmasa bile `null` döner.
* `$_ENV`, `$_SERVER` veya `phpinfo()` çıktısını göstermek, **gizli bilgilerinizi (örneğin veritabanı şifreleri)** ifşa edebilir.
* Symfony **Web Profiler** arayüzünde de env değişkenleri görüntülenir, ancak bu araç hiçbir zaman üretim ortamında etkin olmamalıdır.

---

### `.env` Dosyalarında Ortam Değişkeni Yapılandırmak

Symfony, ortam değişkenlerini doğrudan proje kök dizinindeki **`.env`** dosyasında tanımlamanızı sağlar.

Bu dosya her istekte okunur ve içindeki değişkenler `$_ENV` ve `$_SERVER` içine eklenir.

⚙️ **Önemli:**

`.env` içindeki değerler, sistemde **zaten tanımlı olan env değişkenlerini asla geçersiz kılmaz** — bu sayede her iki yöntemi birleştirebilirsiniz.

Örnek olarak, veritabanı bağlantısını tanımlayalım:

```bash
# .env
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name"
```

Bu dosya  **git deposuna dahil edilmelidir** , ancak yalnızca **yerel geliştirme için varsayılan değerleri** içermelidir.

Canlı (production) ortam değerleri burada  **olmamalıdır** .

Symfony Flex sayesinde yüklediğiniz üçüncü parti paketler, kendi gerekli env değişkenlerini otomatik olarak `.env` dosyasına ekler.

Docker kullanıyorsanız, `.env` dosyası her istekte okunduğu için **önbelleği temizlemenize veya PHP konteynerini yeniden başlatmanıza gerek yoktur.**

---

### `.env` Dosya Sözdizimi

* **Yorum eklemek** için `#` kullanılır:

  ```bash
  # database credentials
  DB_USER=root
  DB_PASS=pass # this is the secret password
  ```
* **Bir env değişkenini başka bir değerde kullanmak** için `$` öneki kullanılır:

  ```bash
  DB_USER=root
  DB_PASS=${DB_USER}pass # password değeri: rootpass
  ```

  > Değişken bağımlılığı olduğunda **tanımlama sırası önemlidir.**
  >
  > `DB_PASS` tanımı `DB_USER`’dan sonra gelmelidir.
  >
* **Varsayılan değer atamak** için `:-` kullanılır:

  ```bash
  DB_USER=
  DB_PASS=${DB_USER:-root}pass # sonuç: DB_PASS=rootpass
  ```
* **Komut gömme (embedding)** için `$()` kullanılabilir (Windows’ta desteklenmez):

  ```bash
  START_TIME=$(date)
  ```
* `.env` bir **normal shell betiği** olduğundan, kendi script’lerinizde de kaynak alabilirsiniz:

  ```bash
  source .env
  ```

---

### `.env.local` ile Ortam Değerlerini Geçersiz Kılmak

Yerel makinenizdeki bazı değerleri değiştirmek isterseniz, `.env.local` dosyası oluşturabilirsiniz:

```bash
# .env.local
DATABASE_URL="mysql://root:@127.0.0.1:3306/my_database_name"
```

Bu dosya **git tarafından yok sayılmalıdır** (deponuza gönderilmez).

Symfony projeleriyle birlikte gelen `.gitignore` dosyası zaten bunu otomatik olarak sağlar.

#### Symfony’de Kullanılan `.env` Dosyaları

| Dosya Adı                   | Açıklama                                                                                                                     |
| ---------------------------- | ------------------------------------------------------------------------------------------------------------------------------ |
| `.env`                     | Uygulamanın ihtiyaç duyduğu tüm varsayılan env değişkenlerini tanımlar.                                                |
| `.env.local`               | Yerel makineye özel değişiklikler içerir.**Git’e gönderilmez.**Test ortamında da kullanılmaz.                          |
| `.env.<environment>`       | Belirli bir ortama özel değişkenleri tanımlar (örnek:`.env.test`). Bu dosyalar**commit edilir.**                  |
| `.env.<environment>.local` | Belirli bir ortama özel, makineye özgü değişkenleri tanımlar (örnek:`.env.test.local`).**Git’e gönderilmez.** |

> 🧩 **Öncelik sırası:**
>
> Gerçek ortam değişkenleri, `.env` dosyalarında tanımlanan tüm değerlerin **üzerinde** gelir.
>
> Bu davranış PHP’nin `variables_order` ayarına bağlıdır (varsayılan olarak `E` içerir, yani `$_ENV` etkindir).

---


### Sistem Tarafından Tanımlanan Ortam Değişkenlerini Geçersiz Kılmak

( *Overriding Environment Variables Defined By The System* )

Bazen işletim sistemi tarafından tanımlanmış bir **ortam değişkenini geçersiz kılmanız** gerekebilir.

Bunu yapmak için `loadEnv()`, `bootEnv()` veya `populate()` metotlarında bulunan **`overrideExistingVars`** parametresini kullanabilirsiniz:

```php
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__.'/.env', overrideExistingVars: true);

// ...
```

Bu ayar, **sistem tarafından tanımlanan** ortam değişkenlerini geçersiz kılar;

ancak `.env` dosyalarında tanımlanan değişkenleri **geçersiz kılmaz.**

---

### Üretim Ortamında Ortam Değişkenlerini Yapılandırmak

( *Configuring Environment Variables in Production* )

Üretim ortamında da `.env` dosyaları her istekte Symfony tarafından **okunur ve yüklenir.**

Bu nedenle, en basit yöntem, **sunucunuzda bir `.env.local` dosyası oluşturup** üretim değerlerinizi burada tanımlamaktır.

Performansı artırmak için isteğe bağlı olarak şu komutu çalıştırabilirsiniz:

```bash
composer dump-env prod
```

Bu komut, ortam değişkenlerini `.env.local.php` dosyasına **derleyip** kaydeder ve Symfony artık `.env` dosyalarını **parsing** etmek için zaman harcamaz.

---

### Composer Olmadan Ortam Değişkenlerini Derlemek

( *Dumping Environment Variables without Composer* )

Eğer üretim ortamında Composer kurulu değilse, `dotenv:dump` komutunu kullanabilirsiniz

(Bu komut Symfony Flex 1.2 veya üzeri sürümlerde bulunur).

Önce bu komutu **servis olarak tanımlamalısınız:**

```yaml
# config/services.yaml
services:
    Symfony\Component\Dotenv\Command\DotenvDumpCommand: ~
```

Sonra şu şekilde çalıştırın:

```bash
APP_ENV=prod APP_DEBUG=0 php bin/console dotenv:dump
```

Bu işlemden sonra Symfony, ortam değişkenlerini `.env.local.php` dosyasından yükleyecektir.

Artık `.env` dosyalarını her istekte okumaz — böylece **uygulama performansı artar.**

> 🔁 Dağıtım (deployment) işleminizden sonra `dotenv:dump` komutunun otomatik olarak çalıştırıldığından emin olun.

---

### Ortam Değişkenlerini Farklı Dosyalarda Saklamak

( *Storing Environment Variables In Other Files* )

Varsayılan olarak Symfony, ortam değişkenlerini proje kökündeki `.env` dosyasından yükler.

Ancak isterseniz bunları **farklı dosyalarda** saklayabilirsiniz.

#### 1️⃣ Runtime Bileşeni ile composer.json Üzerinden

Eğer `Runtime` bileşenini kullanıyorsanız, `.env` dosyasının yolunu `composer.json` içine ekleyebilirsiniz:

```json
{
    "extra": {
        "runtime": {
            "dotenv_path": "my/custom/path/to/.env"
        }
    }
}
```

#### 2️⃣ Doğrudan Dotenv Sınıfını Kullanarak

Alternatif olarak, `bootstrap.php` dosyanızda `Dotenv` sınıfını doğrudan çağırabilirsiniz:

```php
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(dirname(__DIR__).'/my/custom/path/to/.env');
```

Bu durumda Symfony, belirtilen dosyayı (ve ilgili `.local` ile ortama özgü dosyaları)

env değişkenlerini yüklemek için kullanır.

Eğer Symfony’nin hangi `.env` dosyasını kullandığını bilmek istiyorsanız,

**`SYMFONY_DOTENV_PATH`** ortam değişkenini okuyabilirsiniz.

> 🆕 Bu değişken Symfony **7.1** sürümünde eklenmiştir.

---

### Ortam Değişkenlerini Şifreleme (Secrets)

( *Encrypting Environment Variables* )

Bazı değerler gizli olabilir — örneğin:

* API anahtarları
* Veritabanı şifreleri
* OAuth token’ları

Bu gibi durumlarda değişkenleri `.env` dosyasına yazmak yerine,

Symfony’nin **“Secrets Management System”** özelliğini kullanarak güvenli biçimde **şifreleyebilirsiniz.**

---

### Ortam Değişkenlerini Listeleme

( *Listing Environment Variables* )

Symfony’nin `.env` dosyalarını nasıl okuduğunu görmek için şu komutu kullanabilirsiniz:

```bash
php bin/console debug:dotenv
```

Bu komut, Symfony’nin hangi dosyaları hangi sırayla taradığını ve hangi değişkenlerin nereden geldiğini gösterir:

```
Dotenv Variables & Files
========================

Scanned Files (in descending priority)
--------------------------------------
* ⨯ .env.local.php
* ⨯ .env.dev.local
* ✓ .env.dev
* ⨯ .env.local
* ✓ .env

Variables
---------
---------- ------- ---------- ------
 Variable   Value   .env.dev   .env
---------- ------- ---------- ------
 FOO        BAR     n/a        BAR
 ALICE      BOB     BOB        bob
---------- ------- ---------- ------
```

Belirli bir değişkenin kaynağını görmek için değişken adını belirtin:

```bash
php bin/console debug:dotenv foo
```

Ayrıca, konteynerde tanımlı tüm env değişkenlerini ve

her birinin **kaç kez kullanıldığını** görmek için:

```bash
php bin/console debug:container --env-vars
```

Örnek çıktı:

```
------------ ----------------- ------------------------------------ -------------
 Name         Default value     Real value                           Usage count
------------ ----------------- ------------------------------------ -------------
 APP_SECRET   n/a               "471a62e2d601a8952deb186e44186cb3"   2
 BAR          n/a               n/a                                  1
 BAZ          n/a               "value"                              0
 FOO          "[1, "2.5", 3]"   n/a                                  1
------------ ----------------- ------------------------------------ -------------
```

Belirli bir env değişkenini görmek için:

```bash
php bin/console debug:container --env-vars foo
# veya
php bin/console debug:container --env-var=FOO
```

---

### Kendi Ortam Değişkeni Yükleyicinizi Yazmak

( *Creating Your Own Logic To Load Env Vars* )

Varsayılan Symfony davranışı sizin senaryonuza uymuyorsa,

**kendi ortam değişkeni yükleme mantığınızı (custom loader)** oluşturabilirsiniz.

Bunun için bir sınıf yazıp `EnvVarLoaderInterface` arayüzünü uygulayın:

```php
namespace App\DependencyInjection;

use Symfony\Component\DependencyInjection\EnvVarLoaderInterface;

final class JsonEnvVarLoader implements EnvVarLoaderInterface
{
    private const ENV_VARS_FILE = 'env.json';

    public function loadEnvVars(): array
    {
        $fileName = __DIR__ . DIRECTORY_SEPARATOR . self::ENV_VARS_FILE;
        if (!is_file($fileName)) {
            // Dosya yoksa hata fırlatabilir veya görmezden gelebilirsiniz
        }

        $content = json_decode(file_get_contents($fileName), true);

        return $content['vars'];
    }
}
```

Ve örnek bir `env.json` dosyası şöyle olabilir:

```json
{
    "vars": {
        "APP_ENV": "prod",
        "APP_DEBUG": false
    }
}
```

Bu sınıf eklendikten sonra Symfony, `.env` dosyalarına ek olarak

**`env.json` dosyasını da okuyarak** ortam değişkenlerini yükleyecektir.

> ⚙️ Eğer `services.yaml` dosyasında otomatik yapılandırma (autoconfiguration) açıksa,
>
> bu servis otomatik olarak **`container.env_var_loader`** etiketiyle kaydedilir.
>
> Değilse, etiketi kendiniz eklemeniz gerekir.

---

### Ortamlar Arası Geriye Dönüş (Fallback) Mantığı

Belli bir ortamda bir env değişkenine değer atamak ama

başka bir ortamda loader’dan çekilmesini istiyorsanız, o ortamda değişkene **boş bir değer** atayın:

```bash
# .env (veya .env.local)
APP_ENV=prod

# .env.prod (veya .env.prod.local) - loader'lardan okunur
APP_ENV=
```

---


### Yapılandırma Parametrelerine Erişim

( *Accessing Configuration Parameters* )

Symfony’de hem  **controller** ’lar hem de  **service** ’ler, uygulamada tanımlanmış tüm yapılandırma parametrelerine erişebilir.

Bu parametreler, hem sizin tanımladıklarınız hem de paketler/bundle’lar tarafından oluşturulan parametreleri kapsar.

Tüm mevcut parametreleri listelemek için şu komutu çalıştırabilirsiniz:

```bash
php bin/console debug:container --parameters
```

---

### Controller İçinden Parametrelere Erişim

`AbstractController` sınıfını genişleten controller’larda `getParameter()` yardımcı metodunu kullanabilirsiniz:

```php
// src/Controller/UserController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController
{
    public function index(): Response
    {
        $projectDir = $this->getParameter('kernel.project_dir');
        $adminEmail = $this->getParameter('app.admin_email');

        // ...
    }
}
```

---

### Servislerde Parametre Enjeksiyonu

`AbstractController`’dan türemeyen controller’larda veya servislerde,

parametreleri **constructor** üzerinden enjeksiyon yöntemiyle geçmelisiniz.

Otomatik bağımlılık enjeksiyonu ( **autowiring** ) parametreler için çalışmadığı için bu işlemi açıkça yapmanız gerekir:

```php
// config/services.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Service\MessageGenerator;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('app.contents_dir', '...');

    $container->services()
        ->get(MessageGenerator::class)
            ->arg('$contentsDir', '%app.contents_dir%');
};
```

---

### `bind` ile Parametreleri Otomatik Eşleme

Eğer aynı parametreyi birçok servis veya controller’da kullanıyorsanız,

her seferinde manuel olarak argüman tanımlamak yerine

`services._defaults.bind` seçeneğini kullanabilirsiniz.

Bu özellik, belirli bir **argüman adı** ile eşleşen parametre değerini otomatik olarak enjekte eder:

```php
// config/services.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
            // Herhangi bir $projectDir argümanına otomatik olarak kernel.project_dir değerini geç
            ->bind('$projectDir', '%kernel.project_dir%');
};
```

> 📘 Daha fazlası için: “ **Binding Arguments by Name and/or Type** ” makalesine bakın.

---

### Tüm Parametreleri Tek Seferde Enjekte Etmek

Bazı servislerin birçok parametreye ihtiyacı olabilir.

Bu durumda her birini tek tek enjekte etmek yerine,

Symfony’nin **ContainerBagInterface** arayüzünü kullanarak

tüm parametrelere topluca erişebilirsiniz:

```php
// src/Service/MessageGenerator.php
namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class MessageGenerator
{
    public function __construct(
        private ContainerBagInterface $params,
    ) {
    }

    public function someMethod(): void
    {
        // Container’daki tüm parametreler $this->params içinde yer alır
        $sender = $this->params->get('mailer_sender');
        // ...
    }
}
```

Bu yöntemle istediğiniz parametreye **runtime** sırasında ulaşabilirsiniz.

---

### PHP ConfigBuilder Kullanımı

( *Using PHP ConfigBuilders* )

Bazen PHP ile yapılandırma dosyaları oluşturmak zordur —

özellikle iç içe geçmiş büyük dizilerle uğraşırken veya IDE’niz otomatik tamamlama sunmuyorsa.

Symfony bu durumu kolaylaştırmak için **ConfigBuilder** sınıflarını sağlar.

Bu sınıflar, konfigürasyon dizilerini daha anlaşılır bir şekilde oluşturmanıza yardımcı olur.

Symfony, uygulamanızda kurulu olan tüm bundle’lar için

**ConfigBuilder sınıflarını otomatik olarak** üretir.

Bu sınıflar varsayılan olarak şu namespace altında bulunur:

```
Symfony\Config
```

Örnek kullanım:

```php
// config/packages/security.php
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $security): void {
    $security->firewall('main')
        ->pattern('^/*')
        ->lazy(true)
        ->security(false);

    $security
        ->roleHierarchy('ROLE_ADMIN', ['ROLE_USER'])
        ->roleHierarchy('ROLE_SUPER_ADMIN', ['ROLE_ADMIN', 'ROLE_ALLOWED_TO_SWITCH'])
        ->accessControl()
            ->path('^/user')
            ->roles('ROLE_USER');

    $security->accessControl(['path' => '^/admin', 'roles' => 'ROLE_ADMIN']);
};
```

> 🔹 **Not:**
>
> Yalnızca `Symfony\Config` namespace’inde bulunan kök sınıflar ConfigBuilder’dır.
>
> Örneğin `\Symfony\Config\Framework\CacheConfig` gibi alt sınıflar normal PHP nesneleridir
>
> ve **autowire** edilmezler.

---

### IDE Tamamlama (Autocompletion) Desteği

ConfigBuilder sınıfları, varsayılan olarak

`var/cache/dev/Symfony/Config/` dizininde oluşturulur.

IDE’nizde otomatik tamamlama özelliğini kullanmak istiyorsanız,

bu dizinin **dışlanmadığından (excluded)** emin olun.

---

### Devam Et!

🎉 **Tebrikler!** Symfony’nin yapılandırma sisteminin temellerini başarıyla öğrendiniz.

Şimdi her bir konuyu daha derinlemesine öğrenmek için aşağıdaki kılavuzlara göz atabilirsiniz:

* **Formlar (Forms)**
* **Veritabanları ve Doctrine ORM**
* **Servis Konteyneri (Service Container)**
* **Güvenlik (Security)**
* **E-posta Gönderimi (Mailer)**
* **Loglama (Logging)**

Ayrıca yapılandırmayla ilgili ileri düzey konular:

* Ortam Değişkeni İşleyicileri ( *Environment Variable Processors* )
* Front Controller, Kernel ve Ortamların nasıl birlikte çalıştığı
* MicroKernelTrait ile kendi framework’ünüzü oluşturmak
* Tek bir Kernel ile birden fazla Symfony uygulaması kurmak
* Symfony’nin varsayılan dizin yapısını değiştirmek
* Gizli bilgileri güvenli biçimde saklamak ( *Secrets* )
* Bağımlılık enjeksiyonu sınıfı içinde parametre kullanımı

---
