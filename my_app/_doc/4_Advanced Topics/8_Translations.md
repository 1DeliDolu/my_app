## 🌍 Çeviriler

### 🧩 Genel Bakış

"Uluslararasılaştırma" terimi (genellikle i18n olarak kısaltılır), uygulamanızdaki metinler ve yerel özelliklere özgü diğer öğelerin kullanıcı yereline (yani dil ve ülkeye) göre çevrilebileceği ve dönüştürülebileceği bir katmana soyutlanması sürecini ifade eder. Metinler için bu, her bir metni kullanıcı diline çevirebilen bir fonksiyonla sarmalamak anlamına gelir:

```php
// metin her zaman İngilizce olarak yazdırılacaktır
echo 'Hello World';

// metin, son kullanıcının diline çevrilebilir veya
// varsayılan olarak İngilizce kalır
echo $translator->trans('Hello World');
```

**Locale** terimi kabaca kullanıcının dili ve ülkesine karşılık gelir. Uygulamanızın çevirileri ve diğer format farklarını (örneğin para birimi biçimi) yönetmek için kullandığı herhangi bir dize olabilir. ISO 639-1 dil kodu, ardından alt çizgi (_) ve ISO 3166-1 alpha-2 ülke kodu (örneğin, Fransızca/Fransa için `fr_FR`) önerilir.

Çeviriler **domain** adı verilen gruplar halinde organize edilebilir. Varsayılan olarak, tüm mesajlar `messages` domain'ini kullanır:

```php
echo $translator->trans('Hello World', domain: 'messages');
```

Çeviri süreci birkaç adımdan oluşur:

1. Symfony'nin translation servisini etkinleştirin ve yapılandırın;
2. Metinleri (yani "mesajları") Translator çağrılarıyla soyutlayın;
3. Her desteklenen locale için çeviri kaynaklarını/dosyalarını oluşturun;
4. Kullanıcının locale bilgisini isteğe veya oturuma göre belirleyin, ayarlayın ve yönetin.

---

### ⚙️ Kurulum

Öncelikle çeviriyi kullanmadan önce aşağıdaki komutu çalıştırın:

```bash
composer require symfony/translation
```

Symfony, PHP `intl` eklentisi olmadan bile çeviri özelliklerini kullanmanızı sağlayan birkaç **uluslararasılaştırma polyfill** (örneğin `symfony/polyfill-intl-icu`, `symfony/polyfill-intl-messageformatter` vb.) içerir. Ancak bu polyfill’ler yalnızca İngilizce çevirileri destekler; başka dillere çevirmek istiyorsanız PHP `intl` eklentisini kurmanız gerekir.

---

### ⚙️ Yapılandırma

Yukarıdaki komut, uygulamanın varsayılan locale ayarını ve çeviri dosyalarının bulunduğu dizini tanımlayabileceğiniz başlangıç yapılandırma dosyasını oluşturur:

```php
// config/packages/translation.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    // ...
    $framework
        ->defaultLocale('en')
        ->translator()
            ->defaultPath('%kernel.project_dir%/translations')
    ;
};
```

Ayrıca `enabled_locales` seçeneğini tanımlayarak uygulamanızın hangi yerellerde kullanılabileceğini sınırlayabilirsiniz.

---

### 💬 Temel Çeviri

Metin çevirisi **translator** servisi (Translator) aracılığıyla yapılır. Bir metin bloğunu (mesaj) çevirmek için `trans()` metodunu kullanın. Örneğin bir controller içinden statik bir mesajı çevirmek istiyorsanız:

```php
use Symfony\Contracts\Translation\TranslatorInterface;

public function index(TranslatorInterface $translator): Response
{
    $translated = $translator->trans('Symfony is great');

    // ...
}
```

Bu kod çalıştırıldığında Symfony, kullanıcının locale bilgisine göre "Symfony is great" mesajını çevirmeye çalışacaktır. Bunun çalışması için, mesajın nasıl çevrileceğini belirten bir “çeviri kaynağı” (genellikle bir dosya) oluşturmanız gerekir:

```php
// translations/messages.fr.php
return [
    'Symfony is great' => 'Symfony est génial',
];
```

Kullanıcının locale değeri Fransızca ise (`fr_FR` veya `fr_BE` gibi), mesaj “Symfony est génial” olarak çevrilir. Mesajı şablonlarınızda da çevirebilirsiniz.

---

### 🔑 Gerçek veya Anahtar Mesajlar Kullanmak

Aşağıdaki örnek, çeviri mesajlarını oluşturmanın iki farklı yaklaşımını gösterir:

```php
$translator->trans('Symfony is great');
$translator->trans('symfony.great');
```

İlk yöntem, mesajların varsayılan locale dilinde (örneğin İngilizce) yazılmasını sağlar. Bu metin, çeviriler oluşturulurken "id" olarak kullanılır.

İkinci yöntemde ise mesajlar “anahtar kelimeler” olarak yazılır. Bu anahtar kelime, tüm çevirilerde id olarak kullanılır. Bu durumda, varsayılan locale için bile `symfony.great` → `Symfony is great` şeklinde çeviri yapılmalıdır.

İkinci yöntem, varsayılan locale’deki mesajı (“Symfony is really great”) değiştirmek istediğinizde her çeviri dosyasını düzenleme ihtiyacını ortadan kaldırır.

Çeviri yöntemi tamamen size bağlıdır, ancak çok dilli uygulamalarda genellikle **anahtar kelime yöntemi** tercih edilir. Paylaşılan bundle’larda ise **gerçek mesaj yöntemi** önerilir, çünkü çeviri katmanını devre dışı bıraksanız bile anlamlı metinler görünür.

Ayrıca PHP ve YAML dosya formatları, **iç içe geçmiş id’leri** destekleyerek kendinizi tekrar etmenizi önler:

```php
[
    'symfony' => [
        'is' => [
            'great'   => 'Symfony is great',
            'amazing' => 'Symfony is amazing',
        ],
        'has' => [
            'bundles' => 'Symfony has bundles',
        ],
    ],
    'user' => [
        'login' => 'Login',
    ],
];
```

---

### 🔄 Çeviri Süreci

Bir mesaj `trans()` metodu ile çevrildiğinde Symfony şu adımları izler:

1. İstek üzerindeki kullanıcının locale bilgisi belirlenir (genellikle route üzerindeki `_locale` niteliğiyle ayarlanır);
2. Belirtilen locale’e ait çeviri kaynaklarından mesaj kataloğu yüklenir;
3. Fallback locale ve etkin locale’lerden eksik mesajlar da eklenir;
4. Mesaj katalogda bulunursa çeviri döndürülür, aksi halde orijinal mesaj gösterilir.

---

### 🧮 Mesaj Formatı

Bazen değişken içeren bir mesajı çevirmek gerekebilir:

```php
$translated = $translator->trans('Hello '.$name);
```

Ancak bu şekilde, değişken içeren metin (“Hello Ryan”, “Hello Fabien” vb.) birebir eşleşmeyeceğinden çevrilemez.

Bir başka durum da çoğul ifadelerin (plural) çevrilmesidir:

```
There is one apple.
There are 5 apples.
```

Bu tür durumları yönetmek için Symfony, PHP’nin **MessageFormatter** sınıfını kullanan **ICU MessageFormat** sözdizimini takip eder. Bunun hakkında daha fazla bilgi için “How to Translate Messages using the ICU MessageFormat” sayfasına bakın.

---

### 🧱 Çevrilebilir Nesneler

Bazı durumlarda, her çeviri için mesaj, parametre ve domain bilgilerini şablonlara taşımak zahmetli olabilir. Bu durumda çeviriyi controller veya serviste yapmak şablonları basitleştirir, ancak Translator servisini her yere enjekte etmeniz gerekir.

Bu yerine, **TranslatableMessage** sınıfının bir örneğini kullanabilirsiniz. Bu nesne, çevrileceği zaman ihtiyaç duyulan tüm bilgileri saklar:

```php
use Symfony\Component\Translation\TranslatableMessage;

$message = new TranslatableMessage('Symfony is great!');
$status = new TranslatableMessage('order.status', ['%status%' => $order->getStatus()], 'store');
```

Şimdi şablonlar daha sade hale gelir, çünkü çevirilebilir nesneleri doğrudan `trans` filtresiyle kullanabilirsiniz:

```twig
<h1>{{ message|trans }}</h1>
<p>{{ status|trans }}</p>
```

Çeviri parametreleri de bir `TranslatableMessage` olabilir. Ayrıca Twig ve PHP’de `t()` fonksiyonu, translatable object oluşturmak için kısa bir yoldur.

---

### 🪶 Şablonlarda Çeviriler

Çoğu zaman çeviri işlemleri şablonlarda yapılır. Symfony hem Twig hem de PHP şablonları için yerel destek sağlar.

#### 🔹 Twig Filtreleri Kullanmak

`trans` filtresi değişken metinleri ve karmaşık ifadeleri çevirmek için kullanılabilir:

```twig
{{ message|trans }}
{{ message|trans({'%name%': 'Fabien'}, 'app') }}
```

Tüm Twig şablonu için tek bir domain tanımlamak isterseniz:

```twig
{% trans_default_domain 'app' %}
```

Bu yalnızca mevcut şablonu etkiler; “include” edilen şablonları etkilemez.

Varsayılan olarak çevrilen metinler **escape** edilir. Escape edilmemiş çıktı almak için `raw` filtresini `trans` filtresinden sonra uygulayın:

```twig
{% set message = '<h3>foo</h3>' %}
{{ message|trans|raw }}
{{ '<h3>bar</h3>'|trans|raw }}
```

---

#### 🔹 Twig Tag Kullanmak

Symfony, statik metin bloklarının çevirisini kolaylaştırmak için özel bir Twig etiketi sağlar:

```twig
{% trans %}Hello %name%{% endtrans %}
```

Twig şablonlarında `%var%` biçiminde placeholder kullanımı zorunludur.

Yüzde karakteri (%) kullanmanız gerekirse, çiftleyerek kaçışlayın:

```twig
{% trans %}Percent: %percent%%%{% endtrans %}
```

Ayrıca mesaj domain’ini ve değişkenleri de belirtebilirsiniz:

```twig
{% trans with {'%name%': 'Fabien'} from 'app' %}Hello %name%{% endtrans %}
{% trans with {'%name%': 'Fabien'} from 'app' into 'fr' %}Hello %name%{% endtrans %}
```

`trans` etiketi, filtreyle aynı etkiye sahiptir; ancak **otomatik çıktı kaçışlaması** (escaping) uygulanmaz.


## 🌐 Global Translation Parameters (Küresel Çeviri Parametreleri)

### 🆕 Symfony 7.3 Yeniliği

**Global translation parameters** özelliği Symfony 7.3 ile tanıtılmıştır.

Bir çeviri parametresinin içeriği birden fazla çeviri mesajında tekrar ediyorsa (örneğin bir şirket adı veya sürüm numarası), bu parametreyi **global bir çeviri parametresi** olarak tanımlayabilirsiniz. Bu, aynı değeri her mesajda manuel olarak tekrar etmenizi önler.

Bu global parametreleri, ana yapılandırma dosyanızda `translations.globals` seçeneğinde `%...%` veya `{...}` sözdizimiyle tanımlayabilirsiniz:

```php
// config/packages/translator.php
use Symfony\Config\TwigConfig;

return static function (TwigConfig $translator): void {
    // ...
    // '%' karakterlerini kullanırken bunları kaçışlamanız gerekir
    $translator->globals('%%app_name%%')->value('My application');
    $translator->globals('{app_version}')->value('1.2.3');
    $translator->globals('{url}')->value(['message' => 'url', 'parameters' => ['scheme' => 'https://']]);
};
```

Tanımlandıktan sonra, bu parametreleri uygulamanızın herhangi bir yerindeki çeviri mesajlarında kullanabilirsiniz:

```twig
{{ 'Application version: {app_version}'|trans }}
{# çıktı: "Application version: 1.2.3" #}

{# Mesaja geçirilen parametreler global parametreleri geçersiz kılar #}
{{ 'Package version: {app_version}'|trans({'{app_version}': '2.3.4'}) }}
{# çıktı: "Package version: 2.3.4" #}
```

---

### 🌎 Çeviri Locale’ini Zorla Ayarlama

Bir mesaj çevrilirken, çevirici (translator) belirtilen locale’i veya gerekirse  **fallback locale** ’i kullanır. Ancak, kullanılacak locale’i manuel olarak da belirtebilirsiniz:

```php
$translator->trans('Symfony is great', locale: 'fr_FR');
```

---

### ⚡ Çeviri İçeriklerini Otomatik Olarak Çıkarmak ve Katalogları Güncellemek

Bir uygulamayı çevirirken en zaman alıcı görev, çevrilecek tüm şablon içeriklerini çıkarmak ve tüm çeviri dosyalarını senkronize tutmaktır. Symfony bu işlemleri kolaylaştırmak için `translation:extract` adlı bir komut sağlar:

```bash
php bin/console translation:extract --dump-messages fr
php bin/console translation:extract --force fr
php bin/console translation:extract --help
```

`translation:extract` komutu eksik çevirileri şu yerlerde arar:

* `templates/` dizinindeki şablonlarda (veya `twig.default_path` ve `twig.paths` seçeneklerinde tanımlanan dizinlerde);
* `translator` servisini enjekte eden veya autowire eden ve `trans()` metodunu çağıran tüm PHP dosya/sınıflarında;
* `src/` dizininde bulunan ve `TranslatableMessage` sınıfını ya da `t()` metodunu kullanan tüm PHP dosya/sınıflarında;
* `src/` dizininde bulunan ve `Constraints` Attribute’larını `message` adlı argüman(lar)la kullanan tüm PHP dosya/sınıflarında.

Daha iyi sonuçlar almak için projenize `nikic/php-parser` paketini kurun. Bu paket, daha fazla çevrilebilir öğe bulabilen bir **AST parser** sağlar:

```bash
composer require nikic/php-parser
```

Varsayılan olarak, `translation:extract` komutu yeni girişler oluşturduğunda kaynak ve bekleyen çeviri için aynı içeriği kullanır. Bekleyen çeviri, yalnızca başına `__` ön eki eklenmiş olur. Bu öneki `--prefix` seçeneğiyle değiştirebilirsiniz:

```bash
php bin/console translation:extract --force --prefix="NEW_" fr
```

Alternatif olarak, `--no-fill` seçeneğini kullanarak yeni oluşturulan çeviri girişlerini tamamen boş bırakabilirsiniz. Bu, özellikle harici çeviri araçlarını kullanırken, çevrilmemiş metinleri daha kolay fark etmenizi sağlar:

```bash
php bin/console translation:extract --force --no-fill fr
```

🆕 `--no-fill` seçeneği Symfony 7.2 ile tanıtılmıştır.

---

### 📁 Çeviri Kaynağı / Dosya Adları ve Konumları

Symfony, mesaj dosyalarını (yani çevirileri) aşağıdaki varsayılan konumlarda arar:

1. Proje kök dizinindeki `translations/` dizini;
2. Herhangi bir bundle içindeki `translations/` dizini (veya artık önerilmeyen `Resources/translations/` dizini).

Liste, **öncelik sırasına** göre düzenlenmiştir:

Yani bir bundle’ın çevirilerini, ilk dizinde yer alan bir dosyayla geçersiz kılabilirsiniz.

Bundle’lar `config/bundles.php` dosyasında listelendikleri sırayla işlenir, dolayısıyla listede önce gelen bundle’ların önceliği daha yüksektir.

Bu geçersiz kılma mekanizması anahtar düzeyinde çalışır: sadece geçersiz kılmak istediğiniz anahtarları üst öncelikli dosyada belirtmeniz yeterlidir.

Çeviri dosyalarının isimlendirme biçimi de önemlidir. Her mesaj dosyası şu şekilde adlandırılmalıdır:

```
domain.locale.loader
```

* **domain** : Çeviri domain’i
* **locale** : Hangi locale için çeviri yapıldığı (örneğin `en_GB`, `en` vb.)
* **loader** : Symfony’nin dosyayı nasıl yükleyeceği ve ayrıştıracağı (örneğin `xlf`, `php`, `yaml` vb.)

Symfony’nin varsayılan olarak desteklediği loader’lar ve dosya uzantıları:

| Uzantı             | Format                          |
| ------------------- | ------------------------------- |
| `.yaml`,`.yml`  | YAML dosyası                   |
| `.xlf`,`.xliff` | XLIFF dosyası                  |
| `.php`            | PHP dosyası (dizi döndürür) |
| `.csv`            | CSV dosyası                    |
| `.json`           | JSON dosyası                   |
| `.ini`            | INI dosyası                    |
| `.dat`,`.res`   | ICU kaynak paketi               |
| `.mo`             | Machine object format           |
| `.po`             | Portable object format          |
| `.qt`             | QT Translations TS XML dosyası |

Hangi loader’ı kullanacağınız tamamen size bağlıdır. Küçük projeler için  **YAML** , profesyonel çeviri araçlarıyla çalışıyorsanız **XLIFF** önerilir.

Yeni bir mesaj kataloğu oluşturduğunuzda (veya çeviri kataloğu içeren bir bundle kurduğunuzda), Symfony’nin yeni çeviri kaynaklarını keşfetmesi için önbelleği temizlemeyi unutmayın:

```bash
php bin/console cache:clear
```

Ek dizinleri yapılandırmaya şu şekilde ekleyebilirsiniz:

```php
// config/packages/translation.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->translator()
        ->paths(['%kernel.project_dir%/custom/path/to/translations'])
    ;
};
```

---

### 🧩 Doctrine Entity Çevirileri

Şablon içeriklerinin aksine, **Doctrine Entity** verilerini çeviri kataloglarıyla çevirmek pratik değildir. Bunun yerine **Doctrine Translatable Extension** kullanın.

---

### ⚙️ Özel Çeviri Kaynakları

Çevirileriniz Symfony tarafından desteklenmeyen bir format kullanıyorsa veya farklı bir depolama yöntemi (örneğin dosyalar yerine veritabanı) kullanıyorsanız, **LoaderInterface** arayüzünü uygulayan özel bir sınıf sağlamanız gerekir. Daha fazla bilgi için “Built-in Symfony Service Tags” başlığına bakın.

---

### 🌐 Çeviri Sağlayıcıları (Translation Providers)

Uygulamanızın çevirisini harici servislerle yaptığınızda, yeni içerikleri düzenli olarak dışa aktarmanız ve çevirileri geri birleştirmeniz gerekir. Symfony bunu kolaylaştırmak için çeşitli **üçüncü taraf çeviri servisleri** ile entegrasyon sağlar. Bu servislerle çevirileri yükleyebilir (push) veya indirebilir (pull) ve sonuçları uygulamanıza otomatik olarak birleştirebilirsiniz.

#### 🔧 Kurulum ve Yapılandırma

Öncelikle, kullanmak istediğiniz sağlayıcıya ait paketi kurun:

| Sağlayıcı        | Kurulum Komutu                                             |
| ------------------- | ---------------------------------------------------------- |
| Crowdin             | `composer require symfony/crowdin-translation-provider`  |
| Loco (localise.biz) | `composer require symfony/loco-translation-provider`     |
| Lokalise            | `composer require symfony/lokalise-translation-provider` |
| Phrase              | `composer require symfony/phrase-translation-provider`   |

Her kütüphane, `.env` dosyanıza bir yapılandırma örneği ekleyen bir **Symfony Flex recipe** içerir.

Örneğin, **Loco** kullanmak istiyorsanız:

```bash
composer require symfony/loco-translation-provider
```

Sonra `.env` dosyanıza şu satır eklenir:

```bash
# .env
LOCO_DSN=loco://API_KEY@default
```

Bu DSN gerçek bir adres değildir; sadece Symfony’nin konfigürasyon yükünü azaltan bir formattır. `loco` şeması, yüklediğiniz  **Loco provider** ’ını etkinleştirir. Tek yapmanız gereken `API_KEY` kısmını kendi anahtarınızla değiştirmektir.

#### 📋 DSN Formatları

| Sağlayıcı | DSN Formatı                                                   |
| ------------ | -------------------------------------------------------------- |
| Crowdin      | `crowdin://PROJECT_ID:API_TOKEN@ORGANIZATION_DOMAIN.default` |
| Loco         | `loco://API_KEY@default`                                     |
| Lokalise     | `lokalise://PROJECT_ID:API_KEY@default`                      |
| Phrase       | `phrase://PROJECT_ID:API_TOKEN@default?userAgent=myProject`  |

#### ⚙️ Sağlayıcıyı Etkinleştirme

`.env` dosyanızda DSN’i özelleştirdikten sonra yapılandırmaya ekleyin:

```php
# config/packages/translation.php
$container->loadFromExtension('framework', [
    'translator' => [
        'providers' => [
            'loco' => [
                'dsn' => env('LOCO_DSN'),
                'domains' => ['messages'],
                'locales' => ['en', 'fr'],
            ],
        ],
    ],
]);
```

> 🔸 **Phrase** kullanıyorsanız DSN içinde bir `userAgent` tanımlamanız gerekir.
>
> 🔸 **Crowdin** veya **Lokalise** kullanıyorsanız, yerel kod eşleştirmelerini (custom language codes/names) doğru biçimde yapılandırmalısınız.
>
> 🔸  **Phrase provider** , çeviri domain’lerini etiketlerle (tags) eşler. Tag yönetimi için `Phrase Tag Bundle` kullanabilirsiniz.

---

### ⬆️⬇️ Çevirileri Gönderme ve Çekme

Sağlayıcı erişim bilgilerini yapılandırdıktan sonra, aşağıdaki komutlarla çevirileri yükleyebilir (push) veya indirebilirsiniz (pull):

```bash
php bin/console translation:push loco --force
php bin/console translation:push loco --locales fr --domains validators
php bin/console translation:push loco --delete-missing --locales fr --domains validators
php bin/console translation:push --help

php bin/console translation:pull loco --force
php bin/console translation:pull loco --locales fr --domains validators
php bin/console translation:pull --help
php bin/console translation:pull loco --force --as-tree
```

---

### 🧠 Özel Sağlayıcılar Oluşturma

Symfony’nin yerleşik çeviri sağlayıcılarına ek olarak, kendi özel sağlayıcılarınızı da oluşturabilirsiniz. Bunun için iki sınıf oluşturmanız gerekir:

1. **ProviderInterface** arayüzünü uygulayan bir sağlayıcı sınıfı,
2. Bu sınıfın örneklerini oluşturan bir fabrika sınıfı ( **ProviderFactoryInterface** ’i uygulamalı, tercihen `AbstractProviderFactory`’den türetilmeli).

Bu iki sınıfı oluşturduktan sonra fabrikanızı bir servis olarak kaydedin ve `translation.provider_factory` etiketiyle işaretleyin.


## 🌐 Kullanıcının Locale Bilgisini Yönetme

### 🗣️ Kullanıcı Locale’ine Göre Çeviri

Çeviri işlemi kullanıcının **locale** bilgisine göre gerçekleşir. Mevcut kullanıcının locale bilgisi, `Request` nesnesinde saklanır ve oradan erişilebilir:

```php
use Symfony\Component\HttpFoundation\Request;

public function index(Request $request): void
{
    $locale = $request->getLocale();
}
```

Kullanıcının locale’ini ayarlamak için, sistemin diğer bölümleri (örneğin çevirici) tarafından kullanılmadan önce ayarlanmasını sağlamak amacıyla özel bir **event listener** oluşturabilirsiniz:

```php
public function onKernelRequest(RequestEvent $event): void
{
    $request = $event->getRequest();

    // $locale değerini belirleyen bir mantık
    $request->setLocale($locale);
}
```

Bu özel listener, locale’i istekten başlatan  **LocaleListener** ’dan önce çağrılmalıdır. Bunun için listener’ınızın önceliğini (priority) `LocaleListener`’ınkinden daha yüksek ayarlayın. `debug:event kernel.request` komutunu çalıştırarak mevcut önceliği öğrenebilirsiniz.

Kullanıcının locale bilgisinin oturum boyunca kalıcı olmasını sağlamak için **Sessions** bölümünü inceleyin.

> 💡 Controller içinde `$request->setLocale()` kullanmak, çevirici üzerinde etkili olmak için  **çok geçtir** . Locale’i bir listener aracılığıyla, URL üzerinden (aşağıda açıklanmıştır) veya çevirici servisine doğrudan `setLocale()` çağrısı yaparak ayarlayın.

---

### 🌍 Locale ve URL

Kullanıcının locale bilgisini oturumda saklayabiliyor olsanız da, aynı URL’yi farklı kullanıcılar için farklı dillerde göstermek iyi bir uygulama değildir.

Örneğin `http://www.example.com/contact` adresinin bir kullanıcıya İngilizce, diğerine Fransızca içerik göstermesi, web’in temel kuralını ihlal eder:

**Aynı URL her zaman aynı içeriği döndürmelidir.**

Bunun yerine locale bilgisini URL’ye dahil edin. Symfony, `_locale` adlı özel bir parametre sağlar:

```php
// config/routes.php
use App\Controller\ContactController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->add('contact', '/{_locale}/contact')
        ->controller([ContactController::class, 'index'])
        ->requirements([
            '_locale' => 'en|fr|de',
        ])
    ;
};
```

Bir route içinde `_locale` parametresi kullanıldığında, eşleşen locale değeri otomatik olarak `Request` nesnesine ayarlanır ve `getLocale()` metoduyla erişilebilir.

Yani bir kullanıcı `/fr/contact` adresini ziyaret ettiğinde, `fr` locale değeri otomatik olarak geçerli istek için ayarlanır.

> 🔸 Locale gereksinimini (örneğin `'en|fr|de'`) tüm route’larda tekrar yazmamak için bir **container parameter** olarak tanımlayabilirsiniz.

---

### ⚙️ Varsayılan Locale Ayarlamak

Kullanıcının locale bilgisi belirlenmediyse, her istekte bir locale değeri garanti altına almak için `default_locale` tanımlayabilirsiniz:

```php
// config/packages/translation.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->defaultLocale('en');
};
```

Bu `default_locale` ayarı, çevirici (translator) için de geçerlidir.

---

### 🌐 Kullanıcının Tercih Ettiği Dili Seçmek

Uygulamanız birden fazla dili destekliyorsa, kullanıcı siteyi ilk kez ziyaret ettiğinde onu tercih ettiği dile yönlendirmek yaygın bir yaklaşımdır.

Bu işlem, `Request` nesnesindeki `getPreferredLanguage()` metodu ile yapılır:

```php
// Request nesnesini bir şekilde alın (örneğin controller argümanı olarak)
$request = ...;

// Uygulamanızın desteklediği locale listesini gönderin
$locale = $request->getPreferredLanguage(['pt', 'fr_Latn_CH', 'en_US']);
```

Symfony, `Accept-Language` HTTP başlığına ve sağladığınız locale listesine göre en uygun dili bulur:

* Mükemmel eşleşme bulunamazsa, kısmi eşleşme aranır (örneğin `fr_CA` → `fr_Latn_CH`);
* Hiçbir eşleşme bulunamazsa, listede ilk belirtilen locale döndürülür (bu yüzden sıraya dikkat edin).

🆕 Kısmi locale eşleştirme özelliği **Symfony 7.1** sürümünde tanıtılmıştır.

---

### 🔁 Fallback Translation Locales

Diyelim ki kullanıcının locale değeri `es_AR` ve siz `"Symfony is great"` anahtarını çevirmek istiyorsunuz. Symfony aşağıdaki adımlarla uygun çeviriyi bulur:

1. `es_AR` (Arjantin İspanyolcası) dosyasında arar (`messages.es_AR.yaml`);
2. Bulunamazsa, varsa  **parent locale** ’de arar (örneğin `es_419`, Latin Amerika İspanyolcası);
3. Bulunamazsa, genel `es` (İspanyolca) dosyasında arar (`messages.es.yaml`);
4. Hâlâ bulunamazsa, yapılandırmada belirtilen **fallbacks** ayarına başvurur.

   Eğer bu ayar tanımlanmadıysa, `default_locale` değeri kullanılır.

```php
// config/packages/translation.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->translator()
        ->fallbacks(['en']);
};
```

Symfony, belirli bir locale’de çeviri bulamadığında eksik çeviriyi log dosyasına ekler. Ayrıntılar için **Framework Configuration Reference (FrameworkBundle)** bölümüne bakın.

---

### 🔄 Programatik Olarak Locale Değiştirme

Bazı durumlarda, uygulamanın locale’ini **geçici olarak** değiştirmek gerekebilir (örneğin, farklı dillerde e-posta şablonlarını işleyen bir komut).

Bunun için `LocaleSwitcher` sınıfını kullanabilirsiniz:

```php
use Symfony\Component\Translation\LocaleSwitcher;

class SomeService
{
    public function __construct(
        private LocaleSwitcher $localeSwitcher,
    ) {
    }

    public function someMethod(): void
    {
        $currentLocale = $this->localeSwitcher->getLocale();

        // Uygulama locale’ini 'fr' (Fransızca) olarak ayarla
        $this->localeSwitcher->setLocale('fr');

        // Locale’i varsayılan değere sıfırla (config’deki default_locale)
        $this->localeSwitcher->reset();

        // Belirli bir locale ile geçici olarak kod çalıştır
        $this->localeSwitcher->runWithLocale('es', function() {
            // örn. 'es' (İspanyolca) locale ile şablon oluştur, e-posta gönder, vb.
        });

        // İsteğe bağlı olarak geçerli locale argüman olarak alınabilir
        $this->localeSwitcher->runWithLocale('es', function(string $locale) {
            // $locale = 'es'
        });
    }
}
```

**LocaleSwitcher** şu bileşenlerin locale’ini değiştirir:

* `kernel.locale_aware` etiketiyle işaretlenmiş tüm servisler,
* PHP’nin varsayılan locale değeri (`\Locale::setDefault()`),
* `RequestContext` servisinin `_locale` parametresi (varsa, böylece oluşturulan URL’ler de yeni locale’i yansıtır).

> 🧩 `LocaleSwitcher` yalnızca geçerli istek süresince etkilidir; yönlendirmeden sonra (redirect) kaybolur.

Kalıcı hale getirmek için “locale’i istekler arasında nasıl koruyacağınızı” anlatan bölüme bakın.

Autowiring kullanıyorsanız, controller veya service bağımlılığına `LocaleSwitcher` tip ipucu (type hint) ekleyerek otomatik olarak inject ettirebilirsiniz.

Aksi halde `translation.locale_switcher` servisini manuel olarak enjekte edin.

---

### 🔍 Eksik veya Kullanılmayan Çevirileri Bulma

Çok sayıda dil ve mesajla çalışırken, hangi çevirilerin eksik veya kullanılmadığını takip etmek zor olabilir.

Symfony, bu durumu analiz etmek için `debug:translation` komutunu sağlar.

Örnek olarak:

```twig
{% trans %}Symfony is great{% endtrans %}
{{ 'Symfony is great'|trans }}
```

Ancak,  **form etiketleri** , **controller metinleri** veya **değişkenlerle dinamik çeviriler** otomatik olarak tespit edilmez:

```twig
{% set message = 'Symfony is great' %}
{{ message|trans }} {# bu algılanmaz #}
```

#### Örnek:

Varsayalım varsayılan locale `fr`, fallback locale `en` olarak ayarlanmış ve şu çeviriler mevcut:

```php
// translations/messages.fr.php
return [
    'Symfony is great' => 'Symfony est génial',
];

// translations/messages.en.php
return [
    'Symfony is great' => 'Symfony is great',
];
```

Komut:

```bash
php bin/console debug:translation fr
```

Çıktı:

```
---------  ------------------  ----------------------  -------------------------------
 State      Id                  Message Preview (fr)    Fallback Message Preview (en)
---------  ------------------  ----------------------  -------------------------------
 unused     Symfony is great    Symfony est génial      Symfony is great
---------  ------------------  ----------------------  -------------------------------
```

Bu tablo, `fr` locale’deki çeviriyi, fallback (`en`) çevirisini ve mesajın kullanım durumunu gösterir.

* `unused`: Çeviri mevcut ama kullanılmıyor
* Boş durum: Çeviri mevcut ve kullanılıyor
* `missing`: Çeviri eksik
* `fallback`: Aynı çeviri fallback locale’den kullanılıyor

Sadece belirli bir domain’i kontrol etmek için:

```bash
php bin/console debug:translation en --domain=messages
```

Sadece **eksik** veya **kullanılmayan** mesajları görmek için:

```bash
php bin/console debug:translation en --only-unused
php bin/console debug:translation en --only-missing
```

---

### 🚦 Debug Komutunun Çıkış Kodları

`debug:translation` komutu, çeviri durumuna göre farklı çıkış kodları döndürür.

Aşağıdaki sabitlerle bu durumları kontrol edebilirsiniz:

```php
use Symfony\Bundle\FrameworkBundle\Command\TranslationDebugCommand;

TranslationDebugCommand::EXIT_CODE_GENERAL_ERROR; // genel hata
TranslationDebugCommand::EXIT_CODE_MISSING;       // eksik çeviriler var
TranslationDebugCommand::EXIT_CODE_UNUSED;        // kullanılmayan çeviriler var
TranslationDebugCommand::EXIT_CODE_FALLBACK;      // fallback çeviriler kullanılıyor
```

Bu sabitler **bit mask** olarak tanımlanmıştır, bu yüzden birleştirilebilirler:

```php
if (TranslationDebugCommand::EXIT_CODE_MISSING | TranslationDebugCommand::EXIT_CODE_UNUSED) {
    // eksik ve/veya kullanılmayan çeviriler var
}
```



## 🧾 Çeviri Dosyalarındaki Hataları Bulma

Symfony, uygulama kodunu derlemeden önce tüm çeviri dosyalarını işler.

Eğer herhangi bir çeviri dosyasında hata varsa, Symfony size sorunun nedenini açıklayan bir hata mesajı gösterir.

Ancak isterseniz çeviri dosyalarınızın sözdizimini manuel olarak da doğrulayabilirsiniz.

Bunun için **YAML** ve **XLIFF** dosyalarını denetleyen aşağıdaki komutları kullanın:

```bash
php bin/console lint:yaml translations/messages.en.yaml
php bin/console lint:xliff translations/messages.en.xlf
```

Bir dizindeki tüm çeviri dosyalarını denetlemek için:

```bash
php bin/console lint:yaml translations
php bin/console lint:xliff translations
```

Belirli birden fazla dosya veya dizin kontrol etmek için:

```bash
php bin/console lint:yaml translations path/to/trans
php bin/console lint:xliff translations/messages.en.xlf translations/messages.es.xlf
```

---

### 📦 JSON veya GitHub Formatında Çıktı

Lint komutlarının sonuçlarını JSON biçiminde almak isterseniz `--format` seçeneğini kullanabilirsiniz:

```bash
php bin/console lint:yaml translations/ --format=json
php bin/console lint:xliff translations/ --format=json
```

GitHub Actions ortamında çalıştırıldığında Symfony çıktıyı otomatik olarak GitHub’ın gerektirdiği biçime dönüştürür.

İsterseniz bu formatı manuel olarak da zorlayabilirsiniz:

```bash
php bin/console lint:yaml translations/ --format=github
php bin/console lint:xliff translations/ --format=github
```

---

### 🧩 Bağımsız YAML Denetleyicisi (yaml-lint)

**Yaml** bileşeni, Symfony Console uygulaması oluşturmadan YAML dosyalarını doğrulamanızı sağlayan bağımsız bir `yaml-lint` aracı da sunar:

```bash
php vendor/bin/yaml-lint translations/
```

---

### ✅ İçerik Doğrulaması

`lint:yaml` ve `lint:xliff` komutları yalnızca dosyaların **sözdizimini** (YAML veya XML formatını) doğrular, içeriklerin doğruluğunu kontrol etmez.

Çeviri içeriklerinin de doğru olup olmadığını kontrol etmek için şu komutu kullanın:

```bash
php bin/console lint:translations
```

Belirli locale’leri denetlemek için:

```bash
php bin/console lint:translations --locale=it --locale=ja
```

🆕 `lint:translations` komutu **Symfony 7.2** sürümünde tanıtılmıştır.

---

### 🧪 Pseudo-localization Translator

 **Pseudo-localization translator** , yalnızca **geliştirme ortamında** kullanılmak üzere tasarlanmıştır.

Aşağıdaki görsel, bir web sayfasındaki tipik bir menüde bunun nasıl görünebileceğini gösterir:

![1761992991928](image/8_Translations/1761992991928.png)


## 🧪 Pseudo-localization Translator (Sahte Yerelleştirme Çevirmeni)

Aşağıdaki örnek görselde, kullanıcı dili **İspanyolca**ya geçtiğinde aynı menü gösterilmektedir.

Beklenmedik bir şekilde bazı metinler  **kesilmekte** , bazı içerikler ise **çok uzun hale gelerek taşmakta** ve tamamen görüntülenememektedir:

![1761993027525](image/8_Translations/1761993027525.png)




## 🌍 Pseudo-localization (Sahte Yerelleştirme)

Bu tür hatalar oldukça yaygındır; çünkü farklı diller, uygulamanın orijinal diline göre **daha uzun veya daha kısa** olabilir.

Bir diğer sık yapılan hata ise, uygulamanın yalnızca temel aksanlı harflerle (örneğin `é`, `ñ`) düzgün çalışıp çalışmadığını test etmek, ancak  **Lehçe** , **Çekçe** gibi dillerdeki daha karmaşık karakterleri test etmemektir.

Bu problemler, **pseudolocalization** adı verilen bir yazılım test yöntemiyle çözülebilir.

Bu yöntemde, metinler gerçek bir yabancı dile çevrilmek yerine, orijinal dilin **değiştirilmiş bir versiyonu** ile değiştirilir.

Örneğin:

`Account Settings` metni şu şekilde çevrilir:

**[!!! Àççôûñţ Šéţţîñĝš !!!]**

Bu yöntem iki ana sorunu çözer:

1. **Metin uzunluğu testi:**

   Orijinal metin, `[!!! !!!]` gibi karakterlerle uzatılarak, metinlerin daha uzun dillerde (örneğin Almanca veya Fransızca) nasıl davrandığı test edilir.

   Böylece **taşma ve kesilme** sorunları tespit edilir.
2. **Özel karakter testi:**

   Harfler benzer ama aksanlı karakterlerle değiştirilir.

   Bu, metni okunabilir tutarken aynı zamanda uygulamanın **özel karakterleri destekleyip desteklemediğini** test etmenizi sağlar.

---

### ⚙️ Symfony’de Pseudo-localization Desteği

Symfony, uygulamalardaki uluslararasılaştırma hatalarını tespit etmenize yardımcı olmak için **tam pseudolocalization desteği** sunar.

Bu özelliği `translator` yapılandırmasında etkinleştirebilirsiniz:

```php
// config/packages/translation.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework) {
    // ...
    $framework
        ->translator()
            ->pseudoLocalization()
                // karakterleri aksanlı versiyonlarıyla değiştir
                ->accents(true)
                // metinleri köşeli parantez içine al
                ->brackets(true)
                // metni uzatmak için eklenecek ekstra karakter oranı
                ->expansionFactor(1.4)
                // çevrilen içeriklerin orijinal HTML etiketlerini koru
                ->parseHtml(true)
                // bu HTML özniteliklerinin içeriğini de çevir
                ->localizableHtmlAttributes(['title'])
    ;
};
```

Artık uygulamanız, **okunabilir ama kasıtlı olarak “bozulmuş”** bu içerikleri gösterecektir.

Bu sayede uluslararasılaştırma (i18n) sorunlarını kolayca fark edebilirsiniz.

Symfony Demo uygulamasındaki farkı görmek için aşağıdaki örneğe bakabilirsiniz.

Bu, orijinal sayfadır:


![1761993058874](image/8_Translations/1761993058874.png)


## 🧩 Aynı Sayfa – Pseudo-localization Etkinleştirildiğinde

Aşağıdaki görselde, aynı sayfa **pseudo-localization** etkinleştirildiğinde nasıl göründüğü gösterilmektedir.

Metinler artık kasıtlı olarak uzatılmış, aksanlı karakterlerle değiştirilmiş ve köşeli parantezler içine alınmıştır.

Bu sayede, uygulamanın farklı dillerdeki metin uzunlukları ve özel karakterlerle nasıl davrandığını görsel olarak test edebilirsiniz.

![1761993108337](image/8_Translations/1761993108337.png)


## 🧾 Özet

**Symfony Translation** bileşeni sayesinde, bir uygulamayı uluslararasılaştırmak artık zahmetli bir süreç olmaktan çıkar ve şu adımlara indirgenir:

1. Uygulamanızdaki mesajları `trans()` metodu ile sarmalayarak soyutlayın;
2. Her mesajı birden fazla locale için çeviri dosyaları oluşturarak çevirin.

   Symfony, dosya adlarının belirli bir biçimi takip etmesi sayesinde bu dosyaları otomatik olarak bulur ve işler;
3. Kullanıcının locale bilgisini yönetin.

   Bu bilgi `Request` üzerinde saklanır, ancak kullanıcı oturumunda (session) da ayarlanabilir.

---

### 📚 Daha Fazla Bilgi

* **[How to Translate Messages using the ICU MessageFormat](https://chatgpt.com/g/g-p-6904ef4ae8fc81918bdb521301b0c9c6-symfony/c/6905dff3-9b64-832f-88e4-94e4265cc13a#)**
* **[The XLIFF format](https://chatgpt.com/g/g-p-6904ef4ae8fc81918bdb521301b0c9c6-symfony/c/6905dff3-9b64-832f-88e4-94e4265cc13a#)**
