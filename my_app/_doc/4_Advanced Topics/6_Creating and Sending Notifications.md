### 📨 Bildirim Oluşturma ve Gönderme

#### 🧩 Kurulum

Günümüz web uygulamaları, kullanıcılara mesaj göndermek için birçok farklı kanal kullanır (örneğin SMS, Slack mesajları, e-postalar, push bildirimleri vb.). Symfony’deki **Notifier** bileşeni, tüm bu kanalların üzerinde bir soyutlama katmanıdır. Mesajların nasıl gönderileceğini dinamik bir şekilde yönetmeyi sağlar. Notifier bileşenini kurmak için:

```
composer require symfony/notifier
```

---

#### 📡 Kanallar

Kanallar, bildirimlerin iletilebileceği farklı ortamları ifade eder. Bu kanallar arasında e-posta, SMS, sohbet servisleri, push bildirimleri vb. bulunur. Her kanal, **transports** kullanarak farklı sağlayıcılarla (örneğin Slack veya Twilio SMS) entegre olabilir.

**Notifier** bileşeni aşağıdaki kanalları destekler:

* **SMS kanalı** : Bildirimleri SMS mesajlarıyla telefonlara gönderir.
* **Chat kanalı** : Slack ve Telegram gibi sohbet servislerine bildirim gönderir.
* **Email kanalı** : Symfony Mailer ile entegredir.
* **Browser kanalı** : Flash mesajlarını kullanır.
* **Push kanalı** : Telefonlara ve tarayıcılara push bildirimleri gönderir.
* **Desktop kanalı** : Aynı cihazda masaüstü bildirimleri gösterir.

> 🆕  **Desktop kanalı** , Symfony 7.2 sürümünde tanıtılmıştır.

---

#### 📲 SMS Kanalı

 **SMS kanalı** , cep telefonlarına SMS mesajları göndermek için **Texter** sınıflarını kullanır. Bu özellik, SMS mesajlarını gönderen üçüncü taraf bir hizmete abone olmayı gerektirir. Symfony, birkaç popüler SMS servisiyle entegrasyon sağlar.

Eğer herhangi bir DSN değeri, URI içinde özel karakterler içeriyorsa (`: / ? # [ ] @ ! $ & ' ( ) * + , ; =` gibi), bu karakterleri **encode** etmeniz gerekir. Tüm ayrılmış karakterlerin listesini görmek için  **RFC 3986** ’ya bakabilir veya **urlencode()** fonksiyonunu kullanabilirsiniz.

---

#### 💬 Desteklenen Servisler ve DSN Bilgileri

| Servis          | Kurulum                                                                          | DSN                                                                                                                       | Webhook Desteği                               |
| --------------- | -------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------- |
| 46elks          | `composer require symfony/forty-six-elks-notifier`                             | `forty-six-elks://API_USERNAME:API_PASSWORD@default?from=FROM`                                                          | Hayır                                         |
| AllMySms        | `composer require symfony/all-my-sms-notifier`                                 | `allmysms://LOGIN:APIKEY@default?from=FROM`                                                                             | Hayır*(Ek özellikler: nbSms, balance, cost)* |
| AmazonSns       | `composer require symfony/amazon-sns-notifier`                                 | `sns://ACCESS_KEY:SECRET_KEY@default?region=REGION`                                                                     | Hayır                                         |
| Bandwidth       | `composer require symfony/bandwidth-notifier`                                  | `bandwidth://USERNAME:PASSWORD@default?from=FROM&account_id=ACCOUNT_ID&application_id=APPLICATION_ID&priority=PRIORITY` | Hayır                                         |
| Brevo           | `composer require symfony/brevo-notifier`                                      | `brevo://API_KEY@default?sender=SENDER`                                                                                 | ✅ Evet                                        |
| Clickatell      | `composer require symfony/clickatell-notifier`                                 | `clickatell://ACCESS_TOKEN@default?from=FROM`                                                                           | Hayır                                         |
| ContactEveryone | `composer require symfony/contact-everyone-notifier`                           | `contact-everyone://TOKEN@default?&diffusionname=DIFFUSION_NAME&category=CATEGORY`                                      | Hayır                                         |
| Esendex         | `composer require symfony/esendex-notifier`                                    | `esendex://USER_NAME:PASSWORD@default?accountreference=ACCOUNT_REFERENCE&from=FROM`                                     | Hayır                                         |
| FakeSms         | `composer require symfony/fake-sms-notifier`                                   | `fakesms+email://MAILER_SERVICE_ID?to=TO&from=FROM`veya `fakesms+logger://default`                                    | Hayır                                         |
| FreeMobile      | `composer require symfony/free-mobile-notifier`                                | `freemobile://LOGIN:API_KEY@default?phone=PHONE`                                                                        | Hayır                                         |
| GatewayApi      | `composer require symfony/gateway-api-notifier`                                | `gatewayapi://TOKEN@default?from=FROM`                                                                                  | Hayır                                         |
| GoIP            | `composer require symfony/go-ip-notifier`                                      | `goip://USERNAME:PASSWORD@HOST:80?sim_slot=SIM_SLOT`                                                                    | Hayır                                         |
| Infobip         | `composer require symfony/infobip-notifier`                                    | `infobip://AUTH_TOKEN@HOST?from=FROM`                                                                                   | Hayır                                         |
| Iqsms           | `composer require symfony/iqsms-notifier`                                      | `iqsms://LOGIN:PASSWORD@default?from=FROM`                                                                              | Hayır                                         |
| iSendPro        | `composer require symfony/isendpro-notifier`                                   | `isendpro://ACCOUNT_KEY_ID@default?from=FROM&no_stop=NO_STOP&sandbox=SANDBOX`                                           | Hayır                                         |
| KazInfoTeh      | `composer require symfony/kaz-info-teh-notifier`                               | `kaz-info-teh://USERNAME:PASSWORD@default?sender=FROM`                                                                  | Hayır                                         |
| LightSms        | `composer require symfony/light-sms-notifier`                                  | `lightsms://LOGIN:TOKEN@default?from=PHONE`                                                                             | Hayır                                         |
| LOX24           | `composer require symfony/lox24-notifier`                                      | `lox24://USER:TOKEN@default?from=FROM`                                                                                  | Hayır                                         |
| Mailjet         | `composer require symfony/mailjet-notifier`                                    | `mailjet://TOKEN@default?from=FROM`                                                                                     | Hayır                                         |
| MessageBird     | `composer require symfony/message-bird-notifier`                               | `messagebird://TOKEN@default?from=FROM`                                                                                 | Hayır                                         |
| MessageMedia    | `composer require symfony/message-media-notifier`                              | `messagemedia://API_KEY:API_SECRET@default?from=FROM`                                                                   | Hayır                                         |
| Mobyt           | `composer require symfony/mobyt-notifier`                                      | `mobyt://USER_KEY:ACCESS_TOKEN@default?from=FROM`                                                                       | Hayır                                         |
| Nexmo           | `composer require symfony/nexmo-notifier` *(Vonage lehine terk edilmiştir)* | -                                                                                                                         | -                                              |
| Octopush        | `composer require symfony/octopush-notifier`                                   | `octopush://USERLOGIN:APIKEY@default?from=FROM&type=TYPE`                                                               | Hayır                                         |
| OrangeSms       | `composer require symfony/orange-sms-notifier`                                 | `orange-sms://CLIENT_ID:CLIENT_SECRET@default?from=FROM&sender_name=SENDER_NAME`                                        | Hayır                                         |
| OvhCloud        | `composer require symfony/ovh-cloud-notifier`                                  | `ovhcloud://APPLICATION_KEY:APPLICATION_SECRET@default?consumer_key=CONSUMER_KEY&service_name=SERVICE_NAME`             | Hayır*(Ek özellik: totalCreditsRemoved)*     |
| Plivo           | `composer require symfony/plivo-notifier`                                      | `plivo://AUTH_ID:AUTH_TOKEN@default?from=FROM`                                                                          | Hayır                                         |
| Primotexto      | `composer require symfony/primotexto-notifier`                                 | `primotexto://API_KEY@default?from=FROM`                                                                                | Hayır                                         |
| Redlink         | `composer require symfony/redlink-notifier`                                    | `redlink://API_KEY:APP_KEY@default?from=SENDER_NAME&version=API_VERSION`                                                | Hayır                                         |
| RingCentral     | `composer require symfony/ring-central-notifier`                               | `ringcentral://API_TOKEN@default?from=FROM`                                                                             | Hayır                                         |
| Sendberry       | `composer require symfony/sendberry-notifier`                                  | `sendberry://USERNAME:PASSWORD@default?auth_key=AUTH_KEY&from=FROM`                                                     | Hayır                                         |
| Sendinblue      | `composer require symfony/sendinblue-notifier`                                 | `sendinblue://API_KEY@default?sender=PHONE`                                                                             | Hayır                                         |
| Sms77           | `composer require symfony/sms77-notifier`                                      | `sms77://API_KEY@default?from=FROM`                                                                                     | Hayır                                         |
| SimpleTextin    | `composer require symfony/simple-textin-notifier`                              | `simpletextin://API_KEY@default?from=FROM`                                                                              | Hayır                                         |
| Sinch           | `composer require symfony/sinch-notifier`                                      | `sinch://ACCOUNT_ID:AUTH_TOKEN@default?from=FROM`                                                                       | Hayır                                         |
| Sipgate         | `composer require symfony/sipgate-notifier`                                    | `sipgate://TOKEN_ID:TOKEN@default?senderId=SENDER_ID`                                                                   | Hayır                                         |
| SmsSluzba       | `composer require symfony/sms-sluzba-notifier`                                 | `sms-sluzba://USERNAME:PASSWORD@default`                                                                                | Hayır                                         |
| Smsapi          | `composer require symfony/smsapi-notifier`                                     | `smsapi://TOKEN@default?from=FROM`                                                                                      | Hayır                                         |
| Smsbox          | `composer require symfony/smsbox-notifier`                                     | `smsbox://APIKEY@default?mode=MODE&strategy=STRATEGY&sender=SENDER`                                                     | ✅ Evet                                        |
| SmsBiuras       | `composer require symfony/sms-biuras-notifier`                                 | `smsbiuras://UID:API_KEY@default?from=FROM&test_mode=0`                                                                 | Hayır                                         |
| Smsc            | `composer require symfony/smsc-notifier`                                       | `smsc://LOGIN:PASSWORD@default?from=FROM`                                                                               | Hayır                                         |
| SMSense         | `composer require smsense-notifier`                                            | `smsense://API_TOKEN@default?from=FROM`                                                                                 | Hayır                                         |
| SMSFactor       | `composer require symfony/sms-factor-notifier`                                 | `sms-factor://TOKEN@default?sender=SENDER&push_type=PUSH_TYPE`                                                          | Hayır                                         |
| SpotHit         | `composer require symfony/spot-hit-notifier`                                   | `spothit://TOKEN@default?from=FROM`                                                                                     | Hayır                                         |
| Sweego          | `composer require symfony/sweego-notifier`                                     | `sweego://API_KEY@default?region=REGION&campaign_type=CAMPAIGN_TYPE`                                                    | ✅ Evet                                        |
| Telnyx          | `composer require symfony/telnyx-notifier`                                     | `telnyx://API_KEY@default?from=FROM&messaging_profile_id=MESSAGING_PROFILE_ID`                                          | Hayır                                         |
| TurboSms        | `composer require symfony/turbo-sms-notifier`                                  | `turbosms://AUTH_TOKEN@default?from=FROM`                                                                               | Hayır                                         |
| Twilio          | `composer require symfony/twilio-notifier`                                     | `twilio://SID:TOKEN@default?from=FROM`                                                                                  | ✅ Evet                                        |
| Unifonic        | `composer require symfony/unifonic-notifier`                                   | `unifonic://APP_SID@default?from=FROM`                                                                                  | Hayır                                         |
| Vonage          | `composer require symfony/vonage-notifier`                                     | `vonage://KEY:SECRET@default?from=FROM`                                                                                 | ✅ Evet                                        |
| Yunpian         | `composer require symfony/yunpian-notifier`                                    | `yunpian://APIKEY@default`                                                                                              | Hayır                                         |


### 🔐 Symfony Gizli Yapılandırmaları ile Güvenli API Saklama

API anahtarlarınızı güvenli bir şekilde saklamak için **Symfony configuration secrets** kullanın.

Bazı üçüncü taraf  **transports** , API kullanırken webhooks aracılığıyla durum geri bildirimlerini destekler. Daha fazla bilgi için **Webhook** dokümantasyonuna bakın.

---

### 🆕 Sürüm Notları

* **7.1:**

  *Smsbox* ,  *SmsSluzba* ,  *SMSense* , *LOX24* ve *Unifonic* entegrasyonları eklendi.
* **7.2:**

  *Primotexto* , *Sipgate* ve *Sweego* entegrasyonları eklendi.
* **7.3:**

  *Brevo* entegrasyonu için **Webhook** desteği eklendi.

  Ayrıca *AllMySms* ve *OvhCloud* sağlayıcıları için **SentMessage** nesnesine ekstra özellikler eklendi.
* **7.1:**

  *Sms77* entegrasyonu, Symfony 7.1 itibarıyla  **kullanımdan kaldırıldı** . Bunun yerine **Seven.io** entegrasyonu kullanılmalıdır.

---

### 📱 Texter (SMS) Yapılandırması

Bir **Texter** etkinleştirmek için, doğru  **DSN** ’i `.env` dosyanıza ekleyin ve **texter_transports** yapılandırmasını tanımlayın:

```bash
# .env
TWILIO_DSN=twilio://SID:TOKEN@default?from=FROM
```

```php
// config/packages/notifier.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->notifier()
        ->texterTransport('twilio', env('TWILIO_DSN'))
    ;
};
```

---

### ✉️ TexterInterface Kullanımı

**TexterInterface** sınıfı, SMS mesajları göndermenizi sağlar:

```php
// src/Controller/SecurityController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\TexterInterface;
use Symfony\Component\Routing\Attribute\Route;

class SecurityController
{
    #[Route('/login/success')]
    public function loginSuccess(TexterInterface $texter): Response
    {
        $options = (new ProviderOptions())
            ->setPriority('high');

        $sms = new SmsMessage(
            '+1411111111',               // SMS gönderilecek telefon numarası
            'A new login was detected!', // Mesaj içeriği
            '+1422222222',               // Opsiyonel: varsayılan "from" değerini geçersiz kılabilir
            $options                     // Opsiyonel: MessageOptionsInterface uygulayan seçenek nesnesi
        );

        $sentMessage = $texter->send($sms);

        // ...
    }
}
```

**send()** metodu, mesaj kimliği ve orijinal içerik gibi bilgiler sağlayan bir **SentMessage** nesnesi döndürür.

---

### 💬 Chat Kanalı

Eğer DSN değerinde URI içinde özel karakterler (örneğin `: / ? # [ ] @ ! $ & ' ( ) * + , ; =`) varsa, bunları **encode** etmeniz gerekir. Ayrılmış karakterlerin tam listesi için  **RFC 3986** ’ya bakabilir veya **urlencode()** fonksiyonunu kullanabilirsiniz.

 **Chat kanalı** , kullanıcıya sohbet mesajları göndermek için **Chatter** sınıflarını kullanır. Symfony aşağıdaki sohbet servisleriyle entegredir:

| Servis         | Kurulum                                               | DSN                                                                            | Ek Özellikler |
| -------------- | ----------------------------------------------------- | ------------------------------------------------------------------------------ | -------------- |
| AmazonSns      | `composer require symfony/amazon-sns-notifier`      | `sns://ACCESS_KEY:SECRET_KEY@default?region=REGION`                          | —             |
| Bluesky        | `composer require symfony/bluesky-notifier`         | `bluesky://USERNAME:PASSWORD@default`                                        | `cid`        |
| Chatwork       | `composer require symfony/chatwork-notifier`        | `chatwork://API_TOKEN@default?room_id=ID`                                    | —             |
| Discord        | `composer require symfony/discord-notifier`         | `discord://TOKEN@default?webhook_id=ID`                                      | —             |
| FakeChat       | `composer require symfony/fake-chat-notifier`       | `fakechat+email://default?to=TO&from=FROM`veya `fakechat+logger://default` | —             |
| Firebase       | `composer require symfony/firebase-notifier`        | `firebase://USERNAME:PASSWORD@default`                                       | —             |
| GoogleChat     | `composer require symfony/google-chat-notifier`     | `googlechat://ACCESS_KEY:ACCESS_TOKEN@default/SPACE?thread_key=THREAD_KEY`   | —             |
| LINE Bot       | `composer require symfony/line-bot-notifier`        | `linebot://TOKEN@default?receiver=RECEIVER`                                  | —             |
| LINE Notify    | `composer require symfony/line-notify-notifier`     | `linenotify://TOKEN@default`                                                 | —             |
| LinkedIn       | `composer require symfony/linked-in-notifier`       | `linkedin://TOKEN:USER_ID@default`                                           | —             |
| Mastodon       | `composer require symfony/mastodon-notifier`        | `mastodon://ACCESS_TOKEN@HOST`                                               | —             |
| Matrix         | `composer require symfony/matrix-notifier`          | `matrix://HOST:PORT/?accessToken=ACCESSTOKEN&ssl=SSL`                        | —             |
| Mattermost     | `composer require symfony/mattermost-notifier`      | `mattermost://ACCESS_TOKEN@HOST/PATH?channel=CHANNEL`                        | —             |
| Mercure        | `composer require symfony/mercure-notifier`         | `mercure://HUB_ID?topic=TOPIC`                                               | —             |
| MicrosoftTeams | `composer require symfony/microsoft-teams-notifier` | `microsoftteams://default/PATH`                                              | —             |
| RocketChat     | `composer require symfony/rocket-chat-notifier`     | `rocketchat://TOKEN@ENDPOINT?channel=CHANNEL`                                | —             |
| Slack          | `composer require symfony/slack-notifier`           | `slack://TOKEN@default?channel=CHANNEL`                                      | —             |
| Telegram       | `composer require symfony/telegram-notifier`        | `telegram://TOKEN@default?channel=CHAT_ID`                                   | —             |
| Twitter        | `composer require symfony/twitter-notifier`         | `twitter://API_KEY:API_SECRET:ACCESS_TOKEN:ACCESS_SECRET@default`            | —             |
| Zendesk        | `composer require symfony/zendesk-notifier`         | `zendesk://EMAIL:TOKEN@SUBDOMAIN`                                            | —             |
| Zulip          | `composer require symfony/zulip-notifier`           | `zulip://EMAIL:TOKEN@HOST?channel=CHANNEL`                                   | —             |

---

### 🆕 Sürüm Notları (Chat)

* **7.1:** *Bluesky* entegrasyonu eklendi.
* **7.2:** *LINE Bot* entegrasyonu eklendi.
* **7.2:** *Gitter* entegrasyonu kaldırıldı (API artık mevcut değil).
* **7.3:** *Matrix* entegrasyonu eklendi.

---

### ⚙️ Messenger ile Bildirim Gönderimi

Varsayılan olarak, **Messenger** bileşeni yüklüyse bildirimler **MessageBus** aracılığıyla gönderilir. Eğer bir **message consumer** çalışmıyorsa, mesajlar asla gönderilmez.

Bu davranışı değiştirmek ve mesajları doğrudan transport üzerinden göndermek için şu yapılandırmayı ekleyin:

```yaml
# config/packages/notifier.yaml
framework:
    notifier:
        message_bus: false
```

---

### 💬 Chatter Yapılandırması

Chatter servisleri, **chatter_transports** ayarıyla yapılandırılır:

```bash
# .env
SLACK_DSN=slack://TOKEN@default?channel=CHANNEL
```

```php
// config/packages/notifier.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->notifier()
        ->chatterTransport('slack', env('SLACK_DSN'))
    ;
};
```

---

### 💬 ChatterInterface Kullanımı

**ChatterInterface** sınıfı, sohbet servislerine mesaj göndermenizi sağlar:

```php
// src/Controller/CheckoutController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Routing\Attribute\Route;

class CheckoutController extends AbstractController
{
    #[Route('/checkout/thankyou')]
    public function thankyou(ChatterInterface $chatter): Response
    {
        $message = (new ChatMessage('You got a new invoice for 15 EUR.'))
            // Eğer belirtilmezse, mesaj varsayılan transport üzerinden gönderilir
            ->transport('slack');

        $sentMessage = $chatter->send($message);

        // ...
    }
}
```

**send()** metodu, mesaj kimliği ve orijinal içerik gibi bilgileri sağlayan bir **SentMessage** nesnesi döndürür.


### 📧 Email Kanalı

 **Email kanalı** , bildirimleri göndermek için **Symfony Mailer** bileşenini kullanır ve özel bir sınıf olan **NotificationEmail** ile çalışır. Bunun için  **Twig bridge** ’in yanı sıra **Inky** ve **CSS Inliner** Twig uzantılarının da kurulması gerekir:

```
composer require symfony/twig-pack twig/cssinliner-extra twig/inky-extra
```

Bundan sonra  **Mailer** ’ı yapılandırın. Ayrıca bildirim e-postalarını göndermek için kullanılacak varsayılan bir **“from”** e-posta adresi de tanımlayabilirsiniz:

```php
// config/packages/mailer.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->mailer()
        ->dsn(env('MAILER_DSN'))
        ->envelope()
            ->sender('notifications@example.com')
    ;
};
```

---

### 📲 Push Kanalı

Eğer DSN değerinde URI içinde özel karakterler (örneğin `: / ? # [ ] @ ! $ & ' ( ) * + , ; =`) varsa, bunları **encode** etmeniz gerekir. Ayrılmış karakterlerin tam listesi için  **RFC 3986** ’ya bakabilir veya **urlencode()** fonksiyonunu kullanabilirsiniz.

 **Push kanalı** , kullanıcılara bildirim göndermek için **Texter** sınıflarını kullanır. Symfony aşağıdaki push servisleriyle entegredir:

| Servis     | Kurulum                                          | DSN                                                                            |
| ---------- | ------------------------------------------------ | ------------------------------------------------------------------------------ |
| Engagespot | `composer require symfony/engagespot-notifier` | `engagespot://API_KEY@default?campaign_name=CAMPAIGN_NAME`                   |
| Expo       | `composer require symfony/expo-notifier`       | `expo://TOKEN@default`                                                       |
| Novu       | `composer require symfony/novu-notifier`       | `novu://API_KEY@default`                                                     |
| Ntfy       | `composer require symfony/ntfy-notifier`       | `ntfy://default/TOPIC`                                                       |
| OneSignal  | `composer require symfony/one-signal-notifier` | `onesignal://APP_ID:API_KEY@default?defaultRecipientId=DEFAULT_RECIPIENT_ID` |
| PagerDuty  | `composer require symfony/pager-duty-notifier` | `pagerduty://TOKEN@SUBDOMAIN`                                                |
| Pushover   | `composer require symfony/pushover-notifier`   | `pushover://USER_KEY:APP_TOKEN@default`                                      |
| Pushy      | `composer require symfony/pushy-notifier`      | `pushy://API_KEY@default`                                                    |

> 🆕  **Pushy entegrasyonu** , Symfony **7.1** sürümünde tanıtılmıştır.

Bir **Texter** etkinleştirmek için, doğru  **DSN** ’i `.env` dosyanıza ekleyin ve **texter_transports** yapılandırmasını tanımlayın:

```bash
# .env
EXPO_DSN=expo://TOKEN@default
```

```php
// config/packages/notifier.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->notifier()
        ->texterTransport('expo', env('EXPO_DSN'))
    ;
};
```

---

### 💻 Desktop Kanalı

 **Desktop kanalı** , aynı cihazda yerel masaüstü bildirimlerini göstermek için **Texter** sınıflarını kullanır. Şu anda Symfony, aşağıdaki sağlayıcıyla entegredir:

| Sağlayıcı | Kurulum                                          | DSN                     |
| ------------ | ------------------------------------------------ | ----------------------- |
| JoliNotif    | `composer require symfony/joli-notif-notifier` | `jolinotif://default` |

> 🆕  **JoliNotif bridge** , Symfony **7.2** sürümünde tanıtılmıştır.

Eğer **Symfony Flex** kullanıyorsanız, bu paketin kurulumu gerekli ortam değişkeni ve yapılandırmayı otomatik olarak oluşturur. Aksi durumda, aşağıdakileri manuel olarak eklemeniz gerekir:

#### `.env` dosyasına DSN ekleyin:

```bash
# .env
JOLINOTIF=jolinotif://default
```

#### Notifier yapılandırmasını güncelleyin:

```php
// config/packages/notifier.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->notifier()
        ->texterTransport('jolinotif', env('JOLINOTIF'))
    ;
};
```

---

### 💬 Masaüstü Bildirim Gönderimi

Artık masaüstünüze bildirim gönderebilirsiniz:

```php
// src/Notifier/SomeService.php
use Symfony\Component\Notifier\Message\DesktopMessage;
use Symfony\Component\Notifier\TexterInterface;
// ...

class SomeService
{
    public function __construct(
        private TexterInterface $texter,
    ) {
    }

    public function notifyNewSubscriber(User $user): void
    {
        $message = new DesktopMessage(
            'New subscription! 🎉',
            sprintf('%s is a new subscriber', $user->getFullName())
        );

        $this->texter->send($message);
    }
}
```

Bu bildirimler, işletim sisteminize bağlı olarak özel sesler, simgeler ve benzeri özelliklerle özelleştirilebilir:

```php
use Symfony\Component\Notifier\Bridge\JoliNotif\JoliNotifOptions;
// ...

$options = (new JoliNotifOptions())
    ->setIconPath('/path/to/icons/error.png')
    ->setExtraOption('sound', 'sosumi')
    ->setExtraOption('url', 'https://example.com');

$message = new DesktopMessage('Production is down', <<<CONTENT
    ❌ Server prod-1 down
    ❌ Server prod-2 down
    ✅ Network is up
    CONTENT, $options);

$texter->send($message);
```

---

### 🔁 Failover veya Round-Robin Transports Yapılandırması

Birden fazla transport tanımlamanın yanı sıra, özel `||` ve `&&` karakterlerini kullanarak **failover** veya **round-robin** yapısını uygulayabilirsiniz:

```php
// config/packages/notifier.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->notifier()
        // Slack başarısız olursa Telegram'a gönder
        ->chatterTransport('main', env('SLACK_DSN').' || '.env('TELEGRAM_DSN'))

        // Bildirimleri round-robin yöntemiyle sırayla gönder
        ->chatterTransport('roundrobin', env('SLACK_DSN').' && '.env('TELEGRAM_DSN'))
    ;
};
```


### 🛠️ Bildirim Oluşturma ve Gönderme

Bir bildirim göndermek için,  **NotifierInterface** ’i (service ID: `notifier`) otomatik olarak bağlayın. Bu sınıfın **send()** metodu, bir **Notification** nesnesini bir  **Recipient** ’a göndermenizi sağlar:

```php
// src/Controller/InvoiceController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;

class InvoiceController extends AbstractController
{
    #[Route('/invoice/create')]
    public function create(NotifierInterface $notifier): Response
    {
        // ...

        // "email" kanalını kullanarak gönderilecek bir bildirim oluştur
        $notification = (new Notification('New Invoice', ['email']))
            ->content('You got a new invoice for 15 EUR.');

        // Bildirimin alıcısı
        $recipient = new Recipient(
            $user->getEmail(),
            $user->getPhonenumber()
        );

        // Bildirimi alıcıya gönder
        $notifier->send($notification, $recipient);

        // ...
    }
}
```

 **Notification** , iki argümanla oluşturulur: **konu (subject)** ve  **kanallar (channels)** .

Kanallar, bildirimin hangi kanallar (veya transportlar) aracılığıyla gönderileceğini belirtir.

Örneğin, `['email', 'sms']` kullanmak, bildirimi hem e-posta hem de SMS olarak gönderecektir.

Varsayılan bildirim ayrıca içeriği ve simgeyi ayarlamak için `content()` ve `emoji()` metodlarını sağlar.

---

### 👥 Symfony’deki Alıcı Türleri

* **NoRecipient**

  Varsayılandır ve alıcı hakkında bilgiye gerek olmadığında kullanılır.

  Örneğin, **browser channel** mevcut isteğin session flashbag’ini kullanır.
* **Recipient**

  Kullanıcının hem e-posta adresini hem de telefon numarasını içerebilir.

  Tüm kanallarda (mevcut oldukları sürece) kullanılabilir.

---

### ⚙️ Kanal Politikalarının Yapılandırılması

Bildirimin oluşturulması sırasında kanal belirlemek yerine, Symfony **önem (importance)** seviyelerine göre kanal politikaları tanımlamanıza da izin verir.

```php
// config/packages/notifier.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    // ...
    $framework->notifier()
        // Acil bildirimlerde SMS, Slack ve e-posta kullan
        ->channelPolicy('urgent', ['sms', 'chat/slack', 'email'])
        // Yüksek önem dereceli bildirimlerde Slack kullan
        ->channelPolicy('high', ['chat/slack'])
        // Orta ve düşük önem dereceli bildirimlerde tarayıcı kullan
        ->channelPolicy('medium', ['browser'])
        ->channelPolicy('low', ['browser'])
    ;
};
```

Artık bir bildirimin önemi **“high”** olarak ayarlandığında, bildirim **Slack** transportu üzerinden gönderilecektir:

```php
// ...
class InvoiceController extends AbstractController
{
    #[Route('/invoice/create')]
    public function invoice(NotifierInterface $notifier): Response
    {
        $notification = (new Notification('New Invoice'))
            ->content('You got a new invoice for 15 EUR.')
            ->importance(Notification::IMPORTANCE_HIGH);

        $notifier->send($notification, new Recipient('wouter@example.com'));

        // ...
    }
}
```

---

### 🧩 Bildirimleri Özelleştirme

**Notification** veya **Recipient** sınıflarını genişleterek davranışlarını özelleştirebilirsiniz.

Örneğin, fatura fiyatı çok yüksekse yalnızca SMS göndermek istiyorsanız `getChannels()` metodunu geçersiz kılabilirsiniz:

```php
namespace App\Notifier;

use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\RecipientInterface;
use Symfony\Component\Notifier\Recipient\SmsRecipientInterface;

class InvoiceNotification extends Notification
{
    public function __construct(
        private int $price,
    ) {
    }

    public function getChannels(RecipientInterface $recipient): array
    {
        if (
            $this->price > 10000
            && $recipient instanceof SmsRecipientInterface
        ) {
            return ['sms'];
        }

        return ['email'];
    }
}
```

---

### 💬 Bildirim Mesajlarını Özelleştirme

Her kanalın kendi özel bildirim arayüzü vardır.

Bunları uygulayarak, mesajların kanal bazında özelleştirilmesini sağlayabilirsiniz.

Örneğin, mesajı kullanılan sohbet servisine göre değiştirmek için

**ChatNotificationInterface** ve onun **asChatMessage()** metodunu uygulayabilirsiniz:

```php
// src/Notifier/InvoiceNotification.php
namespace App\Notifier;

use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Notification\ChatNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\RecipientInterface;

class InvoiceNotification extends Notification implements ChatNotificationInterface
{
    public function __construct(
        private int $price,
    ) {
    }

    public function asChatMessage(RecipientInterface $recipient, ?string $transport = null): ?ChatMessage
    {
        // Mesaj Slack'e gönderiliyorsa özel konu ve emoji ekle
        if ('slack' === $transport) {
            $this->subject('You\'re invoiced '.strval($this->price).' EUR.');
            $this->emoji('money');
            return ChatMessage::fromNotification($this);
        }

        // null döndürülürse, Notifier varsayılan şekilde ChatMessage oluşturur
        return null;
    }
}
```

Ayrıca  **SmsNotificationInterface** ,  **EmailNotificationInterface** ,

**PushNotificationInterface** ve **DesktopNotificationInterface** gibi diğer arayüzler de

ilgili kanallarda gönderilen mesajları özelleştirmek için kullanılabilir.

---

### 🌐 Tarayıcı Bildirimlerini (Flash Mesajlar) Özelleştirme

Varsayılan olarak,  **browser channel** , bildirimi flash mesaj olarak ekler

ve bildirimin konusu (subject) anahtar olarak kullanılır.

Ancak, bildirimin önem seviyesini **Bootstrap CSS** uyarı türleriyle eşleştirmek istiyorsanız,

kendi **FlashMessageImportanceMapperInterface** implementasyonunuzu tanımlayabilirsiniz.

Symfony, Bootstrap temelli bir implementasyon sunar:

 **BootstrapFlashMessageImportanceMapper** , bunu doğrudan etkinleştirebilirsiniz:

```php
// config/services.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Notifier\FlashMessage\BootstrapFlashMessageImportanceMapper;

return function(ContainerConfigurator $containerConfigurator) {
    $containerConfigurator->services()
        ->set('notifier.flash_message_importance_mapper', BootstrapFlashMessageImportanceMapper::class)
    ;
};
```

Bu sayede bildirimlerin önem seviyeleri, Bootstrap’in `alert-success`, `alert-warning`, `alert-danger` gibi görsel uyarı sınıflarıyla uyumlu hale gelir.


### 🧪 Notifier’ı Test Etme

Symfony, **Notifier** implementasyonunuzu test etmek için kullanışlı yöntemler sağlayan **NotificationAssertionsTrait** isimli bir trait sunar.

Bu sınıfı doğrudan kullanabilir veya  **KernelTestCase** ’i genişleterek ondan yararlanabilirsiniz.

Mevcut assertion’ların tam listesi için  **testing documentation** ’a bakabilirsiniz.

---

### 🚫 Bildirim Gönderimini Devre Dışı Bırakma

Geliştirme (veya test) sırasında, bildirimlerin gerçekten gönderilmesini tamamen devre dışı bırakmak isteyebilirsiniz.

Bunu, **dev** (ve/veya  **test** ) ortamında tüm yapılandırılmış **texter** ve **chatter** transportlarının **NullTransport** kullanmasını sağlayarak yapabilirsiniz:

```yaml
# config/packages/dev/notifier.yaml
framework:
    notifier:
        texter_transports:
            twilio: 'null://null'
        chatter_transports:
            slack: 'null://null'
```

---

### ⚙️ Olayları (Events) Kullanma

**Notifier** bileşeninin **Transport** sınıfı, mesaj gönderim döngüsüne olaylar aracılığıyla müdahale etmenize izin verir.

Bu sayede, mesaj gönderilmeden önce veya sonra özel işlemler yapabilirsiniz.

---

### 📤 MessageEvent Olayı

**Amaç:** Mesaj gönderilmeden **önce** bir işlem yapmak (örneğin, gönderilecek mesajı loglamak veya işlem öncesi bilgi göstermek).

Mesaj gönderilmeden hemen önce, **MessageEvent** olayı yayınlanır.

Dinleyiciler bu olayı yakalayabilir:

```php
use Symfony\Component\Notifier\Event\MessageEvent;

$dispatcher->addListener(MessageEvent::class, function (MessageEvent $event): void {
    // Mesaj örneğini al
    $message = $event->getMessage();

    // Loglama işlemi
    $this->logger(sprintf(
        'Message with subject: %s will be sent to %s',
        $message->getSubject(),
        $message->getRecipientId()
    ));
});
```

---

### ❌ FailedMessageEvent Olayı

**Amaç:** Mesaj gönderimi sırasında bir hata oluştuğunda, hata fırlatılmadan **önce** işlem yapmak

(örneğin, yeniden denemek veya ek bilgi loglamak).

Bir mesaj gönderilirken istisna (exception) oluşursa, **FailedMessageEvent** olayı yayınlanır.

Dinleyiciler bu olayı yakalayarak hata öncesi işlemler gerçekleştirebilir:

```php
use Symfony\Component\Notifier\Event\FailedMessageEvent;

$dispatcher->addListener(FailedMessageEvent::class, function (FailedMessageEvent $event): void {
    // Mesaj örneğini al
    $message = $event->getMessage();

    // Hata örneğini al
    $error = $event->getError();

    // Loglama işlemi
    $this->logger(sprintf(
        'The message with subject: %s has not been sent successfully. The error is: %s',
        $message->getSubject(),
        $error->getMessage()
    ));
});
```

---

### ✅ SentMessageEvent Olayı

**Amaç:** Mesaj başarılı bir şekilde gönderildiğinde işlem yapmak

(örneğin, sistemin döndürdüğü mesaj kimliğini almak veya başarı log’u eklemek).

Mesaj başarıyla gönderildikten sonra, **SentMessageEvent** olayı yayınlanır.

Dinleyiciler bu olayı yakalayarak işlem yapabilir:

```php
use Symfony\Component\Notifier\Event\SentMessageEvent;

$dispatcher->addListener(SentMessageEvent::class, function (SentMessageEvent $event): void {
    // Mesaj örneğini al
    $message = $event->getMessage();

    // Loglama işlemi
    $this->logger(sprintf(
        'The message has been successfully sent and has id: %s',
        $message->getMessageId()
    ));
});
```

---

Bu olay yapısı sayesinde Symfony  **Notifier** , geliştirme ve test aşamalarında

bildirim sürecinin her adımına müdahale etmenize olanak tanır — örneğin hata ayıklama, loglama veya özel iş akışlarını tetikleme gibi işlemler için.
