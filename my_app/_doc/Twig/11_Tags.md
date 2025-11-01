İşte Twig’in **Tags (Etiketler)** listesi ve her birinin **kısa açıklaması ile ne işe yaradığını** belirten bir markdown tablosu:

---

# 🏷️ Twig Tags (Etiketler)

| Etiket               | Açıklama                                               | Ne İşe Yarar                                                           |
| -------------------- | -------------------------------------------------------- | ------------------------------------------------------------------------ |
| **apply**      | Bir bloğa filtre uygular.                               | Birden fazla ifadeye aynı anda filtre uygulamak için kullanılır.     |
| **autoescape** | Otomatik kaçış modunu ayarlar.                        | HTML, JS vb. içeriklerde otomatik güvenli çıkış sağlar.           |
| **block**      | Şablon bloğu tanımlar.                                | `extends`ile kalıtım alınan şablonlarda içerik alanı oluşturur. |
| **cache**      | Şablonun belirli bir kısmını önbelleğe alır.      | Performansı artırmak için render sonucunu cache’ler.                 |
| **deprecated** | Bir kod bloğunun kullanımdan kalktığını belirtir.  | Gelecekte kaldırılacak Twig bölümleri için uyarı verir.            |
| **do**         | Bir ifadenin sonucunu göstermeden çalıştırır.      | Çıktı üretmeden işlem yapar (ör. fonksiyon çağrısı).           |
| **embed**      | Bir şablonu dahil eder ve üzerine yazar.               | `include`+`extends`kombinasyonu gibidir.                             |
| **extends**    | Başka bir şablondan kalıtım alır.                   | Ana şablonu belirtir (template inheritance).                            |
| **flush**      | Çıktı tamponunu boşaltır.                           | Büyük çıktı işlemlerinde veriyi parça parça gönderir.           |
| **for**        | Döngü başlatır.                                      | Koleksiyonlar veya diziler üzerinde yineleme yapar.                     |
| **from**       | Bir makroyu dış şablondan import eder.                | `import`ile birlikte belirli makroları getirir.                       |
| **if**         | Koşul başlatır.                                       | Şartlı ifadeleri çalıştırmak için kullanılır.                   |
| **import**     | Başka bir şablondan makroları içe aktarır.          | Fonksiyon benzeri makroları kullanıma açar.                           |
| **include**    | Başka bir şablonu dahil eder.                          | Parçalı (reusable) şablonları içeri alır.                          |
| **macro**      | Tekrar kullanılabilir Twig fonksiyonu tanımlar.        | Parametre alabilen şablon fonksiyonları oluşturur.                    |
| **sandbox**    | Güvenlik sınırlamaları içinde kod çalıştırır.  | Yalnızca izin verilen Twig özelliklerini çalıştırır.              |
| **set**        | Değişken tanımlar veya değer atar.                   | Twig içinde yeni değişkenler oluşturur.                              |
| **types**      | (Yeni/Deneysel) Veri türü veya içerik tanımı yapar. | Şablonlarda tip tabanlı işlem kontrolü sağlar.                      |
| **use**        | Başka bir şablondaki blokları içe aktarır.          | `extends`kullanmadan blok paylaşımı sağlar.                        |
| **verbatim**   | İçeriği olduğu gibi yazar.                           | Twig söz dizimi işlemeye kapatılır (`{{ }}`yorumlanmaz).           |
| **with**       | Dahil edilen şablona değişkenler gönderir.           | `include`veya `embed`içinde değişken bağlamı sağlar.           |

---

İstersen bu tabloyu **kategoriye göre** (örneğin “kontrol yapıları”, “kalıtım & dahil etme”, “değişken & filtre yönetimi” gibi) de gruplayabilirim.

Böyle bir sınıflandırma ister misin?
