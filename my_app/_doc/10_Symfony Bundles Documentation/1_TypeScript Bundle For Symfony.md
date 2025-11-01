### 🚀 Symfony için TypeScript Bundle

Bu bundle, TypeScript’i derleyip Symfony’nin  **AssetMapper Component** ’ı ile birlikte kullanmanı sağlar ( **Node.js gerekmez!** ).

* Doğru **SWC binary** dosyasını otomatik olarak indirir
* TypeScript dosyalarını derlemek için **typescript:build** komutunu ekler
* **asset-map:compile** komutu çalıştırıldığında TypeScript dosyalarını otomatik olarak derler

---

### ⚙️ Kurulum

Bundle’ı yükle:

```bash
composer require sensiolabs/typescript-bundle
```

---

### 🧩 Kullanım

Öncelikle, TypeScript dosyalarının bulunduğu konumu belirten `sensiolabs_typescript.source_dir` seçeneğini ayarla.

Örneğin, TypeScript kodun `assets/typescript/` dizininde bulunuyorsa ve giriş noktası `assets/typescript/app.ts` ise, ayar şu şekilde olmalıdır:

```yaml
# config/packages/asset_mapper.yaml
sensiolabs_typescript:
    source_dir: ['%kernel.project_dir%/assets/typescript']
```

Daha sonra TypeScript dosyanı şablonlarda yükle:

```twig
{# templates/base.html.twig #}

{% block javascripts %}
    <script type="text/javascript" src="{{ asset('typescript/app.ts') }}"></script>
{% endblock %}
```

Komutu çalıştır:

```bash
php bin/console typescript:build --watch
php bin/console asset-map:compile
```

Hepsi bu kadar!

---

### 🧰 Symfony CLI ile Kullanım

Symfony CLI kullanıyorsan, build komutunu bir **worker** olarak ekleyebilirsin:

```yaml
# .symfony.local.yaml
workers:
    # ...
    typescript:
        cmd: ['symfony', 'console', 'typescript:build', '--watch']
```

Eğer `symfony server:start` komutunu **daemon** olarak çalıştırıyorsan, `symfony server:log` komutu ile worker’ın çıktılarını izleyebilirsin.

---

### 🧠 Nasıl Çalışır?

TypeScript komutlarından birini ilk kez çalıştırdığında, bundle sistemine uygun **SWC binary** dosyasını `var/` dizinine indirir.

`typescript:build` komutu çalıştırıldığında bu binary, TypeScript dosyalarını `var/typescript/` dizinine derler.

Sonrasında, `assets/typescript/app.ts` içeriği istendiğinde, bundle bu dosyanın içeriğini `var/typescript/` dizinindeki derlenmiş dosya ile değiştirir.

---

### ⚙️ Yapılandırma

Bundle’ın tam yapılandırmasını görmek için:

```bash
php bin/console config:dump sensiolabs_typescript
```

Ana seçenek:

`source_dir` — varsayılan olarak `[%kernel.project_dir%/assets]` dizinini kullanır.

Bu, derlenecek dizinlerin bir listesidir.

---

### 🔧 Farklı Binary Kullanma

Bundle, doğru **SWC binary** dosyasını senin için zaten yükler.

Ancak makinede halihazırda bir **SWC binary** varsa, bundle’a onu kullanmasını söyleyebilirsin:

```yaml
# config/packages/asset_mapper.yaml
sensiolabs_typescript:
    swc_binary: 'node_modules/.bin/swc'
```

Varsayılan olarak bundle **SWC v1.3.92** kullanır.

Yeni bir özellik veya hata düzeltmesi gerekiyorsa, farklı bir sürüm belirtebilirsin:

```yaml
# config/packages/sensiolabs_typescript.yaml
sensiolabs_typescript:
    swc_version: v1.7.27-nightly-20240911.1
```

Not: `swc_version` değiştirdikten sonra, mevcut binary dosyasını (`var` dizininde) silmelisin.

İndirme işlemi yalnızca binary bulunmadığında gerçekleşir.

---

### 🧱 Derleyiciyi Yapılandırma

**SWC derleyicisini** yapılandırmak için `.swcrc` dosyanın yolunu `swc_config_file` seçeneğiyle belirtebilirsin:

```yaml
# config/packages/asset_mapper.yaml
sensiolabs_typescript:
    swc_config_file: '%kernel.project_dir%/.swcrc'
```
