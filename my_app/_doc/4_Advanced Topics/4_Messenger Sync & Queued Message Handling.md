# 📨 Messenger: Eşzamanlı ve Kuyruklu Mesaj İşleme

Messenger, mesajları gönderebilme ve bunları uygulamanızda hemen işleyebilme veya daha sonra işlenmek üzere transport’lar (örneğin kuyruklar) aracılığıyla gönderebilme yeteneğine sahip bir mesaj otobüsü (message bus) sağlar. Daha fazla bilgi için Messenger bileşeni belgelerini okuyun.

## ⚙️ Kurulum

Symfony Flex kullanan uygulamalarda Messenger’ı yüklemek için bu komutu çalıştırın:

```bash
composer require symfony/messenger
```

## 🧱 Mesaj ve Handler Oluşturma

Messenger, oluşturacağınız iki farklı sınıfa odaklanır:

(1) veriyi tutan bir **mesaj sınıfı** ve

(2) bu mesaj gönderildiğinde çağrılacak bir veya daha fazla **handler** sınıfı.

Handler sınıfı mesaj sınıfını okur ve bir veya daha fazla görev gerçekleştirir.

Mesaj sınıfı için özel bir gereksinim yoktur, sadece **serileştirilebilir** olması gerekir:

```php
// src/Message/SmsNotification.php
namespace App\Message;

class SmsNotification
{
    public function __construct(
        private string $content,
    ) {
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
```

Bir mesaj handler’ı bir PHP callable’dır. Bunu oluşturmanın önerilen yolu, `AsMessageHandler` özniteliğine sahip bir sınıf yaratmak ve `__invoke()` metodunu mesaj sınıfı (veya bir mesaj arayüzü) ile type-hint etmektir:

```php
// src/MessageHandler/SmsNotificationHandler.php
namespace App\MessageHandler;

use App\Message\SmsNotification;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SmsNotificationHandler
{
    public function __invoke(SmsNotification $message)
    {
        // ... bir SMS mesajı gönderme gibi işlemler yap!
    }
}
```

Ayrıca `#[AsMessageHandler]` özniteliğini bireysel sınıf metotlarında da kullanabilirsiniz.

Bir sınıfta bu özniteliği istediğiniz kadar metotta kullanarak birden fazla ilgili mesaj türünü gruplayabilirsiniz.

Autoconfiguration ve `SmsNotification` type-hint sayesinde Symfony, bu handler’ın bir `SmsNotification` mesajı gönderildiğinde çağrılması gerektiğini bilir.

Çoğu zaman bu yeterlidir. Ancak handler’ları manuel olarak da yapılandırabilirsiniz.

Tüm yapılandırılmış handler’ları görmek için şunu çalıştırın:

```bash
php bin/console debug:messenger
```

## 🚀 Mesajı Dispatch Etme

Artık hazırsınız! Mesajı göndermek (ve handler’ı çağırmak) için `messenger.default_bus` servisini (`MessageBusInterface` aracılığıyla) enjekte edin:

```php
// src/Controller/DefaultController.php
namespace App\Controller;

use App\Message\SmsNotification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

class DefaultController extends AbstractController
{
    public function index(MessageBusInterface $bus): Response
    {
        // SmsNotificationHandler çağrılacak
        $bus->dispatch(new SmsNotification('Bak! Bir mesaj oluşturdum!'));

        // ...
    }
}
```

## 🕓 Transport’lar: Asenkron/Kuyruklu Mesajlar

Varsayılan olarak mesajlar gönderildikleri anda işlenir.

Bir mesajı **asenkron** olarak işlemek istiyorsanız, bir **transport** yapılandırabilirsiniz.

Transport, mesajları (örneğin bir kuyruk sistemine) gönderebilen ve ardından bir worker aracılığıyla alabilen bir mekanizmadır.

Messenger birden fazla transport’u destekler.

Desteklenmeyen bir transport kullanmak istiyorsanız, Kafka veya Google Pub/Sub gibi servisleri destekleyen **Enqueue** transport’una bakın.

Bir transport bir “DSN” kullanılarak kaydedilir. Messenger’ın Flex tarifi sayesinde `.env` dosyanızda zaten bazı örnekler bulunur:

```bash
# MESSENGER_TRANSPORT_DSN=amqp://guest:guest@localhost:5672/%2f/messages
# MESSENGER_TRANSPORT_DSN=doctrine://default
# MESSENGER_TRANSPORT_DSN=redis://localhost:6379/messages
```

Kullanmak istediğiniz transport’u yorumdan çıkarın (veya `.env.local` içinde ayarlayın).

Ardından `config/packages/messenger.yaml` dosyasında `async` adında bir transport tanımlayın:

```php
// config/packages/messenger.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->messenger()
        ->transport('async')
            ->dsn(env('MESSENGER_TRANSPORT_DSN'))
    ;

    $framework->messenger()
        ->transport('async')
            ->dsn(env('MESSENGER_TRANSPORT_DSN'))
            ->options([])
    ;
};
```

## 🗺️ Mesajları Bir Transport’a Yönlendirme

Artık bir transport yapılandırdığınıza göre, bir mesajı hemen işlemek yerine onu bir transport’a gönderebilirsiniz:

```php
// config/packages/messenger.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->messenger()
        // async yukarıda verdiğiniz transport adıdır
        ->routing('App\Message\SmsNotification')->senders(['async'])
    ;
};
```

### 🆕 Symfony 7.2 Özelliği

`#[AsMessage]` özniteliği Symfony 7.2’de tanıtıldı.

Bunun sayesinde `App\Message\SmsNotification` mesajı `async` transport’una gönderilir ve handler(lar) hemen çağrılmaz.

Routing altında eşleşmeyen tüm mesajlar ise hâlâ **eşzamanlı** olarak işlenecektir.

Hem YAML/XML/PHP yapılandırma dosyaları hem de PHP öznitelikleriyle routing yapılandırırsanız, dosya yapılandırması  **önceliklidir** .

Bu, ortam bazında routing’i geçersiz kılmanıza olanak tanır.

Routing yapılandırırken `'App\Message\*'` gibi kısmi bir PHP namespace kullanarak o namespace altındaki tüm mesajları eşleştirebilirsiniz.

Tek gereksinim, `*` karakterinin namespace’in sonunda yer almasıdır.

`*` karakterini mesaj sınıfı olarak da kullanabilirsiniz.

Bu, routing altında eşleşmeyen tüm mesajlar için varsayılan bir routing kuralı işlevi görür.

Bu sayede hiçbir mesajın varsayılan olarak eşzamanlı işlenmemesini sağlayabilirsiniz.

Ancak `*` kuralı, Symfony Mailer tarafından gönderilen e-postalara da uygulanır (Messenger mevcutsa `SendEmailMessage` kullanılır).

Bu, eğer e-postalarınız serileştirilebilir değilse (örneğin dosya ekleri PHP kaynakları/akışları içeriyorsa) sorunlara neden olabilir.

Ayrıca mesajları üst sınıf veya arayüzlerine göre de yönlendirebilir veya birden fazla transport’a gönderebilirsiniz:

```php
// config/packages/messenger.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $messenger = $framework->messenger();
    // bu örnek üst sınıf veya arayüzü genişleten tüm mesajları yönlendir
    $messenger->routing('App\Message\AbstractAsyncMessage')->senders(['async']);
    $messenger->routing('App\Message\AsyncMessageInterface')->senders(['async']);
    $messenger->routing('My\Message\ToBeSentToTwoSenders')->senders(['async', 'audit']);
};
```

Hem alt hem de üst sınıf için routing yapılandırırsanız, her iki kural da uygulanır.

Örneğin `SmsNotification` sınıfı `Notification` sınıfından türemişse, hem `Notification` hem de `SmsNotification` için routing kuralları kullanılacaktır.

Bir mesajın hangi transport’u kullanacağını çalışma zamanında değiştirmek için mesajın zarfına (`envelope`) **TransportNamesStamp** ekleyebilirsiniz.

Bu damga, tek argüman olarak transport adlarının bir dizisini alır.

Stamps hakkında daha fazla bilgi için bkz.  **Envelopes & Stamps** .

## 🧩 Doctrine Entity’lerini Mesajlarda Kullanmak

Bir Doctrine entity’sini mesaj içinde iletmeniz gerekiyorsa, nesnenin kendisini değil **birincil anahtarını** (veya handler’ın gerçekten ihtiyaç duyduğu bilgiyi, örneğin e-posta adresi vb.) iletmek daha iyidir.

Aksi takdirde Entity Manager ile ilgili hatalar görebilirsiniz:

```php
// src/Message/NewUserWelcomeEmail.php
namespace App\Message;

class NewUserWelcomeEmail
{
    public function __construct(
        private int $userId,
    ) {
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}
```

Handler içinde yeni bir nesneyi sorgulayabilirsiniz:

```php
// src/MessageHandler/NewUserWelcomeEmailHandler.php
namespace App\MessageHandler;

use App\Message\NewUserWelcomeEmail;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class NewUserWelcomeEmailHandler
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    public function __invoke(NewUserWelcomeEmail $welcomeEmail): void
    {
        $user = $this->userRepository->find($welcomeEmail->getUserId());

        // ... bir e-posta gönder!
    }
}
```

Bu yöntem, entity’nin güncel verileri içerdiğinden emin olmanızı sağlar.


# ⚡ Mesajları Eşzamanlı (Sync) İşleme

Bir mesaj herhangi bir yönlendirme (routing) kuralıyla eşleşmezse, hiçbir transport’a gönderilmez ve  **hemen işlenir** .

Bazı durumlarda (örneğin handler’ları farklı transport’lara bağlarken), bunu açıkça yapmak daha kolay veya esnek olabilir:

Bir **sync transport** oluşturarak mesajları “hemen işlenmek üzere” buraya gönderebilirsiniz:

```php
// config/packages/messenger.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $messenger = $framework->messenger();

    // ... diğer transport’lar

    $messenger->transport('sync')->dsn('sync://');
    $messenger->routing('App\Message\SmsNotification')->senders(['sync']);
};
```

---

## 🛠️ Kendi Transport’unuzu Oluşturma

Desteklenmeyen bir kaynaktan mesaj göndermeniz veya almanız gerekiyorsa, kendi transport’unuzu oluşturabilirsiniz.

Bkz.  **How to Create Your Own Messenger Transport** .

---

## 🧑‍💻 Mesajları Tüketmek (Worker’ı Çalıştırmak)

Mesajlar yönlendirildikten sonra çoğu durumda onları “tüketmeniz” gerekir.

Bunu şu komutla yapabilirsiniz:

```bash
php bin/console messenger:consume async
```

Daha fazla çıktı almak için:

```bash
php bin/console messenger:consume async -vv
```

İlk argüman, alıcının (receiver) adıdır (veya özel bir servis yönlendirmesi yaptıysanız servis id’sidir).

Varsayılan olarak komut  **sürekli çalışır** , transport’ta yeni mesajlar arar ve bunları işler.

Bu komut, **worker** olarak adlandırılır.

Tüm mevcut alıcılardan mesajları tüketmek istiyorsanız `--all` seçeneğini kullanabilirsiniz:

```bash
php bin/console messenger:consume --all
```

🆕 `--all` seçeneği Symfony **7.1** sürümünde tanıtılmıştır.

---

### 🔁 Uzun Süreli Mesajlar İçin Keepalive

İşlenmesi uzun süren mesajlar bazı transport’lar tarafından erken tekrar gönderilebilir.

Bunun nedeni, onaylanmayan (acknowledge edilmemiş) mesajların “kaybolduğunun” varsayılmasıdır.

Bu sorunu önlemek için `--keepalive` seçeneğini kullanarak mesajın “işleniyor” olarak işaretlenmesini sağlayabilirsiniz:

```bash
php bin/console messenger:consume --keepalive
```

Bu seçenek,  **Beanstalkd** ,  **AmazonSQS** , **Doctrine** ve **Redis** transport’larında kullanılabilir.

🆕 `--keepalive` seçeneği Symfony **7.2** sürümünde tanıtılmıştır.

---

Geliştirme ortamında Symfony CLI aracı kullanıyorsanız, worker’ları web sunucusuyla birlikte otomatik olarak çalışacak şekilde yapılandırabilirsiniz.

Bkz. **Symfony CLI Workers** belgeleri.

Worker’ı düzgün biçimde durdurmak için bir `StopWorkerException` örneği fırlatın.

---

## 🚀 Üretim Ortamına Dağıtım (Deployment)

Üretimde aşağıdaki noktalara dikkat etmeniz gerekir:

### 🔄 Worker’ları Sürekli Çalışır Tutun

Worker’ların her zaman çalıştığından emin olmak için **Supervisor** veya **systemd** gibi bir **Process Manager** kullanın.

---

### 🧹 Worker’ların Sonsuza Kadar Çalışmasına İzin Vermeyin

Bazı servisler (ör. Doctrine EntityManager) zamanla daha fazla bellek tüketir.

Bu nedenle worker’ların sürekli çalışmasına izin vermek yerine aşağıdaki gibi sınırlandırma bayraklarını kullanın:

```bash
php bin/console messenger:consume --limit=10
```

Bu komut worker’ın sadece 10 mesaj işlemesini sağlar, ardından kapanır.

Süreç yöneticiniz (ör. Supervisor) yeni bir worker oluşturacaktır.

Alternatif olarak şunları da kullanabilirsiniz:

```bash
--memory-limit=128M
--time-limit=3600
```

---

### ❌ Hatalarla Karşılaşan Worker’ları Durdurma

Bir bağımlılık (örneğin veritabanı) erişilemiyorsa veya zaman aşımı oluyorsa, yeniden bağlanma (reconnect) mantığı ekleyebilir veya şu seçeneği kullanarak worker’ı sonlandırabilirsiniz:

```bash
--failure-limit=<değer>
```

---

### 🔁 Deploy Sonrası Worker’ları Yeniden Başlatma

Yeni bir sürüm dağıttığınızda, tüm worker süreçlerini yeniden başlatmanız gerekir.

Bunun için şu komutu çalıştırın:

```bash
php bin/console messenger:stop-workers
```

Bu komut her worker’a mevcut mesajını bitirdikten sonra düzgün şekilde kapanmasını söyler.

Process manager daha sonra yeni worker süreçleri başlatır.

Komut, dahili olarak **app cache** kullanır, bu nedenle bunun uygun bir adapter kullandığından emin olun.

---

### 🧮 Deploy’lar Arasında Aynı Cache’i Kullanma

Deploy stratejiniz yeni dizinler oluşturuyorsa, `cache.prefix_seed` yapılandırma seçeneğini ayarlayın.

Aksi takdirde `cache.app` havuzu her deploy’da farklı bir namespace kullanır.

---

## 🎯 Öncelikli (Prioritized) Transport’lar

Bazı mesaj türlerinin diğerlerinden daha yüksek öncelikle işlenmesi gerekebilir.

Bunun için birden fazla transport oluşturabilir ve farklı mesajları farklı transport’lara yönlendirebilirsiniz:

```php
// config/packages/messenger.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $messenger = $framework->messenger();

    $messenger->transport('async_priority_high')
        ->dsn(env('MESSENGER_TRANSPORT_DSN'))
        ->options(['queue_name' => 'high']);

    $messenger->transport('async_priority_low')
        ->dsn(env('MESSENGER_TRANSPORT_DSN'))
        ->options(['queue_name' => 'low']);

    $messenger->routing('App\Message\SmsNotification')->senders(['async_priority_low']);
    $messenger->routing('App\Message\NewUserWelcomeEmail')->senders(['async_priority_high']);
};
```

Ardından her transport için ayrı worker’lar çalıştırabilir veya tek bir worker’a öncelik sırasına göre işlem yapmasını söyleyebilirsiniz:

```bash
php bin/console messenger:consume async_priority_high async_priority_low
```

Worker önce `async_priority_high` kuyruğunu kontrol eder; eğer boşsa `async_priority_low` kuyruğundaki mesajları işler.

---

## 🎚️ Belirli Kuyruklarla Sınırlı Tüketim

Bazı transport’larda (özellikle  **AMQP** ) **exchange** ve **queue** kavramları vardır.

Symfony’de bir transport her zaman bir exchange’e bağlıdır.

Varsayılan olarak worker, bu exchange’e bağlı **tüm kuyruklardan** mesaj tüketir.

Ancak yalnızca belirli kuyruklardan mesaj almak isteyebilirsiniz:

```bash
php bin/console messenger:consume my_transport --queues=fasttrack
php bin/console messenger:consume my_transport --queues=fasttrack1 --queues=fasttrack2
```

Bu özelliği kullanabilmek için receiver’ın `QueueReceiverInterface` arayüzünü uygulaması gerekir.

---

## 📊 Kuyruktaki Mesaj Sayısını Kontrol Etme

Bazı veya tüm transport’lardaki kuyruklarda kaç mesaj olduğunu görmek için şu komutu çalıştırın:

```bash
php bin/console messenger:stats
php bin/console messenger:stats my_transport_name other_transport_name
php bin/console messenger:stats --format=json
php bin/console messenger:stats my_transport_name other_transport_name --format=json
```

🆕 `--format` seçeneği Symfony **7.2** sürümünde tanıtılmıştır.

Bu komutun çalışması için transport’un receiver’ı `MessageCountAwareInterface` arayüzünü uygulamalıdır.

---

## 🧩 Supervisor Yapılandırması

 **Supervisor** , worker süreçlerinizin her zaman çalışmasını garanti eden harika bir araçtır

(hata, mesaj limiti veya `messenger:stop-workers` nedeniyle kapansalar bile).

Ubuntu üzerinde kurulumu:

```bash
sudo apt-get install supervisor
```

Yapılandırma dosyaları genellikle `/etc/supervisor/conf.d` dizininde bulunur.

Örneğin 2 worker örneğini sürekli çalışır tutmak için aşağıdaki dosyayı oluşturabilirsiniz:

```
;/etc/supervisor/conf.d/messenger-worker.conf
[program:messenger-consume]
command=php /path/to/your/app/bin/console messenger:consume async --time-limit=3600
user=ubuntu
numprocs=2
startsecs=0
autostart=true
autorestart=true
startretries=10
process_name=%(program_name)s_%(process_num)02d
```

`async` argümanını kendi transport adınıza göre, `user` değerini de sunucudaki kullanıcıya göre değiştirin.

Deploy sırasında bazı servisler (örneğin veritabanı) geçici olarak kullanılamayabilir.

Bu durumda Supervisor, `startretries` kadar yeniden başlatmayı dener.

Bu değeri sistemin **FATAL** durumuna düşmemesi için yeterince yüksek ayarlayın.

Supervisor her yeniden başlatmada bekleme süresini 1 saniye artırır.

Örneğin değer 10 ise sırasıyla 1, 2, 3 … saniye bekleyerek toplam 55 saniyelik bir süre tanır.

Redis Transport kullanıyorsanız, her worker’ın benzersiz bir  **consumer name** ’e sahip olması gerekir.

Bunu Supervisor yapılandırmasında bir ortam değişkeni tanımlayarak sağlayabilirsiniz:

```
environment=MESSENGER_CONSUMER_NAME=%(program_name)s_%(process_num)02d
```

Supervisor’ı yapılandırdıktan sonra:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start messenger-consume:*
sudo supervisorctl restart messenger-consume:*
```

Daha fazla bilgi için Supervisor belgelerine bakın.

---

## 🧘 Graceful Shutdown (Zarif Kapanış)

Projenize **PCNTL PHP eklentisi** kuruluysa, worker’lar **SIGTERM** veya **SIGINT** sinyallerini yakalayarak mevcut mesajlarını bitirip düzgünce kapanabilir.

Farklı POSIX sinyalleri kullanmak istiyorsanız, `framework.messenger.stop_worker_on_signals` yapılandırma seçeneğini ayarlayabilirsiniz:

```php
// config/packages/messenger.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->messenger()
        ->stopWorkerOnSignals(['SIGTERM', 'SIGINT', 'SIGUSR1']);
};
```

🆕 Düz sinyal adlarıyla yapılandırma desteği Symfony **7.3** sürümünde tanıtılmıştır.

Öncesinde `pcntl` sabitlerinin sayısal değerleri kullanılmalıydı.

Bazı durumlarda (örneğin Supervisor bir Docker container’ı durdururken) **SIGTERM** sinyali Supervisor tarafından gönderilir.

Bu gibi durumlarda Supervisor yapılandırmanıza şu satırı ekleyin:

```
[program:x]
stopwaitsecs=20
```

Bu, worker’ın düzgün şekilde kapanabilmesi için 20 saniyelik bir “zarif kapanış” süresi tanır.


# ⚙️ Systemd Yapılandırması

Supervisor harika bir araç olsa da, çalıştırmak için sistem erişimi gerektirir.

Çoğu Linux dağıtımında standart hâline gelen  **systemd** , bunun yerine kullanılabilecek **user service** adlı iyi bir alternatife sahiptir.

Systemd kullanıcı servisi yapılandırma dosyaları genellikle `~/.config/systemd/user` dizininde bulunur.

Örneğin bir `messenger-worker.service` dosyası oluşturabilir veya aynı anda birden fazla worker çalıştırmak için `messenger-worker@.service` dosyasını kullanabilirsiniz:

```
[Unit]
Description=Symfony messenger-consume %i

[Service]
ExecStart=php /path/to/your/app/bin/console messenger:consume async --time-limit=3600
# Redis için, her instance’a özel consumer adı belirleyin
Environment="MESSENGER_CONSUMER_NAME=symfony-%n-%i"
Restart=always
RestartSec=30

[Install]
WantedBy=default.target
```

Ardından systemd’ye bir worker’ı etkinleştirmesini ve başlatmasını söyleyin:

```bash
systemctl --user enable messenger-worker@1.service
systemctl --user start messenger-worker@1.service
```

Birden fazla worker çalıştırmak için:

```bash
systemctl --user enable messenger-worker@{1..20}.service
systemctl --user start messenger-worker@{1..20}.service
```

Servis yapılandırma dosyanızı değiştirdiyseniz, daemon’u yeniden yüklemeniz gerekir:

```bash
systemctl --user daemon-reload
```

Tüm consumer’ları yeniden başlatmak için:

```bash
systemctl --user restart messenger-consume@*.service
```

Systemd kullanıcı servisleri, yalnızca ilgili kullanıcının ilk oturum açmasından sonra başlatılır.

Consumer’ların sistem açılışında başlamasını istiyorsanız, kullanıcı için **lingering** özelliğini etkinleştirin:

```bash
loginctl enable-linger <kullanıcı-adınız>
```

Loglar **journald** tarafından yönetilir ve `journalctl` komutuyla görüntülenebilir:

```bash
journalctl -f --user-unit messenger-consume@11.service
journalctl -f --user-unit messenger-consume@*
journalctl -f _UID=$UID
```

Daha fazla bilgi için systemd belgelerine bakın.

`journalctl` komutu için yükseltilmiş ayrıcalıklara ihtiyacınız olabilir veya kullanıcıyı `systemd-journal` grubuna ekleyebilirsiniz:

```bash
sudo usermod -a -G systemd-journal <kullanıcı-adınız>
```

---

## 🧠 Stateless Worker

PHP doğası gereği **stateless** bir dildir — farklı istekler arasında paylaşılan kaynak yoktur.

HTTP bağlamında, yanıt gönderildikten sonra PHP her şeyi temizler, bu nedenle bellek sızıntısı yapan servislerle ilgilenmek zorunda kalmazsınız.

Ancak **worker** süreçleri CLI üzerinde uzun süre çalıştığından (tek bir mesajdan sonra bitmezler), servis durumlarına dikkat etmeniz gerekir.

Symfony, her mesaj arasında aynı servis örneğini enjekte eder, bu da servislerin iç durumunun korunmasına ve dolayısıyla bellek sızıntısına yol açabilir.

Bazı Symfony servisleri (örneğin  **Monolog fingers crossed handler** ) tasarım gereği “leak” yapar.

Symfony bu durumu çözmek için **service reset** özelliği sağlar.

Kapsayıcı (container) her iki mesaj arasında otomatik olarak sıfırlandığında, Symfony `ResetInterface` arayüzünü uygulayan tüm servisleri bulur ve onların `reset()` metodunu çağırır.

Eğer bir servis **stateless** değilse ve her mesajdan sonra durumunu sıfırlamak istiyorsanız, bu servisin `ResetInterface`’i uygulaması ve `reset()` metodunda kendi özelliklerini sıfırlaması gerekir.

Container’ın sıfırlanmasını istemiyorsanız, `messenger:consume` komutunu çalıştırırken `--no-reset` seçeneğini kullanabilirsiniz.

---

## ⏳ Hız Sınırlamalı (Rate Limited) Transport

Bazı durumlarda mesaj worker’ınızın işleme hızını sınırlamanız gerekebilir.

Bunu, **RateLimiter** bileşenini gerektiren `rate_limiter` seçeneğiyle transport düzeyinde yapılandırabilirsiniz:

```php
// config/packages/messenger.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework) {
    $framework->messenger()
        ->transport('async')
            ->options(['rate_limiter' => 'your_rate_limiter_name'])
    ;
};
```

Bir transport üzerinde **rate limiter** yapılandırıldığında, sınır aşıldığında tüm worker bloke olur.

Bu nedenle hız sınırlamalı transport’lar için özel worker’lar yapılandırmanız önerilir, aksi takdirde diğer transport’lar da engellenebilir.

---

## 🔁 Yeniden Denemeler (Retries) ve Hatalar (Failures)

Bir mesajın işlenmesi sırasında bir istisna atılırsa, mesaj otomatik olarak transport’a geri gönderilir ve tekrar denenir.

Varsayılan olarak, bir mesaj **3 kez** yeniden denenir, ardından **silinir** veya  **failure transport** ’a gönderilir.

Her deneme gecikmeli olarak yapılır. Tüm bu ayarlar her transport için yapılandırılabilir:

```php
// config/packages/messenger.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $messenger = $framework->messenger();

    $messenger->transport('async_priority_high')
        ->dsn(env('MESSENGER_TRANSPORT_DSN'))
        ->retryStrategy()
            ->maxRetries(3)
            // milisaniye cinsinden gecikme
            ->delay(1000)
            // her yeniden denemede gecikmeyi artırır (ör. 1, 2, 4 saniye)
            ->multiplier(2)
            ->maxDelay(0)
            // thundering herd etkisini önlemek için rastgelelik uygular
            ->jitter(0.1)
            // tüm stratejiyi özel bir servise devredebilirsiniz
            ->service(null)
    ;
};
```

🆕 `jitter` seçeneği Symfony **7.1** sürümünde tanıtılmıştır.

Symfony, bir mesaj yeniden denendiğinde **WorkerMessageRetriedEvent** olayını tetikler, böylece kendi mantığınızı çalıştırabilirsiniz.

Ayrıca `SerializedMessageStamp` sayesinde mesajın serileştirilmiş formu kaydedilir, bu da tekrar serileştirme ihtiyacını ortadan kaldırır.

---

### 🚫 Yeniden Denemeyi Engelleme

Bazen bir mesajın işlenmesi kalıcı bir şekilde başarısız olabilir ve yeniden denenmemesi gerekir.

Bu durumda `UnrecoverableMessageHandlingException` fırlatın; mesaj yeniden denenmez.

Bu mesajlar yine de  **failure transport** ’ta görünür.

Bunu istemiyorsanız hatayı handler içinde kendiniz yakalayabilir ve işlemi başarıyla sonlandırabilirsiniz.

---

### 🔄 Zorunlu Yeniden Deneme

Bazı hatalar geçicidir ve mesajın mutlaka yeniden denenmesi gerekir.

Bu durumda `RecoverableMessageHandlingException` fırlatın; mesaj **sonsuz** olarak yeniden denenir ve `max_retries` ayarı göz ardı edilir.

Ayrıca, yeniden denemeler arasında özel bir gecikme tanımlamak için istisnanın yapıcısına `retryDelay` argümanını iletebilirsiniz

(örneğin bir HTTP yanıtındaki `Retry-After` başlığının değerini kullanmak için).

🆕 `retryDelay` argümanı ve `getRetryDelay()` metodu Symfony **7.2** sürümünde tanıtılmıştır.

---

## 💾 Başarısız Mesajları Kaydetme ve Yeniden Deneme

Bir mesaj birkaç denemeden (varsayılan 3) sonra başarısız olursa,  **failure transport** ’a gönderilebilir:

```php
// config/packages/messenger.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $messenger = $framework->messenger();

    // yeniden denemelerden sonra mesajlar "failed" transport’una gönderilir
    $messenger->failureTransport('failed');

    $messenger->transport('failed')
        ->dsn('doctrine://default?queue_name=failed');
};
```

Bu durumda başarısız mesajları görüntülemek veya yeniden denemek için şu komutları kullanabilirsiniz:

```bash
php bin/console messenger:failed:show
php bin/console messenger:failed:show --max=10
php bin/console messenger:failed:show --class-filter='App\Message\MyMessage'
php bin/console messenger:failed:show --stats
php bin/console messenger:failed:retry -vv
php bin/console messenger:failed:retry 20 30 --force
php bin/console messenger:failed:remove 20
php bin/console messenger:failed:remove --all
```

🆕 Symfony  **7.2** : `messenger:failed:retry` komutuna mesaj atlama seçeneği eklendi.

🆕 Symfony  **7.3** : `messenger:failed:remove` komutuna mesaj sınıfına göre filtreleme seçeneği eklendi.

---

## 📦 Birden Fazla Failure Transport

Bazı durumlarda, tüm mesajlar için tek bir genel failure transport yeterli olmayabilir.

Bu durumda belirli transport’lar için özel failure transport’lar tanımlayabilirsiniz:

```php
// config/packages/messenger.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $messenger = $framework->messenger();

    $messenger->failureTransport('failed_default');

    $messenger->transport('async_priority_high')
        ->dsn(env('MESSENGER_TRANSPORT_DSN'))
        ->failureTransport('failed_high_priority');

    $messenger->transport('async_priority_low')
        ->dsn('doctrine://default?queue_name=async_priority_low');

    $messenger->transport('failed_default')
        ->dsn('doctrine://default?queue_name=failed_default');

    $messenger->transport('failed_high_priority')
        ->dsn('doctrine://default?queue_name=failed_high_priority');
};
```

Genel veya transport düzeyinde `failure_transport` tanımlanmadıysa, mesajlar belirtilen deneme sayısından sonra  **kalıcı olarak silinir** .

`messenger:failed` komutlarında `--transport` seçeneğiyle hangi failure transport’un kullanılacağını belirtebilirsiniz:

```bash
php bin/console messenger:failed:show --transport=failure_transport
php bin/console messenger:failed:retry 20 30 --transport=failure_transport --force
php bin/console messenger:failed:remove 20 --transport=failure_transport
```

---

## ⚙️ Transport Yapılandırması

Messenger birçok farklı transport türünü destekler, her birinin kendine özgü seçenekleri vardır.

Seçenekler **DSN string’i** veya **konfigürasyon dosyası** aracılığıyla tanımlanabilir.

```bash
# .env
MESSENGER_TRANSPORT_DSN=amqp://localhost/%2f/messages?auto_setup=false
```

```php
// config/packages/messenger.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $messenger = $framework->messenger();

    $messenger->transport('my_transport')
        ->dsn(env('MESSENGER_TRANSPORT_DSN'))
        ->options(['auto_setup' => false]);
};
```

`options` altında tanımlanan ayarlar, DSN içindekilere göre  **önceliklidir** .

---

## 🐇 AMQP Transport

AMQP transport, PHP’nin **AMQP uzantısını** kullanarak mesajları RabbitMQ gibi kuyruklara gönderir.

Kurmak için:

```bash
composer require symfony/amqp-messenger
```

AMQP DSN örnekleri:

```bash
MESSENGER_TRANSPORT_DSN=amqp://guest:guest@localhost:5672/%2f/messages
# TLS/SSL (AMQPS) kullanımı
MESSENGER_TRANSPORT_DSN=amqps://guest:guest@localhost/%2f/messages
```

TLS/SSL kullanmak için bir CA sertifikası tanımlamalısınız (ör. `amqp.cacert = /etc/ssl/certs`).

Varsayılan TLS portu  **5671** ’dir, `port` parametresiyle değiştirilebilir.

Varsayılan olarak transport, gerekli tüm  **exchange** , **queue** ve **binding key** yapılarını otomatik oluşturur.

Bu davranışı devre dışı bırakmak için:

```php
->options(['queues' => []])
```

AMQP transport ayrıca exchange/queue yapılandırmaları ve binding anahtarları dahil birçok seçeneği destekler.

`exchange[name]` için boş string (`""`) kullanımı Symfony **7.3** sürümünde eklenmiştir.


# 🧠 In-Memory Transport

 **In-memory transport** , mesajları gerçek bir transport’a iletmez; bunun yerine isteğin süresi boyunca bellekte tutar.

Bu, özellikle **testler** için kullanışlıdır.

Örneğin, bir `async_priority_normal` transport’unuz varsa, test ortamında bu transport’u aşağıdaki gibi `in-memory` transport ile geçersiz kılabilirsiniz:

```php
// config/packages/test/messenger.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $messenger = $framework->messenger();

    $messenger->transport('async_priority_normal')
        ->dsn('in-memory://');
};
```

Bu sayede test sırasında mesajlar gerçek transport’a gönderilmez.

Dahası, testinizde tam olarak **bir mesaj** gönderilip gönderilmediğini kolayca doğrulayabilirsiniz:

```php
// tests/Controller/DefaultControllerTest.php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

class DefaultControllerTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();
        // ...

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        /** @var InMemoryTransport $transport */
        $transport = $this->getContainer()->get('messenger.transport.async_priority_normal');
        $this->assertCount(1, $transport->getSent());
    }
}
```

### 🔧 In-Memory Transport Seçenekleri

| Seçenek      | Varsayılan | Açıklama                                                                                                                               |
| ------------- | ----------- | ---------------------------------------------------------------------------------------------------------------------------------------- |
| `serialize` | false       | Mesajların serileştirilip serileştirilmeyeceğini belirler. Özellikle kendi serializer’ınızı test etmek için kullanışlıdır. |

`KernelTestCase` veya `WebTestCase` sınıflarını genişleten testlerde, **tüm in-memory transport’lar her testten sonra otomatik olarak sıfırlanır.**

---

# ☁️ Amazon SQS Transport

 **Amazon SQS transport** , AWS üzerinde barındırılan uygulamalar için idealdir.

Kurmak için:

```bash
composer require symfony/amazon-sqs-messenger
```

DSN örnekleri:

```bash
# .env
MESSENGER_TRANSPORT_DSN=https://sqs.eu-west-3.amazonaws.com/123456789012/messages?access_key=AKIAIOSFODNN7EXAMPLE&secret_key=j17M97ffSVoKI0briFoo9a
MESSENGER_TRANSPORT_DSN=sqs://localhost:9494/messages?sslmode=disable
```

Transport, gerekli kuyrukları otomatik olarak oluşturur.

Bunu devre dışı bırakmak için `auto_setup=false` ayarını kullanabilirsiniz.

Symfony, mesaj göndermeden/almadan önce AWS’in `GetQueueUrl` API’sini çağırarak kuyruk adını URL’ye dönüştürür.

Bu ek çağrıdan kaçınmak için DSN’i doğrudan kuyruk URL’si olarak belirtebilirsiniz.

### 🔧 SQS Transport Seçenekleri

| Seçenek               | Varsayılan                             | Açıklama                                                  |
| ---------------------- | --------------------------------------- | ----------------------------------------------------------- |
| `access_key`         | —                                      | AWS erişim anahtarı (URL-encoded olmalı)                 |
| `account`            | credentials sahibi                      | AWS hesap kimliği                                          |
| `auto_setup`         | true                                    | Kuyruğu otomatik oluştur                                  |
| `buffer_size`        | 9                                       | Önceden alınacak mesaj sayısı                           |
| `debug`              | false                                   | HTTP istek/yanıtlarını loglar (performansı düşürür) |
| `endpoint`           | `https://sqs.eu-west-1.amazonaws.com` | SQS servis URL’si                                          |
| `poll_timeout`       | 0.1                                     | Yeni mesaj bekleme süresi (sn)                             |
| `queue_name`         | messages                                | Kuyruk adı                                                 |
| `queue_attributes`   | —                                      | SQS `CreateQueue`API’ye göre kuyruk öznitelikleri      |
| `queue_tags`         | —                                      | SQS `CreateQueue`API’ye göre kuyruk etiketleri          |
| `region`             | eu-west-1                               | AWS bölgesi                                                |
| `secret_key`         | —                                      | AWS gizli anahtarı                                         |
| `session_token`      | —                                      | AWS oturum belirteci                                        |
| `visibility_timeout` | Kuyruk yapılandırması                | Mesajın görünmez olma süresi (sn)                       |
| `wait_time`          | 20                                      | Uzun polling süresi (sn)                                   |

🆕 `queue_attributes` ve `queue_tags` seçenekleri Symfony  **7.3** ’te eklenmiştir.

* **`wait_time`** → SQS’in yanıt döndürmeden önce bekleyeceği maksimum süre. Boş yanıt sayısını azaltarak maliyeti düşürür.
* **`poll_timeout`** → Worker’ın null dönmeden önce bekleyeceği süre. Diğer alıcıların engellenmesini önler.

Kuyruk adı `.fifo` ile bitiyorsa, AWS **FIFO kuyruğu** oluşturur.

Bu durumda `AmazonSqsFifoStamp` kullanarak **Message Group ID** ve **Deduplication ID** belirtebilirsiniz.

Alternatif olarak,  **AddFifoStampMiddleware** ’i etkinleştirebilirsiniz.

Mesajınız `MessageDeduplicationAwareInterface` veya `MessageGroupAwareInterface` arayüzlerini uygularsa, middleware bu değerleri otomatik olarak ayarlar.

FIFO kuyruklar, mesaj başına gecikme (`delay`) desteği vermez.

Bu nedenle `retry strategy` ayarlarında `delay: 0` kullanılmalıdır.

SQS transport, **--keepalive** seçeneğini destekler.

Bu, `ChangeMessageVisibility` işlemini kullanarak mesajın görünmezlik süresini düzenli olarak yeniler.

🆕 Keepalive desteği Symfony  **7.2** ’de tanıtılmıştır.

---

# 🔄 Mesajların Serileştirilmesi

Mesajlar bir transport’a gönderilirken veya oradan alınırken PHP’nin yerel `serialize()` ve `unserialize()` fonksiyonları kullanılır.

Bu davranışı global olarak veya transport bazında değiştirebilir, kendi serializer servisinizi tanımlayabilirsiniz.

```php
// config/packages/messenger.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $messenger = $framework->messenger();

    $messenger->serializer()
        ->defaultSerializer('messenger.transport.symfony_serializer')
        ->symfonySerializer()
            ->format('json')
            ->context('foo', 'bar');

    $messenger->transport('async_priority_normal')
        ->dsn('...')
        ->serializer('messenger.transport.symfony_serializer');
};
```

`messenger.transport.symfony_serializer`, Symfony’nin **Serializer bileşenini** kullanır ve birkaç farklı biçimde yapılandırılabilir.

Ayrıca, `SerializerStamp` ile her mesaj için özel context değerleri belirleyebilirsiniz.

Diğer uygulamalarla mesaj alışverişi yaparken daha fazla kontrol gerekiyorsa, özel bir serializer yazabilirsiniz.

---

# 🔌 Bağlantıları Kapatma

Bağlantı gerektiren transport’larda, uzun süre çalışan süreçlerde kaynakları serbest bırakmak için `close()` metodunu çağırabilirsiniz.

Bu özellik şu transport’lar tarafından desteklenir:

* AmazonSqs
* Amqp
* Redis

Doctrine bağlantısını kapatmak için ise **middleware** kullanabilirsiniz.

🆕 `CloseableTransportInterface` ve `close()` metodu Symfony  **7.3** ’te tanıtılmıştır.

---

# 🧰 Komut ve Dış Süreç Çalıştırma

## ▶️ Komut Çalıştırma

Herhangi bir Symfony komutunu tetiklemek için `RunCommandMessage` gönderebilirsiniz.

Symfony bu mesajı otomatik olarak işler ve komutu çalıştırır:

```php
use Symfony\Component\Console\Messenger\RunCommandMessage;
use Symfony\Component\Messenger\MessageBusInterface;

class CleanUpService
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public function cleanUp(): void
    {
        $this->bus->dispatch(new RunCommandMessage('app:my-cache:clean-up --dir=var/temp'));
        $this->bus->dispatch(new RunCommandMessage('cache:clear'));
    }
}
```

`RunCommandMessage` oluştururken `throwOnFailure` ve `catchExceptions` parametrelerini kullanarak hatalarda davranışı belirleyebilirsiniz.

İşlendikten sonra handler, **RunCommandContext** nesnesi döndürür (çıkış kodu, çıktı vb. içerir).

---

## ⚙️ Harici Süreç (External Process) Çalıştırma

Messenger, dış süreçleri çalıştırmak için `RunProcessMessage` adında kullanışlı bir yardımcı sağlar.

Bu, **Process** bileşeninden yararlanır.

```php
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Process\Messenger\RunProcessMessage;

class CleanUpService
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public function cleanUp(): void
    {
        $this->bus->dispatch(new RunProcessMessage(['rm', '-rf', 'var/log/temp/*'], cwd: '/my/custom/working-dir'));
    }
}
```

Kabuk özelliklerini (pipe, yönlendirme vb.) kullanmak istiyorsanız, `fromShellCommandline()` metodunu kullanın:

```php
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Process\Messenger\RunProcessMessage;

class CleanUpService
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public function cleanUp(): void
    {
        $this->bus->dispatch(RunProcessMessage::fromShellCommandline('echo "Hello World" > var/log/hello.txt'));
    }
}
```

🆕 `RunProcessMessage::fromShellCommandline()` metodu Symfony  **7.3** ’te tanıtılmıştır.

Handler, yine **RunProcessContext** döndürür (çıkış kodu, çıktı vb. içerir).

---

# 🌐 Bir Web Servisini Ping Etme

Bir web servisini düzenli olarak ping’leyip durumunu kontrol etmek için `PingWebhookMessage` kullanabilirsiniz:

```php
use Symfony\Component\HttpClient\Messenger\PingWebhookMessage;
use Symfony\Component\Messenger\MessageBusInterface;

class LivenessService
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public function ping(): void
    {
        // 3xx/4xx/5xx durumlarında HttpExceptionInterface fırlatır
        $this->bus->dispatch(new PingWebhookMessage('GET', 'https://example.com/status'));

        // 3xx/4xx/5xx hatalarında exception fırlatmaz
        $this->bus->dispatch(new PingWebhookMessage('GET', 'https://example.com/status', throw: false));

        // HttpClientInterface seçenekleri kullanılabilir
        $this->bus->dispatch(new PingWebhookMessage('POST', 'https://example.com/status', [
            'headers' => [
                'Authorization' => 'Bearer ...'
            ],
            'json' => [
                'data' => 'some-data',
            ],
        ]));
    }
}
```

Handler, bir **ResponseInterface** döndürür, bu da HTTP isteğinden dönen bilgileri almanızı ve işlemenizi sağlar.


# 🐇 AMQP Özeline Ait Ayarlar

Mesaj üzerinde AMQP’ye özgü ayarlar yapmak için **AmqpStamp** ekleyebilirsiniz:

```php
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
// ...

$attributes = [];
$bus->dispatch(new SmsNotification(), [
    new AmqpStamp('custom-routing-key', AMQP_NOPARAM, $attributes),
]);
```

Bu transport, **\AmqpQueue::consume()** gibi bloklayıcı bir mekanizmaya dayanmadığı için tüketiciler (consumers) yönetim panelinde görünmez.

Bloklayıcı alıcılar (`blocking receiver`), `--time-limit`, `--memory-limit` ve `messenger:stop-workers` gibi komutların verimli çalışmasını engeller.

Bu komutların düzgün çalışabilmesi için alıcının hemen yanıt dönmesi gerekir.

Worker, bir mesaj alıncaya veya durdurma koşullarından biri gerçekleşinceye kadar döngüye devam eder.

Bu yüzden, worker bloklayıcı bir çağrıda takılırsa **durma mantığı (stop logic)** çalışmaz.

Uygulamanızda **socket exception** sorunları veya yüksek bağlantı sirkülasyonu (bağlantıların hızlı açılıp kapanması) yaşıyorsanız, **AMQProxy** kullanmayı düşünün.

Bu araç, Symfony Messenger ile AMQP sunucusu arasında bir geçit (gateway) görevi görür, bağlantıların kararlılığını artırır, yükü azaltır ve genel performansı iyileştirir.

---

# 🧱 Doctrine Transport

Doctrine transport, mesajları bir **veritabanı tablosunda** saklamak için kullanılır.

Kurmak için:

```bash
composer require symfony/doctrine-messenger
```

DSN örneği:

```bash
# .env
MESSENGER_TRANSPORT_DSN=doctrine://default
```

Birden fazla bağlantınız varsa, “default” dışında başka bir bağlantı adı belirtebilirsiniz.

Transport otomatik olarak `messenger_messages` adlı bir tablo oluşturur.

Tablo adını değiştirmek için DSN içinde `table_name` parametresini kullanabilirsiniz:

```bash
# .env
MESSENGER_TRANSPORT_DSN=doctrine://default?table_name=your_custom_table_name
```

Tabloyu kendiniz oluşturmak istiyorsanız, `auto_setup=false` olarak ayarlayıp migration oluşturabilirsiniz.

### 🔧 Doctrine Transport Seçenekleri

| Seçenek              | Varsayılan        | Açıklama                                                              |
| --------------------- | ------------------ | ----------------------------------------------------------------------- |
| `table_name`        | messenger_messages | Tablo adı                                                              |
| `queue_name`        | default            | Kuyruk adı (bir tabloyu birden fazla transport için kullanmak üzere) |
| `redeliver_timeout` | 3600               | İşlenmekte olan mesajın yeniden denenmeden önceki süresi (saniye)  |
| `auto_setup`        | true               | Tabloyu otomatik oluşturur                                             |

`redeliver_timeout` değerini, en uzun mesaj sürenizden daha yüksek tutun.

Aksi takdirde aynı mesaj iki kez işlenmeye başlayabilir.

### 🐘 PostgreSQL için Ekstra Ayarlar (LISTEN/NOTIFY)

PostgreSQL kullanıyorsanız, **LISTEN/NOTIFY** özelliğiyle Doctrine transport’u daha performanslı hâle getirebilirsiniz.

Bu sayede polling yerine PostgreSQL, tabloya yeni bir mesaj eklendiğinde worker’ları  **doğrudan bilgilendirir** .

| Seçenek                   | Varsayılan | Açıklama                                              |
| -------------------------- | ----------- | ------------------------------------------------------- |
| `use_notify`             | true        | LISTEN/NOTIFY özelliğini kullan                       |
| `check_delayed_interval` | 60000       | Gecikmiş mesajlar için kontrol aralığı (ms)        |
| `get_notify_timeout`     | 0           | PDO::pgsqlGetNotify çağrısında bekleme süresi (ms) |

Doctrine transport, **--keepalive** seçeneğini destekler.

Bu özellik, mesajın `delivered_at` zaman damgasını düzenli olarak güncelleyerek yeniden teslimi önler.

🆕 Symfony **7.3** ile eklenmiştir.

---

# 🟢 Beanstalkd Transport

**Beanstalkd** transport, mesajları doğrudan bir **Beanstalkd iş kuyruğuna** gönderir.

Kurmak için:

```bash
composer require symfony/beanstalkd-messenger
```

DSN örneği:

```bash
# .env
MESSENGER_TRANSPORT_DSN=beanstalkd://localhost:11300?tube_name=foo&timeout=4&ttr=120
# veya port belirtmezseniz varsayılan 11300 olur
MESSENGER_TRANSPORT_DSN=beanstalkd://localhost
```

### 🔧 Beanstalkd Transport Seçenekleri

| Seçenek           | Varsayılan | Açıklama                                                                   |
| ------------------ | ----------- | ---------------------------------------------------------------------------- |
| `bury_on_reject` | false       | true olursa reddedilen mesajlar silinmek yerine “buried” durumuna alınır |
| `timeout`        | 0           | Mesaj rezervasyon süresi (sn)                                               |
| `ttr`            | 90          | Mesajın “çalışma süresi” sınırı (sn)                               |
| `tube_name`      | default     | Kuyruk adı                                                                  |

🆕 `bury_on_reject` seçeneği Symfony  **7.3** ’te tanıtılmıştır.

🆕 `--keepalive` desteği Symfony  **7.2** ’de eklenmiştir (Beanstalkd’ın `touch` komutunu kullanır).

### 🎚️ Mesaj Önceliği

Beanstalkd, mesajların önceliğini belirlemenizi sağlar.

`BeanstalkdPriorityStamp` kullanarak öncelik numarası belirtebilirsiniz (düşük sayı = yüksek öncelik):

```php
use App\Message\SomeMessage;
use Symfony\Component\Messenger\Stamp\BeanstalkdPriorityStamp;

$this->bus->dispatch(new SomeMessage('some data'), [
    // 0 = en yüksek öncelik
    // 2**32 - 1 = en düşük öncelik
    new BeanstalkdPriorityStamp(0),
]);
```

🆕 `BeanstalkdPriorityStamp` Symfony  **7.3** ’te tanıtılmıştır.

---

# 🔴 Redis Transport

Redis transport, mesajları  **stream** ’ler kullanarak sıraya alır.

Bu transport, Redis PHP uzantısı (>=4.3) ve çalışan bir Redis sunucusu (^5.0) gerektirir.

Kurmak için:

```bash
composer require symfony/redis-messenger
```

DSN örnekleri:

```bash
# Basit
MESSENGER_TRANSPORT_DSN=redis://localhost:6379/messages

# Tam
MESSENGER_TRANSPORT_DSN=redis://password@localhost:6379/messages/symfony/consumer?auto_setup=true&serializer=1&stream_max_entries=0&dbindex=0

# Redis Cluster
MESSENGER_TRANSPORT_DSN=redis://host-01:6379,redis://host-02:6379,redis://host-03:6379,redis://host-04:6379

# Unix Socket
MESSENGER_TRANSPORT_DSN=redis:///var/run/redis.sock

# TLS
MESSENGER_TRANSPORT_DSN=rediss://localhost:6379/messages

# Redis Sentinel
MESSENGER_TRANSPORT_DSN=redis:?host[redis1:26379]&host[redis2:26379]&host[redis3:26379]&sentinel_master=db
```

### 🔧 Redis Transport Seçenekleri

| Seçenek                | Varsayılan           | Açıklama                                                                       |
| ----------------------- | --------------------- | -------------------------------------------------------------------------------- |
| `stream`              | messages              | Redis stream adı                                                                |
| `group`               | symfony               | Consumer grubu adı                                                              |
| `consumer`            | consumer              | Worker kimliği — aynı consumer adıyla birden fazla worker çalıştırmayın |
| `auto_setup`          | true                  | Redis grubunu otomatik oluştur                                                  |
| `auth`                | -                     | Redis şifresi                                                                   |
| `delete_after_ack`    | true                  | İşlenmiş mesajları sil                                                       |
| `delete_after_reject` | true                  | Reddedilen mesajları sil                                                        |
| `lazy`                | false                 | Bağlantıyı yalnızca gerektiğinde aç                                        |
| `serializer`          | Redis::SERIALIZER_PHP | Mesajın serileştirme biçimi                                                   |
| `stream_max_entries`  | 0                     | Stream’de tutulacak maksimum kayıt sayısı                                    |
| `redeliver_timeout`   | 3600                  | Sahipsiz kalan mesajların yeniden teslim süresi (sn)                           |
| `claim_interval`      | 60000                 | Sahipsiz mesaj kontrol aralığı (ms)                                           |
| `persistent_id`       | null                  | Kalıcı bağlantı kimliği                                                     |
| `retry_interval`      | 0                     | Yeniden deneme aralığı (ms)                                                   |
| `read_timeout`        | 0                     | Okuma zaman aşımı (sn)                                                        |
| `timeout`             | 0                     | Bağlantı zaman aşımı (sn)                                                   |
| `sentinel_master`     | null                  | Redis Sentinel ana adı                                                          |
| `redis_sentinel`      | null                  | `sentinel_master`için alias                                                   |
| `ssl`                 | null                  | TLS kanalı için SSL bağlamı seçenekleri                                     |

🆕 `redis_sentinel` seçeneği Symfony  **7.1** ’de tanıtılmıştır.

### 🔒 SSL (TLS) Test Ortamı Örneği

```yaml
# config/packages/test/messenger.yaml
framework:
    messenger:
        transports:
            redis:
                dsn: "rediss://localhost"
                options:
                    ssl:
                        allow_self_signed: true
                        capture_peer_cert: true
                        verify_peer: true
```

Redis transport kullanırken aynı `stream + group + consumer` kombinasyonuyla **birden fazla worker** çalıştırmayın — aksi takdirde mesajlar birden fazla kez işlenebilir.

Docker veya Kubernetes ortamlarında, consumer adını ortam değişkeninden (`%env(MESSENGER_CONSUMER_NAME)%`) veya `HOSTNAME`’den türetmek yaygın bir yöntemdir.

Kubernetes kullanıyorsanız, stabil adlar için **StatefulSet** kullanın.

### 💡 Bellek Sızıntısını Önleme

* `delete_after_ack=true` (tek grup kullanıyorsanız)
* veya `stream_max_entries` değerini uygun şekilde ayarlayın.

Aksi takdirde tüm mesajlar Redis’te sonsuza kadar kalır.

Redis transport, **--keepalive** seçeneğini destekler ve bu, Redis’in `XCLAIM` komutunu kullanarak mesajın bekleme süresini sıfırlar.

🆕 Keepalive desteği Symfony  **7.3** ’te tanıtılmıştır.



# ⚙️ Messenger’ı Genişletme

## 💌 Zarf (Envelope) ve Damgalar (Stamps)

Bir mesaj, herhangi bir PHP nesnesi olabilir.

Bazen mesajın nasıl işleneceğiyle ilgili ek bilgi eklemeniz gerekebilir — örneğin AMQP üzerinde nasıl gönderileceği veya işlenmeden önce bir **gecikme süresi** ayarlamak gibi.

Bunu mesajınıza bir **stamp** (damga) ekleyerek yapabilirsiniz:

```php
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

public function index(MessageBusInterface $bus): void
{
    // Mesajın işlenmesini 5 saniye geciktir
    $bus->dispatch(new SmsNotification('...'), [
        new DelayStamp(5000),
    ]);

    // Ya da zarfı (envelope) açıkça oluştur
    $bus->dispatch(new Envelope(new SmsNotification('...'), [
        new DelayStamp(5000),
    ]));

    // ...
}
```

Dahili olarak her mesaj bir **Envelope** (zarf) içine sarılır.

Bu zarf mesajı ve onunla ilişkili tüm damgaları (stamps) tutar.

Kendiniz oluşturabilir veya Message Bus’un bunu otomatik olarak yapmasına izin verebilirsiniz.

Symfony, birçok farklı amaç için çok sayıda damga türü sağlar — örneğin hangi bus’ın mesajı yönettiğini veya mesajın başarısızlıktan sonra yeniden denenip denenmediğini takip etmek gibi.

---

## 🧩 Middleware (Ara Katmanlar)

Bir mesajı Message Bus’a gönderdiğinizde neler olacağı, Bus’a tanımlanan **middleware zinciri**ne ve bunların sırasına bağlıdır.

Varsayılan middleware sıralaması aşağıdaki gibidir:

1. **add_bus_name_stamp_middleware** → Mesajın hangi bus’a gönderildiğini kaydeden bir damga ekler
2. **dispatch_after_current_bus** → (Bkz.  *Messenger: Sync & Queued Message Handling* )
3. **failed_message_processing_middleware** → Failure transport’tan yeniden denenen mesajları işler
4. **(kendi middleware’leriniz)**
5. **send_message** → Mesaj yönlendirme yapılandırılmışsa mesajı ilgili transport’a gönderir ve zinciri durdurur
6. **handle_message** → Mesaj için uygun handler’ları çağırır

> 💡 Bu kısa isimler aslında `messenger.middleware.*` önekli servis kimlikleridir
>
> Örneğin: `messenger.middleware.handle_message`

Middleware’ler, hem mesaj ilk kez gönderildiğinde hem de bir **worker** tarafından yeniden işlendiğinde çalıştırılır.

Kendi middleware’inizi yazarken bu davranışı göz önünde bulundurun.

İsterseniz varsayılan middleware’leri devre dışı bırakıp yalnızca kendi middleware’lerinizi tanımlayabilirsiniz:

```php
// config/packages/messenger.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $messenger = $framework->messenger();

    $bus = $messenger->bus('messenger.bus.default')
        ->defaultMiddleware(false); // varsayılan middleware'leri devre dışı bırak

    // Varsayılanlardan sadece bazılarını kullan
    $bus->middleware()->id('add_bus_name_stamp_middleware')->arguments(['messenger.bus.default']);

    // Kendi middleware sınıflarınızı ekleyin
    $bus->middleware()->id('App\Middleware\MyMiddleware');
    $bus->middleware()->id('App\Middleware\AnotherMiddleware');
};
```

> 🧱 `MakerBundle` kuruluysa, `make:messenger-middleware` komutuyla yeni middleware sınıfı oluşturabilirsiniz.

---

## 🏛️ Doctrine İçin Middleware’ler

Doctrine kullanan uygulamalarda aşağıdaki isteğe bağlı middleware’ler kullanılabilir:

```php
// config/packages/messenger.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $messenger = $framework->messenger();

    $bus = $messenger->bus('command_bus');
    $bus->middleware()->id('doctrine_transaction');
    $bus->middleware()->id('doctrine_ping_connection');
    $bus->middleware()->id('doctrine_close_connection');
    $bus->middleware()->id('doctrine_open_transaction_logger');

    // Özel bir EntityManager kullanmak isterseniz:
    $bus->middleware()->id('doctrine_transaction')
        ->arguments(['custom']);
};
```

---

## 🔧 Diğer Middleware’ler

* **router_context** → Worker’da mutlak URL’ler oluşturmanız gerekiyorsa kullanın.

  Orijinal isteğin host, port vb. bağlamını saklar.
* **validation** → Mesajı işlemeye geçmeden önce `Validator` bileşeniyle doğrular.

  Doğrulama başarısız olursa `ValidationFailedException` fırlatılır.

  `ValidationStamp` ile validation gruplarını ayarlayabilirsiniz.

```php
// config/packages/messenger.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $messenger = $framework->messenger();

    $bus = $messenger->bus('command_bus');
    $bus->middleware()->id('router_context');
    $bus->middleware()->id('validation');
};
```

---

## 🪶 Messenger Olayları (Events)

Messenger, middleware’lere ek olarak bir dizi **event** de yayar.

Bu sayede sürecin farklı noktalarına dinleyici ekleyebilirsiniz.

| Event                            | Açıklama                                      |
| -------------------------------- | ----------------------------------------------- |
| `SendMessageToTransportsEvent` | Mesaj transport’a gönderildiğinde            |
| `WorkerMessageFailedEvent`     | Worker bir mesajı işlerken hata oluştuğunda |
| `WorkerMessageHandledEvent`    | Worker mesajı başarıyla işlediğinde        |
| `WorkerMessageReceivedEvent`   | Worker bir mesaj aldığında                   |
| `WorkerMessageRetriedEvent`    | Mesaj yeniden işlendiğinde                    |
| `WorkerRateLimitedEvent`       | Rate limiter devreye girdiğinde                |
| `WorkerRunningEvent`           | Worker çalıştığında                       |
| `WorkerStartedEvent`           | Worker başlatıldığında                     |
| `WorkerStoppedEvent`           | Worker durdurulduğunda                         |

---

## 🧩 Handler’lara Ek Argümanlar Aktarma

Messenger, handler’lara **ek veri** iletebilir.

Bunu `HandlerArgumentsStamp` kullanarak middleware içinde gerçekleştirebilirsiniz:

```php
// src/Messenger/AdditionalArgumentMiddleware.php
namespace App\Messenger;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\HandlerArgumentsStamp;

final class AdditionalArgumentMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $envelope = $envelope->with(new HandlerArgumentsStamp([
            $this->resolveAdditionalArgument($envelope->getMessage()),
        ]));

        return $stack->next()->handle($envelope, $stack);
    }

    private function resolveAdditionalArgument(object $message): mixed
    {
        // ...
    }
}
```

Handler tarafında bu ek argümanlar otomatik olarak alınır:

```php
// src/MessageHandler/SmsNotificationHandler.php
namespace App\MessageHandler;

use App\Message\SmsNotification;

final class SmsNotificationHandler
{
    public function __invoke(SmsNotification $message, mixed $additionalArgument)
    {
        // ...
    }
}
```

---

## 🧾 Özel Mesaj Serializer (Custom Data Formats)

Başka sistemlerden gelen mesajlar Symfony’nin beklediği JSON formatında olmayabilir.

Bu durumda `SerializerInterface` uygulayan özel bir **message serializer/decoder** oluşturabilirsiniz:

```php
// src/Messenger/Serializer/MessageWithTokenDecoder.php
namespace App\Messenger\Serializer;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class MessageWithTokenDecoder implements SerializerInterface
{
    public function decode(array $encodedEnvelope): Envelope
    {
        try {
            $data = $encodedEnvelope['data'];
            $data['token'] = $encodedEnvelope['token'];
        } catch (\Throwable $throwable) {
            return new Envelope($throwable);
        }

        return new Envelope($data);
    }

    public function encode(Envelope $envelope): array
    {
        throw new \LogicException('This serializer is only used for decoding messages.');
    }
}
```

Ve bu serializer’ı belirli bir transport’ta kullanabilirsiniz:

```php
// config/packages/messenger.php
use App\Messenger\Serializer\MessageWithTokenDecoder;
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->messenger()
        ->transport('my_transport')
            ->dsn('%env(MY_TRANSPORT_DSN)%')
            ->serializer(MessageWithTokenDecoder::class);
};
```

---

## 🚦 Birden Fazla Bus (Command, Query, Event)

Varsayılan olarak Messenger tek bir **Message Bus** oluşturur.

Ancak ister “command”, ister “query”, ister “event” bus’ları oluşturabilir ve bunların middleware’lerini ayrı ayrı tanımlayabilirsiniz.

Bu, **CQRS** (Command Query Responsibility Segregation) mimarisine uygundur — yani **komutlar (commands)** eylemleri, **sorgular (queries)** ise veriyi temsil eder.

```php
// config/packages/messenger.php
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    // MessageBusInterface olarak enjekte edilecek varsayılan bus
    $framework->messenger()->defaultBus('command.bus');

    $commandBus = $framework->messenger()->bus('command.bus');
    $commandBus->middleware()->id('validation');
    $commandBus->middleware()->id('doctrine_transaction');

    $queryBus = $framework->messenger()->bus('query.bus');
    $queryBus->middleware()->id('validation');

    $eventBus = $framework->messenger()->bus('event.bus');
    $eventBus->defaultMiddleware()
        ->enabled(true)
        ->allowNoHandlers(false)
        ->allowNoSenders(true);

    $eventBus->middleware()->id('validation');
};
```

Bu, üç yeni servis oluşturur:

| Servis          | Açıklama                                                           |
| --------------- | -------------------------------------------------------------------- |
| `command.bus` | Varsayılan Bus —`MessageBusInterface`tip ipucuyla enjekte edilir |
| `query.bus`   | `MessageBusInterface $queryBus`olarak enjekte edilir               |
| `event.bus`   | `MessageBusInterface $eventBus`olarak enjekte edilir               |

---

## 🚫 Handler’ları Belirli Bus’larla Sınırlama

Varsayılan olarak, tüm handler’lar tüm bus’lar tarafından görülebilir.

Bir handler’ın yalnızca belirli bir bus tarafından kullanılmasını istiyorsanız:

```php
// config/services.php
$container->services()
    ->set(App\MessageHandler\SomeCommandHandler::class)
    ->tag('messenger.message_handler', ['bus' => 'command.bus']);
```

Daha da iyisi, bunu otomatik yapmak için `_instanceof` yapılandırmasını kullanabilirsiniz:

```php
// config/services.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\MessageHandler\CommandHandlerInterface;
use App\MessageHandler\QueryHandlerInterface;

return function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->instanceof(CommandHandlerInterface::class)
        ->tag('messenger.message_handler', ['bus' => 'command.bus']);

    $services->instanceof(QueryHandlerInterface::class)
        ->tag('messenger.message_handler', ['bus' => 'query.bus']);
};
```

---

## 🧭 Bus’ları Hata Ayıklama

Aşağıdaki komutla her bus’taki mesaj ve handler listesini görebilirsiniz:

```bash
php bin/console debug:messenger
```

Belirli bir bus adı vererek çıktıyı filtreleyebilirsiniz.

---

## 🔁 Mesajı Yeniden Gönderme (Redispatch)

Bir mesajı aynı transport ve envelope ile yeniden göndermek isterseniz,

`RedispatchMessage` kullanabilirsiniz:

```php
// src/MessageHandler/SmsNotificationHandler.php
namespace App\MessageHandler;

use App\Message\SmsNotification;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Message\RedispatchMessage;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class SmsNotificationHandler
{
    public function __construct(private MessageBusInterface $bus)
    {
    }

    public function __invoke(SmsNotification $message): void
    {
        // Mesajla ilgili işlemler
        // Yeniden gönderim ihtiyacına göre:

        if ($needsRedispatch) {
            $this->bus->dispatch(new RedispatchMessage($message));
        }
    }
}
```

Symfony’nin yerleşik `RedispatchMessageHandler` sınıfı, mesajı ilk gönderildiği bus üzerinden yeniden dispatch eder.

İsterseniz `RedispatchMessage` kurucusuna transport isimlerini de geçerek mesajı özel transport’lara yeniden yönlendirebilirsiniz.
