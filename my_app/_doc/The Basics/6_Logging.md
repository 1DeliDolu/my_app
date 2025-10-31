### 🧾 Symfony’de Logging (Kayıt Tutma)

Symfony, hem HTTP hem CLI (komut satırı) ortamları için iki minimalist **PSR-3 uyumlu logger** ile gelir:

* **`Logger`** → HTTP isteklerinde kullanılır.
* **`ConsoleLogger`** → CLI komutları için kullanılır.

Her iki logger da **twelve-factor app** prensiplerine uygun şekilde, **WARNING** seviyesinden itibaren mesajları  **stderr** ’e gönderir.

---

## ⚙️ Minimum Log Seviyesini Değiştirme

Log seviyesi, ortam değişkeni olan **`SHELL_VERBOSITY`** ile ayarlanabilir:

| SHELL_VERBOSITY | Minimum Log Seviyesi |
| --------------- | -------------------- |
| `-1`          | ERROR                |
| `1`           | NOTICE               |
| `2`           | INFO                 |
| `3`           | DEBUG                |

Ayrıca, log seviyesi, çıktı hedefi ve formatı doğrudan `Logger` veya `ConsoleLogger` sınıfı yapıcılarına (constructor) parametre olarak verilebilir.

`Logger` servisine özel yapılandırma yapmak istiyorsanız, `logger` servis tanımını kendi ayarlarınızla **override** edebilirsiniz.

---

## 🧠 Bir Mesaj Loglamak

Bir log mesajı kaydetmek için, `LoggerInterface` servisini controller veya servisinize enjekte edin:

```php
use Psr\Log\LoggerInterface;
// ...

public function index(LoggerInterface $logger): Response
{
    $logger->info('I just got the logger');
    $logger->error('An error occurred');

    // Değişkenli log mesajları için yer tutucular (placeholders)
    $logger->debug('User {userId} has logged in', [
        'userId' => $this->getUserId(),
    ]);

    $logger->critical('I left the oven on!', [
        'cause' => 'in_hurry',
    ]);
}
```

### 🔍 Neden Yer Tutucu (Placeholder) Kullanmalısınız?

✅ **Log yönetimi kolaylaşır:**

Aynı log metnini paylaşan mesajlar, sadece değişken değerleri farklı olsa bile gruplanabilir.

✅ **Çeviri (i18n) kolaylaşır:**

Metinler sabit kalır, sadece değişkenler değişir.

✅ **Güvenlik artar:**

Kaçış işlemleri (escaping) doğru bağlama göre otomatik yapılır.

---

## 📚 Log Seviyeleri

`LoggerInterface`, farklı öncelik seviyelerinde mesaj yazmak için çeşitli metotlar sunar:

`emergency()`, `alert()`, `critical()`, `error()`, `warning()`, `notice()`, `info()`, `debug()`.

---

## 🧩 Monolog Entegrasyonu

Symfony, PHP ekosisteminde en popüler logging kütüphanesi olan **[Monolog](https://github.com/Seldaek/monolog)** ile doğrudan entegredir.

Monolog sayesinde:

* Farklı kaynaklara (dosya, e-posta, Slack, syslog vb.) log yazabilirsiniz.
* Log seviyesine göre farklı işlemler tetikleyebilirsiniz (örneğin hata olduğunda e-posta gönderme).

### 🔧 Kurulum

```bash
composer require symfony/monolog-bundle
```

---

## 📁 Logların Saklandığı Yer

| Ortam          | Logların Yazıldığı Yer |
| -------------- | --------------------------- |
| **dev**  | `var/log/dev.log`dosyası |
| **prod** | `stderr`akışı (stream) |

> 💡 Modern konteyner tabanlı uygulamalarda disk erişimi kısıtlı olduğu için, `stderr` kullanımı tercih edilir.

Eğer prod ortamında dosya kullanmak istiyorsanız:

```php
->path('%kernel.logs_dir%/prod.log')
```

---

## 🧱 Handlers (Log Yönlendiricileri)

Monolog, logları farklı yerlere yönlendirmek için bir **handler yığını** (stack) kullanır.

Her handler, belirli bir **log kaynağına** (dosya, veritabanı, syslog, Slack vs.) yazar.

Ayrıca, loglarınızı **kanallara (channels)** da ayırabilirsiniz.

Her kanalın kendi handler’ı olabilir — örneğin `security` logları farklı bir dosyada tutulabilir.

---

### 📘 Örnek: Stream + Syslog Handler’ları

```php
// config/packages/prod/monolog.php
use Psr\Log\LogLevel;
use Symfony\Config\MonologConfig;

return static function (MonologConfig $monolog): void {
    // Dosyaya yazan handler
    $monolog->handler('file_log')
        ->type('stream')
        ->path('%kernel.logs_dir%/%kernel.environment%.log')
        ->level(LogLevel::DEBUG);

    // Syslog'a yazan handler
    $monolog->handler('syslog_handler')
        ->type('syslog')
        ->level(LogLevel::ERROR);
};
```

---

## 🧭 Handler Öncelikleri (Priority)

Handler’lar bir **öncelik değeri (priority)** alabilir.

Yüksek öncelikli olanlar önce çağrılır:

```php
$monolog->handler('syslog_handler')
    ->type('syslog')
    ->priority(10); // ilk çağrılır
```

> 🔸 Aynı önceliğe sahip handler’lar, tanımlandıkları sırayla çalıştırılır.

---

## 🪄 Logları Filtreleyen veya Değiştiren Handler’lar

Bazı handler’lar logları yazmadan önce  **filtreler veya tamponlar (buffer)** .

En yaygın örnek:  **`fingers_crossed` handler** .

Bu handler, istekte oluşan tüm logları tutar, ancak yalnızca belirli bir seviye

örneğin `ERROR` olduğunda tümünü başka bir handler’a gönderir.

```php
// config/packages/prod/monolog.php
use Psr\Log\LogLevel;
use Symfony\Config\MonologConfig;

return static function (MonologConfig $monolog): void {
    $monolog->handler('filter_for_errors')
        ->type('fingers_crossed')
        ->actionLevel(LogLevel::ERROR)
        ->handler('file_log');

    $monolog->handler('file_log')
        ->type('stream')
        ->path('%kernel.logs_dir%/%kernel.environment%.log')
        ->level(LogLevel::DEBUG);

    $monolog->handler('syslog_handler')
        ->type('syslog')
        ->level(LogLevel::ERROR);
};
```

> ⚡ Bu sayede, bir istekte hata olursa o isteğe ait **tüm log mesajları** kaydedilir.
>
> Hata yoksa hiçbir şey yazılmaz — log dosyaları temiz kalır.

---

## 📦 Monolog’un Dahili Handler’ları

Monolog, onlarca yerleşik handler içerir:

* E-posta gönderimi (`swiftmailer`, `native_mail`)
* Log yönetimi servisleri (Loggly, Sentry)
* Bildirim servisleri (Slack, Telegram)
* Dönen dosya sistemi (`rotating_file`)

Tam liste için: [Monolog Configuration](https://symfony.com/doc/current/reference/configuration/monolog.html)

---

## 🔁 Log Dosyalarını Döndürme (Rotate)

Log dosyaları zamanla büyüyebilir.

Bunu önlemenin iki yolu vardır:

1. **Linux logrotate** komutu (sistem düzeyinde)
2. **Monolog’un `rotating_file` handler’ı**

```php
// config/packages/prod/monolog.php
use Psr\Log\LogLevel;
use Symfony\Config\MonologConfig;

return static function (MonologConfig $monolog): void {
    $monolog->handler('main')
        ->type('rotating_file')
        ->path('%kernel.logs_dir%/%kernel.environment%.log')
        ->level(LogLevel::DEBUG)
        ->maxFiles(10); // en fazla 10 günlük log sakla
};
```

---

## 🧩 Servis İçinde Logger Kullanımı

Uygulamanız **autoconfiguration** destekliyorsa,

`Psr\Log\LoggerAwareInterface` arayüzünü uygulayan her servis otomatik olarak bir logger alır.

```php
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class MyService implements LoggerAwareInterface
{
    private LoggerInterface $logger;

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
```

> 🔸 Belirli bir kanal (ör. `security`, `doctrine`) kullanmak istiyorsanız,
>
> `monolog.logger` etiketiyle (tag) channel belirtebilirsiniz.

---

## 🧱 Her Log’a Ek Bilgi Eklemek (Processor Kullanımı)

 **Processors** , her log kaydına otomatik olarak ekstra bilgi (örneğin `request_id`, `user_ip`) eklemenizi sağlar.

Böylece her mesaj, isteğe özel bağlam verisiyle kaydedilir.

Daha fazla bilgi:

📘 [How to Add extra Data to Log Messages via a Processor](https://symfony.com/doc/current/logging/processors.html)

---

## ⚡ Uzun Süreli İşlemlerde Log Yönetimi

Uzun süren işlemlerde (`queue consumer`, `worker`, `daemon`) loglar bellekte birikir

ve zamanla **hafıza taşması (memory leak)** veya **tekrarlanan loglar** oluşturabilir.

Bunu önlemek için her görevden sonra logger’ı sıfırlayın:

```php
$logger->reset();
```

---

## 📚 Daha Fazla Kaynak

* 📨 [How to Configure Monolog to Email Errors](https://symfony.com/doc/current/logging/monolog_email.html)
* 📁 [How to Log Messages to different Files](https://symfony.com/doc/current/logging/channels_handlers.html)
* 🧰 [How to Define a Custom Logging Formatter](https://symfony.com/doc/current/logging/formatter.html)
* 🔍 [How to Exclude Specific HTTP Codes from Logs](https://symfony.com/doc/current/logging/exclude_http_codes.html)

---

📜 **Lisans Bilgisi:**

Bu çalışma ve içeriğindeki kod örnekleri, [Creative Commons BY-SA 3.0](https://creativecommons.org/licenses/by-sa/3.0/) lisansı altındadır.
