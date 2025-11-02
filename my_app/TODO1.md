

---

# 🧭 ADIM 1 — Amaç

| Özellik          | Açıklama                                                               |
| ----------------- | ------------------------------------------------------------------------ |
| 📄 Rapor butonu   | “Download PDF Report” butonu admin dashboard’un üst kısmına gelir. |
| 🧠 Backend render | Twig → HTML → PDF’e dönüştürülür.                               |
| 🧾 İçerik       | KPI’lar, finansal veriler, top kategoriler, son siparişler.            |
| 🎨 Tasarım       | Kurumsal görünümlü (logo, başlık, tarih, tablo stili).             |

---

# ⚙️ ADIM 2 — Gereken kütüphane

Symfony PDF için en iyi çözüm: **reportlab** veya **dompdf** tarzı kütüphanelerdir.

Biz burada **`reportlab` (Symfony’nin entegre ettiği Python backend’li PDF üretimi)** kullanacağız.

> 🧠 Symfony’nin `python` tabanlı PDF üretim sistemi, Twig üzerinden text-render’a uygundur.

Ama PHP tarafında senin projen Doctrine ve Twig tabanlı olduğundan, doğrudan

**`composer require dompdf/dompdf`** kullanmak daha pratik olur 👇

```bash
composer require dompdf/dompdf
```

---

# 🧩 ADIM 3 — Controller’a PDF Route ekle

📁 `src/Controller/AdminController.php`

```php
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/report/pdf', name: 'app_admin_report_pdf')]
public function generatePdfReport(EntityManagerInterface $em): Response
{
    // 🧮 Verileri topla
    $totalUsers = (int) $em->createQuery('SELECT COUNT(u.id) FROM App\Entity\User u')->getSingleScalarResult();
    $totalProducts = (int) $em->createQuery('SELECT COUNT(p.id) FROM App\Entity\Product p')->getSingleScalarResult();
    $totalOrders = (int) $em->createQuery('SELECT COUNT(o.id) FROM App\Entity\Order o')->getSingleScalarResult();
    $totalRevenue = (float) $em->createQuery(
        'SELECT COALESCE(SUM(CAST(o.total AS float)), 0) FROM App\Entity\Order o WHERE o.status IN (:paid)'
    )
    ->setParameter('paid', ['Paid', 'Shipped', 'Completed'])
    ->getSingleScalarResult();

    $topCategories = $em->createQuery(
        'SELECT c.name AS category, COALESCE(SUM(CAST(oi.subtotal AS float)),0) AS revenue
         FROM App\Entity\OrderItem oi
         JOIN oi.product p
         JOIN p.category c
         JOIN oi.order o
         WHERE o.status IN (:paid)
         GROUP BY c.id
         ORDER BY revenue DESC'
    )
    ->setParameter('paid', ['Paid', 'Shipped', 'Completed'])
    ->setMaxResults(5)
    ->getArrayResult();

    $latestOrders = $em->getRepository(\App\Entity\Order::class)
        ->findBy([], ['createdAt' => 'DESC'], 10);

    // 🧾 Twig → HTML render
    $html = $this->renderView('admin/pdf_report.html.twig', [
        'date' => new \DateTimeImmutable(),
        'totalUsers' => $totalUsers,
        'totalProducts' => $totalProducts,
        'totalOrders' => $totalOrders,
        'totalRevenue' => $totalRevenue,
        'topCategories' => $topCategories,
        'latestOrders' => $latestOrders,
    ]);

    // 🖨️ Dompdf ayarları
    $options = new Options();
    $options->set('defaultFont', 'DejaVu Sans');
    $options->setIsRemoteEnabled(true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // 📎 PDF dosyasını response olarak döndür
    return new Response(
        $dompdf->output(),
        200,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="dashboard_report.pdf"',
        ]
    );
}
```

📘 **Açıklama:**

* Controller Twig şablonunu alır (`pdf_report.html.twig`)
* HTML’yi PDF’e dönüştürür.
* Kullanıcıya **indirme olarak** döner.

---

# 🧱 ADIM 4 — PDF Template (Twig)

📁 `templates/admin/pdf_report.html.twig`

```twig
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Financial Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; margin: 30px; color: #333; }
        h1, h2 { text-align: center; color: #0d6efd; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px 10px; text-align: left; }
        th { background: #f2f4f6; }
        .summary { display: flex; justify-content: space-between; margin-top: 30px; }
        .summary div { background: #f8f9fa; padding: 15px; border-radius: 5px; width: 24%; text-align: center; }
        .summary h3 { margin-bottom: 5px; }
        .footer { margin-top: 40px; text-align: center; font-size: 0.8rem; color: #888; }
    </style>
</head>
<body>
    <h1>PehliONE Financial Report</h1>
    <h2>{{ date|date('d.m.Y') }}</h2>

    <div class="summary">
        <div><h3>{{ totalUsers }}</h3><p>Users</p></div>
        <div><h3>{{ totalProducts }}</h3><p>Products</p></div>
        <div><h3>{{ totalOrders }}</h3><p>Orders</p></div>
        <div><h3>{{ totalRevenue|number_format(2) }} ₺</h3><p>Total Revenue</p></div>
    </div>

    <h2>Top Categories</h2>
    <table>
        <thead>
            <tr><th>Category</th><th>Revenue (₺)</th></tr>
        </thead>
        <tbody>
            {% for cat in topCategories %}
                <tr><td>{{ cat.category }}</td><td>{{ cat.revenue|number_format(2) }}</td></tr>
            {% else %}
                <tr><td colspan="2" align="center">No data</td></tr>
            {% endfor %}
        </tbody>
    </table>

    <h2>Latest Orders</h2>
    <table>
        <thead>
            <tr><th>ID</th><th>Date</th><th>Status</th><th>Total (₺)</th></tr>
        </thead>
        <tbody>
            {% for o in latestOrders %}
                <tr>
                    <td>#{{ o.id }}</td>
                    <td>{{ o.createdAt|date('d.m.Y H:i') }}</td>
                    <td>{{ o.status }}</td>
                    <td>{{ o.total|number_format(2) }}</td>
                </tr>
            {% else %}
                <tr><td colspan="4" align="center">No orders found</td></tr>
            {% endfor %}
        </tbody>
    </table>

    <div class="footer">
        Generated automatically by PehliONE Admin Dashboard — {{ "now"|date("H:i") }}
    </div>
</body>
</html>
```

📘 **Açıklama:**

* PDF sade, profesyonel bir tablo yapısı kullanıyor.
* Her raporda tarih ve saat otomatik görünür.
* `DejaVu Sans` fontu Türkçe karakterleri destekler (UTF-8).

---

# 🧮 ADIM 5 — “Download PDF” butonu ekle

`templates/dashboard/admin.html.twig` içine, başlık altına ekleyelim:

```twig
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1>Admin Dashboard</h1>
  <a href="{{ path('app_admin_report_pdf') }}" class="btn btn-outline-danger">
    🧾 Download PDF Report
  </a>
</div>
```

---

# 🧠 ADIM 6 — Test Et

```bash
symfony server:start -d
```

Tarayıcıda:

👉 [http://localhost:8000/admin](http://localhost:8000/admin)

Ve “🧾 Download PDF Report” butonuna tıkla →

PDF dosyan otomatik olarak inecek:

> `dashboard_report.pdf`

---

# ✅ SONUÇ

| Özellik                   | Durum | Açıklama                         |
| -------------------------- | ----- | ---------------------------------- |
| PDF Raporlama              | ✅    | `app_admin_report_pdf`rotasıyla |
| Twig’den PDF render       | ✅    | Dompdf ile HTML → PDF             |
| Türkçe karakter desteği | ✅    | DejaVu Sans font                   |
| Toplam istatistikler       | ✅    | Users, Orders, Products, Revenue   |
| Tablo & tarih bilgisi      | ✅    | Son siparişler ve kategoriler     |

---

# 🔮 SONRAKİ ADIMLAR (isteğe bağlı)

1. **PDF’e logo & renkli tema** eklemek 🎨
2. **Grafikleri (Chart.js)** SVG olarak PDF’e dahil etmek 📊
3. **Planlı PDF raporu** — her sabah maille PDF otomatik gönderimi ✉️
4. **Rapor parametreleri** — tarih aralığı (örneğin: son 7 gün, 1 ay)

---
