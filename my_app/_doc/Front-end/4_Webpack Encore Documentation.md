## 📘 Webpack Encore Dokümantasyonu

Symfony projelerinde modern **frontend asset** yönetimi için Webpack Encore en popüler çözümlerden biridir.

Encore, Webpack’in karmaşık yapılandırmalarını basitleştirir ve **CSS, JS, TypeScript, React, Vue** gibi teknolojileri kolayca entegre etmeni sağlar.

---

### 🚀 Başlangıç (Getting Started)

#### 🔧 Kurulum (Installation)

Encore’u projene eklemek için aşağıdaki komutu çalıştır:

```bash
composer require symfony/webpack-encore-bundle
npm install --save-dev @symfony/webpack-encore
```

Ardından `webpack.config.js` dosyası oluşturulur.

Bu dosya, Webpack yapılandırmalarını tanımlar.

#### 🧠 Temel Kullanım (Using Webpack Encore)

Giriş noktası olarak (`entry`) genellikle `assets/app.js` dosyası kullanılır:

```js
// webpack.config.js
Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/app.js')
    .enableStimulusBridge('./assets/controllers.json')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .enablePostCssLoader()
    .enableVersioning(Encore.isProduction())
;
```

Daha sonra varlıkları derlemek için:

```bash
npm run dev      # Geliştirme modu
npm run build    # Üretim modu
```

---

### 🎨 CSS ve Stil Dosyaları

#### ✅ CSS Preprocessor’ları (Sass, LESS, vb.)

Sass kullanmak için:

```bash
npm install sass-loader sass --save-dev
```

Ardından:

```js
Encore.enableSassLoader();
```

LESS veya Stylus için benzer şekilde `less-loader` veya `stylus-loader` ekleyebilirsin.

---

#### ✨ PostCSS & Autoprefixing

PostCSS ile CSS’i optimize etmek ve tarayıcı ön eklerini eklemek için:

```bash
npm install postcss-loader autoprefixer --save-dev
```

Ardından, `postcss.config.js` dosyası oluştur:

```js
module.exports = {
  plugins: [
    require('autoprefixer')
  ]
}
```

---

### ⚛️ React & Vue Desteği

#### ⚛️ React.js Etkinleştirme

```bash
npm install @babel/preset-react --save-dev
```

Ve `webpack.config.js` içine:

```js
Encore.enableReactPreset();
```

#### 🖼️ Vue.js Etkinleştirme

```bash
npm install vue vue-loader@next vue-style-loader --save-dev
```

Sonra:

```js
Encore.enableVueLoader();
```

---

### 🖼️ Görselleri Yönetmek (Images & Fonts)

Görselleri kopyalamak ve doğru şekilde referans vermek için:

```js
Encore.copyFiles({
    from: './assets/images',
    to: 'images/[path][name].[ext]'
});
```

Şablonda kullanımı:

```twig
<img src="{{ asset('build/images/logo.png') }}" alt="Logo">
```

---

### 🧬 Babel Yapılandırması

Babel, modern JavaScript özelliklerini eski tarayıcılarda çalışır hale getirir.

```js
Encore.configureBabel((config) => {
    config.plugins.push('@babel/plugin-proposal-class-properties');
});
```

---

### 🗺️ Source Maps

Kaynak haritalarını (source maps) etkinleştirerek hata ayıklamayı kolaylaştır:

```js
Encore.enableSourceMaps(!Encore.isProduction());
```

---

### 🧑‍💻 TypeScript Desteği

TypeScript kullanmak için:

```bash
npm install ts-loader typescript --save-dev
```

Ve Webpack ayarında etkinleştir:

```js
Encore.enableTypeScriptLoader();
```

---

### ⚡ Optimizasyonlar (Optimizing)

#### 📦 Kod Bölme (Code Splitting)

Ortak kodların birden fazla sayfada tekrar yüklenmesini önlemek için:

```js
Encore.splitEntryChunks();
Encore.enableSingleRuntimeChunk();
```

#### 🏷️ Versiyonlama (Versioning)

Cache sorunlarını önlemek için dosya isimlerine hash eklenir:

```js
Encore.enableVersioning(Encore.isProduction());
```

Bu işlem sonucunda `entrypoints.json` ve `manifest.json` dosyaları oluşturulur.

---

### 🌐 CDN Kullanımı

Encore, derlenen varlıklarını bir CDN üzerinden sunmak için kolay yapılandırma sağlar:

```js
Encore.setPublicPath('https://cdn.example.com/build');
```

---

### ⏩ Async Kod Bölme (Async Code Splitting)

JavaScript modüllerini sadece ihtiyaç duyulduğunda yüklemek için:

```js
import('./some-module.js').then(module => {
    module.init();
});
```

---

### ⚙️ Gelişmiş Konular (Guides & Advanced Config)

#### 🧱 Bootstrap Entegrasyonu

```bash
npm install bootstrap
```

```js
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';
```

#### 🧩 jQuery Entegrasyonu

```bash
npm install jquery --save
```

```js
Encore.autoProvidejQuery();
```

#### 🔥 webpack-dev-server & HMR

Canlı yenileme (Hot Module Replacement) için:

```bash
npm run dev-server
```

---

### 🧰 Özel Yükleyiciler ve Plugin’ler

Encore, özel Webpack eklentilerini eklemene de izin verir:

```js
const { DefinePlugin } = require('webpack');

Encore.addPlugin(new DefinePlugin({
    APP_VERSION: JSON.stringify('1.0.0')
}));
```

---

### 💻 Sanal Makine Ortamında Kullanım

Encore, Docker veya VM üzerinde çalıştırıldığında `public` dizinini host makineyle paylaşabilir.

`--watch-poll` seçeneği dosya değişikliklerinin algılanmasını sağlar:

```bash
npm run dev -- --watch-poll
```

---

### ❓ Sık Sorulan Sorular (FAQ & Common Issues)

**S:** “Entrypoints.json bulunamadı” hatası ne anlama geliyor?

**C:** `npm run dev` komutunu çalıştırarak dosyaların oluşturulduğundan emin ol.

**S:** `manifest.json` neden önemli?

**C:** Twig’de `asset()` fonksiyonu, bu dosyadan doğru build yolunu bulur.

**S:** Encore neden `yarn` yerine `npm` kullanıyor?

**C:** Her ikisini de destekler. `package-lock.json` veya `yarn.lock` tercihine göre ayarlanabilir.

---

İstersen bu Webpack Encore dokümantasyonunu da diğer Symfony UX çevirileriyle birleştirip

tek bir modern **Frontend Rehberi (frontend-guide-tr.md)** olarak düzenleyebilirim.
