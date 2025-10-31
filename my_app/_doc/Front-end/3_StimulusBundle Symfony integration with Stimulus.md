## 🧩 StimulusBundle: Symfony ile Stimulus Entegrasyonu

> **Canlı demoları inceleyin:** [https://ux.symfony.com](https://ux.symfony.com/)

Symfony UX ekosistemiyle Stimulus’u entegre eden bu bundle, Twig içinde Stimulus özelliklerini kolayca kullanmanı sağlar ve UX paketlerini yüklemek için gerekli altyapıyı sunar.

---

### 🚀 Özellikler

* **Twig entegrasyonu:**

  `stimulus_` fonksiyonları ve filtreleri ile template’lerinde Stimulus controller, action ve target’larını kolayca tanımlayabilirsin.
* **UX Paketleri entegrasyonu:**

  Ek Stimulus controller’larını (örneğin `symfony/ux-*` paketleri) otomatik olarak yükler.

---

### ⚙️ Kurulum

Öncelikle, bir **asset yönetim sistemi** seçmelisin. StimulusBundle her ikisiyle de uyumludur:

* 🧱 **AssetMapper:** PHP tabanlı asset sistemi
* ⚙️ **Webpack Encore:** Node.js tabanlı paketleme sistemi

> Hangisinin senin projen için daha uygun olduğunu öğrenmek için: **[Encore vs AssetMapper](https://symfony.com/doc/current/frontend/encore_vs_assetmapper.html)**

#### 1. Bundle’ı yükle:

```bash
composer require symfony/stimulus-bundle
```

Eğer **Symfony Flex** kullanıyorsan, işlem burada biter! Flex otomatik olarak gerekli dosyaları günceller.

Eğer manuel kurulum yapıyorsan, [Manual Setup](https://symfony.com/doc/current/frontend/stimulus.html#manual-setup) kısmına göz atabilirsin.

> 💡 Encore kullanıyorsan, `npm install` çalıştırmayı ve Encore sürecini yeniden başlatmayı unutma.

---

### 🧠 Kullanım

Artık kendi Stimulus controller’larını `assets/controllers` dizininde oluşturabilirsin.

Symfony Flex sana örnek olarak `hello_controller.js` dosyasını zaten ekler:

```js
// assets/controllers/hello_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.element.textContent = 'Hello Stimulus! Edit me in assets/controllers/hello_controller.js';
    }
}
```

HTML içinde controller’ı etkinleştirmek için:

```html
<div data-controller="hello">
   ...
</div>
```

Alternatif olarak Twig fonksiyonunu da kullanabilirsin:

```twig
<div {{ stimulus_controller('hello') }}>
    ...
</div>
```

Bu, aşağıdaki çıktıyı üretir:

```html
<div data-controller="hello">
   ...
</div>
```

> Bu element sayfada göründüğünde, `hello` controller’ı otomatik olarak etkinleşir.

📚 Daha fazla bilgi için: [Stimulus Dokümantasyonu](https://stimulus.hotwired.dev/)

---

### 🧩 TypeScript Controller’ları

Controller’larını **TypeScript** ile yazmak istiyorsan:

1. `sensiolabs/typescript-bundle` paketini yükle ve yapılandır.
2. `assets/controllers` yolunu `sensiolabs_typescript.source_dir` ayarına ekle.
3. Controller’larını bu dizinde oluştur.

Ve hazırsın!

---

### 🎁 Symfony UX Paketleri

Symfony, sık kullanılan işlemler için ekstra Stimulus controller’ları içeren **UX paketleri** sunar.

* Tüm UX paketleri, `assets/controllers.json` dosyasında tanımlanır.
* Bu dosya, yeni bir UX paketi yüklediğinde otomatik olarak güncellenir.

👉 Resmî UX paketlerini keşfet: [Symfony UX Packages](https://symfony.com/ux)

---

### 💤 Lazy (Tembel) Stimulus Controller’lar

Varsayılan olarak, tüm controller’lar her sayfada yüklenir.

Ancak bazı controller’lar yalnızca belirli sayfalarda kullanılıyorsa, bunları **lazy** (tembel) yüklemeye çevirebilirsin.

Lazy controller, sayfa ilk yüklendiğinde  **indirilmez** .

Yalnızca ilgili `data-controller` elementi DOM’a girdiğinde AJAX ile yüklenir.

#### Kendi controller’ını lazy yapmak için:

```js
import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    // ...
}
```

#### Üçüncü parti controller’ı lazy yapmak için:

`assets/controllers.json` içinde ilgili controller için `fetch` değerini `lazy` olarak ayarla:

```json
{
  "controllers": {
    "@symfony/ux-dropzone": {
      "enabled": true,
      "fetch": "lazy"
    }
  }
}
```

> ⚠️ Eğer TypeScript kullanıyor ve StimulusBundle 2.21.0 veya öncesini kullanıyorsan,
>
> `tsconfig.json` içinde `removeComments` ayarının `true` olmadığından emin ol.

---

### 🌍 Stimulus Dünyasındaki Faydalı Araçlar

Stimulus sadece Symfony’ye özgü değildir.

Global toplulukta birçok ek araç ve paket mevcuttur:

* **[stimulus-use](https://stimulus-use.github.io/stimulus-use/)**

  Debouncing, dış tıklama algılama ve birçok davranışı kolayca eklemeyi sağlar.
* **[stimulus-components](https://stimulus-components.netlify.app/)**

  Kopyala-yapıştır, sıralanabilir listeler, popover (tooltip benzeri) gibi yüzlerce hazır Stimulus controller’ı içerir.

---

### 🔗 Özet

| Konu                           | Açıklama                                        |
| ------------------------------ | ------------------------------------------------- |
| **Ana Kütüphane**      | [@hotwired/stimulus](https://stimulus.hotwired.dev/) |
| **Symfony Entegrasyonu** | `symfony/stimulus-bundle`                       |
| **Ek Özellikler**       | Symfony UX Paketleri                              |
| **Lazy Load Desteği**   | `/* stimulusFetch: 'lazy' */`                   |
| **TypeScript Desteği**  | `sensiolabs/typescript-bundle`                  |

---


## ⚙️ Stimulus Twig Helpers

Bu bundle, Twig şablonlarında  **Stimulus controller** , **action** ve **target** özelliklerini kolayca eklemeye yardımcı olan Twig fonksiyonları ve filtreleri sağlar.

> 💡 **Öneri:**
>
> Bu Twig yardımcılarını kullanabilirsin, ancak mümkün olduğunda doğrudan `data-*` özniteliklerini yazmak daha basit ve anlaşılırdır.
>
> 🧠 Eğer **PhpStorm** kullanıyorsan, Stimulus özellikleri için otomatik tamamlama desteği almak üzere **Stimulus eklentisini** yükleyebilirsin.

---

### 🧩 `stimulus_controller`

`stimulus_controller()` Twig fonksiyonu, Stimulus  **controller’larını** ,  **values** , **CSS class’larını** ve **outlet’leri** oluşturmak için kullanılır.

#### 🔹 Temel Kullanım

```twig
<div {{ stimulus_controller('hello', { 'name': 'World', 'data': [1, 2, 3, 4] }) }}>
    Hello
</div>
```

⬇️ **Üretilen HTML:**

```html
<div
   data-controller="hello"
   data-hello-name-value="World"
   data-hello-data-value="[1,2,3,4]"
>
   Hello
</div>
```

> 🧾 Not: Sayısal veya dizi gibi **scalar olmayan değerler JSON formatında** kodlanır ve doğru şekilde escape edilir (`&#x5B;` karakteri `[` anlamına gelir).

---

#### 🔹 CSS Sınıfları Eklemek

```twig
<div {{ stimulus_controller('hello', { 'name': 'World', 'data': [1, 2, 3, 4] }, { 'loading': 'spinner' }) }}>
    Hello
</div>
```

⬇️ **Üretilen HTML:**

```html
<div
   data-controller="hello"
   data-hello-name-value="World"
   data-hello-data-value="[1,2,3,4]"
   data-hello-loading-class="spinner"
>
   Hello
</div>
```

Sadece class eklemek istiyorsan:

```twig
<div {{ stimulus_controller('hello', controllerClasses: { 'loading': 'spinner' }) }}>
    Hello
</div>
```

---

#### 🔹 Outlets Kullanımı

```twig
<div {{ stimulus_controller('hello',
        { 'name': 'World', 'data': [1, 2, 3, 4] },
        { 'loading': 'spinner' },
        { 'other': '.target' }) }}>
    Hello
</div>
```

⬇️ **Üretilen HTML:**

```html
<div
   data-controller="hello"
   data-hello-name-value="World"
   data-hello-data-value="[1,2,3,4]"
   data-hello-loading-class="spinner"
   data-hello-other-outlet=".target"
>
   Hello
</div>
```

Sadece outlet eklemek istersen:

```twig
<div {{ stimulus_controller('hello', controllerOutlets: { 'other': '.target' }) }}>
    Hello
</div>
```

---

#### 🔹 Birden Fazla Controller Kullanımı

Aynı elementte birden fazla controller tanımlayabilirsin. Bunun için `stimulus_controller` filtresi kullanılabilir:

```twig
<div {{ stimulus_controller('hello', { 'name': 'World' })|stimulus_controller('other-controller') }}>
    Hello
</div>
```

⬇️ **Üretilen HTML:**

```html
<div data-controller="hello other-controller" data-hello-name-value="World">
    Hello
</div>
```

---

#### 🔹 Formlarda Kullanım

Controller özniteliklerini **dizi olarak** almak istersen `.toArray()` metodunu kullanabilirsin:

```twig
{{ form_start(form, { attr: stimulus_controller('hello', { 'name': 'World' }).toArray() }) }}
```

---

### ⚡ `stimulus_action`

`stimulus_action()` Twig fonksiyonu, Stimulus **action** özniteliklerini oluşturur.

#### 🔹 Temel Kullanım

```twig
<div {{ stimulus_action('controller', 'method') }}>Hello</div>
<div {{ stimulus_action('controller', 'method', 'click') }}>Hello</div>
```

⬇️ **Üretilen HTML:**

```html
<div data-action="controller#method">Hello</div>
<div data-action="click->controller#method">Hello</div>
```

---

#### 🔹 Birden Fazla Action Zincirleme

```twig
<div {{ stimulus_action('controller', 'method')|stimulus_action('other-controller', 'test') }}>
    Hello
</div>
```

⬇️ **Üretilen HTML:**

```html
<div data-action="controller#method other-controller#test">
    Hello
</div>
```

---

#### 🔹 Formlarda Kullanım

```twig
{{ form_row(form.password, { attr: stimulus_action('hello-controller', 'checkPasswordStrength').toArray() }) }}
```

---

#### 🔹 Parametre Geçmek

```twig
<div {{ stimulus_action('hello-controller', 'method', 'click', { 'count': 3 }) }}>Hello</div>
```

⬇️ **Üretilen HTML:**

```html
<div data-action="click->hello-controller#method" data-hello-controller-count-param="3">Hello</div>
```

---

### 🎯 `stimulus_target`

`stimulus_target()` Twig fonksiyonu, Stimulus **target** özniteliklerini oluşturur.

#### 🔹 Temel Kullanım

```twig
<div {{ stimulus_target('controller', 'myTarget') }}>Hello</div>
<div {{ stimulus_target('controller', 'myTarget secondTarget') }}>Hello</div>
```

⬇️ **Üretilen HTML:**

```html
<div data-controller-target="myTarget">Hello</div>
<div data-controller-target="myTarget secondTarget">Hello</div>
```

---

#### 🔹 Birden Fazla Target Zincirleme

```twig
<div {{ stimulus_target('controller', 'myTarget')|stimulus_target('other-controller', 'anotherTarget') }}>
    Hello
</div>
```

⬇️ **Üretilen HTML:**

```html
<div data-controller-target="myTarget" data-other-controller-target="anotherTarget">
    Hello
</div>
```

---

#### 🔹 Formlarda Kullanım

```twig
{{ form_row(form.password, { attr: stimulus_target('hello-controller', 'myTarget').toArray() }) }}
```

---

### 🧾 Özet Tablo

| Twig Fonksiyonu           | Amaç                           | Örnek Kullanım                                           |
| ------------------------- | ------------------------------- | ---------------------------------------------------------- |
| `stimulus_controller()` | Controller ve value eklemek     | `{{ stimulus_controller('hello', {'name': 'World'}) }}`  |
| `stimulus_action()`     | Event-action bağlamak          | `{{ stimulus_action('controller', 'method', 'click') }}` |
| `stimulus_target()`     | Target tanımlamak              | `{{ stimulus_target('controller', 'myTarget') }}`        |
| `.toArray()`            | Formlarda attr olarak kullanmak | `stimulus_controller(...).toArray()`                     |

---

İstersen bu kısmı da önceki “StimulusBundle” çevirisiyle birleştirip modern bir **markdown döküman** (örneğin `stimulus-bundle-tr.md`) olarak hazırlayayım.


## ⚙️ StimulusBundle Yapılandırma ve Manuel Kurulum

Bu bölüm,  **StimulusBundle** ’ın nasıl yapılandırıldığını ve **Flex** kullanmadan manuel olarak nasıl kurulabileceğini açıklar.

---

### 🧠 **Yapılandırma (Configuration)**

Eğer **AssetMapper** kullanıyorsan, kendi **controller dizinini** veya **controllers.json** dosyasının yolunu özelleştirebilirsin:

```yaml
# config/packages/stimulus.yaml
stimulus:
    # Varsayılan değerler
    controller_paths:
        - '%kernel.project_dir%/assets/controllers'
    controllers_json: '%kernel.project_dir%/assets/controllers.json'
```

---

### 🛠️ **Manuel Kurulum Detayları**

Symfony Flex, StimulusBundle’ı yüklediğinde gerekli tüm dosyaları otomatik olarak günceller.

Ancak **Flex kullanmıyorsan** veya yapılan değişiklikleri kontrol etmek istiyorsan, aşağıdaki adımlar manuel olarak yapılmalıdır.

#### 📁 Flex Tarifinde Yer Alan Dosyalar

| Dosya                                 | Açıklama                                                                                                                                                                                    |
| ------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **`assets/bootstrap.js`**     | Stimulus uygulamasını başlatır ve controller’ları yükler.`assets/app.js`tarafından içe aktarılır. İçeriği kullanılan sistem (Encore / AssetMapper) türüne göre değişir. |
| **`assets/app.js`**           | `assets/bootstrap.js`dosyasını import eder.                                                                                                                                               |
| **`assets/controllers.json`** | Başlangıçta genellikle boştur. Symfony UX paketleri yüklendikçe otomatik olarak güncellenir.                                                                                           |
| **`assets/controllers/`**     | Kendi Stimulus controller’larını burada oluşturursun. Varsayılan olarak `hello_controller.js`örneği bulunur.                                                                         |

---

### ⚙️ **AssetMapper ile Kullanım**

AssetMapper kullanıyorsan, Flex aşağıdaki iki girişi **importmap.php** dosyana ekler:

```php
// importmap.php
return [
    // ...
    '@symfony/stimulus-bundle' => [
        'path' => '@symfony/stimulus-bundle/loader.js',
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
];
```

Ardından, **`assets/bootstrap.js`** dosyası aşağıdaki hale gelir:

```js
// assets/bootstrap.js
import { startStimulusApp } from '@symfony/stimulus-bundle';

const app = startStimulusApp();
```

🧩 Burada `@symfony/stimulus-bundle`, importmap.php’ye eklenen yeni girdiyi ifade eder.

Bu dosya, tüm özel controller’larını ve `controllers.json` içindekileri  **dinamik olarak yükler** .

Ayrıca, Symfony uygulaması **debug modundayken Stimulus’un debug modunu da etkinleştirir.**

> ⚠️ **AssetMapper 6.3** sürümü için ayrıca `base.html.twig` dosyana
>
> `{{ ux_controller_link_tags() }}` eklemen gerekir.
>
> Bu, **AssetMapper 6.4+** sürümlerinde artık  **gereksizdir** .

---

### ⚙️ **Webpack Encore ile Kullanım**

Eğer **Webpack Encore** kullanıyorsan, Flex tarifine aşağıdaki satır eklenir:

```js
// webpack.config.js
.enableStimulusBridge('./assets/controllers.json')
```

Ve **`assets/bootstrap.js`** şu şekilde güncellenir:

```js
// assets/bootstrap.js
import { startStimulusApp } from '@symfony/stimulus-bridge';

// controllers.json ve controllers/ dizinindeki controller’ları kaydeder
export const app = startStimulusApp(require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
    true,
    /\.[jt]sx?$/
));
```

Ek olarak, aşağıdaki iki bağımlılık **package.json** dosyana eklenir:

```json
{
  "dependencies": {
    "@hotwired/stimulus": "^3.2.2",
    "@symfony/stimulus-bridge": "^3.0.0"
  }
}
```

---

### 🔍 **Stimulus Controller’ları Nasıl Yüklenir?**

Bir **Symfony UX PHP paketi** yüklediğinde, Flex sihirli bir şey yapar:

#### 1️⃣ package.json Güncellenir

Yeni UX paketi (örneğin `@symfony/ux-chartjs`) `vendor/` dizinindeki dosyalara bağlanır:

```json
{
    "devDependencies": {
        "@symfony/ux-chartjs": "file:vendor/symfony/ux-chartjs/assets"
    }
}
```

Yani bu, aslında NPM’den indirilen değil, **PHP paketi içinde bulunan bir “sanal” Node paketi** olur.

---

#### 2️⃣ controllers.json Güncellenir

Flex ayrıca `assets/controllers.json` dosyasına ilgili controller’ı ekler:

```json
{
    "controllers": {
        "@symfony/ux-chartjs": {
            "chart": {
                "enabled": true,
                "fetch": "eager"
            }
        }
    },
    "entrypoints": []
}
```

---

#### 3️⃣ bootstrap.js Controller’ları Yükler

Stimulus uygulaması, aşağıdaki iki kaynaktan controller’ları otomatik olarak kaydeder:

* `assets/controllers/` dizinindeki tüm dosyalar
* `assets/controllers.json` içinde tanımlı olan controller’lar

🧩

* Webpack Encore kullanıyorsan, bu işlem `@symfony/stimulus-bridge` aracılığıyla yapılır.
* AssetMapper kullanıyorsan, `@symfony/stimulus-bundle` bu işi doğrudan üstlenir.

---

### ✅ **Sonuç**

Bir UX paketi yüklediğinde, hiçbir manuel işlem yapmadan Stimulus controller’ı hemen kullanabilirsin.

Örneğin `@symfony/ux-chartjs` yüklendiğinde, controller’ın adı:

```
@symfony/ux-chartjs/chart
```

ama gerçekte HTML’de şu şekilde görünür:

```
data-controller="symfony--ux-chartjs--chart"
```

Twig fonksiyonu bunu senin için otomatik dönüştürür:

```twig
<div {{ stimulus_controller('@symfony/ux-chartjs/chart') }}>
```

⬇️ **Üretilen HTML:**

```html
<div data-controller="symfony--ux-chartjs--chart">
```

---

📄 **Lisans:**

Bu içerik ve kod örnekleri, [Creative Commons BY-SA 3.0](https://creativecommons.org/licenses/by-sa/3.0/) lisansı altındadır.

---

İstersen şimdiye kadar çevirdiğimiz tüm StimulusBundle belgelerini birleştirip tam, modern biçimli bir **Markdown dökümanı (`stimulus-bundle-tr.md`)** olarak oluşturabilirim.
