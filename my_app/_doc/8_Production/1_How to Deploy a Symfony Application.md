# Symfony Uygulamasını Dağıtma 🚀

Symfony uygulamasını dağıtmak, yapılandırmaya ve uygulamanızın gereksinimlerine bağlı olarak karmaşık ve çeşitli bir görev olabilir. Bu makale adım adım bir rehber değil, daha çok dağıtım için en yaygın gereksinimlerin ve fikirlerin genel bir listesidir.

## ⚙️ Symfony Dağıtım Temelleri

Bir Symfony uygulaması dağıtılırken tipik olarak şu adımlar izlenir:

* Kodunuzu üretim sunucusuna yükleyin;
* Vendor bağımlılıklarını yükleyin (genellikle Composer aracılığıyla yapılır ve yüklemeden önce yapılabilir);
* Veritabanı göçlerini (migrations) veya benzer görevleri çalıştırarak değişen veri yapılarınızı güncelleyin;
* Cache’i temizleyin (ve isteğe bağlı olarak önceden ısıtın).

Bir dağıtım ayrıca şu görevleri de içerebilir:

* Kodunuzun belirli bir sürümünü kaynak kontrol deposunda **sürüm etiketi (tag)** olarak işaretlemek;
* Güncellenmiş yapınızı “çevrimdışı” olarak oluşturmak için geçici bir **staging** alanı oluşturmak;
* Kodun ve/veya sunucunun kararlılığını sağlamak için mevcut testleri çalıştırmak;
* Üretim ortamınızı temiz tutmak için **public/** dizininden gereksiz dosyaları kaldırmak;
* **Memcached** veya **Redis** gibi harici önbellek sistemlerini temizlemek.

---

## 🚚 Symfony Uygulamasını Dağıtma Yöntemleri

Symfony uygulamanızı dağıtmanın birkaç yolu vardır. Öncelikle bazı temel dağıtım stratejileriyle başlayın ve oradan geliştirin.

### 📂 Temel Dosya Transferi

Bir uygulamayı dağıtmanın en basit yolu, dosyaları **FTP/SCP** (veya benzeri bir yöntem) ile manuel olarak kopyalamaktır.

Bu yöntemin dezavantajı, yükseltme süreci boyunca sistem üzerinde kontrol eksikliğidir. Ayrıca dosyaları aktardıktan sonra bazı manuel adımlar atmanız gerekir (bkz.  **Ortak Dağıtım Görevleri** ).

---

### 🧩 Kaynak Kontrolü Kullanma

Kaynak kontrolü (ör. **Git** veya  **SVN** ) kullanıyorsanız, canlı kurulumunuzu deponuzun bir kopyası haline getirerek süreci kolaylaştırabilirsiniz.

Yükseltmeye hazır olduğunuzda, kaynak kontrol sisteminizden en son güncellemeleri alın.

**Git** kullanıyorsanız, her sürüm için bir **tag** oluşturmak ve dağıtım sırasında ilgili tag’i **checkout** etmek yaygın bir yaklaşımdır (bkz.  **Git Tagging** ).

Bu, dosyalarınızı güncellemeyi kolaylaştırır ancak yine de manuel olarak diğer adımları uygulamanız gerekir (bkz.  **Ortak Dağıtım Görevleri** ).

---

### ☁️ Platform as a Service (PaaS) Kullanma

Bir **PaaS (Platform as a Service)** kullanmak, Symfony uygulamanızı hızlı bir şekilde dağıtmanın harika bir yolu olabilir.

Birçok PaaS seçeneği vardır, ancak Symfony’ye özel entegrasyon sağlayan ve Symfony geliştirmesini destekleyen **Platform.sh** önerilir.

---

### 🛠️ Build Scriptleri ve Diğer Araçlar

Dağıtımı kolaylaştırmak için çeşitli araçlar mevcuttur. Bazıları Symfony’nin gereksinimlerine özel olarak hazırlanmıştır:

* **Deployer**

  Capistrano’nun PHP ile yeniden yazılmış bir sürümüdür ve Symfony için hazır tarifler içerir.
* **Ansistrano**

  YAML dosyaları aracılığıyla güçlü bir dağıtım yapılandırmanızı sağlayan bir **Ansible** rolüdür.
* **Magallanes**

  PHP ile yazılmış, Capistrano benzeri bir dağıtım aracıdır ve PHP geliştiricilerinin ihtiyaçlarına göre genişletilmesi daha kolay olabilir.
* **Fabric**

  Yerel veya uzak shell komutlarını çalıştırmak, dosya yüklemek/indirmek için temel bir işlem seti sağlayan Python tabanlı bir kütüphanedir.
* **Capistrano + Symfony plugin**

  Ruby ile yazılmış bir uzak sunucu otomasyon ve dağıtım aracıdır. Symfony plugin, Symfony’ye özgü görevleri kolaylaştırır ve yalnızca Capistrano 2 ile çalışan  **Capifony** ’den esinlenmiştir.

---

## 🔁 Ortak Dağıtım Görevleri

Kaynak kodunuzu dağıtmadan  **önce ve sonra** , gerçekleştirmeniz gereken bir dizi ortak işlem vardır:

### 🧾 A) Gereksinimleri Kontrol Etme

Symfony uygulamalarını çalıştırmak için bazı teknik gereksinimler vardır.

Geliştirme makinenizde bunları kontrol etmenin önerilen yolu **Symfony CLI** kullanmaktır.

Ancak üretim sunucusunda Symfony CLI’yı yüklemek istemeyebilirsiniz. Bu durumda, şu paketi uygulamanıza yükleyin:

```bash
composer require symfony/requirements-checker
```

Daha sonra denetleyicinin (checker) Composer scriptlerine dahil olduğundan emin olun:

```json
{
    "...": "...",
    "scripts": {
        "auto-scripts": {
            "vendor/bin/requirements-checker": "php-script",
            "...": "..."
        },
        "...": "..."
    }
}
```

---

### ⚙️ B) Ortam Değişkenlerini Yapılandırma

Çoğu Symfony uygulaması yapılandırmasını **ortam değişkenlerinden (environment variables)** okur.

Yerel geliştirme sırasında bunları genellikle **.env** dosyalarında saklarsınız.

Üretim ortamında iki seçeneğiniz vardır:

1. **Gerçek ortam değişkenleri oluşturmak:**

   Bu, kurulumunuza bağlıdır – komut satırında, Nginx yapılandırmasında veya barındırma hizmetinizin sağladığı yöntemlerle yapılabilir.
2. **.env.prod.local** dosyası oluşturmak:**

   Bu dosya, üretim ortamınıza özel değerleri içerir.

Her iki yöntemin de belirgin bir üstünlüğü yoktur: barındırma ortamınıza en uygun olanı kullanın.

---

Uygulamanızın her istekte **.env.*** dosyalarını işlemesini istemeyebilirsiniz.

Tüm diğer yapılandırma dosyalarının yerine geçecek **optimize edilmiş bir .env.local.php** dosyası oluşturabilirsiniz:

```bash
composer dump-env prod
```

Oluşturulan dosya, .env’de saklanan tüm yapılandırmaları içerir.

Yalnızca ortam değişkenlerine güvenmek istiyorsanız, değer içermeyen bir sürüm oluşturabilirsiniz:

```bash
composer dump-env prod --empty
```

Üretim sunucusunda **Composer** yüklü değilse, bunun yerine Symfony komutunu kullanın:

```bash
php bin/console dotenv:dump
```


### 🧩 C) Vendor’ları Yükleme/Güncelleme

Vendor’larınızı, kaynak kodunuzu aktarmadan **önce** (yani `vendor/` dizinini güncelleyip onu kaynak kodunuzla birlikte aktarmak) veya **aktardıktan sonra** sunucuda güncelleyebilirsiniz.

Her iki durumda da, vendor’ları normalde yaptığınız gibi güncelleyin:

```bash
composer install --no-dev --optimize-autoloader
```

`--optimize-autoloader` bayrağı, Composer’ın autoloader performansını bir “class map” oluşturarak önemli ölçüde artırır.

`--no-dev` bayrağı ise geliştirme paketlerinin üretim ortamında yüklenmemesini sağlar.

Bu adım sırasında **“class not found”** hatası alırsanız, komutu çalıştırmadan önce şu değişkeni ayarlamanız gerekebilir:

```bash
export APP_ENV=prod
```

(**Symfony Flex** kullanmıyorsanız bunun yerine `export SYMFONY_ENV=prod` komutunu kullanın.)

Bu, `post-install-cmd` scriptlerinin **prod** ortamında çalışmasını sağlar.

---

### 🧹 D) Symfony Cache’inizi Temizleme

Symfony cache’inizi temizlediğinizden ve önceden ısıttığınızdan emin olun:

```bash
APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear
```

---

### ⚙️ E) Diğer İşlemler

Kurulumunuza bağlı olarak yapmanız gereken başka birçok işlem olabilir:

* Veritabanı **migrasyonlarını** çalıştırma
* **APCu cache** ’inizi temizleme
* **CRON job** ’ları ekleme veya düzenleme
* **Worker** ’ları yeniden başlatma
* **Webpack Encore** ile varlıkları (assets) derleme ve küçültme
* **AssetMapper** bileşenini kullanıyorsanız varlıkları derleme
* Varlıkları bir  **CDN** ’e gönderme
* **Apache** web sunucusu kullanan paylaşımlı hosting ortamlarında `symfony/apache-pack` paketini yükleme
* vb.

---

## 🔄 Uygulama Yaşam Döngüsü: Sürekli Entegrasyon, QA vb.

Bu makale dağıtımın teknik yönlerini kapsasa da, geliştirmeden üretime kodu taşımanın tam yaşam döngüsü genellikle daha fazla adımdan oluşur:

 **Staging** ,  **QA (Kalite Güvencesi)** , **testlerin çalıştırılması** gibi süreçler.

 **Staging** ,  **test** ,  **QA** ,  **sürekli entegrasyon** , **veritabanı migrasyonları** ve **başarısızlık durumunda geri alma (rollback)** yeteneklerinin kullanımı şiddetle tavsiye edilir.

Basit veya karmaşık araçlar kullanılabilir; dağıtımı ortamınıza göre kolay veya gelişmiş hale getirebilirsiniz.

Uygulamanızı dağıtmanın aynı zamanda şu işlemleri de içerdiğini unutmayın:

* Bağımlılıkları (genellikle Composer aracılığıyla) güncellemek,
* Veritabanını migrate etmek,
* Cache’i temizlemek,
* Gerekirse varlıkları (assets) bir  **CDN** ’e göndermek.

(Bkz.  **Ortak Dağıtım Görevleri** )

---

## 🧭 Sorun Giderme

### composer.json Dosyası Kullanılmayan Dağıtımlar

Proje kök dizini (`kernel.project_dir` parametresi ve `getProjectDir()` metodu ile belirlenen değer), Symfony tarafından otomatik olarak ana `composer.json` dosyasının bulunduğu dizin olarak hesaplanır.

**composer.json** dosyasını kullanmayan dağıtımlarda, `getProjectDir()` metodunu manuel olarak geçersiz kılmanız (override etmeniz) gerekir.

Bu işlemin nasıl yapılacağı ilgili bölümde açıklanmıştır.

---

## 📚 Daha Fazla Bilgi

🔗 **Nasıl yapılır:**

[Symfony’yi bir Load Balancer veya Reverse Proxy arkasında çalışacak şekilde yapılandırma](https://symfony.com/doc/current/deployment/proxies.html)
