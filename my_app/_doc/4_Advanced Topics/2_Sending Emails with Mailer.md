📧 **Mailer ile E-posta Gönderimi**

Bu sayfayı düzenle

### ⚙️ Kurulum

Symfony'nin Mailer ve Mime bileşenleri, çok parçalı mesaj desteği, Twig entegrasyonu, CSS satır içi ekleme, dosya ekleri ve daha fazlasını içeren güçlü bir e-posta oluşturma ve gönderme sistemi oluşturur. Şunları yükleyerek kurulum yapın:

```
composer require symfony/mailer
```

---

### 🚚 Transport (Taşıyıcı) Kurulumu

E-postalar bir “transport” aracılığıyla iletilir. Varsayılan olarak, `.env` dosyanızdaki DSN’i yapılandırarak SMTP üzerinden e-posta gönderebilirsiniz (user, pass ve port parametreleri isteğe bağlıdır):

```bash
# .env
MAILER_DSN=smtp://user:pass@smtp.example.com:port
```

```php
// config/packages/mailer.php
use Symfony\Config\FrameworkConfig;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

return static function (FrameworkConfig $framework): void {
    $framework->mailer()->dsn(env('MAILER_DSN'));
};
```

Kullanıcı adı, parola veya host URI’de özel karakterler içeriyorsa (örneğin `: / ? # [ ] @ ! $ & ' ( ) * + , ; =`), bunları kodlamanız gerekir. Tüm ayrılmış karakterlerin tam listesi için RFC 3986’ya bakın veya bunları `urlencode` fonksiyonu ile kodlayın.

---

### 🧰 Dahili Transport Türleri

| DSN protokolü     | Örnek                               | Açıklama                                                                                                                                                                                          |
| ------------------ | ------------------------------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **smtp**     | smtp://user:pass@smtp.example.com:25 | Mailer bir SMTP sunucusu kullanır                                                                                                                                                                  |
| **sendmail** | sendmail://default                   | Mailer yerel sendmail binary’sini kullanır                                                                                                                                                        |
| **native**   | native://default                     | Mailer, php.ini’deki `sendmail_path`ayarına göre sendmail binary’sini kullanır. Windows üzerinde,`sendmail_path`yapılandırılmamışsa `smtp`ve `smtp_port`ayarlarına geri döner. |

`native://default` kullanırken, eğer php.ini `sendmail -t` komutunu kullanıyorsa hata raporlaması yapılmaz ve Bcc başlıkları kaldırılmaz. `native://default` kullanılması önerilmez; bunun yerine `sendmail://default` tercih edilmelidir.

---

### 🌐 3. Parti Transport Kullanımı

Kendi SMTP sunucunuz veya sendmail binary’niz yerine, üçüncü taraf bir sağlayıcı aracılığıyla e-posta gönderebilirsiniz:

| Servis     | Kurulum                                     | Webhook desteği |
| ---------- | ------------------------------------------- | ---------------- |
| AhaSend    | composer require symfony/aha-send-mailer    | ✔️             |
| Amazon SES | composer require symfony/amazon-mailer      |                  |
| Azure      | composer require symfony/azure-mailer       |                  |
| Brevo      | composer require symfony/brevo-mailer       | ✔️             |
| Infobip    | composer require symfony/infobip-mailer     |                  |
| Mailgun    | composer require symfony/mailgun-mailer     | ✔️             |
| Mailjet    | composer require symfony/mailjet-mailer     | ✔️             |
| Mailomat   | composer require symfony/mailomat-mailer    | ✔️             |
| MailPace   | composer require symfony/mail-pace-mailer   |                  |
| MailerSend | composer require symfony/mailer-send-mailer | ✔️             |
| Mailtrap   | composer require symfony/mailtrap-mailer    | ✔️             |
| Mandrill   | composer require symfony/mailchimp-mailer   | ✔️             |
| Postal     | composer require symfony/postal-mailer      |                  |
| Postmark   | composer require symfony/postmark-mailer    | ✔️             |
| Resend     | composer require symfony/resend-mailer      | ✔️             |
| Scaleway   | composer require symfony/scaleway-mailer    |                  |
| SendGrid   | composer require symfony/sendgrid-mailer    | ✔️             |
| Sweego     | composer require symfony/sweego-mailer      | ✔️             |

> 🆕  **Symfony 7.1** : Azure ve Resend entegrasyonları eklendi.
>
> 🆕  **Symfony 7.2** : Mailomat, Mailtrap, Postal ve Sweego entegrasyonları eklendi.
>
> 🆕  **Symfony 7.3** : AhaSend entegrasyonu eklendi.

Symfony ayrıca **Gmail** desteği de sunar (`composer require symfony/google-mailer`), ancak bu üretim ortamında kullanılmamalıdır. Geliştirme ortamında e-posta yakalayıcı kullanmanız önerilir. Çoğu desteklenen sağlayıcı ücretsiz katman da sunar.

Her kütüphane, `.env` dosyanıza bir yapılandırma örneği ekleyen bir Symfony Flex tarifi içerir. Örneğin, **SendGrid** kullanmak istiyorsanız:

```
composer require symfony/sendgrid-mailer
```

`.env` dosyanızda yeni bir satır görünür:

```bash
# .env
MAILER_DSN=sendgrid://KEY@default
```

`MAILER_DSN` gerçek bir adres değildir; yapılandırmanın çoğunu Mailer’a devreden uygun bir formattır. `sendgrid` şeması, SendGrid sağlayıcısını etkinleştirir ve teslimat protokolünü yönetir. Değiştirmeniz gereken tek kısım **KEY** kısmıdır.

Bazı sağlayıcılar, `?region=` gibi ek sorgu parametreleriyle ayarlanabilen seçeneklere sahiptir. Symfony varsayılan olarak en uygun protokolü seçer, ancak isterseniz belirli birini zorlayabilirsiniz:

```bash
# .env
# HTTP (varsayılan) yerine SMTP kullanmaya zorlamak için
MAILER_DSN=sendgrid+smtp://$SENDGRID_KEY@default
```

---

### 🧾 3. Parti Sağlayıcılar için DSN Formatları

| Sağlayıcı           | Formatlar                                                                                                                                          |
| ---------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------- |
| **AhaSend**      | SMTP `ahasend+smtp://USERNAME:PASSWORD@default`/ API `ahasend+api://KEY@default`                                                               |
| **Amazon SES**   | SMTP `ses+smtp://USERNAME:PASSWORD@default`/ HTTP `ses+https://ACCESS_KEY:SECRET_KEY@default`/ API `ses+api://ACCESS_KEY:SECRET_KEY@default` |
| **Azure**        | API `azure+api://ACS_RESOURCE_NAME:KEY@default`                                                                                                  |
| **Brevo**        | SMTP `brevo+smtp://USERNAME:PASSWORD@default`/ API `brevo+api://KEY@default`                                                                   |
| **Google Gmail** | SMTP `gmail+smtp://USERNAME:APP-PASSWORD@default`                                                                                                |
| **Infobip**      | SMTP `infobip+smtp://KEY@default`/ API `infobip+api://KEY@BASE_URL`                                                                            |
| **Mandrill**     | SMTP `mandrill+smtp://USERNAME:PASSWORD@default`/ HTTP `mandrill+https://KEY@default`/ API `mandrill+api://KEY@default`                      |
| **MailerSend**   | SMTP `mailersend+smtp://KEY@default`/ API `mailersend+api://KEY@BASE_URL`                                                                      |
| **Mailgun**      | SMTP `mailgun+smtp://USERNAME:PASSWORD@default`/ HTTP `mailgun+https://KEY:DOMAIN@default`/ API `mailgun+api://KEY:DOMAIN@default`           |
| **Mailjet**      | SMTP `mailjet+smtp://ACCESS_KEY:SECRET_KEY@default`/ API `mailjet+api://ACCESS_KEY:SECRET_KEY@default`                                         |
| **Mailomat**     | SMTP `mailomat+smtp://USERNAME:PASSWORD@default`/ API `mailomat+api://KEY@default`                                                             |
| **MailPace**     | API `mailpace+api://API_TOKEN@default`                                                                                                           |
| **Mailtrap**     | SMTP `mailtrap+smtp://PASSWORD@default`/ API `mailtrap+api://API_TOKEN@default`                                                                |
| **Postal**       | API `postal+api://API_KEY@BASE_URL`                                                                                                              |
| **Postmark**     | SMTP `postmark+smtp://ID@default`/ API `postmark+api://KEY@default`                                                                            |
| **Resend**       | SMTP `resend+smtp://resend:API_KEY@default`/ API `resend+api://API_KEY@default`                                                                |
| **Scaleway**     | SMTP `scaleway+smtp://PROJECT_ID:API_KEY@default`/ API `scaleway+api://PROJECT_ID:API_KEY@default`                                             |
| **SendGrid**     | SMTP `sendgrid+smtp://KEY@default`/ API `sendgrid+api://KEY@default`                                                                           |
| **Sweego**       | SMTP `sweego+smtp://LOGIN:PASSWORD@HOST:PORT`/ API `sweego+api://API_KEY@default`                                                              |


🔐 **Kimlik Bilgilerinde Özel Karakterler Kullanımı**

Kimlik bilgileriniz özel karakterler içeriyorsa, bunları URL-encode etmeniz gerekir.

Örneğin, şu DSN:

```
ses+smtp://ABC1234:abc+12/345@default
```

şu şekilde yapılandırılmalıdır:

```
ses+smtp://ABC1234:abc%2B12%2F345@default
```

---

⚙️ **Messenger ile Arka Planda Gönderim**

`ses+smtp` transport’unu Messenger ile arka planda mesaj göndermek için kullanmak istiyorsanız, `MAILER_DSN` içine `ping_threshold` parametresini 10’dan küçük bir değerle eklemeniz gerekir:

```
ses+smtp://USERNAME:PASSWORD@default?ping_threshold=9
```

---

⏱️ **SMTP Zaman Aşımı**

SMTP kullanırken, bir mesaj gönderimi sırasında istisna fırlatılmadan önceki varsayılan zaman aşımı süresi, `php.ini` dosyasındaki `default_socket_timeout` seçeneği ile belirlenir.

---

🌐 **HTTP API ile E-posta Gönderimi**

SMTP dışında, birçok 3. parti transport e-posta göndermek için web API’si sunar. Bunu kullanmak için, köprü (bridge) paketine ek olarak `HttpClient` bileşenini yüklemeniz gerekir:

```
composer require symfony/http-client
```

---

📮 **Google Gmail Kullanımı**

Google Gmail’i kullanmak için, **2 Adımlı Doğrulama (2FA)** etkinleştirilmiş bir Google hesabınız olmalı ve kimlik doğrulamak için **Uygulama Parolası (App Password)** kullanmalısınız.

Ayrıca, Google hesabı parolanızı değiştirdiğinizde, App Password’lar iptal edilir ve yenisini oluşturmanız gerekir.

XOAUTH2 veya Gmail API gibi diğer yöntemler şu anda desteklenmemektedir.

Gmail yalnızca test amaçlı kullanılmalı, üretim ortamında gerçek bir sağlayıcı tercih edilmelidir.

---

🧪 **Varsayılan Host’u Geçersiz Kılma (Örneğin requestbin.com ile hata ayıklama)**

Varsayılan host’u değiştirmek için `default` yerine kendi host’unuzu yazabilirsiniz:

```bash
# .env
MAILER_DSN=mailgun+https://KEY:DOMAIN@requestbin.com
```

> Protokol her zaman  **HTTPS** ’tir ve değiştirilemez.

---

🚫 **Port Değişikliği**

Belirli transportlar (örneğin `mailgun+smtp`) manuel yapılandırma olmadan çalışacak şekilde tasarlanmıştır.

Bu transport türlerinde DSN’e port ekleyerek port değiştirmek desteklenmez.

Port değiştirmek istiyorsanız, bunun yerine `smtp` transport’unu kullanın:

```bash
# .env
MAILER_DSN=smtp://KEY:DOMAIN@smtp.eu.mailgun.org.com:25
```

---

📡 **Webhook Bildirimleri**

Bazı üçüncü taraf mailer’lar, API kullanırken webhooks aracılığıyla durum bildirimlerini destekler.

Daha fazla bilgi için Webhook dokümantasyonuna bakın.

---

🛡️ **Yüksek Erişilebilirlik (High Availability)**

Symfony Mailer, bir sunucu arızalansa bile e-postaların gönderilmeye devam etmesini sağlayan **failover** tekniğiyle yüksek erişilebilirliği destekler.

```bash
MAILER_DSN="failover(postmark+api://ID@default sendgrid+smtp://KEY@default)"
```

Failover transport, ilk transport ile başlar ve başarısız olursa sıradakini dener.

Varsayılan olarak, başarısız bir gönderimden **60 saniye** sonra yeniden denenir.

Bu süreyi `retry_period` seçeneğiyle ayarlayabilirsiniz:

```bash
MAILER_DSN="failover(postmark+api://ID@default sendgrid+smtp://KEY@default)?retry_period=15"
```

> 🆕 `retry_period` seçeneği Symfony  **7.3** ’te eklendi.

---

⚖️ **Yük Dengeleme (Load Balancing)**

Symfony Mailer, yükü birden fazla transport arasında dağıtmak için **round-robin** tekniğini destekler:

```bash
MAILER_DSN="roundrobin(postmark+api://ID@default sendgrid+smtp://KEY@default)"
```

Round-robin transport, rastgele bir transport ile başlar ve her e-posta gönderiminde bir sonrakine geçer.

Varsayılan yeniden deneme süresi 60 saniyedir; `retry_period` ile değiştirilebilir:

```bash
MAILER_DSN="roundrobin(postmark+api://ID@default sendgrid+smtp://KEY@default)?retry_period=15"
```

> 🆕 `retry_period` seçeneği Symfony  **7.3** ’te eklendi.

---

🔒 **TLS Peer Doğrulaması**

Varsayılan olarak, SMTP transport’ları TLS peer doğrulaması yapar.

Bu davranış `verify_peer` seçeneğiyle yapılandırılabilir:

```php
$dsn = 'smtp://user:pass@smtp.example.com?verify_peer=0';
```

> Güvenlik nedeniyle devre dışı bırakılması önerilmez, ancak geliştirme sırasında veya self-signed sertifikalarda yararlı olabilir.

---

🧬 **TLS Peer Fingerprint Doğrulaması**

Ek güvenlik için `peer_fingerprint` seçeneğiyle parmak izi doğrulaması eklenebilir:

```php
$dsn = 'smtp://user:pass@smtp.example.com?peer_fingerprint=6A1CF3B08D175A284C30BC10DE19162307C7286E';
```

Parmak izi SHA1 veya MD5 olarak belirtilebilir.

---

🚫 **Otomatik TLS Devre Dışı Bırakma**

> 🆕 Symfony  **7.1** ’de tanıtıldı.

Varsayılan olarak, OpenSSL etkinse ve SMTP sunucusu STARTTLS destekliyorsa Mailer şifreleme kullanır.

Bu davranışı `auto_tls=false` ile devre dışı bırakabilirsiniz:

```php
$dsn = 'smtp://user:pass@10.0.0.25?auto_tls=false';
```

Bu ayar yalnızca `smtp://` protokolüyle çalışır.

Güvenli ağ ortamlarında şifreleme gereksizse kullanılabilir.

---

✅ **TLS Zorunlu Tutma (Ensure TLS)**

SMTP üzerinden gönderim yaparken TLS’in mutlaka kullanılmasını isteyebilirsiniz.

Bunu `require_tls=true` ile ayarlayın:

```php
$dsn = 'smtp://user:pass@10.0.0.25?require_tls=true';
```

> TLS kurulamazsa `TransportException` fırlatılır.
>
> 🆕 `require_tls` seçeneği Symfony  **7.3** ’te eklendi.

---

🌍 **IPv4 / IPv6 Bağlama**

> 🆕 Symfony  **7.3** ’te tanıtıldı.

Varsayılan olarak Mailer, mevcut arayüzlere göre IPv4 veya IPv6’ya bağlanır.

Belirli bir protokol veya IP’ye bağlanmak için `source_ip` seçeneğini kullanın:

```php
# IPv4
$dsn = 'smtp://smtp.example.com?source_ip=0.0.0.0';

# IPv6
$dsn = 'smtp://smtp.example.com?source_ip=[::]';
```

Bu ayar yalnızca `smtp://` protokolüyle çalışır.

---

🔑 **Varsayılan SMTP Kimlik Doğrulayıcılarını Geçersiz Kılma**

SMTP transport’ları varsayılan olarak tüm kimlik doğrulama yöntemlerini dener.

Belirli bir yöntemi öncelikli yapmak için `setAuthenticators()` kullanabilirsiniz:

```php
use Symfony\Component\Mailer\Transport\Smtp\Auth\XOAuth2Authenticator;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

// Seçenek 1
$transport = new EsmtpTransport(
    host: 'oauth-smtp.domain.tld',
    authenticators: [new XOAuth2Authenticator()]
);

// Seçenek 2
$transport->setAuthenticators([new XOAuth2Authenticator()]);
```

---

⚙️ **Diğer Seçenekler**

| Seçenek                          | Açıklama                                                                    | Örnek                                                                               |
| --------------------------------- | ----------------------------------------------------------------------------- | ------------------------------------------------------------------------------------ |
| **command**                 | sendmail transport’unun çalıştıracağı komut                            | `$dsn = 'sendmail://default?command=/usr/sbin/sendmail%20-oi%20-t'`                |
| **local_domain**            | HELO komutunda kullanılacak domain adı                                      | `$dsn = 'smtps://smtp.example.com?local_domain=example.org'`                       |
| **restart_threshold**       | Transport yeniden başlatılmadan önce gönderilecek maksimum mesaj sayısı | `$dsn = 'smtps://smtp.example.com?restart_threshold=10&restart_threshold_sleep=1'` |
| **restart_threshold_sleep** | Transport yeniden başlatılmadan önce bekleme süresi (saniye)              | `$dsn = 'smtps://smtp.example.com?restart_threshold=10&restart_threshold_sleep=1'` |
| **ping_threshold**          | İki mesaj arasında minimum süre (sunucuya ping atmak için)                | `$dsn = 'smtps://smtp.example.com?ping_threshold=200'`                             |
| **max_per_second**          | Saniye başına gönderilecek maksimum mesaj sayısı                         | `$dsn = 'smtps://smtp.example.com?max_per_second=2'`                               |

---

🧩 **Özel Transport Factory Oluşturma**

Kendi DSN türünüzü (`acme://`) desteklemek istiyorsanız, `TransportFactoryInterface`’i uygulayan özel bir sınıf oluşturabilirsiniz:

```php
// src/Mailer/AcmeTransportFactory.php
final class AcmeTransportFactory extends AbstractTransportFactory
{
    public function create(Dsn $dsn): TransportInterface
    {
        // DSN'i ayrıştır, bilgileri al ve transport'u oluştur
    }

    protected function getSupportedSchemes(): array
    {
        // 'acme://' DSN'lerini destekler
        return ['acme'];
    }
}
```

Sınıfı oluşturduktan sonra, uygulamanızda bir servis olarak kaydedin ve `mailer.transport_factory` etiketiyle etiketleyin.


📨 **Mesaj Oluşturma ve Gönderme**

Bir e-posta göndermek için `MailerInterface` türünde bir `Mailer` örneği alın ve bir `Email` nesnesi oluşturun:

```php
// src/Controller/MailerController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class MailerController extends AbstractController
{
    #[Route('/email')]
    public function sendEmail(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('hello@example.com')
            ->to('you@example.com')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html('<p>See Twig integration for better HTML integration!</p>');

        $mailer->send($email);

        // ...
    }
}
```

Hepsi bu kadar! Mesaj, yapılandırdığınız transport aracılığıyla hemen gönderilecektir.

Performansı artırmak için e-postaları **asenkron** olarak göndermek istiyorsanız, “Sending Messages Async” bölümüne bakın.

Ayrıca uygulamanızda **Messenger** bileşeni yüklüyse, tüm e-postalar varsayılan olarak asenkron gönderilir (bu davranışı değiştirebilirsiniz).

---

📧 **E-posta Adresleri**

E-posta adresi gerektiren tüm metotlar (`from()`, `to()`, vb.) hem string hem de `Address` nesnesini kabul eder:

```php
use Symfony\Component\Mime\Address;

$email = (new Email())
    // Basit string olarak e-posta adresi
    ->from('fabien@example.com')

    // ASCII dışı karakterler (ör. jânë.dœ@ëxãmplę.com) desteklenir
    ->from('jânë.dœ@ëxãmplę.com')

    // Nesne olarak e-posta adresi
    ->from(new Address('fabien@example.com'))

    // Ad ve e-posta adresi nesne olarak
    ->from(new Address('fabien@example.com', 'Fabien'))

    // Ad ve e-posta adresi string olarak
    ->from(Address::create('Fabien Potencier <fabien@example.com>'));
```

Her e-postada `->from()` çağırmak yerine, tüm e-postalar için **global bir From adresi** tanımlayabilirsiniz.

> 🆕 **Symfony 7.2** ile birlikte, ASCII dışı e-posta adresleri (ör. `jânë.dœ@ëxãmplę.com`) desteklenmeye başladı.

`@` öncesindeki kısım (local part) UTF-8 karakterler içerebilir, ancak gönderici adresi için bu geçerli değildir (bounce sorunlarını önlemek için).

Örnekler: `föóbàr@example.com`, `用户@example.com`, `θσερ@example.com`.

Birden fazla alıcı eklemek için `addTo()`, `addCc()` veya `addBcc()` metodlarını kullanın:

```php
$email = (new Email())
    ->to('foo@example.com')
    ->addTo('bar@example.com')
    ->cc('cc@example.com')
    ->addCc('cc2@example.com');
```

Alternatif olarak, her metoda birden fazla adres de verebilirsiniz:

```php
$toAddresses = ['foo@example.com', new Address('bar@example.com')];

$email = (new Email())
    ->to(...$toAddresses)
    ->cc('cc1@example.com', 'cc2@example.com');
```

---

📑 **Mesaj Başlıkları (Headers)**

Mesajlar, içeriklerini tanımlayan birçok başlık alanı içerir. Symfony gerekli başlıkları otomatik olarak ayarlasa da, özel başlıklar ekleyebilirsiniz:

```php
$email = (new Email())
    ->getHeaders()
        // Otomatik e-postalara yanıt verilmemesi için özel başlık
        ->addTextHeader('X-Auto-Response-Suppress', 'OOF, DR, RN, NRN, AutoReply')

        // Çok değerli başlık eklemek için dizi kullanabilirsiniz
        ->addIdHeader('References', ['123@example.com', '456@example.com']);
```

Tüm e-postalara aynı başlıkları eklemek istiyorsanız, bunları global olarak da tanımlayabilirsiniz.

---

📝 **Mesaj İçerikleri**

E-posta içeriği (text ve HTML) basit string’ler veya PHP kaynakları (resource) olabilir:

```php
$email = (new Email())
    ->text('Lorem ipsum...')
    ->html('<p>Lorem ipsum...</p>')

    // Dosya akışını içeriğe bağlama
    ->text(fopen('/path/to/emails/user_signup.txt', 'r'))
    ->html(fopen('/path/to/emails/user_signup.html', 'r'));
```

Ayrıca Twig şablonlarını kullanarak HTML ve metin içeriklerini oluşturabilirsiniz (detaylar için “Twig: HTML & CSS” bölümüne bakın).

---

📎 **Dosya Ekleri**

Dosya eklemek için `addPart()` metodunu `File` nesnesiyle kullanın:

```php
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;

$email = (new Email())
    ->addPart(new DataPart(new File('/path/to/documents/terms-of-use.pdf')))
    ->addPart(new DataPart(new File('/path/to/documents/privacy.pdf'), 'Privacy Policy'))
    ->addPart(new DataPart(new File('/path/to/documents/contract.doc'), 'Contract', 'application/msword'));
```

Akış (stream) içeriğini doğrudan da ekleyebilirsiniz:

```php
$email = (new Email())
    ->addPart(new DataPart(fopen('/path/to/documents/contract.doc', 'r')));
```

---

🖼️ **Resim Gömme (Embedding Images)**

E-posta içinde resim göstermek istiyorsanız, ek olarak değil **embed** (gömülü) olarak eklemeniz gerekir. Twig ile oluşturulan e-postalarda bu işlem otomatik yapılır, aksi halde manuel ekleme gerekir.

```php
$email = (new Email())
    ->addPart((new DataPart(fopen('/path/to/images/logo.png', 'r'), 'logo', 'image/png'))->asInline())
    ->addPart((new DataPart(new File('/path/to/images/signature.gif'), 'footer-signature', 'image/gif'))->asInline());
```

HTML içinde gömülü resimlere `cid:` önekiyle referans verilir:

```php
$email = (new Email())
    ->addPart((new DataPart(fopen('/path/to/images/logo.png', 'r'), 'logo', 'image/png'))->asInline())
    ->addPart((new DataPart(new File('/path/to/images/signature.gif'), 'footer-signature', 'image/gif'))->asInline())
    ->html('<img src="cid:logo"> ... <img src="cid:footer-signature"> ...');
```

Arka plan resimleri için de aynı sözdizimi kullanılabilir:

```php
->html('... <div background="cid:footer-signature"> ... </div> ...');
```

Symfony, e-posta kaynaklarında benzersiz **Content-ID** değerleri oluşturur.

Ancak isterseniz özel bir `Content-ID` belirleyebilirsiniz:

```php
$part = new DataPart(new File('/path/to/images/signature.gif'));
$part->setContentId('footer-signature@my-app');

$email = (new Email())
    ->addPart($part->asInline())
    ->html('... <img src="cid:footer-signature@my-app"> ...');
```

---

⚙️ **E-postaları Global Olarak Yapılandırma**

Her `Email` nesnesinde `->from()` çağırmak yerine, bu değeri global olarak belirleyebilirsiniz. Aynı şey `->to()` ve başlıklar (headers) için de geçerlidir:

```php
// config/packages/mailer.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $mailer = $framework->mailer();
    $mailer
        ->envelope()
            ->sender('fabien@example.com')
            ->recipients(['foo@example.com', 'bar@example.com']);

    $mailer->header('From')->value('Fabien <fabien@example.com>');
    $mailer->header('Bcc')->value('baz@example.com');
    $mailer->header('X-Custom-Header')->value('foobar');
};
```

> ⚠️ Bazı üçüncü taraf sağlayıcılar, başlıklarda `from` gibi anahtar kelimeleri desteklemez.
>
> Global başlıkları ayarlamadan önce sağlayıcınızın belgelerini kontrol edin.



📤 **E-posta Gönderim Hatalarını Yönetme**

Symfony Mailer, bir e-postanın  **başarıyla gönderildiğini** , transport’unuzun (SMTP sunucusu veya üçüncü taraf sağlayıcı) e-postayı teslim almak üzere kabul ettiği anda varsayar.

Mesaj daha sonra sağlayıcı tarafında kaybolabilir veya iletilmeyebilir, ancak bu Symfony uygulamanızın kontrolü dışındadır.

Eğer e-posta transport’a teslim edilirken bir hata oluşursa, Symfony bir **`TransportExceptionInterface`** fırlatır.

Bu hatayı yakalayarak hatadan kurtulabilir veya kullanıcıya bilgi verebilirsiniz:

```php
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

$email = new Email();
// ...
try {
    $mailer->send($email);
} catch (TransportExceptionInterface $e) {
    // E-posta gönderimi başarısız oldu; kullanıcıya hata göster veya tekrar dene
}
```

---

🧩 **E-postaları Hata Ayıklama (Debugging Emails)**

`MailerInterface` kullanıldığında `send()` metodu herhangi bir değer döndürmez, bu nedenle gönderilen e-posta bilgilerine erişemezsiniz.

Bunun nedeni, **Messenger** bileşeni kullanıldığında e-postaların **asenkron** olarak gönderilmesidir.

E-posta gönderim bilgilerine erişmek için `MailerInterface` yerine **`TransportInterface`** kullanın:

```diff
-use Symfony\Component\Mailer\MailerInterface;
+use Symfony\Component\Mailer\Transport\TransportInterface;
// ...

class MailerController extends AbstractController
{
    #[Route('/email')]
-   public function sendEmail(MailerInterface $mailer): Response
+   public function sendEmail(TransportInterface $mailer): Response
    {
        $email = (new Email())
            // ...

        $sentEmail = $mailer->send($email);

        // ...
    }
}
```

`TransportInterface`’in `send()` metodu bir **`SentMessage`** nesnesi döndürür.

Bu nesne, e-postayı **her zaman senkron** olarak gönderir (Messenger kullanılsa bile).

`SentMessage` nesnesi:

* **getOriginalMessage()** → gönderilen orijinal mesajı döndürür,
* **getDebug()** → HTTP çağrıları gibi hata ayıklama bilgilerini sağlar.

Ayrıca bu bilgilere şu olayları dinleyerek de erişebilirsiniz:

* `SentMessageEvent` → gönderilen mesajlara erişim sağlar,
* `FailedMessageEvent` → hata durumunda `getDebug()` bilgisine ulaşmanızı sağlar.

> Bazı mailer sağlayıcıları, e-posta gönderilirken `Message-Id` değerini değiştirir.
>
> `SentMessage::getMessageId()` metodu, her zaman **nihai (son)** kimliği döndürür.

`TransportException` arayüzünü uygulayan tüm istisnalar da `getDebug()` yöntemiyle hata ayıklama bilgilerini içerir.

---

💡 **Twig ile HTML ve CSS Kullanımı**

**Mime** bileşeni, gelişmiş özellikler sunmak için **Twig** ile entegre çalışır:

* CSS stillerini otomatik satır içine alma (inlining),
* HTML/CSS framework’leriyle uyumlu şablon desteği.

Kurulum:

```
composer require symfony/twig-bundle
```

---

### 🧱 HTML İçeriği Tanımlama

HTML içeriğini Twig şablonlarıyla tanımlamak için **`TemplatedEmail`** sınıfını kullanın:

```php
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

$email = (new TemplatedEmail())
    ->from('fabien@example.com')
    ->to(new Address('ryan@example.com'))
    ->subject('Thanks for signing up!')
    ->htmlTemplate('emails/signup.html.twig')
    ->locale('de')
    ->context([
        'expiration_date' => new \DateTime('+7 days'),
        'username' => 'foo',
    ]);
```

Şablonu oluşturun:

```twig
{# templates/emails/signup.html.twig #}
<h1>Welcome {{ email.toName }}!</h1>

<p>You signed up as {{ username }} using the following email:</p>
<p><code>{{ email.to[0].address }}</code></p>

<p>
    <a href="#">Activate your account</a>
    (this link is valid until {{ expiration_date|date('F jS') }})
</p>
```

Twig şablonları:

* `context()` metodunda belirtilen tüm değişkenlere erişebilir,
* `email` adlı özel bir değişkene (WrappedTemplatedEmail) sahiptir.

---

### 🪶 Metin İçeriği (Text Content)

Eğer `TemplatedEmail` için metin içeriği tanımlanmazsa, Symfony bunu  **HTML’den otomatik üretir** .

**Oluşturma sırası:**

1. Eğer `twig.mailer.html_to_text_converter` yapılandırılmışsa, o kullanılır.
2. Eğer `league/html-to-markdown` yüklüyse, HTML → Markdown dönüşümü yapılır.
3. Aksi halde `strip_tags()` PHP fonksiyonu uygulanır.

Metin içeriğini kendiniz tanımlamak isterseniz:

```php
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

$email = (new TemplatedEmail())
    ->htmlTemplate('emails/signup.html.twig')
    ->textTemplate('emails/signup.txt.twig');
```

---

### 🖼️ Twig ile Resim Gömme

`<img src="cid:...">` sözdizimiyle uğraşmadan, Twig’in `email.image()` yardımcı fonksiyonunu kullanabilirsiniz.

#### 1️⃣ Twig’te bir `images` namespace’i tanımlayın:

```php
// config/packages/twig.php
use Symfony\Config\TwigConfig;

return static function (TwigConfig $twig): void {
    $twig->path('%kernel.project_dir%/assets/images', 'images');
};
```

#### 2️⃣ Twig şablonunda resmi çağırın:

```twig
<img src="{{ email.image('@images/logo.png') }}" alt="Logo">

<h1>Welcome {{ email.toName }}!</h1>
```

Varsayılan olarak, dosya yolu dosya adı olarak kullanılır (`filename="@images/logo.png"`).

Bu davranışı değiştirmek için özel bir dosya adı belirtebilirsiniz:

```twig
<img src="{{ email.image('@images/logo.png', 'image/png', 'logo-acme.png') }}" alt="ACME Logo">
```

> 🆕 Üçüncü argüman (özel dosya adı) Symfony  **7.3** ’te eklendi.

---

### 🎨 CSS Stillerini Satır İçi Hale Getirme

Birçok e-posta istemcisi `<style>` etiketlerini desteklemez; bu nedenle CSS stillerini **inline** hale getirmek gerekir.

Twig’in **CssInlinerExtension** uzantısı bu işlemi otomatikleştirir.

Kurulum:

```
composer require twig/extra-bundle twig/cssinliner-extra
```

Kullanım:

```twig
{% apply inline_css %}
    <style>
        h1 { color: #333; }
    </style>

    <h1>Welcome {{ email.toName }}!</h1>
{% endapply %}
```

#### Harici CSS Dosyalarıyla Kullanım:

```twig
{% apply inline_css(source('@styles/email.css')) %}
    <h1>Welcome {{ username }}!</h1>
{% endapply %}
```

Birden fazla CSS dosyası tanımlayabilirsiniz. Bunun için `styles` namespace’i ekleyin:

```php
// config/packages/twig.php
$twig->path('%kernel.project_dir%/assets/styles', 'styles');
```

---

### ✍️ Markdown İçeriği Oluşturma

Twig’in **MarkdownExtension** uzantısı ile e-postaları Markdown formatında yazabilirsiniz.

Kurulum:

```
composer require twig/extra-bundle twig/markdown-extra league/commonmark
```

Kullanım:

```twig
{% apply markdown_to_html %}
Welcome {{ email.toName }}!
===========================

You signed up using:
`{{ email.to[0].address }}`

[Activate your account]({{ url('...') }})
{% endapply %}
```

---

### 🧩 Inky E-posta Şablon Dili

Görsel olarak zengin e-postalar oluşturmak için **Inky** framework’ünü kullanabilirsiniz.

Kurulum:

```
composer require twig/extra-bundle twig/inky-extra
```

Kullanım:

```twig
{% apply inky_to_html %}
<container>
    <row class="header">
        <columns>
            <spacer size="16"></spacer>
            <h1 class="text-center">Welcome {{ email.toName }}!</h1>
        </columns>
    </row>
</container>
{% endapply %}
```

#### Filtreleri Birleştirerek:

```twig
{% apply inky_to_html|inline_css(source('@styles/foundation-emails.css')) %}
    {# ... #}
{% endapply %}
```

Bu yapı, daha önce oluşturduğunuz `styles` namespace’ini kullanır.

Örneğin, `foundation-emails.css` dosyasını GitHub’dan indirip `assets/styles` dizinine kaydedebilirsiniz.


🔐 **Mesajları İmzalama ve Şifreleme**

E-posta mesajlarının bütünlüğünü ve güvenliğini artırmak için mesajları **imzalamak** ve/veya **şifrelemek** mümkündür.

Bu iki yöntem birlikte kullanılabilir — örneğin, imzalanmış bir mesajı şifrelemek veya şifrelenmiş bir mesajı imzalamak gibi.

---

### ⚙️ Ön Gereksinimler

İmzalama ve şifreleme yapmadan önce şunların doğru kurulduğundan emin olun:

* PHP’nin **OpenSSL** uzantısı yüklü ve yapılandırılmış olmalı
* Geçerli bir **S/MIME güvenlik sertifikası** mevcut olmalı

> 🔸 OpenSSL kullanarak sertifika oluştururken `-addtrust emailProtection` seçeneğini eklemeyi unutmayın.

---

### ⏱️ İşleme Sırası

İmzalama ve şifreleme işlemleri, mesaj içeriği tamamen oluşturulduktan sonra yapılmalıdır.

Örneğin, `TemplatedEmail` içeriği **MessageListener** tarafından işlenir.

Dolayısıyla böyle bir mesajı imzalamak/şifrelemek için, kendi dinleyicinizi (`MessageEvent` listener) **ondan sonra çalışacak şekilde (negatif öncelik)** ayarlamalısınız.

---

## ✍️ Mesajları İmzalama

Bir mesaj imzalandığında, tüm içeriğin (ekler dahil) kriptografik bir özeti (hash) oluşturulur.

Bu hash, mesajın bütünlüğünün alıcı tarafından doğrulanabilmesi için bir ek olarak eklenir.

Ancak mesajın içeriği hâlâ okunabilir durumda kalır — bu yüzden içeriği gizlemek istiyorsanız mesajı ayrıca  **şifrelemelisiniz** .

Mesajlar **S/MIME** veya **DKIM** kullanılarak imzalanabilir.

Her iki yöntemde de sertifika ve özel anahtar **PEM** formatında olmalıdır.

Alıcının, imzayı doğrulayabilmesi için CA sertifikasının güvenilir sertifika listesinde bulunması gerekir.

> ⚠️ İmzalı mesajlarda `Bcc` kullanımı kaldırılır.
>
> Birden fazla alıcıya göndermek istiyorsanız, her alıcı için ayrı bir imza oluşturmanız gerekir.

---

### 📜 **S/MIME İmzalayıcı (SMimeSigner)**

S/MIME, MIME verilerinin açık anahtar ile şifrelenmesi ve imzalanması için bir standarttır.

Bir sertifika ve özel anahtar gerektirir.

```php
use Symfony\Component\Mime\Crypto\SMimeSigner;
use Symfony\Component\Mime\Email;

$email = (new Email())
    ->from('hello@example.com')
    ->html('...');

$signer = new SMimeSigner('/path/to/certificate.crt', '/path/to/certificate-private-key.key');
// Parola varsa üçüncü argüman olarak geçebilirsiniz:
// new SMimeSigner('/path/to/certificate.crt', '/path/to/key.key', 'the-passphrase');

$signedEmail = $signer->sign($email);
// Artık $signedEmail'i Mailer bileşeniyle gönderebilirsiniz.
```

`SMimeSigner` sınıfı, ara sertifikalar eklemek veya `openssl_pkcs7_sign` için ek seçenekler tanımlamak amacıyla ek parametreleri de destekler.

---

### 📨 **DKIM İmzalayıcı (DkimSigner)**

DKIM, her e-postaya bir **dijital imza** ekleyen ve bunu alan adınızla ilişkilendiren bir doğrulama yöntemidir.

Bir **özel anahtar** gerektirir, ancak sertifika gerekmez.

```php
use Symfony\Component\Mime\Crypto\DkimSigner;
use Symfony\Component\Mime\Email;

$email = (new Email())
    ->from('hello@example.com')
    ->html('...');

$signer = new DkimSigner('file:///path/to/private-key.key', 'example.com', 'sf');
// Parola varsa beşinci argüman olarak geçilebilir.
// new DkimSigner('file:///path/to/private-key.key', 'example.com', 'sf', [], 'passphrase');

$signedEmail = $signer->sign($email);
```

`DkimSigner` ayrıca çeşitli yapılandırma seçeneklerini destekler:

```php
use Symfony\Component\Mime\Crypto\DkimOptions;

$signedEmail = $signer->sign($email, (new DkimOptions())
    ->bodyCanon('relaxed')
    ->headerCanon('relaxed')
    ->headersToIgnore(['Message-ID'])
    ->toArray()
);
```

---

### 🌍 **Global Mesaj İmzalama**

Her e-posta için ayrı imzalama nesnesi oluşturmak yerine, **global bir imzalama yapılandırması** tanımlayabilirsiniz.

```php
// config/packages/mailer.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $mailer = $framework->mailer();
    $mailer->dsn('%env(MAILER_DSN)%');

    $mailer->dkimSigner()
        ->key('file://%kernel.project_dir%/var/certificates/dkim.pem')
        ->domain('symfony.com')
        ->select('s1');

    $mailer->smimeSigner()
        ->key('%kernel.project_dir%/var/certificates/smime.key')
        ->certificate('%kernel.project_dir%/var/certificates/smime.crt')
        ->passphrase('');
};
```

> 🆕 **Global mesaj imzalama** özelliği Symfony  **7.3** ’te tanıtıldı.

---

## 🔒 Mesajları Şifreleme

Bir mesaj şifrelendiğinde, tüm mesaj (ekler dahil) alıcının sertifikası kullanılarak şifrelenir.

Böylece yalnızca ilgili **özel anahtara** sahip alıcı mesajı çözebilir.

```php
use Symfony\Component\Mime\Crypto\SMimeEncrypter;
use Symfony\Component\Mime\Email;

$email = (new Email())
    ->from('hello@example.com')
    ->html('...');

$encrypter = new SMimeEncrypter('/path/to/certificate.crt');
$encryptedEmail = $encrypter->encrypt($email);
```

Birden fazla sertifika da tanımlayabilirsiniz:

```php
$encrypter = new SMimeEncrypter([
    'jane@example.com' => '/path/to/first-certificate.crt',
    'john@example.com' => '/path/to/second-certificate.crt',
]);
```

---

### 🌐 **Global Mesaj Şifreleme**

Her e-posta için yeni bir `SMimeEncrypter` oluşturmak yerine, **global bir şifreleyici** tanımlayabilirsiniz:

```php
// config/packages/mailer.php
use App\Security\LocalFileCertificateRepository;
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $mailer = $framework->mailer();
    $mailer->smimeEncrypter()
        ->repository(LocalFileCertificateRepository::class);
};
```

Bu örnekte `repository`, `SmimeCertificateRepositoryInterface` arayüzünü uygulayan bir servistir:

```php
namespace App\Security;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\EventListener\SmimeCertificateRepositoryInterface;

class LocalFileCertificateRepository implements SmimeCertificateRepositoryInterface
{
    public function __construct(
        #[Autowire(param: 'kernel.project_dir')]
        private readonly string $projectDir
    ) {}

    public function findCertificatePathFor(string $email): ?string
    {
        $hash = hash('sha256', strtolower(trim($email)));
        $path = sprintf('%s/storage/%s.crt', $this->projectDir, $hash);

        return file_exists($path) ? $path : null;
    }
}
```

> 🆕 **Global mesaj şifreleme** yapılandırması Symfony  **7.3** ’te tanıtıldı.

---

## 🚚 Birden Fazla Mailer Transport Kullanımı

Birden fazla e-posta transport’u kullanmak için `dsn` yerine `transports` yapılandırması tanımlayabilirsiniz:

```php
// config/packages/mailer.php
use Symfony\Config\FrameworkConfig;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

return static function (FrameworkConfig $framework): void {
    $framework->mailer()
        ->transport('main', env('MAILER_DSN'))
        ->transport('alternative', env('MAILER_DSN_IMPORTANT'));
};
```

Varsayılan olarak ilk transport kullanılır.

Belirli bir transport’u seçmek için `X-Transport` başlığını ekleyin:

```php
// Varsayılan (main)
$mailer->send($email);

// Alternatif transport kullanımı
$email->getHeaders()->addTextHeader('X-Transport', 'alternative');
$mailer->send($email);
```

---

## 🕒 Mesajları Asenkron Gönderme

`$mailer->send($email)` çağrıldığında, e-posta hemen gönderilir.

Performansı artırmak için, **Messenger** kullanarak mesajları daha sonra gönderebilirsiniz.

### 1️⃣ Messenger yapılandırması:

```php
// config/packages/messenger.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->messenger()
        ->transport('async')->dsn(env('MESSENGER_TRANSPORT_DSN'));

    $framework->messenger()
        ->routing('Symfony\Component\Mailer\Messenger\SendEmailMessage')
        ->senders(['async']);
};
```

Artık `$mailer->send()` çağrıldığında, e-postalar **async** kuyruğuna aktarılacaktır.

> E-posta içeriği (header’lar, gövde) sadece gönderim sırasında oluşturulur.

---

### ⚠️ Serileştirilebilirlik

Asenkron gönderilen e-postalar **serileştirilebilir** olmalıdır.

`TemplatedEmail` kullanıyorsanız, `context` içeriğinin serileştirilebilir olduğundan emin olun.

Doctrine entity gibi serileştirilemeyen nesneler varsa, bunları önceden işleyin:

```php
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\BodyRendererInterface;

public function action(MailerInterface $mailer, BodyRendererInterface $bodyRenderer): void
{
    $email = (new TemplatedEmail())
        ->htmlTemplate($template)
        ->context($context);

    $bodyRenderer->render($email);
    $mailer->send($email);
}
```

---

### ⚙️ Özel Message Bus Ayarı

Farklı bir mesaj bus’ı kullanmak için `message_bus` seçeneğini ayarlayın:

```php
// config/packages/mailer.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->mailer()
        ->messageBus('app.another_bus');
};
```

---

### 🧩 Uzun Süreli Çalışan Scriptler

`SmtpTransport` kullanıyorsanız, SMTP bağlantısını manuel olarak kapatmak için `stop()` metodunu çağırabilirsiniz.

---

### 🚦 Bus Transport Başlığı

Belirli bir mesaj bus’ını e-posta bazında seçmek için `X-Bus-Transport` başlığını kullanın:

```php
$email->getHeaders()->addTextHeader('X-Bus-Transport', 'app.another_bus');
$mailer->send($email);
```


🏷️ **E-postalara Etiket ve Metadata Ekleme**

Bazı üçüncü taraf transport’lar, e-postalara **etiket** (tag) ve **metadata** eklemeyi destekler.

Bu özellik, e-postaları  **gruplama** , **izleme** veya **iş akışları** için kullanışlıdır.

Aşağıdaki sınıflarla etiket ve metadata ekleyebilirsiniz:

```php
use Symfony\Component\Mailer\Header\MetadataHeader;
use Symfony\Component\Mailer\Header\TagHeader;

$email->getHeaders()->add(new TagHeader('password-reset'));
$email->getHeaders()->add(new MetadataHeader('Color', 'blue'));
$email->getHeaders()->add(new MetadataHeader('Client-ID', '12345'));
```

Transport bu özellikleri desteklemiyorsa, Symfony bunları özel başlık olarak ekler:

```
X-Tag: password-reset
X-Metadata-Color: blue
X-Metadata-Client-ID: 12345
```

🔹 **Etiket ve metadata destekleyen transport’lar:**

* Brevo
* Mailgun
* Mailtrap
* Mandrill
* Postmark
* Sendgrid

🔹 **Sadece etiket destekleyen transport’lar:**

* MailPace
* Resend

🔹 **Sadece metadata destekleyen transport:**

* Amazon SES

  *(Amazon buna “tags” dese de, Symfony “metadata” olarak adlandırır çünkü anahtar-değer çiftleri içerir.)*

---

📥 **Taslak E-postalar (Draft Emails)**

`DraftEmail`, `Email` sınıfının özel bir türüdür.

Amaç: e-postayı (gövde, ekler vb. dahil) oluşturup **.eml** dosyası olarak indirilebilir hale getirmek.

Bu dosyalar çoğu e-posta istemcisi tarafından “taslak e-posta” olarak açılabilir.

```php
// src/Controller/DownloadEmailController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Mime\DraftEmail;
use Symfony\Component\Routing\Attribute\Route;

class DownloadEmailController extends AbstractController
{
    #[Route('/download-email')]
    public function __invoke(): Response
    {
        $message = (new DraftEmail())
            ->html($this->renderView(/* ... */))
            ->addPart(/* ... */);

        $response = new Response($message->toString());
        $contentDisposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'download.eml'
        );

        $response->headers->set('Content-Type', 'message/rfc822');
        $response->headers->set('Content-Disposition', $contentDisposition);

        return $response;
    }
}
```

> ⚠️ `DraftEmail` bir **taslak** olduğundan `To` veya `From` alanları olmayabilir ve **Mailer** ile gönderilemez.

---

📡 **Mailer Olayları (Events)**

### 📨 MessageEvent

**Sınıf:** `Symfony\Component\Mailer\Event\MessageEvent`

Bu olay, e-posta gönderilmeden önce mesajı veya zarfı (envelope) değiştirmeye izin verir:

```php
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Email;

public function onMessage(MessageEvent $event): void
{
    $message = $event->getMessage();
    if (!$message instanceof Email) {
        return;
    }

    // Örneğin loglama yapılabilir
    // veya Messenger damgaları (stamps) eklenebilir
    $event->addStamp(new SomeMessengerStamp());
}
```

Gönderimi durdurmak isterseniz:

```php
public function onMessage(MessageEvent $event): void
{
    $event->reject();
}
```

Kayıtlı dinleyicileri ve önceliklerini görmek için:

```
php bin/console debug:event-dispatcher "Symfony\Component\Mailer\Event\MessageEvent"
```

---

### ✅ SentMessageEvent

**Sınıf:** `Symfony\Component\Mailer\Event\SentMessageEvent`

E-posta gönderildikten sonra tetiklenir.

`SentMessage` nesnesine erişebilir, orijinal mesajı veya hata ayıklama bilgilerini alabilirsiniz:

```php
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\SentMessageEvent;

public function onMessage(SentMessageEvent $event): void
{
    $message = $event->getMessage();
    // örneğin mesaj kimliğini alabilirsiniz
}
```

Dinleyicileri görmek için:

```
php bin/console debug:event-dispatcher "Symfony\Component\Mailer\Event\SentMessageEvent"
```

---

### ❌ FailedMessageEvent

**Sınıf:** `Symfony\Component\Mailer\Event\FailedMessageEvent`

Gönderim başarısız olduğunda tetiklenir.

`getDebug()` ile hata hakkında detaylı bilgi alınabilir:

```php
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\FailedMessageEvent;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

public function onMessage(FailedMessageEvent $event): void
{
    $error = $event->getError();
    if ($error instanceof TransportExceptionInterface) {
        $error->getDebug();
    }

    // mesajla ilgili başka işlemler yapılabilir
}
```

Kayıtlı dinleyicileri görmek için:

```
php bin/console debug:event-dispatcher "Symfony\Component\Mailer\Event\FailedMessageEvent"
```

---

🧪 **Geliştirme ve Hata Ayıklama (Development & Debugging)**

### 🧲 E-posta Yakalama (Email Catcher)

Yerel geliştirme ortamında bir **email catcher** kullanmanız önerilir.

Symfony CLI veya Docker kullanıyorsanız, mailer DSN otomatik olarak yapılandırılır.

---

### ✉️ Test E-postası Gönderme

E-posta gönderiminin doğru çalıştığını test etmek için:

```
php bin/console mailer:test someone@example.com
```

> Bu komut, Messenger kuyruğunu atlayarak doğrudan gönderim yapar.

---

### 🚫 E-posta Teslimini Devre Dışı Bırakma

Geliştirme veya test ortamında gönderimi tamamen kapatabilirsiniz:

```php
// config/packages/mailer.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->mailer()->dsn('null://null');
};
```

> Messenger kullanıyorsanız, mesaj yine de ilgili transport’a gönderilir.

---

### 🎯 Her Zaman Aynı Adrese Gönderme

Gerçek adreslere gönderim yapmak yerine tüm e-postaları belirli bir adrese yönlendirebilirsiniz:

```php
// config/packages/mailer.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->mailer()
        ->envelope()
            ->recipients(['youremail@example.com']);
};
```

---

### 🧾 İzinli Alıcılar (Allowed Recipients)

Bazı e-postaların gerçek adreslerine gitmesine izin vermek için:

```php
// config/packages/mailer.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->mailer()
        ->envelope()
            ->recipients(['youremail@example.com'])
            ->allowedRecipients([
                'internal@example.com',
                'internal-.*@example.(com|fr)',
            ]);
};
```

Bu yapılandırma ile:

* Tüm e-postalar `youremail@example.com` adresine yönlendirilir,
* Ancak `internal@example.com`, `internal-monitoring@example.fr` gibi adreslere gönderim yapılmaya devam edilir.

> 🆕 `allowed_recipients` seçeneği Symfony  **7.1** ’de tanıtıldı.

---

🧩 **Fonksiyonel Test Yazma**

Symfony, mailer işlemleri için birçok yerleşik **assertion** sunar.

`KernelTestCase` sınıfından türeyen veya `MailerAssertionsTrait` kullanan testlerde kullanılabilir:

```php
// tests/Controller/MailControllerTest.php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MailControllerTest extends WebTestCase
{
    public function testMailIsSentAndContentIsOk(): void
    {
        $client = static::createClient();
        $client->request('GET', '/mail/send');
        $this->assertResponseIsSuccessful();

        $this->assertEmailCount(1); // Messenger kullanıyorsanız assertQueuedEmailCount() kullanın

        $email = $this->getMailerMessage();

        $this->assertEmailHtmlBodyContains($email, 'Welcome');
        $this->assertEmailTextBodyContains($email, 'Welcome');
    }
}
```

> Eğer kontrolör e-posta gönderdikten sonra yönlendirme (redirect) dönüyorsa,
>
> test istemcinizin **yönlendirmeleri takip etmediğinden** emin olun.
>
> Çünkü kernel yeniden başlatıldığında, mailer olayındaki mesaj kaybolur.
