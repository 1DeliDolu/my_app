## Teknik Gereksinimler

İlk Symfony uygulamanızı oluşturmadan önce aşağıdakilerin kurulu olması gerekir:

* **PHP 8.2 veya üzeri**

  Ayrıca, çoğu PHP 8 kurulumunda varsayılan olarak etkin olan şu PHP eklentilerinin de yüklü olması gerekir:

  `Ctype`, `iconv`, `PCRE`, `Session`, `SimpleXML` ve `Tokenizer`.
* **Composer**

  PHP paketlerini yüklemek için kullanılan **Composer** aracını kurun.
* **Symfony CLI (isteğe bağlı)**

  Symfony CLI’yi yüklemeniz önerilir. Bu araç, yerel olarak Symfony uygulamanızı geliştirmek ve çalıştırmak için ihtiyaç duyacağınız tüm araçları sağlayan `symfony` adlı bir komut satırı aracı sunar.

---

### Gereksinimlerin Kontrolü

`symfony` aracı, bilgisayarınızın tüm sistem gereksinimlerini karşılayıp karşılamadığını kontrol eden bir komut içerir. Terminalinizi açın ve şu komutu çalıştırın:

```bash
symfony check:requirements
```

---

### Açık Kaynak ve Katkı

Symfony CLI açık kaynaklıdır. Projeye katkıda bulunmak isterseniz, GitHub üzerindeki şu depoyu ziyaret edebilirsiniz:

👉 [symfony-cli/symfony-cli](https://github.com/symfony-cli/symfony-cli)

## Symfony Uygulaması Oluşturma

Yeni bir Symfony uygulaması oluşturmak için terminalinizi açın ve aşağıdaki komutlardan birini çalıştırın:

```bash
symfony new my_project_directory --version="7.3.x" --webapp
```

veya

```bash
symfony new my_project_directory --version="7.3.x"
```

Bu iki komut arasındaki tek fark, **varsayılan olarak yüklenen paketlerin sayısıdır.**

`--webapp` seçeneği, bir web uygulaması geliştirmek için gerekli olan ek paketleri yükler.

---

### Symfony CLI Kullanmadığınız Durumda

Eğer Symfony CLI kullanmıyorsanız, Composer aracılığıyla yeni bir Symfony uygulaması oluşturabilirsiniz:

#### Web uygulaması kurmak için:

```bash
composer create-project symfony/skeleton:"7.3.x" my_project_directory
cd my_project_directory
composer require webapp
```

#### Temel (minimal) kurulum için:

```bash
composer create-project symfony/skeleton:"7.3.x" my_project_directory
```

---

### Kurulum Sonrası

Hangi komutu kullanırsanız kullanın, Symfony aşağıdakileri sizin için yapacaktır:

* `my_project_directory/` adlı yeni bir klasör oluşturur,
* Gerekli bağımlılıkları indirir,
* Başlangıç için gereken temel dosya ve klasör yapısını oluşturur.

**Kısacası, yeni Symfony uygulamanız kullanıma hazırdır! 🎉**

---

### İzinler (Permissions)

Projenin **önbellek** ve **log** dizinlerinin (varsayılan olarak `<project>/var/cache/` ve `<project>/var/log/`) web sunucusu tarafından yazılabilir olması gerekir.

Eğer bu dizinlerde izin hatasıyla karşılaşırsanız, Symfony uygulamaları için izinlerin nasıl ayarlanacağını anlatan dokümantasyona göz atın.

## Mevcut Bir Symfony Projesini Kurma

Yeni Symfony projeleri oluşturmanın yanı sıra, başka geliştiriciler tarafından oluşturulmuş projelerde de çalışabilirsiniz.

Bu durumda yapmanız gereken, **proje kodlarını almak** ve **bağımlılıkları Composer ile yüklemektir.**

Takımınızın **Git** kullandığını varsayarsak, mevcut bir Symfony projesini kurmak için şu adımları izleyin:

```bash
cd projects/
git clone <proje-deposu-url>
```

Daha sonra proje klasörüne girin ve bağımlılıkları yükleyin:

```bash
cd my-project/
composer install
```

---

### Ortam Ayarları (.env)

Projenizi çalıştırmadan önce, muhtemelen `.env` dosyasını özelleştirmeniz gerekecektir.

Ayrıca proje türüne bağlı olarak bazı ek adımlar (örneğin bir  **veritabanı oluşturmak** ) da gerekebilir.

---

### Proje Bilgilerini Görüntüleme

Mevcut bir Symfony uygulamasında ilk kez çalışıyorsanız, proje hakkında genel bilgi almak için şu komutu çalıştırabilirsiniz:

```bash
php bin/console about
```

Bu komut, projenizin sürümü, kurulu bileşenler ve ortam bilgileri gibi yararlı detayları görüntüler.

## Symfony Uygulamalarını Çalıştırma

### Üretim Ortamında (Production)

Üretim ortamında Symfony uygulamanızı çalıştırmak için bir web sunucusu (örneğin **Nginx** veya  **Apache** ) kurmalı ve Symfony’yi çalışacak şekilde yapılandırmalısınız.

Bu yöntem, Symfony’nin yerel web sunucusunu kullanmadan geliştirme yapmak isteyenler için de uygundur.

---

### Geliştirme Ortamında (Local Development)

Yerel geliştirme sırasında Symfony uygulamasını çalıştırmanın en kolay ve önerilen yolu, **Symfony CLI** tarafından sağlanan **yerel web sunucusunu** kullanmaktır.

Bu yerel sunucu aşağıdaki özellikleri destekler:

* **HTTP/2** desteği
* **Eşzamanlı (concurrent) istekler**
* **TLS/SSL** (güvenli bağlantılar)
* **Otomatik güvenlik sertifikası oluşturma**

---

### Sunucuyu Başlatma

Yeni projenizin dizinine gidin ve yerel web sunucusunu başlatmak için şu komutları çalıştırın:

```bash
cd my-project/
symfony server:start
```

Ardından tarayıcınızı açın ve şu adrese gidin:

👉 [http://localhost:8000/](http://localhost:8000/)

Her şey doğru yapılandırıldıysa, **Symfony hoş geldiniz sayfasını** göreceksiniz. 🎉

İşiniz bittiğinde sunucuyu durdurmak için terminalde `Ctrl + C` tuşlarına basabilirsiniz.

---

### Not

Bu web sunucusu yalnızca Symfony projeleriyle değil, **herhangi bir PHP uygulamasıyla** da çalışır.

Bu nedenle genel amaçlı, kullanışlı bir geliştirme aracıdır.

---

## Symfony Docker Entegrasyonu

Symfony’yi **Docker** ile kullanmak istiyorsanız, resmi dökümantasyondaki **[Symfony ile Docker Kullanımı](https://symfony.com/doc/current/setup/docker.html)** sayfasına göz atabilirsiniz.

---

## Paketleri (Bundle) Yükleme

Symfony uygulamaları geliştirirken sıkça yapılan bir işlem, **hazır özellikler sunan paketleri (bundle)** yüklemektir.

Bu paketler genellikle kullanılmadan önce bazı yapılandırmalar gerektirir (örneğin bir dosyayı düzenlemek, bir yapılandırma dosyası oluşturmak vb.).

Bu yapılandırma işlemlerini otomatikleştirmek için Symfony, **Symfony Flex** adlı bir araç içerir.

Symfony Flex, Symfony uygulamalarında paketlerin yüklenmesini veya kaldırılmasını kolaylaştıran bir  **Composer eklentisidir** .

Yeni bir Symfony projesi oluşturduğunuzda varsayılan olarak yüklenir.

> İsterseniz Symfony Flex’i mevcut bir projeye de sonradan ekleyebilirsiniz.

---

### Symfony Flex’in Çalışma Şekli

Symfony Flex, Composer’ın `require`, `update` ve `remove` komutlarının davranışını değiştirerek gelişmiş özellikler sunar.

Örneğin:

```bash
cd my-project/
composer require logger
```

Eğer Flex  **yüklü değilse** , bu komut bir hata döndürür çünkü `logger` geçerli bir paket ismi değildir.

Ancak **Symfony Flex** varsa, bu komut Symfony’nin resmi **logger** bileşenini çalıştırmak için gereken tüm paketleri otomatik olarak yükler ve etkinleştirir.

---

### Flex Tarifleri (Recipes)

Birçok Symfony paketi/bundle, yükleme ve etkinleştirme işlemini otomatikleştiren **“recipe”** (tarif) dosyaları tanımlar.

Flex, hangi tariflerin kurulduğunu `symfony.lock` dosyasında saklar — bu dosya sürüm kontrolüne (örneğin Git) dahil edilmelidir.

#### Tarif Depoları:

1. **Ana tarif deposu (Main Recipe Repository)**
   * Symfony tarafından onaylanmış, bakımı yapılan yüksek kaliteli paketlerin tariflerini içerir.
   * Symfony Flex varsayılan olarak yalnızca bu depoyu kullanır.
2. **Katkı (Contrib) tarif deposu**
   * Topluluk tarafından oluşturulan tüm tarifleri içerir.
   * Bu tariflerin çalışması garanti edilir, ancak ilgili paketler artık bakımı yapılmıyor olabilir.
   * Flex, bu tarifleri yüklemeden önce sizden onay ister.

> Kendi paketiniz için tarif oluşturmayı öğrenmek isterseniz **Symfony Recipes** dökümantasyonuna bakın.

---

## Symfony Paket Grupları (Packs)

Bazen tek bir özelliği kullanmak için birden fazla paketin yüklenmesi gerekir.

Symfony bu işlemi kolaylaştırmak için **pack** adını verdiği meta paketleri sunar.

Bir pack, birden fazla bağımlılığı tek seferde yükleyen bir  **Composer metapackage** ’tir.

Örneğin, hata ayıklama (debugging) araçlarını eklemek için şu komutu çalıştırabilirsiniz:

```bash
composer require --dev debug
```

Bu komut `symfony/debug-pack` paketini yükler ve bu paket aşağıdaki bağımlılıkları otomatik olarak kurar:

* `symfony/debug-bundle`
* `symfony/monolog-bundle`
* `symfony/var-dumper`
* vb.

Flex, pack paketini **otomatik olarak açtığı** için `composer.json` içinde `symfony/debug-pack` görünmez.

Bunun yerine, bu paketin içindeki gerçek bağımlılıklar (`symfony/var-dumper` gibi) `require-dev` kısmına eklenir.

---

## Güvenlik Açıklarını Kontrol Etme

Symfony CLI, projenizin bağımlılıklarında bilinen güvenlik açıkları olup olmadığını kontrol eden bir komut sağlar:

```bash
symfony check:security
```

Bu komutu düzenli olarak çalıştırmak iyi bir güvenlik uygulamasıdır.

Bu sayede tehlikeli bağımlılıkları **erken fark edip** güncelleyebilirsiniz.

Denetim işlemi **yerel olarak** yapılır — `composer.lock` dosyanız ağ üzerinden gönderilmez.

Eğer herhangi bir bağımlılıkta güvenlik açığı varsa, komut **0’dan farklı bir çıkış kodu** döndürür.

Bu nedenle, bu komutu CI/CD (sürekli entegrasyon) süreçlerinize dahil ederek güvenlik sorunlarında otomatik hata alınmasını sağlayabilirsiniz.

> CI ortamlarında Symfony CLI yüklemek istemiyorsanız, aynı kontrolü şu komutla yapabilirsiniz:
>
> ```bash
> composer audit
> ```

---

## Symfony LTS (Long-Term Support) Sürümleri

Symfony sürüm sürecine göre, **LTS (uzun vadeli destek)** sürümleri **her iki yılda bir** yayınlanır.

En güncel LTS sürümünü görmek için [Symfony Releases](https://symfony.com/releases) sayfasına göz atabilirsiniz.

Yeni bir Symfony uygulaması oluştururken komut varsayılan olarak **en son kararlı sürümü** kullanır.

Ancak belirli bir sürümü seçmek isterseniz `--version` seçeneğini ekleyebilirsiniz:

```bash
symfony new my_project_directory --version=lts
symfony new my_project_directory --version=next
symfony new my_project_directory --version="6.4.*"
```

`lts` ve `next` kısayolları yalnızca **Symfony CLI** ile kullanılabilir.

Composer kullanıyorsanız sürümü açıkça belirtmelisiniz:

```bash
composer create-project symfony/skeleton:"6.4.*" my_project_directory
```

---

## Symfony Demo Uygulaması

 **Symfony Demo Application** , Symfony uygulamalarının önerilen şekilde nasıl geliştirileceğini gösteren **tam işlevli bir örnek uygulamadır.**

Yeni başlayanlar için mükemmel bir öğrenme aracıdır ve kodu, öğretici açıklamalar ve notlarla doludur.

Yeni bir demo projesi oluşturmak için şu komutu çalıştırın:

```bash
symfony new my_project_directory --demo
```

---

## Kodlamaya Başlayın! 🚀

Kurulum tamam!

Artık sıradaki adım — **Symfony’de ilk sayfanızı oluşturmak.**


---

[Create your First Page in Symfony](2_Create your First Page in Symfony.md)
