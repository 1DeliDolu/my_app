## 🪝 Webhook

Webhook bileşeni, uzaktaki webhooks’lara yanıt vererek uygulamanızda eylemleri tetiklemek için kullanılır. Bu belge, diğer Symfony bileşenlerinde uzaktaki olayları dinlemek için webhooks kullanımına odaklanır.

### ⚙️ Kurulum

```
composer require symfony/webhook
```

### ✉️ Mailer Bileşeni ile Kullanım

Üçüncü taraf bir mailer sağlayıcısı kullanırken, bu sağlayıcıdan gelen webhook çağrılarını almak için Webhook bileşenini kullanabilirsiniz.

Şu anda aşağıdaki üçüncü taraf mailer sağlayıcıları webhooks’u desteklemektedir:

| Mailer Servisi | Parser Servis Adı                       |
| -------------- | ---------------------------------------- |
| AhaSend        | mailer.webhook.request_parser.ahasend    |
| Brevo          | mailer.webhook.request_parser.brevo      |
| Mandrill       | mailer.webhook.request_parser.mailchimp  |
| MailerSend     | mailer.webhook.request_parser.mailersend |
| Mailgun        | mailer.webhook.request_parser.mailgun    |
| Mailjet        | mailer.webhook.request_parser.mailjet    |
| Mailomat       | mailer.webhook.request_parser.mailomat   |
| Mailtrap       | mailer.webhook.request_parser.mailtrap   |
| Postmark       | mailer.webhook.request_parser.postmark   |
| Resend         | mailer.webhook.request_parser.resend     |
| Sendgrid       | mailer.webhook.request_parser.sendgrid   |
| Sweego         | mailer.webhook.request_parser.sweego     |

**7.1:** Resend ve MailerSend desteği eklendi.

**7.2:** Mandrill, Mailomat, Mailtrap ve Sweego entegrasyonları eklendi.

**7.3:** AhaSend entegrasyonu eklendi.

Kullanmak istediğiniz üçüncü taraf mailer sağlayıcısını Mailer bileşeni dokümantasyonunda anlatıldığı şekilde kurun. Bu belgede örnek olarak Mailgun kullanılmaktadır.

Sağlayıcıyı uygulamanıza bağlamak için Webhook bileşeni yönlendirmesini yapılandırmanız gerekir:

```php
// config/packages/framework.php
use App\Webhook\MailerWebhookParser;
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $frameworkConfig): void {
    $webhookConfig = $frameworkConfig->webhook();
    $webhookConfig
        ->routing('mailer_mailgun')
        ->service('mailer.webhook.request_parser.mailgun')
        ->secret('%env(MAILER_MAILGUN_SECRET)%')
    ;
};
```

Bu örnekte, `mailer_mailgun` yönlendirme adı olarak kullanılmaktadır. Yönlendirme adı benzersiz olmalıdır çünkü sağlayıcı ile webhook tüketici kodunuzu birbirine bağlayan şey budur.

Webhook yönlendirme adı, üçüncü taraf mailer sağlayıcısında yapılandırmanız gereken URL’nin bir parçasıdır. URL, alan adınız ile seçtiğiniz yönlendirme adının birleştirilmesiyle oluşur (örneğin: `https://example.com/webhook/mailer_mailgun`).

Mailgun için webhook için bir **secret** alırsınız. Bu secret’ı `MAILER_MAILGUN_SECRET` olarak saklayın (gizli yönetim sistemi veya `.env` dosyasında).

Tamamlandığında, gelen webhooks’lara tepki vermek için bir **RemoteEvent consumer** ekleyin (webhook yönlendirme adı sınıfınızı sağlayıcıya bağlayan şeydir).

Mailer webhooks için `MailerDeliveryEvent` veya `MailerEngagementEvent` olaylarına tepki verin:

```php
use Symfony\Component\RemoteEvent\Attribute\AsRemoteEventConsumer;
use Symfony\Component\RemoteEvent\Consumer\ConsumerInterface;
use Symfony\Component\RemoteEvent\Event\Mailer\MailerDeliveryEvent;
use Symfony\Component\RemoteEvent\Event\Mailer\MailerEngagementEvent;
use Symfony\Component\RemoteEvent\RemoteEvent;

#[AsRemoteEventConsumer('mailer_mailgun')]
class WebhookListener implements ConsumerInterface
{
    public function consume(RemoteEvent $event): void
    {
        if ($event instanceof MailerDeliveryEvent) {
            $this->handleMailDelivery($event);
        } elseif ($event instanceof MailerEngagementEvent) {
            $this->handleMailEngagement($event);
        } else {
            // Bu bir e-posta olayı değil
            return;
        }
    }

    private function handleMailDelivery(MailerDeliveryEvent $event): void
    {
        // Mail teslim olayını işle
    }

    private function handleMailEngagement(MailerEngagementEvent $event): void
    {
        // Mail etkileşim olayını işle
    }
}
```

### 📱 Notifier Bileşeni ile Kullanım

Notifier’da üçüncü taraf bir taşıyıcı (transport) kullanıldığında Webhook bileşeninin kullanımı, Mailer ile kullanımına çok benzer.

Şu anda aşağıdaki üçüncü taraf SMS taşıyıcıları webhooks’u desteklemektedir:

| SMS Servisi | Parser Servis Adı                     |
| ----------- | -------------------------------------- |
| Twilio      | notifier.webhook.request_parser.twilio |
| Smsbox      | notifier.webhook.request_parser.smsbox |
| Sweego      | notifier.webhook.request_parser.sweego |
| Vonage      | notifier.webhook.request_parser.vonage |

SMS webhooks için `SmsEvent` olayına tepki verin:

```php
use Symfony\Component\RemoteEvent\Attribute\AsRemoteEventConsumer;
use Symfony\Component\RemoteEvent\Consumer\ConsumerInterface;
use Symfony\Component\RemoteEvent\Event\Sms\SmsEvent;
use Symfony\Component\RemoteEvent\RemoteEvent;

#[AsRemoteEventConsumer('notifier_twilio')]
class WebhookListener implements ConsumerInterface
{
    public function consume(RemoteEvent $event): void
    {
        if ($event instanceof SmsEvent) {
            $this->handleSmsEvent($event);
        } else {
            // Bu bir SMS olayı değil
            return;
        }
    }

    private function handleSmsEvent(SmsEvent $event): void
    {
        // SMS olayını işle
    }
}
```

### 🧩 Özel Bir Webhook Oluşturma

MakerBundle **v1.58.0** sürümünden itibaren, kendi Webhook’unuzu oluşturmak için gerekli istek ayrıştırıcı ve consumer dosyalarını üretmek amacıyla aşağıdaki komutu çalıştırabilirsiniz:

```
php bin/console make:webhook
```
