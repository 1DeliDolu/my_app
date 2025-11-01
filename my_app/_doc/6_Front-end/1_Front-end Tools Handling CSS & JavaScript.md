### Front-end Araçları: CSS ve JavaScript Yönetimi

Symfony, istediğiniz ön yüz (front-end) araçlarını seçmenize olanak tanır. Genellikle iki temel yaklaşım vardır:

1. HTML’inizi **PHP & Twig** ile oluşturmak
2. Ön yüzünüzü **React, Vue, Svelte** gibi bir JavaScript framework’ü ile oluşturmak

Her iki yaklaşım da harika çalışır — aşağıda her ikisi de açıklanmıştır.

---

## 🧩 PHP & Twig Kullanımı

Symfony, modern ve hızlı bir ön yüz oluşturmanıza yardımcı olacak iki güçlü seçeneğe sahiptir:

* **AssetMapper (Yeni projeler için önerilir):** Tamamen PHP üzerinde çalışır, herhangi bir derleme (build) adımı gerektirmez ve modern web standartlarından yararlanır.
* **Webpack Encore:** Node.js ve Webpack üzerine kuruludur.

| Özellik                                           | AssetMapper   | Encore           |
| -------------------------------------------------- | ------------- | ---------------- |
| **Production için hazır mı?**             | ✅ Evet       | ✅ Evet          |
| **Stabil mi?**                               | ✅ Evet       | ✅ Evet          |
| **Gereksinimler**                            | Yok           | Node.js          |
| **Derleme adımı gerekli mi?**              | Hayır        | Evet             |
| **Tüm tarayıcılarda çalışır mı?**    | ✅ Evet       | ✅ Evet          |
| **Stimulus/UX desteği**                     | ✅ Evet       | ✅ Evet          |
| **Sass/Tailwind desteği**                   | ✅ Evet       | ✅ Evet          |
| **React, Vue, Svelte desteği**              | ✅ Evet [1]   | ✅ Evet          |
| **TypeScript desteği**                      | ✅ Evet       | ✅ Evet          |
| **JavaScript yorumlarını kaldırır mı?** | ❌ Hayır [2] | ✅ Evet          |
| **CSS yorumlarını kaldırır mı?**        | ❌ Hayır [2] | ✅ Evet [4]      |
| **Sürümlenmiş (versioned) varlıklar**    | Her zaman     | İsteğe bağlı |
| **3. parti paketleri güncelleyebilir mi?**  | ✅ Evet       | ❌ Hayır [3]    |

**Notlar:**

1. JSX (React), Vue gibi yapılar AssetMapper ile kullanılabilir, ancak önceden derleme için bu framework’lerin kendi araçları gerekir. Bazı özellikler (ör. Vue Single-File Components) doğrudan tarayıcıda çalışacak saf JS’e derlenemez.
2. AssetMapper kullanırken CSS/JS kodunu küçültmek (minify) ve yorumları kaldırmak için **SensioLabs Minify Bundle** yükleyebilirsiniz.
3. Encore kullanıyorsanız, `npm-check` gibi güncelleme denetleyicilerini kullanabilirsiniz.
4. CSS yorumları, **CssMinimizerPlugin** ile kaldırılabilir (Webpack Encore’da mevcuttur).

---

## 🚀 AssetMapper (Önerilen)

🎥  **Video eğitimi** : AssetMapper screencast serisine göz atın.

AssetMapper, varlıklarınızı yönetmek için önerilen sistemdir.

Tamamen PHP üzerinde çalışır, karmaşık bağımlılıklar veya derleme adımları içermez.

Tüm bunları tarayıcınızın **importmap** özelliğinden yararlanarak yapar — bu özellik tüm tarayıcılarda **polyfill** sayesinde çalışır.

📘 [AssetMapper dokümantasyonunu okuyun](https://symfony.com/doc/current/frontend/asset_mapper.html)

---

## ⚙️ Webpack Encore

🎥  **Video eğitimi** : Webpack Encore screencast serisine göz atın.

Webpack Encore, Webpack’i Symfony uygulamanıza entegre etmenin daha basit bir yoludur.

JavaScript modüllerini paketlemek, CSS/JS’yi önceden işlemek, derlemek ve küçültmek (minify) için güçlü ve sade bir API sunar.

📘 [Encore dokümantasyonunu okuyun](https://symfony.com/doc/current/frontend/encore/installation.html)

---

## 🔄 AssetMapper’dan Encore’a Geçiş

Yeni Symfony web uygulamaları (`symfony new --webapp myapp`) varsayılan olarak **AssetMapper** kullanır.

Encore kullanmak istiyorsanız, aşağıdaki adımları izleyin (yeni bir proje üzerinde yapılması önerilir):

```bash
composer remove symfony/ux-turbo symfony/asset-mapper symfony/stimulus-bundle

composer require symfony/webpack-encore-bundle symfony/ux-turbo symfony/stimulus-bundle

npm install
npm run dev
```

Bu kurulum, varsayılan web uygulamasında olduğu gibi **Turbo** ve **Stimulus** desteğini de sağlar.

---

## ⚡ Stimulus ve Symfony UX Bileşenleri

AssetMapper veya Webpack Encore’u kurduktan sonra, ön yüzünüzü oluşturmaya başlayabilirsiniz.

JavaScript’inizi istediğiniz gibi yazabilirsiniz, ancak  **Stimulus** , **Turbo** ve **Symfony UX** araçlarını kullanmanız önerilir.

📘 Daha fazla bilgi için: [StimulusBundle dokümantasyonu](https://symfony.com/bundles/StimulusBundle/current/index.html)

---

## 🧠 Front-end Framework Kullanımı (React, Vue, Svelte, vb.)

🎥  **Video eğitimi** : API Platform screencast serisine göz atın.

React, Vue, Svelte veya Next.js gibi framework’lerle çalışmak istiyorsanız, en iyi yaklaşım Symfony’yi **yalnızca bir API olarak** kullanmaktır.

Bu durumda **API Platform** harika bir çözümdür.

**API Platform** şunları içerir:

* Symfony tabanlı API backend’i
* Next.js (veya diğer frameworkler) ile frontend iskeleti
* React tabanlı admin arayüzü
* Tam Docker ortamı ve dahili web sunucusu

📘 [API Platform belgelerini inceleyin](https://api-platform.com/docs/)

---

## 🔗 Diğer Ön Yüz Konuları

* [Bir UX bundle oluşturma](https://symfony.com/doc/current/frontend/ux.html)
* [Varlıklar için özel sürüm stratejisi kullanma](https://symfony.com/doc/current/frontend/custom_version_strategy.html)
* [Twig’den JavaScript’e bilgi aktarma](https://symfony.com/doc/current/frontend/twig_to_js.html)

---
