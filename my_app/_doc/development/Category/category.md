Harika — çok doğru bir soru 🎯

“ **Kategori bölümünün oluşum aşaması** ” yani şu anda projemizde **Category (kategori)** kısmı **nasıl planlandı, hangi adımlarla oluşturuldu ve nasıl çalışıyor** — adım adım sana özetleyeyim.

---

## 🧭 **Symfony E-Commerce Projesinde “Category” Bölümünün Oluşum Aşamaları**

Bu bölümü baştan sona 5 ana aşamada kurduk 👇

---

### 🧱 1️⃣ Category Entity’nin Oluşturulması

**Amaç:** Her ürünün bir kategoriye ait olabilmesi için veritabanında “kategori” tablosu oluşturmak.

**Yaptığımız adımlar:**

```bash
php bin/console make:entity Category
```

* Alanlar:| Alan Adı   | Tip         | Açıklama                                   |
  | ----------- | ----------- | -------------------------------------------- |
  | id          | integer     | otomatik ID                                  |
  | name        | string(255) | kategori adı                                |
  | description | text        | açıklama (opsiyonel)                       |
  | slug        | string(255) | SEO dostu URL adı (örnek: “electronics”) |

Sonra `Product` ile **ManyToOne** ilişki kurduk:

* Her **Product** bir  **Category** ’ye ait.
* Her **Category** birden fazla **Product** içerebilir.

Migration ile tabloyu oluşturduk:

```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

✅ Sonuç:

Veritabanında artık `category` tablosu ve `product.category_id` sütunu var.

---

### 🧩 2️⃣ 8 Kategori Verisinin Eklenmesi (Fixtures)

**Amaç:** Test ve geliştirme için örnek kategori verileri oluşturmak.

`src/DataFixtures/AppFixtures.php` içine şu 8 kategori eklendi:

* Electronics
* Fashion
* Home & Kitchen
* Books
* Sports
* Beauty
* Toys
* Automotive

Komut:

```bash
php bin/console doctrine:fixtures:load
```

✅ Sonuç:

Veritabanında 8 kategori kaydı oluştu.

Ayrıca her kategoriye rastgele ürünler atandı (Product fixtures içinde `setCategory(...)`).

---

### 🧭 3️⃣ CategoryController ile Kategori Sayfaları

**Amaç:**

Her kategoriye özel sayfa — örneğin `/category/electronics` — açıldığında sadece o kategoriye ait ürünleri göstermek.

```php
#[Route('/category/{slug}', name: 'app_category_show')]
public function show(Category $category, ProductRepository $productRepository): Response
{
    $products = $productRepository->findBy(['category' => $category]);
    return $this->render('category/show.html.twig', [
        'category' => $category,
        'products' => $products,
    ]);
}
```

✅ Sonuç:

* `/category/{slug}` → yalnızca o kategoriye ait ürünleri listeliyor.
* Template: `templates/category/show.html.twig`

---

### ⚡ 4️⃣ Ana Sayfada Kategori Sidebar’ı

**Amaç:**

Kategorilerin sol tarafta listelenmesi (sidebar), her birine tıklayınca ürünlerin filtrelenmesi.

`HomeController` içinde:

```php
$categories = $categoryRepository->findAll();
$products = $productRepository->findAll();
return $this->render('home/index.html.twig', [
    'categories' => $categories,
    'products' => $products,
]);
```

**Twig tarafı:**

```twig
<div class="list-group">
    <a href="#" id="all-products-link" class="list-group-item list-group-item-action active">
        All Products
    </a>
    {% for category in categories %}
        <a href="#" class="list-group-item list-group-item-action category-link" data-slug="{{ category.slug }}">
            {{ category.name }}
        </a>
    {% endfor %}
</div>
```

✅ Sonuç:

Ana sayfada kategori listesi dinamik olarak veritabanından geliyor.

---

### ⚙️ 5️⃣ AJAX ile Dinamik Ürün Güncelleme

**Amaç:**

Kullanıcı kategoriye tıkladığında **sayfa yenilenmeden** sadece ürünlerin değişmesi.

* `/category/{slug}/products` route eklendi → sadece `_products.html.twig` döner.
* JavaScript `fetch()` ile bu URL çağrılır.
* Dönen HTML, sayfadaki `#product-list` alanına yazılır.

**Kod:**

```js
fetch(`/category/${slug}/products`)
    .then(response => response.text())
    .then(html => {
        productList.innerHTML = html;
    });
```

✅ Sonuç:

Sadece ürün bölümü değişiyor, navbar ve kategori menüsü sabit kalıyor.

Ayrıca “All Products” linki ile `/products/all` çağrılarak tüm ürünler geri getiriliyor.

---

### 🧭 6️⃣ Breadcrumb (Navigasyon Yolu)

**Amaç:**

Kullanıcıya hangi sayfada olduğunu göstermek:

`Home › Category › Product`

**Partial Twig:** `templates/partials/_breadcrumb.html.twig`

Tüm sayfalarda dinamik olarak çağrılıyor.

✅ Sonuç:

Kategorideyken → “Home › Electronics”

Üründe → “Home › Electronics › iPhone 15”

---

## ✅ Özet

| Aşama | İşlem                                               | Sonuç                                   |
| ------ | ----------------------------------------------------- | ---------------------------------------- |
| 1      | `Category`entity oluşturuldu                       | Veritabanında kategori tablosu var      |
| 2      | 8 örnek kategori eklendi (Fixtures)                  | Geliştirme verisi hazır                |
| 3      | `CategoryController`ile `/category/{slug}`rotası | Her kategoriye özel ürün sayfası     |
| 4      | Ana sayfada kategori listesi                          | Sidebar dinamik hale geldi               |
| 5      | AJAX ile ürün yükleme                              | Sayfa yenilenmeden ürünler değişiyor |
| 6      | Breadcrumb eklendi                                    | Navigasyon yolu dinamik gösteriliyor    |

---
