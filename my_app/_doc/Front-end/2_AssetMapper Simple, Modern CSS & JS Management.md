### 🪶 AssetMapper: Basit ve Modern CSS & JS Yönetimi

**AssetMapper** bileşeni, herhangi bir derleyici (bundler) karmaşıklığı olmadan modern **JavaScript** ve **CSS** yazmanızı sağlar.

Tarayıcılar artık **import** ifadesi ve **ES6 sınıfları** gibi birçok modern JS özelliğini destekliyor. Ayrıca **HTTP/2** protokolü sayesinde, varlıkları tek dosyada birleştirerek HTTP bağlantılarını azaltma ihtiyacı da büyük ölçüde ortadan kalkmıştır.

Bu bileşen, dosyalarınızı tarayıcıya doğrudan sunmanıza yardımcı olan hafif bir katmandır.

---

## ⚙️ Temel Özellikler

**AssetMapper** iki ana özellikle çalışır:

1. **Varlıkların (Assets) Haritalanması ve Sürümlenmesi (Mapping & Versioning)**

   `assets/` klasöründeki tüm dosyalar otomatik olarak herkese açık hale getirilir ve sürümlenir.

   Örneğin, `assets/images/product.jpg` dosyasını bir Twig şablonunda şöyle çağırabilirsiniz:

   ```twig
   {{ asset('images/product.jpg') }}
   ```

   Tarayıcıya gönderilen URL, sürüm bilgisini içerecektir:

   `/assets/images/product-3c16d92m.jpg`
2. **Importmaps**

   Tarayıcıların doğal olarak desteklediği bir özelliktir.

   Derleme sistemine gerek kalmadan, şu tür modern import ifadelerini kullanmayı kolaylaştırır:

   ```js
   import { Modal } from 'bootstrap';
   ```

   Tüm tarayıcılarda çalışır (bir **shim/polyfill** sayesinde) ve HTML standardının bir parçasıdır.

---

## 🧩 Kurulum

AssetMapper bileşenini yüklemek için şu komutu çalıştırın:

```bash
composer require symfony/asset-mapper symfony/asset symfony/twig-pack
```

Bu komut:

* `symfony/asset-mapper` bileşenini,
* `Asset Component`’i,
* ve `Twig`’i projenize ekler.

### 🔹 Symfony Flex Kullanıyorsanız

Her şey otomatik olarak yapılandırılır. Flex, aşağıdaki dosyaları oluşturur:

| Dosya                                 | Açıklama                                    |
| ------------------------------------- | --------------------------------------------- |
| `assets/app.js`                     | Ana JavaScript dosyanız                      |
| `assets/styles/app.css`             | Ana CSS dosyanız                             |
| `config/packages/asset_mapper.yaml` | Varlık yollarını tanımladığınız dosya |
| `importmap.php`                     | Importmap yapılandırma dosyası             |

Ayrıca, `templates/base.html.twig` dosyasını aşağıdaki şekilde günceller:

```twig
{% block javascripts %}
    {% block importmap %}{{ importmap('app') }}{% endblock %}
{% endblock %}
```

### 🔹 Flex Kullanmıyorsanız

Bu dosyaları ve ayarları manuel olarak oluşturmanız gerekir.

En güncel örnekler için [AssetMapper Flex tarifi](https://github.com/symfony/recipes-contrib/tree/main/symfony/asset-mapper) içeriğine bakabilirsiniz.

---

## 🗺️ Varlıkları Haritalama ve Referans Verme

AssetMapper, herkese açık hale getirmek istediğiniz dizinleri tanımlayarak çalışır.

Bu varlıklar **otomatik olarak sürümlenir** ve Twig şablonlarında kolayca çağrılabilir.

Varsayılan olarak, `asset_mapper.yaml` dosyası **assets/** dizinini haritalar.

Örneğin `assets/images/duck.png` dosyası oluşturduysanız, Twig içinde şöyle referans verebilirsiniz:

```twig
<img src="{{ asset('images/duck.png') }}">
```

Burada `images/duck.png`, haritalanan dizine (`assets/`) göre **mantıksal yol** (logical path) anlamına gelir.

Sayfanızın HTML çıktısında URL şu şekilde görünür:

```
/assets/images/duck-3c16d92m.png
```

Dosyayı değiştirdiğinizde, URL’deki sürüm kısmı otomatik olarak güncellenir.

---

## 🌍 Geliştirme (dev) ve Üretim (prod) Ortamlarında Varlık Sunumu

* **dev ortamında:**

  `/assets/images/duck-3c16d92m.png` URL’si Symfony uygulamanız tarafından **dinamik** olarak sunulur.
* **prod ortamında:**

  Yayına almadan önce şu komutu çalıştırmalısınız:

  ```bash
  php bin/console asset-map:compile
  ```

  Bu komut, haritalanmış tüm dosyaları fiziksel olarak `public/assets/` dizinine kopyalar.

  Böylece bu varlıklar doğrudan web sunucunuz tarafından sunulabilir.

📦 Daha fazla bilgi için [Deployment belgelerine](https://symfony.com/doc/current/deployment.html) bakabilirsiniz.

---

## 🔁 Geliştirme Ortamında Yeniden Yükleme Sorunu

Eğer `asset-map:compile` komutunu **geliştirme ortamında** çalıştırırsanız, sayfayı yenilediğinizde değişiklikler görünmeyebilir.

Bunu düzeltmek için:

```bash
rm -rf public/assets/*
```

Bu dizini temizlemek, Symfony uygulamanızın varlıkları yeniden dinamik olarak sunmasına izin verir.

---

## ☁️ Farklı Bir Yere Derlenmiş Varlıkları Kopyalamak (ör. S3)

Derlenmiş varlıkları başka bir yere (örneğin AWS S3) yüklemeniz gerekiyorsa, özel bir servis oluşturabilirsiniz.

Bu servis `Symfony\Component\AssetMapper\Path\PublicAssetsFilesystemInterface` arayüzünü uygulamalıdır.

Daha sonra bu servisin kimliğini (ya da alias’ını) şu şekilde değiştirin:

```
asset_mapper.local_public_assets_filesystem
```

Bu sayede Symfony, yerleşik (built-in) sistemi sizin özel servetinizle değiştirir.

---

✅ **Özetle:**

AssetMapper, modern web standartlarını kullanarak **basit, hızlı ve bağımsız** bir varlık yönetimi sunar — hiçbir Node.js veya karmaşık derleme adımı olmadan.


### 🧭 Hata Ayıklama: Haritalanan (Mapped) Tüm Varlıkları Görüntüleme

Uygulamanızda **AssetMapper** tarafından haritalanmış tüm varlıkları görmek için aşağıdaki komutu çalıştırabilirsiniz:

```bash
php bin/console debug:asset-map
```

Bu komut, tüm haritalanmış yolları ve bunların içerdiği varlıkları listeler:

```
AssetMapper Paths
------------------

--------- ------------------
 Path      Namespace prefix
--------- ------------------
assets

Mapped Assets
-------------

------------------ ----------------------------------------------------
 Logical Path       Filesystem Path
------------------ ----------------------------------------------------
 app.js             assets/app.js
 styles/app.css     assets/styles/app.css
 images/duck.png    assets/images/duck.png
```

 **Logical Path (Mantıksal Yol)** , bir şablonda varlığa referans verirken kullanmanız gereken yoldur.

---

## 🎛️ `debug:asset-map` Komutunun Filtreleme Seçenekleri

Sonuçları daraltmak için aşağıdaki seçenekleri kullanabilirsiniz:

```bash
php bin/console debug:asset-map bootstrap.js
php bin/console debug:asset-map style/
php bin/console debug:asset-map --ext=css
php bin/console debug:asset-map --vendor
php bin/console debug:asset-map --no-vendor
php bin/console debug:asset-map bold --no-vendor --ext=woff2
```

> 🆕 **Symfony 7.2** sürümüyle birlikte bu filtreleme seçenekleri eklenmiştir.

---

## 🧩 Importmaps ve Modern JavaScript Yazımı

Modern tarayıcılar, artık **JavaScript import ifadesini** ve **ES6 özelliklerini (ör. class)** doğal olarak destekler.

Bu yüzden aşağıdaki kodlar **hiçbir derleme adımı olmadan** çalışır:

```js
// assets/app.js
import Duck from './duck.js';

const duck = new Duck('Waddles');
duck.quack();

// assets/duck.js
export default class {
    constructor(name) {
        this.name = name;
    }
    quack() {
        console.log(`${this.name} says: Quack!`);
    }
}
```

Twig dosyanızda kullandığınız

```twig
{{ importmap('app') }}
```

ifadesi sayesinde `assets/app.js` dosyası tarayıcı tarafından yüklenir ve çalıştırılır.

> ⚠️ **Dikkat:** Göreceli dosyaları içe aktarırken `.js` uzantısını eklemeyi unutmayın.
>
> Node.js’den farklı olarak, tarayıcı ortamında bu uzantı  **zorunludur** .

---

## 📦 Üçüncü Parti JavaScript Paketlerini İçe Aktarma

Diyelim ki `bootstrap` gibi bir npm paketini kullanmak istiyorsunuz.

Bunu teknik olarak doğrudan URL üzerinden çağırabilirsiniz:

```js
import { Alert } from 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/+esm';
```

Ama bu şekilde URL’lerle uğraşmak hem karmaşık hem hataya açık.

Bunun yerine paketi  **importmap** ’e ekleyebilirsiniz:

```bash
php bin/console importmap:require bootstrap
```

> 💡 Sadece denemek istiyorsanız, `--dry-run` seçeneğini kullanabilirsiniz:
>
> ```bash
> php bin/console importmap:require bootstrap --dry-run
> ```
>
> (Bu özellik **Symfony 7.3** ile eklenmiştir.)

Bu komut, `importmap.php` dosyanıza **bootstrap** paketini ekler:

```php
// importmap.php
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    'bootstrap' => [
        'version' => '5.3.0',
    ],
];
```

Bootstrap gibi bazı paketlerin bağımlılıkları olabilir (ör. `@popperjs/core`).

`importmap:require` komutu, ana paketle birlikte bu bağımlılıkları da otomatik ekler.

Eğer paket bir ana CSS dosyası içeriyorsa, o da importmap’e dahil edilir

(Bkz.  **Üçüncü Parti CSS Yönetimi** ).

---

## 🧱 Olası Hatalar ve Çözümleri

* **404 hatası alıyorsanız:**

  Paket, `jsDelivr` CDN’de doğru yapılandırılmamış olabilir.

  `package.json` içinde `main` veya `module` alanları eksikse bu hata oluşabilir.

  Bu durumda, paket geliştiricisine sorunu bildirmeniz önerilir.
* **Ağ bağlantısı hatası ("Connection was reset") alıyorsanız:**

  Bu durum bir **proxy** veya **firewall** engeli olabilir.

  Aşağıdaki yapılandırmayla geçici olarak bir proxy tanımlayabilirsiniz:

  ```yaml
  # config/packages/framework.yaml
  framework:
      http_client:
          default_options:
              proxy: '185.250.180.238:8080'
              extra:
                  curl:
                      '61': true  # CURLOPT_HTTPPROXYTUNNEL değeri
  ```

---

## 🚀 Paketi Kullanmak

Artık bootstrap paketini doğrudan içe aktarabilirsiniz:

```js
import { Alert } from 'bootstrap';
// ...
```

Tüm importmap paketleri `assets/vendor/` dizinine indirilir.

Bu dizin `.gitignore`’da zaten yok sayılmıştır (Flex tarafından otomatik eklenir).

Eksik dosyalar varsa, diğer makinelerde şu komutu çalıştırarak indirebilirsiniz:

```bash
php bin/console importmap:install
```

Paketlerinizi güncel sürümlerine yükseltmek için:

```bash
php bin/console importmap:outdated
php bin/console importmap:update
```

Belirli paketleri güncellemek isterseniz:

```bash
php bin/console importmap:update bootstrap lodash
php bin/console importmap:outdated bootstrap lodash
```

---

## 🧹 JavaScript Paketlerini Kaldırma

Bir paketi importmap’ten kaldırmak için:

```bash
php bin/console importmap:remove lodash
```

Bu komut:

* `importmap.php` dosyasını günceller
* ilgili bağımlılıkları kaldırır

Sonrasında şu komutu da çalıştırmanız önerilir:

```bash
php bin/console importmap:install
```

> ❗ Not: Paketi kaldırmak, JavaScript dosyalarınızdan yapılan `import` ifadelerini otomatik silmez.
>
> Kodunuzda bu referansları manuel olarak kaldırmalısınız.

---

## ⚙️ Importmap Nasıl Çalışır?

`importmap.php` dosyası sayesinde, JavaScript dosyalarınızda doğrudan paket adlarını kullanabilirsiniz.

Twig’deki `{{ importmap() }}` fonksiyonu, tarayıcıya aşağıdaki gibi bir importmap çıktısı gönderir:

```html
<script type="importmap">{
    "imports": {
        "app": "/assets/app-4e986c1a.js",
        "/assets/duck.js": "/assets/duck-1b7a64b3.js",
        "bootstrap": "/assets/vendor/bootstrap/bootstrap.index-f093544d.js"
    }
}</script>
```

Bu sayede tarayıcı, `import 'bootstrap'` ifadesini gördüğünde hangi dosyayı yükleyeceğini bilir.

> 🦆 `duck.js` gibi dosyalar doğrudan `importmap.php` içinde tanımlı değildir.
>
> Ancak AssetMapper, bu tür **göreceli importları** tespit eder ve otomatik olarak sürümlenmiş yolları haritalar.
>
> Bu yüzden `import './duck.js'` ifadesi  **sihir gibi çalışır** .

Ayrıca `importmap()` fonksiyonu, **eski tarayıcıların** importmap’leri anlaması için bir **ES module shim (polyfill)** da ekler.

---

## 🎯 “app” Entrypoint ve Preloading

 **Entrypoint** , tarayıcı tarafından yüklenen ana JavaScript dosyanızdır.

Varsayılan olarak uygulamanızda bir adet entrypoint bulunur:

```php
// importmap.php
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
];
```

Twig’deki `{{ importmap('app') }}` fonksiyonu sadece importmap’i değil, şu satırı da ekler:

```html
<script type="module">import 'app';</script>
```

Bu, tarayıcıya `assets/app.js` dosyasını yüklemesini ve çalıştırmasını söyler.

Ayrıca **performans optimizasyonu** için şu preload etiketleri de eklenir:

```html
<link rel="modulepreload" href="/assets/app-4e986c1a.js">
<link rel="modulepreload" href="/assets/duck-1b7a64b3.js">
```

Bu sayede tarayıcı, gerekli modülleri önceden yükleyerek sayfa açılışını hızlandırır.

Daha fazla bilgi için bkz. **Performance: Add Preloading** bölümü.


### 🎨 Üçüncü Parti Paketlerden Belirli Dosyaları İçe Aktarma

Bazen bir paketten yalnızca belirli bir dosyayı içe aktarmanız gerekir.

Örneğin, **highlight.js** kütüphanesini entegre ederken yalnızca çekirdeği ve belirli bir dili (ör. JavaScript) kullanmak isteyebilirsiniz:

```js
import hljs from 'highlight.js/lib/core';
import javascript from 'highlight.js/lib/languages/javascript';

hljs.registerLanguage('javascript', javascript);
hljs.highlightAll();
```

Bu durumda, `highlight.js` paketini doğrudan `importmap.php` dosyasına eklemek işe yaramaz.

Çünkü içe aktardığınız yol (ör. `highlight.js/lib/core`)  **importmap.php’deki bir girişle birebir eşleşmelidir** .

Bunun yerine, `importmap:require` komutuna tam yolları iletin.

Birden fazla paketi aynı anda ekleyebilirsiniz:

```bash
php bin/console importmap:require highlight.js/lib/core highlight.js/lib/languages/javascript
```

---

## 💡 Global Değişkenler (Örneğin jQuery)

Geleneksel olarak bazı kütüphaneler (ör. jQuery) global değişkenler üzerinden kullanılır:

Örneğin `$` değişkeni:

```js
// assets/app.js
import 'jquery';

// app.js veya başka bir dosya
$('.something').hide(); // ❌ ÇALIŞMAZ!
```

Ancak  **modül tabanlı bir ortamda (ES Modules)** , `jquery` gibi bir kütüphaneyi import etmek, global değişken oluşturmaz.

Bunun yerine, her dosyada doğrudan import edip bir değişkene atamalısınız:

```js
import $ from 'jquery';
$('.something').hide();
```

Hatta bunu doğrudan bir `<script>` etiketi içinde de yapabilirsiniz:

```html
<script type="module">
    import $ from 'jquery';
    $('.something').hide();
</script>
```

Eğer `$` değişkeninin **global (her yerden erişilebilir)** olmasını istiyorsanız,

bunu manuel olarak `app.js` içinde tanımlayabilirsiniz:

```js
import $ from 'jquery';

// "window" nesnesine eklenen her şey global olur
window.$ = $;
```

---

## 🧵 CSS Yönetimi

Bir JavaScript dosyasından CSS dosyalarını doğrudan içe aktarabilirsiniz.

Varsayılan olarak, `assets/app.js` dosyası zaten `assets/styles/app.css` dosyasını içe aktarır:

```js
// assets/app.js
import '../styles/app.css';

// ...
```

Twig’de `{{ importmap('app') }}` çağrıldığında, AssetMapper şu işlemleri yapar:

1. `assets/app.js` ve onun içe aktardığı tüm JS dosyalarını tarar,
2. Bu dosyalardaki CSS `import` ifadelerini bulur,
3. Bulunan CSS dosyaları için `<link>` etiketleri oluşturur (yükleme sırasına göre).

Bu sayede CSS dosyalarınız otomatik olarak sayfaya eklenir.

> 🔍 **Not:**
>
> CSS dosyalarını içe aktarmak  **JavaScript modüllerinde doğal olarak desteklenmez** .
>
> AssetMapper bu desteği, her CSS dosyası için özel bir `importmap` girişi ekleyerek sağlar.
>
> Bu girişler gerçekte bir şey çalıştırmaz, sadece `<link>` etiketinin oluşturulmasını sağlar.

---

## 🧩 Üçüncü Parti CSS Dosyalarıyla Çalışma

Bazı JavaScript paketleri, kendi CSS dosyalarını içerir.

Örneğin `bootstrap` paketinde `dist/css/bootstrap.min.css` dosyası bulunur.

Bu tür dosyaları da `importmap:require` komutuyla ekleyebilirsiniz:

```bash
php bin/console importmap:require bootstrap/dist/css/bootstrap.min.css
```

Daha sonra bu CSS dosyasını sayfaya dahil etmek için bir JavaScript dosyasından içe aktarın:

```js
// assets/app.js
import 'bootstrap/dist/css/bootstrap.min.css';

// ...
```

Bazı kütüphaneler (ör.  **bootstrap** ), `package.json` içindeki `style` alanında CSS dosyalarını tanımlar.

Bu durumda, `importmap:require bootstrap` komutunu çalıştırdığınızda CSS dosyası da otomatik olarak `importmap.php`’ye eklenir.

Ancak eğer paket bu bilgiyi sağlamıyorsa, **paket geliştiricisinden** `style` alanını eklemesini talep edebilirsiniz.

---

## 🖼️ CSS İçinde Dosya Yolları

CSS içinde diğer dosyalara normal `url()` fonksiyonu ile referans verebilirsiniz:

```css
/* assets/styles/app.css */
.quack {
    /* dosya assets/images/duck.png içinde */
    background-image: url('../images/duck.png');
}
```

AssetMapper, derleme sırasında bu yolu **otomatik olarak sürümlenmiş** hale getirir:

```css
/* public/assets/styles/app-3c16d92m.css */
.quack {
    background-image: url('../images/duck-3c16d92m.png');
}
```

Bu sayede dosyalarınızın önbelleklenmesi sorunsuz bir şekilde yönetilir.

---

## 🌈 Tailwind CSS Kullanımı

AssetMapper ile **Tailwind CSS** kullanmak için şu pakete göz atın:

👉 [symfonycasts/tailwind-bundle](https://github.com/SymfonyCasts/tailwind-bundle)

---

## 🎨 Sass Kullanımı

AssetMapper ile **Sass (SCSS)** desteği eklemek için şu paketi kullanabilirsiniz:

👉 [symfonycasts/sass-bundle](https://github.com/SymfonyCasts/sass-bundle)

---

✅ **Özetle:**

AssetMapper ile:

* JS ve CSS dosyalarınızı doğrudan modül sistemiyle kullanabilirsiniz,
* Üçüncü parti kütüphaneleri kolayca içe aktarabilir,
* CSS dosyalarınızı otomatik olarak sürümlendirip optimize edebilirsiniz —

  **hiçbir derleme adımı veya karmaşık araç gerekmeden.**


### 💤 JavaScript Dosyalarından CSS’i Gecikmeli (Lazy) Yüklemek

Bazı CSS dosyalarını sayfa yüklendikten **sonra** yüklemek isteyebilirsiniz.

Bunu yapmak için normal, **dinamik import** sözdizimini kullanabilirsiniz:

```js
// assets/any-file.js
import('./lazy.css');

// ...
```

Bu durumda `lazy.css` dosyası **asenkron** olarak indirilir ve yükleme tamamlandığında sayfaya eklenir.

Ayrıca eğer dinamik olarak yüklenen bir **JavaScript** dosyası (ör. `import('./module.js')`) kendi içinde **CSS** dosyası içe aktarıyorsa, o CSS de aynı şekilde **asenkron olarak yüklenecektir.**

---

## ⚠️ Sorunlar ve Hata Ayıklama

### 🚫 1. Eksik importmap Girdisi

Tarayıcı konsolunda şöyle bir hata görebilirsiniz:

```
Failed to resolve module specifier " bootstrap". Relative references must start with either "/", "./", or "../".
```

veya

```
The specifier "bootstrap" was a bare specifier, but was not remapped to anything.
Relative module specifiers must start with "./", "../" or "/".
```

Bu hata, JavaScript dosyalarınızda şu tür bir import kullandığınız anlamına gelir:

```js
import 'bootstrap';
```

Tarayıcı, `bootstrap` paketini `importmap.php` dosyanızda bulamaz.

**Çözüm:**

Paketi importmap’e ekleyin:

```bash
php bin/console importmap:require bootstrap
```

> 💡 Firefox bu hatanın hangi dosyada oluştuğunu gösterir.
>
> Chrome ise şu an için bu bilgiyi göstermemektedir.

---

### 🧩 2. JavaScript, CSS veya Görsel Dosyalarda 404 Hatası

Tarayıcı konsolunda şu tür bir hata görebilirsiniz:

```
GET /assets/duck.js 404 (Not Found)
```

ve fark edersiniz ki dosya adında **sürüm hash’i yok**

(ör. `/assets/duck.js` yerine `/assets/duck-1b7a64b3.js` olması gerekirdi).

Bu genellikle **yanlış yol (path)** kullanıldığında olur.

#### 🔹 Twig’te Varlık Kullanımı

Doğru yol, varlığın **mantıksal yolu (logical path)** olmalıdır:

```twig
<img src="{{ asset('images/duck.png') }}">
```

Mantıksal yolları görmek için şu komutu çalıştırabilirsiniz:

```bash
php bin/console debug:asset-map
```

#### 🔹 CSS veya JS İçinden İçe Aktarma

Sorun daha çok şu durumda görülür:

```js
// assets/controllers/farm-controller.js
import '../farm/chicken.js';
```

Yani bir dosyayı başka bir dosyadan import ediyorsanız, **yol her zaman import eden dosyaya göre** olmalıdır.

Yani burada `../farm/chicken.js`, `assets/farm/chicken.js` dosyasını işaret eder.

Geçersiz yolları tespit etmek için şu komutları çalıştırın:

```bash
php bin/console cache:clear
php bin/console debug:asset-map
```

Eğer `symfony/monolog-bundle` kuruluysa, eksik varlıklar uyarı olarak görüntülenir:

```
WARNING [asset_mapper] Unable to find asset "../images/ducks.png" referenced in "assets/styles/app.css".
WARNING [asset_mapper] Unable to find asset "./ducks.js" imported from "assets/app.js".
```

---

### 💬 3. Yorum Satırlarında (Commented-out) Görünen Eksik Varlık Uyarıları

AssetMapper, `import` satırlarını **regex** ile tespit eder.

Bu sistem son derece güçlüdür, ancak yorum satırlarını da “import” olarak algılayabilir.

Örneğin:

```js
// import './old-style.css';
```

Bu durumda AssetMapper, bu import’u da yakalar.

Varlık bulunamadığında şu şekilde bir uyarı görebilirsiniz:

```
WARNING [asset_mapper] Unable to find asset "./old-style.css"
```

Bu zararsızdır — sadece bilgilendirme amaçlıdır ve **güvenle yok sayabilirsiniz.**

---

## 🚀 AssetMapper ile Dağıtım (Deploy)

Yayına çıkmadan önce varlıkları “derlemek” için şu komutu çalıştırın:

```bash
php bin/console asset-map:compile
```

Bu komut:

* Tüm sürümlenmiş varlıkları `public/assets/` dizinine yazar,
* Ayrıca birkaç JSON dosyası (`manifest.json`, `importmap.json` vb.) oluşturur,
* Böylece tarayıcı `importmap`’i **çok daha hızlı** okuyabilir.

---

## ⚡ Performans Optimizasyonu

AssetMapper kullanan bir uygulamayı **maksimum hızda** çalıştırmak için aşağıdakileri uygulayın:

### 1. **HTTP/2 veya HTTP/3 Kullanın**

Tarayıcının aynı anda birden fazla dosya indirmesine izin verir.

* **Caddy** HTTP/2’yi varsayılan olarak etkinleştirir.
* **Nginx** ve **Apache** için manuel olarak etkinleştirilebilir.
* Veya **Cloudflare** gibi bir proxy servisi kullanarak bunu otomatik hale getirebilirsiniz.

---

### 2. **Varlıkları Sıkıştırın (gzip, brotli)**

Sunucunuzun tüm varlıkları (JS, CSS, görseller) **sıkıştırarak** göndermesi gerekir.

* Caddy bunu varsayılan olarak yapar.
* Nginx ve Apache’de kolayca etkinleştirilebilir.
* Cloudflare, varlıkları otomatik olarak sıkıştırır.
* Symfony’nin AssetMapper bileşeni, **önceden sıkıştırılmış varlıklar (precompressed assets)** desteğine de sahiptir.

---

### 3. **Uzun Ömürlü Cache Kullanımı**

Sunucunuzun varlıklar için `Cache-Control` başlığında **uzun bir süre** (ör. 1 yıl) belirlemesi gerekir.

Çünkü AssetMapper her dosya adına **benzersiz bir sürüm hash’i** eklediğinden, önbellek güvenlidir.

Bu ayar manuel yapılır, ancak her sunucu (Nginx, Apache, Caddy) için kolayca eklenebilir.

---

Tüm bu ayarlardan sonra, sitenizin performansını ölçmek için

**Google Lighthouse** veya benzeri bir araçla test yapabilirsiniz.

---

✅ **Özetle:**

AssetMapper ile:

* CSS dosyalarını **lazy load** edebilir,
* import hatalarını kolayca debug edebilir,
* dağıtımda varlıklarınızı optimize edebilir,
* ve modern HTTP/2 altyapısıyla son derece hızlı bir kullanıcı deneyimi sunabilirsiniz.


### ⚡ Performans: Preloading (Ön Yükleme) Mantığını Anlamak

**Google Lighthouse** bazen şu uyarıyı gösterebilir:

> 🚨 *Avoid Chaining Critical Requests*
>
> (Kritik istekleri zincirleme olarak yüklemekten kaçının)

Bu uyarıyı anlamak için şu senaryoyu düşünelim:

* `assets/app.js` dosyası `./duck.js` dosyasını içe aktarıyor
* `assets/duck.js` dosyası ise `bootstrap` kütüphanesini içe aktarıyor

Yani zincir şöyle:

```
app.js → duck.js → bootstrap
```

 **Preloading olmadan** , tarayıcı bu süreci adım adım yürütür:

1. `assets/app.js` dosyasını indirir
2. İçinde `./duck.js` import’unu görür → `assets/duck.js` dosyasını indirir
3. Ardından `bootstrap` import’unu görür → `assets/bootstrap.js` dosyasını indirir

Yani dosyalar **ardışık (tek tek)** indirilir.

Bu da performansı ciddi biçimde düşürür. 🚫

---

### ✅ AssetMapper Bu Sorunu Nasıl Çözüyor?

AssetMapper, bu durumu **preload** link etiketleri oluşturarak çözer.

Mantık şu şekilde işler:

#### 🧩 A)

Twig şablonunuzda `{{ importmap('app') }}` çağrıldığında,

AssetMapper `assets/app.js` dosyasını ve onun import ettiği tüm dosyaları (ve onların import ettiği diğer dosyaları) analiz eder.

#### 🧩 B)

Sonra bu dosyalar için şu şekilde `<link rel="preload">` etiketleri oluşturur:

```html
<link rel="modulepreload" href="/assets/app-4e986c1a.js">
<link rel="modulepreload" href="/assets/duck-1b7a64b3.js">
<link rel="modulepreload" href="/assets/vendor/bootstrap/bootstrap.index-f093544d.js">
```

Bu etiketler, tarayıcıya “ **bu dosyaları hemen indirmeye başla!** ” demektir —

tarayıcı import satırlarını görmeden bile dosyalar paralel olarak indirilmeye başlar. ⚡

Ek olarak, eğer uygulamanızda **WebLink Component** yüklüyse, Symfony yanıt başlıklarına

CSS dosyaları için `Link` header’ı ekleyerek **ön yüklemeyi** HTTP düzeyinde de gerçekleştirir.

---

## 🗜️ Önceden Sıkıştırılmış (Pre-compressed) Varlıklar

> 🆕 Symfony **7.3** sürümünde tanıtıldı.

Çoğu web sunucusu (Caddy, Nginx, Apache, FrankenPHP) veya servis (ör. Cloudflare)

otomatik sıkıştırma desteği sağlar, ancak AssetMapper size bir adım daha ileri gitme olanağı verir:

💡 **Varlıkları yayına almadan önce, en yüksek sıkıştırma oranıyla önceden sıkıştırabilirsiniz.**

Bu yaklaşım:

* CPU kullanımı olmadan **önceden hazırlanmış** sıkıştırılmış dosyalar sunar
* **gzip, Brotli (br)** ve **Zstandard (zst)** formatlarını destekler
* Sunucu yükünü azaltır ve **daha hızlı yanıt süresi** sağlar

### 🔧 Gerekli Araçlar / Uzantılar

| Format              | Gerekenler                                                                    |
| ------------------- | ----------------------------------------------------------------------------- |
| **Brotli**    | `brotli`CLI komutu veya `brotli`PHP uzantısı                            |
| **Zstandard** | `zstd`CLI komutu veya `zstd`PHP uzantısı                                |
| **gzip**      | `zopfli`(önerilen) veya `gzip`CLI komutu, ayrıca `zlib`PHP uzantısı |

---

### ⚙️ Yapılandırma Örneği

`config/packages/asset_mapper.yaml` içinde şu ayarları yapın:

```yaml
framework:
    asset_mapper:
        precompress:
            # kullanılacak sıkıştırma format(lar)ı
            format: 'zstandard'
            # birden fazla format da belirtebilirsiniz:
            # format: ['brotli', 'zstandard']

            # sıkıştırılacak dosya uzantıları
            extensions: ['css', 'js', 'json', 'svg', 'xml']
```

Daha sonra şu komutu çalıştırın:

```bash
php bin/console asset-map:compile
```

Bu, eşleşen tüm dosyaları seçtiğiniz formatta **en yüksek sıkıştırma oranı** ile sıkıştırır.

Oluşturulan dosyalar şu şekilde görünür:

```
app.js         → app.js.zst
styles/app.css → styles/app.css.br
```

Sunucunuzun bu önceden sıkıştırılmış dosyaları kullanmasını sağlamak için konfigürasyon ekleyin:

```nginx
file_server {
    precompressed br zstd gzip
}
```

Symfony ayrıca şu komut ve servisi sunar:

* `php bin/console assets:compress`
* `asset_mapper.compressor` servisi (örneğin kullanıcı yüklemelerini sıkıştırmak için kullanılabilir)

---

## ❓ Sıkça Sorulan Sorular (FAQ)

### 🔸 AssetMapper Varlıkları Birleştiriyor mu?

Hayır, **birleştirmiyor** — ve artık buna gerek de yok!

HTTP/2 ile birlikte, tarayıcı aynı anda birçok dosyayı paralel olarak indirebiliyor.

Ayrıca, dosyaları ayrı tutmak cache yönetimini kolaylaştırır.

---

### 🔸 AssetMapper Varlıkları Küçültüyor (Minify) mu?

Hayır, doğrudan küçültme yapmaz.

Ancak çoğu durumda bu gerekli değildir çünkü:

* Sunucu zaten dosyaları **sıkıştırarak** gönderir.
* Ek olarak, isterseniz **SensioLabs Minify Bundle** kullanabilirsiniz.

  Bu bundle, `asset-map:compile` komutu sırasında tüm varlıkları otomatik olarak minify eder.

📘 Daha fazla bilgi: *Serving assets in production* bölümü.

---

### 🔸 AssetMapper Production Ortamı için Uygun mu?

Evet — **kesinlikle!**

AssetMapper, modern tarayıcı ve web sunucusu teknolojilerinden yararlanır:

* **importmaps** ve  **native imports** ,
* **HTTP/2 paralel indirme** ,
* **ön yükleme ve sıkıştırma desteği** .

Örneğin, [ux.symfony.com](https://ux.symfony.com/) sitesi AssetMapper üzerinde çalışıyor

ve **Google Lighthouse skoru: %99** 🚀

---

### 🔸 Tüm Tarayıcılarda Çalışır mı?

Evet, tüm modern tarayıcılarda!

* `importmap` ve `import` ifadeleri modern tarayıcılarda **yerel** olarak desteklenir.
* AssetMapper, eski tarayıcılar için **ES Module Shim** içerir.

🧩 Ancak, “dynamic import” (`import('./file.js')`) en eski tarayıcılarda çalışmaz.

Bu durumda [es-module-shims](https://www.npmjs.com/package/es-module-shims#user-content-polyfill-edge-case-dynamic-import) paketindeki `importShim()` fonksiyonunu kullanabilirsiniz.

---

### 🔸 Tailwind, Sass, TypeScript veya JSX ile Kullanabilir miyim?

| Araç                  | Destek       | Kaynak                                                                                                          |
| ---------------------- | ------------ | --------------------------------------------------------------------------------------------------------------- |
| **Tailwind CSS** | ✅ Evet      | [symfonycasts/tailwind-bundle](https://github.com/SymfonyCasts/tailwind-bundle)                                    |
| **Sass (SCSS)**  | ✅ Evet      | [symfonycasts/sass-bundle](https://github.com/SymfonyCasts/sass-bundle)                                            |
| **TypeScript**   | ✅ Evet      | [sensiolabs/typescript-bundle](https://github.com/sensiolabs/typescript-bundle)                                    |
| **JSX / Vue**    | ⚠️ Kısmen | JSX derlenmiş dosyalarla çalışır;`.vue`single-file component'lar için Webpack Encore tercih edilmelidir |

---

### 🔸 Kodumu Lint ve Formatlayabilir miyim?

Evet, AssetMapper’ın kendisi bunu yapmaz, ancak şunu kurabilirsiniz:

```bash
composer require kocal/biome-js-bundle
```

Bu araç:

* **Prettier** ’dan çok daha hızlıdır,
* **JavaScript** , **TypeScript** ve **CSS** dosyalarını otomatik olarak biçimlendirir,
* Sıfır yapılandırmayla (zero-config) çalışır.

---

✅ **Özetle:**

AssetMapper modern web altyapısına tam uyumludur:

* **Preloading** sayesinde kritik istekler zincirlenmez,
* **Precompression** ile varlıklarınız maksimum hızla yüklenir,
* HTTP/2/3, caching ve minify destekleriyle,
* **%99 Lighthouse skoru** düzeyinde bir performans elde edebilirsiniz.



### 🧱 Üçüncü Parti Paketler (Bundles) ve Özel Asset Yolları

Symfony’de bazı **bundle’lar** kendi varlık (asset) dosyalarını içerir.

Örneğin bir bundle içinde `Resources/public/` veya `public/` dizini varsa, Symfony bu dizinleri otomatik olarak **“asset path”** olarak ekler.

Bu dizinler, bundle adına göre bir **namespace** ile haritalanır:

`bundles/<BundleName>/`

Örnek:

`BabdevPagerfantaBundle` kullanıyorsanız ve şu komutu çalıştırırsanız:

```bash
php bin/console debug:asset-map
```

şuna benzer bir çıktı görürsünüz:

```
bundles/babdevpagerfanta/css/pagerfanta.css
```

Yani Twig şablonunda bu varlığı şu şekilde kullanabilirsiniz:

```twig
<link rel="stylesheet" href="{{ asset('bundles/babdevpagerfanta/css/pagerfanta.css') }}">
```

---

### 💡 AssetMapper’ın Ek Faydası

Aslında bu yol (`bundles/babdevpagerfanta/css/pagerfanta.css`)

**AssetMapper olmadan da** çalışır.

Çünkü `assets:install` komutu bu dosyaları `public/bundles/` dizinine kopyalar.

Ancak, **AssetMapper aktifken** aynı dosya **otomatik olarak versiyonlanır** ✅

```html
<link rel="stylesheet" href="/assets/bundles/babdevpagerfanta/css/pagerfanta-ea64fc9c.css">
```

---

### 🧩 Üçüncü Parti Varlıkları (Assets) Geçersiz Kılma

Bir paketin sağladığı varlığı (örneğin `pagerfanta.css`) değiştirmek isterseniz,

`assets/` dizininde aynı yola sahip bir dosya oluşturarak bunu **override** edebilirsiniz:

```
assets/bundles/babdevpagerfanta/css/pagerfanta.css
```

Bu dosya, orijinal bundle dosyası yerine kullanılacaktır.

> ⚠️ Not:
>
> Bazı bundle’lar (ör.  **EasyAdminBundle** ) varlıklarını özel bir “asset package” üzerinden çağırır.
>
> Bu tür durumlarda AssetMapper devreye girmez.

---

### 📂 assets/ Dizin Dışındaki Dosyaları Dahil Etmek

Bazen `assets/` dışında kalan dosyaları da dahil etmek isteyebilirsiniz.

Örneğin doğrudan `vendor` dizininden bir CSS dosyası almak mümkündür:

```css
/* assets/styles/app.css */

/* assets/ dizininin dışına çıkıyoruz */
@import url('../../vendor/babdev/pagerfanta-bundle/Resources/public/css/pagerfanta.css');
```

Ancak aşağıdaki gibi bir hata alırsanız:

```
The "app" importmap entry contains the path "vendor/some/package/assets/foo.js" 
but it does not appear to be in any of your asset paths.
```

Bu, dosyanın geçerli bir yol olduğunu ancak AssetMapper’ın taradığı dizinler arasında bulunmadığını gösterir.

**Çözüm:** `config/packages/asset_mapper.yaml` dosyasına yolu ekleyin:

```yaml
framework:
    asset_mapper:
        paths:
            - assets/
            - vendor/some/package/assets
```

Sonrasında komutu tekrar deneyin.

Bu dizin artık AssetMapper tarafından taranacak ve içindeki varlıklar kullanılabilir olacaktır.

---

## ⚙️ AssetMapper Yapılandırma Seçenekleri

Tüm mevcut konfigürasyonları görmek için şu komutu çalıştırabilirsiniz:

```bash
php bin/console config:dump framework asset_mapper
```

Aşağıda en önemli ayarların kısa özeti yer almaktadır 👇

---

### 🗺️ `framework.asset_mapper.paths`

Bu ayar, AssetMapper’ın hangi dizinleri tarayacağını belirler.

**Basit bir liste:**

```yaml
framework:
    asset_mapper:
        paths:
            - assets/
            - vendor/some/package/assets
```

**Namespace tanımlayarak:**

```yaml
framework:
    asset_mapper:
        paths:
            assets/: ''
            vendor/some/package/assets/: 'some-package'
```

Bu durumda `vendor/some/package/assets/` dizinindeki dosyalar

`some-package/foo.js` şeklinde **mantıksal yollara (logical paths)** sahip olur.

---

### 🚫 `framework.asset_mapper.excluded_patterns`

Bazı dosyaları (ör. `.scss`) asset haritalamasından hariç tutmak için kullanılır:

```yaml
framework:
    asset_mapper:
        excluded_patterns:
            - '*/*.scss'
```

💡 `php bin/console debug:asset-map` komutunu kullanarak hariç tutulan dosyaları doğrulayabilirsiniz.

---

### 🕵️ `framework.asset_mapper.exclude_dotfiles`

`.` (nokta) ile başlayan dosyaların hariç tutulup tutulmayacağını belirler.

Bu, `.env`, `.gitignore` gibi hassas dosyaların dışarı sızmasını önler.

```yaml
framework:
    asset_mapper:
        exclude_dotfiles: true
```

> Bu ayar **varsayılan olarak etkindir.**

---

### 🧩 `framework.asset_mapper.importmap_polyfill`

Eski tarayıcılar için gerekli olan **importmap polyfill (ES module shim)** ayarıdır.

Varsayılan olarak `es-module-shims` CDN’den yüklenir.

```yaml
framework:
    asset_mapper:
        # polyfill'i tamamen devre dışı bırakmak için
        importmap_polyfill: false

        # veya kendi polyfill'inizi kullanabilirsiniz:
        # importmap_polyfill: 'custom_polyfill'
```

Polyfill’i yerel olarak yüklemek isterseniz, şu komutu çalıştırın:

```bash
php bin/console importmap:require es-module-shims
```

---

### 🧠 `framework.asset_mapper.importmap_script_attributes`

`{{ importmap() }}` Twig fonksiyonu tarafından oluşturulan `<script>` etiketlerine

özel HTML nitelikleri (attributes) eklemenizi sağlar.

Örnek:

```yaml
framework:
    asset_mapper:
        importmap_script_attributes:
            crossorigin: 'anonymous'
```

Bu durumda, oluşturulan script etiketi şöyle görünür:

```html
<script type="importmap" crossorigin="anonymous"> ... </script>
```

---

✅ **Özetle:**

* Symfony, bundle’ların `public/` veya `Resources/public/` dizinlerini otomatik olarak asset yoluna ekler.
* AssetMapper bu dosyaları  **otomatik olarak versiyonlar** , önbellek dostu hale getirir.
* `assets/` dışındaki dosyaları da kolayca dahil edebilir, yolları `asset_mapper.yaml` üzerinden tanımlayabilirsiniz.
* Gelişmiş yapılandırmalarla dosyalarınızı hem güvenli hem performanslı şekilde yönetebilirsiniz.


### 🎯 Sayfaya Özel CSS ve JavaScript Yönetimi

Bazı durumlarda, CSS veya JavaScript dosyalarını **sadece belirli sayfalarda** yüklemek isteyebilirsiniz.

Symfony’nin **AssetMapper** sistemiyle bunu birkaç farklı şekilde yapabilirsiniz 👇

---

## ⚡ 1. Dinamik (Lazy) JavaScript Yükleme

JavaScript dosyalarını yalnızca belirli koşullar altında yüklemek için **dinamik import** sözdizimini kullanabilirsiniz:

```js
const someCondition = true;

if (someCondition) {
    import('./some-file.js');

    // veya async/await ile:
    // const module = await import('./some-file.js');
}
```

Bu yöntemle, `some-file.js` dosyası yalnızca koşul sağlandığında **asenkron olarak** yüklenir.

---

## ⚙️ 2. Ayrı Bir Entrypoint Oluşturma

Bazı sayfalar için tamamen farklı CSS/JS dosyaları kullanmak istiyorsanız,

o sayfa için ayrı bir “entrypoint” tanımlayabilirsiniz.

Örneğin bir **checkout** sayfanız olsun 👇

### 🔹 Yeni bir dosya oluşturun:

```js
// assets/checkout.js
import './checkout.css';

// özel JS kodlarınız
console.log('Checkout page scripts loaded!');
```

### 🔹 `importmap.php` dosyasına ekleyin:

```php
// importmap.php
return [
    // mevcut 'app' entrypoint’i ...
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],

    // yeni entrypoint
    'checkout' => [
        'path' => './assets/checkout.js',
        'entrypoint' => true,
    ],
];
```

### 🔹 Twig şablonunda çağırın:

```twig
{# templates/products/checkout.html.twig #}

{% block importmap %}
    {# parent() çağırmayın! sadece gerekli entrypointleri çağırın #}
    {{ importmap(['app', 'checkout']) }}
{% endblock %}
```

Bu sayede hem `app.js` hem `checkout.js` dosyaları yüklenir.

> ⚠️ Önemli:
>
> `{% block importmap %}` içinde **`parent()` çağırmayın.**
>
> Her sayfada yalnızca **bir adet importmap()** çağrısı olmalıdır.

Eğer sadece `checkout.js` dosyasını yüklemek istiyorsanız:

```twig
{{ importmap('checkout') }}
```

Bu durumda tam import haritası yine sayfaya dahil edilir, ancak yalnızca `checkout.js` yüklenir.

---

## 🧠 Content Security Policy (CSP) ile Kullanım

Uygulamanızda **Content Security Policy (CSP)** etkinse,

`{{ importmap() }}` tarafından oluşturulan **inline `<script>`** etiketleri CSP tarafından engellenebilir.

CSP’yi koruyarak bu betiklerin çalışmasına izin vermek için her istekte rastgele bir **nonce** değeri üretebilirsiniz.

### 🔹 NelmioSecurityBundle kullanarak nonce oluşturma:

```twig
{# NelmioSecurityBundle csp_nonce() fonksiyonunu sağlar #}
{{ importmap('app', {'nonce': csp_nonce('script')}) }}
```

Bu durumda Symfony:

* CSP başlığına nonce değerini ekler
* `<script>` etiketlerine aynı nonce değerini ekler

  ve böylece CSP ihlali olmadan güvenli şekilde çalışır.

---

## 🎨 CSP ve CSS Dosyaları

AssetMapper, CSS dosyalarını importmap üzerinden yüklerken küçük bir “hack” kullanır:

`data:application/javascript` tipiyle inline olarak tanımlar (Bkz.  *Handling CSS* ).

Bu yöntem, bazı tarayıcılarda **CSP ihlali** olarak algılanabilir.

### 🔹 Çözüm:

CSP’nizde `script-src` yönergesine **`strict-dynamic`** ekleyin:

```
Content-Security-Policy: script-src 'strict-dynamic' 'nonce-...';
```

Bu, tarayıcıya importmap’in başka kaynakları yüklemesine izin verir.

> Ancak `strict-dynamic` kullanıldığında, `script-src` içindeki `'self'` ve `'unsafe-inline'` gibi diğer kaynaklar görmezden gelinir.
>
> Dolayısıyla diğer `<script>` etiketlerinin de **nonce** ile güvenilir kılınması gerekir.

---

## 🧮 Geliştirme Ortamında AssetMapper Önbellekleme

 **debug modunda** , AssetMapper her varlık dosyasının içeriğini önbelleğe alır.

Bir dosya değiştiğinde içeriği otomatik olarak yeniden hesaplar.

Örneğin:

* `app.css`, içinde `@import url('other.css')` satırını içeriyorsa,

  `other.css` değiştiğinde `app.css`’in versiyon hash’i de güncellenir.

Her şey genellikle otomatik çalışır.

Ancak bir dosyanın yeniden hesaplanmadığını fark ederseniz, basitçe:

```bash
php bin/console cache:clear
```

komutunu çalıştırın. Bu, tüm varlıkların içeriğini yeniden hesaplar.

---

## 🔒 Bağımlılıkların Güvenlik Denetimi

Symfony, npm benzeri bir güvenlik denetim aracı sunar.

Projenizde kullanılan tüm paketlerin güvenlik açıklarını kontrol etmek için:

```bash
php bin/console importmap:audit
```

örnek çıktı 👇

```
--------  ---------------------------------------------  ---------  -------  ----------  -----------------------------------------------------
Severity  Title                                          Package    Version  Patched in  More info
--------  ---------------------------------------------  ---------  -------  ----------  -----------------------------------------------------
Medium    jQuery Cross Site Scripting vulnerability      jquery     3.3.1    3.5.0       https://api.github.com/advisories/GHSA-257q-pV89-V3xv
High      Prototype Pollution in JSON5 via Parse Method  json5      1.0.0    1.0.2       https://api.github.com/advisories/GHSA-9c47-m6qq-7p4h
Critical  Prototype Pollution in minimist                minimist   1.1.3    1.2.6       https://api.github.com/advisories/GHSA-xvch-5gv4-984h
...
```

Komutun çıkış kodu:

* **0** → Güvenlik açığı bulunamadı ✅
* **1** → En az bir güvenlik açığı bulundu ⚠️

Bu sayede komutu  **CI/CD pipeline** ’ınıza entegre ederek

yeni açıklar tespit edildiğinde uyarı alabilirsiniz.

Ek olarak:

```bash
php bin/console importmap:audit --format=json
```

komutu JSON formatında çıktı üretir.

---

## 🧾 Lisans

Bu içerik ve örnek kodlar, **Creative Commons BY-SA 3.0** lisansı altındadır.

Kullanabilir, paylaşabilir ve üzerinde değişiklik yapabilirsiniz —

ancak atıf vermeniz ve aynı lisansla paylaşmanız gerekir.
