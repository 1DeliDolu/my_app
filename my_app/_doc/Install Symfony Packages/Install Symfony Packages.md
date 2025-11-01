
---

# 🧩 Symfony Components

| Paket                                  | Açıklama                                                                                  |
| -------------------------------------- | ------------------------------------------------------------------------------------------- |
| **symfony/config**               | Uygulama yapılandırmalarını yüklemek, doğrulamak ve birleştirmek için kullanılır. |
| **symfony/console**              | Komut satırı (CLI) araçları oluşturmak için altyapı sağlar.                         |
| **symfony/dependency-injection** | Servislerin tanımlanması ve bağımlılık yönetimini sağlar (DI Container).            |
| **symfony/http-foundation**      | HTTP istek ve yanıt nesnelerini soyutlayarak web katmanını yönetir.                     |
| **symfony/http-kernel**          | Symfony’nin çekirdeğidir; request-response yaşam döngüsünü yönetir.                |
| **symfony/routing**              | URL yönlendirmesi yapar; controller’lara ulaşımı sağlar.                              |
| **symfony/cache**                | Uygulama önbellekleme sistemini sağlar (PSR-6 / PSR-16 uyumlu).                           |
| **symfony/security-core**        | Kimlik doğrulama, yetkilendirme ve güvenlik mekanizmaları sunar.                         |
| **symfony/validator**            | Nesne ve form verilerini kurallara göre doğrular.                                         |
| **symfony/form**                 | Form oluşturma, doğrulama ve render işlemlerini kolaylaştırır.                        |
| **symfony/translation**          | Çok dilli (i18n) uygulamalar geliştirmek için kullanılır.                              |
| **symfony/mailer**               | E-posta gönderimini yönetir ve farklı servislerle entegre çalışır.                   |
| **symfony/messenger**            | Mesaj kuyruğu sistemleriyle asenkron işlem yapılmasını sağlar.                        |
| **symfony/serializer**           | Nesneleri JSON, XML gibi formatlara dönüştürür (ve tersi).                             |
| **symfony/finder**               | Dosya sisteminde kolay dosya/dizin aramaları yapar.                                        |
| **symfony/filesystem**           | Dosya işlemlerini (oluşturma, silme, kopyalama) güvenli hale getirir.                    |
| **symfony/stopwatch**            | Kodun belirli kısımlarını zamanlayarak performans ölçümü yapar.                     |
| **symfony/string**               | Metin işleme, Unicode desteği ve manipülasyon araçları sunar.                          |
| **symfony/uid**                  | UUID ve ULID oluşturmak ve yönetmek için kullanılır.                                   |
| **symfony/yaml**                 | YAML formatındaki yapılandırma dosyalarını işler.                                     |

---

# 🔌 Third-party SaaS Bridges

| Paket                                      | Açıklama                                                       |
| ------------------------------------------ | ---------------------------------------------------------------- |
| **symfony/amazon-mailer**            | Amazon SES üzerinden e-posta gönderimi sağlar.                |
| **symfony/slack-notifier**           | Slack kanallarına mesaj göndermek için kullanılır.          |
| **symfony/twilio-notifier**          | Twilio üzerinden SMS veya arama bildirimi gönderir.            |
| **symfony/google-chat-notifier**     | Google Chat’e mesaj göndermeyi sağlar.                        |
| **symfony/microsoft-teams-notifier** | Microsoft Teams kanallarına bildirim yollar.                    |
| **symfony/telegram-notifier**        | Telegram bot’ları aracılığıyla mesaj gönderir.            |
| **symfony/matrix-notifier**          | Matrix protokolüyle anlık ileti gönderir.                     |
| **symfony/discord-notifier**         | Discord kanallarına mesaj yollar.                               |
| **symfony/mailjet-mailer**           | Mailjet API’si ile e-posta gönderimi sağlar.                  |
| **symfony/postmark-mailer**          | Postmark servisiyle transactional mail gönderir.                |
| **symfony/sendgrid-mailer**          | SendGrid API ile e-posta gönderimini yönetir.                  |
| **symfony/pushy-notifier**           | Pushy platformu üzerinden mobil push bildirimi yollar.          |
| **symfony/pushover-notifier**        | Pushover servisiyle cihazlara anlık uyarı gönderir.           |
| **symfony/rocket-chat-notifier**     | Rocket.Chat odalarına mesaj gönderir.                          |
| **symfony/firebase-notifier**        | Firebase Cloud Messaging (FCM) üzerinden push bildirimi yollar. |
| **symfony/mailtrap-mailer**          | Test ortamında e-posta yakalama ve görüntüleme aracı.       |

---

# 🧱 Polyfills

| Paket                               | Açıklama                                                             |
| ----------------------------------- | ---------------------------------------------------------------------- |
| **symfony/polyfill-php80**    | PHP 8.0 özelliklerini eski sürümlere taşır.                       |
| **symfony/polyfill-php81**    | PHP 8.1 fonksiyonlarını eski sürümlerde kullanılabilir yapar.     |
| **symfony/polyfill-intl-icu** | ICU eklentisi olmadan çok dilli destek sağlar.                       |
| **symfony/polyfill-mbstring** | `mbstring`uzantısı olmadan çok baytlı karakter işlemleri sunar. |
| **symfony/polyfill-uuid**     | UUID fonksiyonları ekler.                                             |
| **symfony/polyfill-util**     | Temel yardımcı fonksiyonlar içerir.                                 |

---

# 📦 Symfony Flex Packs

| Paket                             | Açıklama                                                                     |
| --------------------------------- | ------------------------------------------------------------------------------ |
| **symfony/orm-pack**        | Doctrine ORM entegrasyonu için ön yapılandırma paketi.                     |
| **symfony/debug-pack**      | Geliştirme ortamı için hata ayıklama araçları (Profiler, Debug Toolbar). |
| **symfony/test-pack**       | PHPUnit ve test araçlarını kurmak için hazır yapılandırma.              |
| **symfony/profiler-pack**   | Symfony Web Profiler ve Debug Toolbar kurulumunu içerir.                      |
| **symfony/serializer-pack** | Serializer bileşeni için temel yapılandırma.                               |
| **symfony/twig-pack**       | Twig şablon motoru için yapılandırma içerir.                              |

---

# 🧰 Symfony Bundles

| Paket                              | Açıklama                                                                 |
| ---------------------------------- | -------------------------------------------------------------------------- |
| **symfony/framework-bundle** | Symfony çekirdeğini çalıştıran ana paket.                            |
| **symfony/security-bundle**  | Güvenlik yapılandırması ve firewall yönetimi sağlar.                 |
| **symfony/twig-bundle**      | Twig şablon motoru entegrasyonunu sağlar.                                |
| **symfony/maker-bundle**     | Komut satırından hızlı kod üretimi sağlar (controller, entity, vb.). |
| **symfony/debug-bundle**     | Debug Toolbar ve hata yakalama işlevlerini içerir.                       |
| **symfony/ai-bundle**        | Symfony’nin yapay zeka destekli entegrasyon araçlarını sunar.          |
| **symfony/monolog-bundle**   | Monolog kütüphanesi ile loglama sağlar.                                 |
| **symfony/stimulus-bundle**  | Symfony UX için StimulusJS entegrasyonunu sağlar.                        |

---

# 🤝 Symfony Contracts

| Paket                                        | Açıklama                                             |
| -------------------------------------------- | ------------------------------------------------------ |
| **symfony/service-contracts**          | Servislerin arayüz sözleşmelerini tanımlar.        |
| **symfony/translation-contracts**      | Çeviri sistemleri için ortak arayüz sağlar.        |
| **symfony/http-client-contracts**      | HTTP istemcileri için ortak API tanımlar.            |
| **symfony/cache-contracts**            | Cache bileşenleri için standart arabirimler içerir. |
| **symfony/event-dispatcher-contracts** | Olay yönetimi için sözleşmeler sağlar.            |

---

# 💡 Symfony UX Packages

| Paket                                | Açıklama                                                          |
| ------------------------------------ | ------------------------------------------------------------------- |
| **symfony/ux-react**           | React bileşenlerini Symfony uygulamalarına entegre eder.          |
| **symfony/ux-vue**             | Vue.js ile frontend bileşenlerini Symfony’de kullanmayı sağlar. |
| **symfony/ux-svelte**          | Svelte framework entegrasyonu sunar.                                |
| **symfony/ux-live-component**  | Gerçek zamanlı, reaktif bileşenler oluşturmayı sağlar.        |
| **symfony/ux-turbo**           | Turbo & Hotwire ile sayfa yenilemeden dinamik etkileşim sağlar.   |
| **symfony/ux-notify**          | Tarayıcı bildirimleri oluşturur.                                 |
| **symfony/ux-chartjs**         | Chart.js ile grafik oluşturmayı kolaylaştırır.                 |
| **symfony/ux-dropzone**        | Dropzone.js entegrasyonu ile dosya yükleme sağlar.                |
| **symfony/ux-icons**           | Popüler ikon kütüphanelerini Twig içinde kullanmayı sağlar.   |
| **symfony/ux-toggle-password** | Formlardaki şifre alanlarında görünürlük geçişi ekler.      |
| **symfony/ux-typed**           | Otomatik yazı efekti oluşturur.                                   |
| **symfony/ux-google-map**      | Google Maps API ile harita bileşenleri sunar.                      |

---


---

# 🧩 Symfony Components

Symfony’nin temel yapı taşlarıdır. Framework’ün her kısmı bu bileşenlerle inşa edilir ve bağımsız da kullanılabilir.

| Paket                                  | Açıklama                                                                        |
| -------------------------------------- | --------------------------------------------------------------------------------- |
| **symfony/config**               | Yapılandırma dosyalarını yükler, doğrular ve birleştirir.                  |
| **symfony/console**              | Komut satırı uygulamaları oluşturmak için altyapı sağlar.                  |
| **symfony/dependency-injection** | Servislerin tanımlanması ve bağımlılıkların yönetimini sağlar.           |
| **symfony/http-foundation**      | HTTP istek/yanıt sistemini nesne tabanlı hale getirir.                          |
| **symfony/http-kernel**          | Symfony uygulamasının çekirdeğidir, request–response döngüsünü yönetir. |
| **symfony/routing**              | URL yönlendirmelerini controller’lara bağlar.                                  |
| **symfony/cache**                | PSR uyumlu cache sistemi sunar.                                                   |
| **symfony/validator**            | Nesne ve form verilerini doğrular.                                               |
| **symfony/form**                 | Form oluşturma, doğrulama ve render işlemlerini kolaylaştırır.              |
| **symfony/translation**          | Çok dilli (i18n) destek sağlar.                                                 |
| **symfony/mailer**               | E-posta gönderimi ve posta servisi entegrasyonları sağlar.                     |
| **symfony/messenger**            | Asenkron mesajlaşma (queue) sistemlerini yönetir.                               |
| **symfony/serializer**           | Nesneleri JSON, XML gibi formatlara dönüştürür.                              |
| **symfony/finder**               | Dosya sisteminde kolay dosya aramaları sağlar.                                  |
| **symfony/filesystem**           | Dosya oluşturma, silme ve taşıma işlemlerini kolaylaştırır.                |
| **symfony/stopwatch**            | Kod performansını ölçer.                                                      |
| **symfony/string**               | Unicode uyumlu string işlemleri sunar.                                           |
| **symfony/uid**                  | UUID ve ULID üretimi sağlar.                                                    |
| **symfony/yaml**                 | YAML dosyalarını okur/yazar.                                                    |

---

# 🔌 Third-party SaaS Bridges

Üçüncü taraf servislerle (örneğin Twilio, Slack, Amazon, vb.) entegre çalışan köprü paketlerdir.

| Paket                                      | Açıklama                                                   |
| ------------------------------------------ | ------------------------------------------------------------ |
| **symfony/amazon-mailer**            | Amazon SES ile e-posta gönderimi sağlar.                   |
| **symfony/slack-notifier**           | Slack kanallarına mesaj yollar.                             |
| **symfony/twilio-notifier**          | Twilio üzerinden SMS ve arama bildirimi yollar.             |
| **symfony/google-chat-notifier**     | Google Chat’e mesaj gönderir.                              |
| **symfony/microsoft-teams-notifier** | Teams kanallarına bildirim gönderir.                       |
| **symfony/telegram-notifier**        | Telegram bot’larıyla mesaj gönderimi yapar.               |
| **symfony/discord-notifier**         | Discord odalarına mesaj gönderir.                          |
| **symfony/mailjet-mailer**           | Mailjet API’siyle e-posta gönderimi sağlar.               |
| **symfony/postmark-mailer**          | Postmark üzerinden transactional e-posta yollar.            |
| **symfony/sendgrid-mailer**          | SendGrid API’si ile e-posta gönderir.                      |
| **symfony/pushy-notifier**           | Mobil push bildirimlerini yönetir.                          |
| **symfony/pushover-notifier**        | Pushover cihaz bildirimlerini yollar.                        |
| **symfony/rocket-chat-notifier**     | Rocket.Chat odalarına mesaj yollar.                         |
| **symfony/firebase-notifier**        | Firebase Cloud Messaging (FCM) ile push bildirimi gönderir. |
| **symfony/mailtrap-mailer**          | Test ortamlarında e-posta yakalama aracıdır.              |

---

# 🧱 Polyfills

PHP’nin eski sürümlerinde bulunmayan işlevleri yeni sürümle uyumlu hale getirir.

| Paket                               | Açıklama                                                    |
| ----------------------------------- | ------------------------------------------------------------- |
| **symfony/polyfill-php80**    | PHP 8.0 özelliklerini eski sürümlere taşır.              |
| **symfony/polyfill-php81**    | PHP 8.1 fonksiyonlarını eski sürümlerde sağlar.          |
| **symfony/polyfill-intl-icu** | ICU uzantısı olmadan çok dilli destek sunar.               |
| **symfony/polyfill-intl-idn** | IDN (uluslararası alan adı) desteğini ekler.               |
| **symfony/polyfill-mbstring** | `mbstring`olmadan çok baytlı string işlemlerini sağlar. |
| **symfony/polyfill-uuid**     | UUID oluşturma işlevleri sağlar.                           |
| **symfony/polyfill-util**     | Temel yardımcı fonksiyonlar içerir.                        |

---

# 📦 Symfony Flex Packs

Flex, Symfony projelerinde hızlı kurulum sağlar. Bu paketler, sık kullanılan bileşenleri tek adımda ekler.

| Paket                             | Açıklama                                         |
| --------------------------------- | -------------------------------------------------- |
| **symfony/orm-pack**        | Doctrine ORM kurulumu için ön yapılandırma.    |
| **symfony/debug-pack**      | Debug araçlarını kurar (Web Profiler, Toolbar). |
| **symfony/test-pack**       | PHPUnit test altyapısını kurar.                 |
| **symfony/profiler-pack**   | Symfony Web Profiler kurulumunu sağlar.           |
| **symfony/serializer-pack** | Serializer bileşenini hazırlar.                  |
| **symfony/twig-pack**       | Twig şablon motoru entegrasyonu.                  |

---

# 🧰 Symfony Bundles

Symfony uygulamalarına özellik kazandıran genişletilmiş modüllerdir.

| Paket                              | Açıklama                                                           |
| ---------------------------------- | -------------------------------------------------------------------- |
| **symfony/framework-bundle** | Symfony’nin çekirdek çalışma altyapısını içerir.            |
| **symfony/security-bundle**  | Güvenlik, kimlik doğrulama ve yetkilendirme işlemlerini yönetir. |
| **symfony/twig-bundle**      | Twig şablon motorunu entegre eder.                                  |
| **symfony/maker-bundle**     | Kod üretimi (controller, entity vb.) için araçlar sunar.          |
| **symfony/debug-bundle**     | Geliştirme ortamında hata yakalama sağlar.                        |
| **symfony/ai-bundle**        | Symfony AI araçlarını entegre eder.                               |
| **symfony/monolog-bundle**   | Loglama altyapısını Monolog ile sağlar.                          |
| **symfony/stimulus-bundle**  | StimulusJS ile etkileşimli arayüzler oluşturur.                   |

---

# 🤝 Symfony Contracts

Bileşenler arası uyumluluğu sağlamak için ortak arabirim sözleşmeleri içerir.

| Paket                                        | Açıklama                                               |
| -------------------------------------------- | -------------------------------------------------------- |
| **symfony/service-contracts**          | Servislerin ortak API tanımlarını belirler.           |
| **symfony/translation-contracts**      | Çeviri sistemleri için arayüz sağlar.                |
| **symfony/http-client-contracts**      | HTTP istemcileri için ortak yapı tanımlar.            |
| **symfony/cache-contracts**            | Cache sistemleri için arayüz sözleşmeleri içerir.   |
| **symfony/event-dispatcher-contracts** | Olay tabanlı sistemler için standart arabirim sağlar. |

---

# 💡 Symfony UX Packages

Frontend ile backend entegrasyonunu güçlendirir; interaktif arayüzler geliştirmeyi sağlar.

| Paket                                | Açıklama                                                            |
| ------------------------------------ | --------------------------------------------------------------------- |
| **symfony/ux-react**           | React bileşenlerini Symfony uygulamasına entegre eder.              |
| **symfony/ux-vue**             | Vue.js ile dinamik UI bileşenleri oluşturur.                        |
| **symfony/ux-svelte**          | Svelte framework entegrasyonu sağlar.                                |
| **symfony/ux-live-component**  | Gerçek zamanlı reaktif bileşenler sunar.                           |
| **symfony/ux-turbo**           | Hotwire & Turbo ile tam sayfa yenilemeden dinamik etkileşim sağlar. |
| **symfony/ux-notify**          | Tarayıcı bildirimlerini yönetir.                                   |
| **symfony/ux-chartjs**         | Chart.js tabanlı grafik bileşenleri ekler.                          |
| **symfony/ux-dropzone**        | Dropzone.js ile sürükle-bırak dosya yükleme alanları oluşturur. |
| **symfony/ux-icons**           | Popüler ikon setlerini Twig içinde kullanmayı sağlar.             |
| **symfony/ux-toggle-password** | Şifre alanı görünürlük geçişi ekler.                          |
| **symfony/ux-typed**           | Yazı animasyonu (typewriter efekti) sağlar.                         |
| **symfony/ux-google-map**      | Google Maps API ile harita bileşenleri oluşturur.                   |
| **symfony/ux-swup**            | Sayfalar arasında geçiş animasyonları ekler.                      |
