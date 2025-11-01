## Symfony’de İlk Sayfanızı Oluşturun

Symfony’de yeni bir sayfa oluşturmak — ister bir **HTML sayfası** ister **JSON endpoint** olsun — iki basit adımdan oluşur:

1. **Controller (denetleyici) oluşturun:**

   Bu, sayfayı oluşturan PHP fonksiyonudur.

   Gelen **HTTP isteğini** (Request) alır, işler ve bir **Response** (yanıt) nesnesi döndürür.

   Bu yanıt bir  **HTML** ,  **JSON** , hatta bir **görsel veya PDF** bile olabilir.
2. **Route (yol) oluşturun:**

   Route, sayfanızın **URL’sidir** (örneğin `/about`) ve belirli bir  **controller** ’a yönlendirme yapar.

> 🎥 Video ile öğrenmeyi tercih eder misiniz?
>
> [Cosmic Coding with Symfony](https://symfonycasts.com/) video serisine göz atabilirsiniz.

Symfony, **HTTP Request–Response yaşam döngüsünü** temel alır.

Bu konuyla ilgili daha fazla bilgi için [Symfony and HTTP Fundamentals](https://symfony.com/doc/current/introduction/http_fundamentals.html) sayfasını inceleyebilirsiniz.

---

## Bir Sayfa Oluşturma: Route ve Controller

Devam etmeden önce, **kurulum adımlarını tamamladığınızdan** ve yeni Symfony uygulamanıza **tarayıcıdan erişebildiğinizden** emin olun.

Örneğin, `/lucky/number` adresine gidildiğinde rastgele bir sayı üreten bir sayfa yapmak istediğinizi varsayalım.

Bunun için bir **Controller sınıfı** ve içinde bir **number() metodu** oluşturacağız:

```php
<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class LuckyController
{
    public function number(): Response
    {
        $number = random_int(0, 100);

        return new Response(
            '<html><body>Lucky number: ' . $number . '</body></html>'
        );
    }
}
```

---

### Controller’ı Bir URL’ye Bağlama (Route Oluşturma)

Şimdi bu controller metodunu bir URL’ye bağlamamız gerekiyor — örneğin `/lucky/number`.

Böylece kullanıcı bu adrese girdiğinde `number()` metodu çalışacak.

Symfony’de bu ilişkilendirme, **#[Route] attribute** (PHP’deki açıklama niteliği) kullanılarak yapılır:

```php
<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LuckyController
{
    #[Route('/lucky/number')]
    public function number(): Response
    {
        $number = random_int(0, 100);

        return new Response(
            '<html><body>Lucky number: ' . $number . '</body></html>'
        );
    }
}
```

---

### Test Etme

Symfony’nin yerel web sunucusunu kullanıyorsanız, şu adrese giderek test edebilirsiniz:

👉 [http://localhost:8000/lucky/number](http://localhost:8000/lucky/number)

Tarayıcıda rastgele bir sayı görüyorsanız, **tebrikler! 🎉**

Ancak loto bileti almadan önce, bunun nasıl çalıştığını anlamaya biraz daha derin bakalım:

---

### Nasıl Çalışır?

Symfony’de bir sayfa oluşturmak **iki temel adıma dayanır:**

1. **Controller ve Metot Oluşturma:**

   Sayfanızı burada inşa edersiniz ve en sonunda bir `Response` nesnesi döndürürsünüz.

   (Controller’lar hakkında daha fazla bilgi için ayrı “Controllers” bölümüne göz atabilirsiniz; JSON döndürmeyi de orada öğreneceksiniz.)
2. **Route Tanımlama:**

   `config/routes.yaml` dosyasında, sayfanızın **URL’sini (path)** ve hangi controller’ın çağrılacağını tanımlarsınız.

   Routing hakkında daha fazla bilgi için “Routing” bölümünde **değişken URL’lerin** nasıl tanımlandığını da öğreneceksiniz.

> 💡 Symfony, route tanımlarını doğrudan controller içinde attribute olarak yazmanızı **önerir** — böylece kod ve konfigürasyon aynı yerde olur.
>
> Ancak isterseniz YAML, XML veya PHP dosyaları kullanarak da route tanımlayabilirsiniz.

## `bin/console` Komutu

Symfony projeniz, içinde güçlü bir hata ayıklama (debugging) aracıyla birlikte gelir: **`bin/console`** komutu.

Bu komutu çalıştırarak başlayın:

```bash
php bin/console
```

Bu komut, size aşağıdaki gibi bir dizi yararlı araç sunar:

* Hata ayıklama bilgilerini görüntüleme,
* Kod üretme,
* Veritabanı migrasyonları oluşturma,
* ve daha birçok özellik.

Yeni paketler yükledikçe, bu listeye daha fazla komut eklenecektir.

---

### Tüm Route’ları Görüntüleme

Sistemdeki tüm rotaları görmek için şu komutu çalıştırın:

```bash
php bin/console debug:router
```

Bu çıktıyı görmelisiniz:

```
----------------  -------  -------  -----  --------------
Name              Method   Scheme   Host   Path
----------------  -------  -------  -----  --------------
app_lucky_number  ANY      ANY      ANY    /lucky/number
----------------  -------  -------  -----  --------------
```

Ayrıca `app_lucky_number` dışında bazı **debug rotaları** da listede yer alır — bunları bir sonraki bölümde öğreneceksiniz.

> 💡 Symfony geliştikçe, yeni komutlar da eklenir. Bunları öğrenmek, projenizi yönetmeyi kolaylaştırır.

---

### Komut Satırı Otomatik Tamamlama (Console Completion)

Eğer terminaliniz destekliyorsa, **otomatik tamamlama (tab completion)** özelliğini etkinleştirebilirsiniz.

Bu sayede `bin/console` kullanırken komut ve argümanlar otomatik tamamlanır.

Nasıl etkinleştireceğinizi öğrenmek için [Symfony Console dökümantasyonuna](https://symfony.com/doc/current/console.html#completion) göz atabilirsiniz.

---

## Web Debug Toolbar: Geliştirici Dostu Bir Araç

Symfony’nin en güçlü özelliklerinden biri: **Web Debug Toolbar** 🎯

Sayfanızın alt kısmında, geliştirme sırasında birçok hata ayıklama bilgisini gösteren bir araç çubuğudur.

Bu araç, `symfony/profiler-pack` paketiyle **varsayılan olarak yüklüdür.**

Sayfanızın alt kısmında koyu renkli bir çubuk görürsünüz.

Üzerine gelin, tıklayın — yönlendirme, performans, loglar ve daha fazlası hakkında bilgi alın.

---

## Şablon (Template) Oluşturma

Controller’dan **HTML** döndürüyorsanız, muhtemelen bir şablon dosyası kullanmak isteyeceksiniz.

Symfony, bunun için güçlü ve sade bir şablon motoru olan **Twig** ile birlikte gelir.

Twig paketini yükleyin:

```bash
composer require twig
```

---

### Controller’ı Güncelleyin

Controller sınıfınızın Symfony’nin `AbstractController` sınıfını genişlettiğinden emin olun:

```php
// src/Controller/LuckyController.php
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LuckyController extends AbstractController
{
    // ...
}
```

---

### Twig Şablonu Render Etme

Artık `render()` metodunu kullanarak Twig şablonunuzu döndürebilirsiniz:

```php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class LuckyController extends AbstractController
{
    #[Route('/lucky/number')]
    public function number(): Response
    {
        $number = random_int(0, 100);

        return $this->render('lucky/number.html.twig', [
            'number' => $number,
        ]);
    }
}
```

---

### Twig Dosyasını Oluşturma

Twig şablon dosyaları, `templates/` klasöründe bulunur.

Bu klasör Twig kurulduğunda otomatik olarak oluşturulur.

Yeni bir dosya oluşturun:

📁 `templates/lucky/number.html.twig`

İçeriği şu şekilde olsun:

```twig
{# templates/lucky/number.html.twig #}
<h1>Your lucky number is {{ number }}</h1>
```

`{{ number }}` sözdizimi Twig’de değişkenleri yazdırmak için kullanılır.

Tarayıcınızı yenileyin:

👉 [http://localhost:8000/lucky/number](http://localhost:8000/lucky/number)

Yeni şanslı sayınızı göreceksiniz 🎉

> Web Debug Toolbar’ı göremiyorsanız, bunun nedeni şablonda `<body>` etiketinin bulunmamasıdır.
>
> Kendi `<body>` etiketinizi ekleyebilir veya `base.html.twig` dosyasını genişletebilirsiniz.

Twig hakkında daha fazla bilgi için “Templates” bölümünde şunları öğreneceksiniz:

* Döngüler (loops)
* Diğer şablonları render etme
* Twig’in güçlü şablon kalıtımı sistemi

---

## Proje Yapısına Genel Bakış

Harika! Artık projenizin en önemli dizinlerinde çalıştınız. 👇

| Dizin                | Açıklama                                              |
| -------------------- | ------------------------------------------------------- |
| **config/**    | Yapılandırma dosyaları (routes, services, packages). |
| **src/**       | PHP kodlarınız burada yer alır.                      |
| **templates/** | Twig şablonları burada bulunur.                       |

Genellikle en çok bu üç dizinle çalışırsınız.

Peki diğer dizinler ne işe yarar?

| Dizin             | Açıklama                                                      |
| ----------------- | --------------------------------------------------------------- |
| **bin/**    | `bin/console`ve diğer çalıştırılabilir dosyalar burada. |
| **var/**    | Önbellek (`var/cache/`) ve log (`var/log/`) dosyaları.    |
| **vendor/** | Composer üzerinden yüklenen üçüncü parti kütüphaneler.  |
| **public/** | Web kök dizini — herkese açık dosyalar burada bulunur.      |

Yeni paketler yükledikçe Symfony gerekli dizinleri otomatik olarak oluşturur.

---

## Sıradaki Adımlar 🚀

Tebrikler! Symfony’nin temellerini öğrenmeye başladınız.

Artık güçlü, hızlı ve bakımı kolay uygulamalar geliştirmek için harika bir konumdasınız.

Temelleri tamamlamak için şu makaleleri okuyun:

* [Routing (Yönlendirme)](https://symfony.com/doc/current/routing.html)
* [Controller (Denetleyici)](https://symfony.com/doc/current/controller.html)
* [Creating and Using Templates (Şablon Oluşturma)](https://symfony.com/doc/current/templates.html)
* [Front-end Tools (CSS &amp; JavaScript Yönetimi)](https://symfony.com/doc/current/frontend.html)
* [Configuring Symfony (Yapılandırma)](https://symfony.com/doc/current/configuration.html)

Daha sonra şu konulara geçebilirsiniz:

* Servis Container (Bağımlılık yönetimi)
* Form Sistemi
* Doctrine (veritabanı sorguları için)
* ve daha fazlası!

> Symfony, modern PHP dünyasında “güzel, işlevsel ve sürdürülebilir” uygulamalar geliştirmenin en güçlü yollarından biridir. 💪

---

## Daha Derine İnmek İsteyenler İçin

* [Symfony vs. Flat PHP](https://symfony.com/doc/current/introduction/from_flat_php_to_symfony.html)
* [Symfony and HTTP Fundamentals](https://symfony.com/doc/current/introduction/http_fundamentals.html)

---
