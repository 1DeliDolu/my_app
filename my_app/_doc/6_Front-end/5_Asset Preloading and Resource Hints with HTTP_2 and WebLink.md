## ⚡ Asset Preloading ve WebLink ile HTTP/2 Kaynak İpuçları

Symfony, **WebLink** bileşeni aracılığıyla **Link HTTP başlıklarını** yönetmek için yerleşik destek sağlar.

Bu başlıklar, **HTTP/2** ve modern tarayıcıların **preload (ön yükleme)** özelliklerinden faydalanarak uygulama performansını artırmanın anahtarıdır.

---

### 🌐 HTTP/2 ve Resource Hints Nedir?

 **Link başlıkları** , tarayıcıya bazı kaynakları (örneğin CSS veya JavaScript dosyaları) **önceden indirmesi veya yüklemesi gerektiğini** bildirir.

Bu teknik, özellikle **HTTP/2 Server Push** ve **W3C Resource Hints** spesifikasyonlarında kullanılır.

Ek olarak, WebLink bileşeni **HTTP/1.x** için de bazı optimizasyonlar sunar:

* Tarayıcıdan başka bir sayfanın arka planda getirilmesini isteme
* Erken DNS çözümleme, TCP el sıkışması veya TLS bağlantısı yapma

> ⚠️ Tüm bu HTTP/2 özellikleri yalnızca **HTTPS bağlantılarda** çalışır — yerel ortamda bile geçerlidir.
>
> Apache, nginx, Caddy gibi sunucular bunu destekler.
>
> Dilersen Symfony topluluğundan **Kévin Dunglas** tarafından hazırlanan Docker tabanlı Symfony çalıştırıcısını da kullanabilirsin.

---

### ⚙️ Kurulum

Symfony Flex kullanan projelerde, WebLink bileşenini kurmak için:

```bash
composer require symfony/web-link
```

---

### 🚀 Asset Preloading (Varlıkları Önceden Yükleme)

#### 🧩 Geleneksel Senaryo

Normalde bir sayfa şu şekilde görünür:

```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>My Application</title>
    <link rel="stylesheet" href="/app.css">
</head>
<body>
    <main role="main" class="container">
        <!-- ... -->
    </main>
</body>
</html>
```

Bu durumda tarayıcı önce HTML dosyasını, ardından CSS dosyasını **ayrı isteklerle** indirir.

HTTP/2 kullanarak CSS dosyasını **HTML ile birlikte** göndermek mümkündür.

---

#### 🧠 WebLink ile Preload Kullanımı

`preload()` Twig fonksiyonu ile bu işlemi yapabiliriz.

Burada `as` niteliği zorunludur; tarayıcı bu bilgiyi indirme önceliğini belirlemek için kullanır.

```twig
<head>
    {# Her varlık için iki <link> etiketi gerekir #}
    <link rel="preload" href="{{ preload('/app.css', {as: 'style'}) }}" as="style">
    <link rel="stylesheet" href="/app.css">
</head>
```

💡 Bu sayede tarayıcı sadece HTML’yi isterken, sunucu CSS dosyasını da  **önceden göndermiş olur** .

Bu da  **sayfa yüklenme hızını gözle görülür biçimde artırır** .

---

#### 🧱 AssetMapper ile Kullanım

Eğer **AssetMapper** bileşenini kullanıyorsan (`importmap('app')` gibi),

manüel olarak `<link rel="preload">` etiketi eklemene gerek yoktur.

`importmap()` Twig fonksiyonu, WebLink etkinse gerekli **Link HTTP header’ını** kendisi ekler.

Ancak istersen elle de belirtebilirsin:

```twig
<head>
    <link rel="preload" href="{{ preload(asset('build/app.css')) }}" as="style">
</head>
```

---

#### 🧩 Priority Hints (Öncelik İpucu)

Kaynağın indirilme önceliğini belirtmek için `importance` niteliğini kullanabilirsin:

```twig
<head>
    <link rel="preload" href="{{ preload('/app.css', {as: 'style', importance: 'low'}) }}" as="style">
</head>
```

---

### ⚙️ Nasıl Çalışır?

`preload()` fonksiyonu kullanıldığında, Symfony yanıtına aşağıdaki gibi bir HTTP başlığı ekler:

```
Link: </app.css>; rel="preload"; as="style"
```

HTTP/2 sunucusu bu başlığı algıladığında,  **ilgili dosyayı otomatik olarak client’a gönderir (push)** .

> ☁️ Bu özellik,  **Cloudflare** ,  **Fastly** , **Akamai** gibi CDN sağlayıcıları tarafından da desteklenmektedir.
>
> Böylece canlı ortamda da uygulamanın hızını artırmak mümkündür.

---

#### 🔧 Push’u Engelleyip Yalnızca Preload Yapmak

Dosyanın HTTP/2 ile push edilmesini istemiyorsan ama tarayıcının yine de önceden yüklemesini istiyorsan, `nopush` seçeneğini kullan:

```twig
<head>
    <link rel="preload" href="{{ preload('/app.css', {as: 'style', nopush: true}) }}" as="style">
</head>
```

---

### 🌍 Resource Hints (Kaynak İpuçları)

 **Resource Hints** , tarayıcıya **hangi kaynaklara öncelik vermesi gerektiğini** söyler.

WebLink bileşeni aşağıdaki Twig fonksiyonlarını sağlar:

| Twig Fonksiyonu    | Açıklama                                                                                          |
| ------------------ | --------------------------------------------------------------------------------------------------- |
| `dns_prefetch()` | Tarayıcının erken DNS çözümlemesi yapmasını sağlar.                                        |
| `preconnect()`   | DNS çözümlemesi, TCP el sıkışması ve TLS bağlantısını erkenden başlatır.               |
| `prefetch()`     | Bir sonraki sayfada kullanılabilecek kaynakları önceden indirir.                                 |
| `prerender()`    | (Eski - Speculation Rules API ile değiştirildi) Bir sonraki sayfayı önceden işleyip hazırlar. |

---

#### 🧩 Örnek Kullanım

```twig
<head>
    <link rel="alternate" href="{{ link('/index.jsonld', 'alternate') }}">
    <link rel="preload" href="{{ preload('/app.css', {as: 'style', nopush: true}) }}" as="style">
</head>
```

Bu durumda sunucu, aşağıdaki HTTP başlığını ekleyecektir:

```
Link: </index.jsonld>; rel="alternate", </app.css>; rel="preload"; nopush
```

---

### 🧰 Controller’dan Link Eklemek

Twig dışında, doğrudan PHP kodunda da Link başlıkları eklenebilir:

```php
// src/Controller/BlogController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\WebLink\GenericLinkProvider;
use Symfony\Component\WebLink\Link;

class BlogController extends AbstractController
{
    public function index(Request $request): Response
    {
        // AbstractController içinde yerleşik addLink() fonksiyonu ile
        $this->addLink($request, (new Link('preload', '/app.css'))->withAttribute('as', 'style'));

        // Alternatif kullanım
        $linkProvider = $request->attributes->get('_links', new GenericLinkProvider());
        $request->attributes->set('_links', $linkProvider->withLink(
            (new Link('preload', '/app.css'))->withAttribute('as', 'style')
        ));

        return $this->render('blog/index.html.twig');
    }
}
```

> 💡 `Link` sınıfında `REL_PRELOAD`, `REL_PRECONNECT` gibi tüm ilişki türleri **sabit (constant)** olarak tanımlıdır.
>
> Örneğin: `Link::REL_PRELOAD`, `Link::REL_PRECONNECT`

---

📄 **Lisans:**

Bu içerik ve örnek kodlar, [Creative Commons BY-SA 3.0](https://creativecommons.org/licenses/by-sa/3.0/) lisansı altında sunulmuştur.

---
