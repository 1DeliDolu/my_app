
# 📊 Dashboard – Analytics & Statistics

> This section provides visual insights into the  **store’s performance** , including user growth, sales metrics, and product activity.
>
> It uses **Chart.js** via Symfony ImportMap or CDN for live data visualization.

---

## 🧭 Overview

The **Admin Dashboard** consolidates the main KPIs:

* **Total Users** 👥
* **Orders & Revenue** 💰
* **Top Selling Products** 🏆
* **Sales by Category** 🧩
* **Monthly Revenue Trends** 📈

---

## 🧩 Folder Structure

```
templates/
└─ admin/
   ├─ dashboard.html.twig     # main dashboard view
   └─ _charts/
      ├─ _sales_overview.html.twig
      ├─ _revenue_trend.html.twig
      ├─ _top_products.html.twig
      └─ _category_distribution.html.twig
```

---

## 🧮 Example: Dashboard Layout

```twig
{% extends 'base.html.twig' %}
{% block title %}Admin Dashboard{% endblock %}

{% block body %}
<div class="container py-4">
  <h1 class="h3 mb-4 fw-bold text-primary">📊 Admin Dashboard</h1>

  <div class="row g-4">
    <div class="col-md-6">
      {% include 'admin/_charts/_sales_overview.html.twig' %}
    </div>
    <div class="col-md-6">
      {% include 'admin/_charts/_revenue_trend.html.twig' %}
    </div>
    <div class="col-lg-6">
      {% include 'admin/_charts/_top_products.html.twig' %}
    </div>
    <div class="col-lg-6">
      {% include 'admin/_charts/_category_distribution.html.twig' %}
    </div>
  </div>
</div>
{% endblock %}
```

---

## 💹 Example: Sales Overview Chart

```twig
<div class="card shadow-sm border-0">
  <div class="card-body">
    <h5 class="card-title mb-3">Sales Overview</h5>
    <canvas id="salesOverviewChart" height="150"></canvas>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const ctx = document.getElementById('salesOverviewChart');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
      datasets: [{
        label: 'Orders',
        data: [120, 190, 170, 220, 300, 250, 400],
        borderWidth: 1,
        backgroundColor: 'rgba(54, 162, 235, 0.6)',
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
      },
      scales: {
        y: { beginAtZero: true },
      },
    }
  });
});
</script>
```

---

## 📈 Example: Monthly Revenue Trend

```twig
<div class="card shadow-sm border-0">
  <div class="card-body">
    <h5 class="card-title mb-3">Monthly Revenue</h5>
    <canvas id="revenueChart" height="150"></canvas>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const ctx = document.getElementById('revenueChart');
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
      datasets: [{
        label: 'Revenue ($)',
        data: [2000, 3000, 2500, 4000, 4500, 5000],
        borderColor: 'rgba(75, 192, 192, 1)',
        tension: 0.3,
        fill: true,
        backgroundColor: 'rgba(75, 192, 192, 0.1)',
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'top' },
      },
    }
  });
});
</script>
```

---

## 🧠 Example: Category Distribution (Pie Chart)

```twig
<div class="card shadow-sm border-0">
  <div class="card-body">
    <h5 class="card-title mb-3">Sales by Category</h5>
    <canvas id="categoryChart" height="150"></canvas>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const ctx = document.getElementById('categoryChart');
  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['Electronics', 'Books', 'Fashion', 'Home', 'Toys'],
      datasets: [{
        label: 'Sales',
        data: [300, 150, 200, 120, 90],
        backgroundColor: [
          '#36A2EB',
          '#FF6384',
          '#FFCE56',
          '#4BC0C0',
          '#9966FF',
        ],
      }]
    },
    options: {
      plugins: {
        legend: { position: 'bottom' },
      }
    }
  });
});
</script>
```

---

## 🏆 Example: Top Selling Products

```twig
<div class="card shadow-sm border-0">
  <div class="card-body">
    <h5 class="card-title mb-3">Top Selling Products</h5>
    <ul class="list-group list-group-flush small">
      {% for product in topProducts %}
        <li class="list-group-item d-flex justify-content-between align-items-center">
          {{ product.name }}
          <span class="badge bg-success rounded-pill">{{ product.salesCount }} sold</span>
        </li>
      {% else %}
        <li class="list-group-item text-muted">No sales data yet.</li>
      {% endfor %}
    </ul>
  </div>
</div>
```

---

## ⚙️ Chart.js Installation (Symfony ImportMap)

If you see this error:

> "The 'chart.js' vendor asset is missing."

Run:

```bash
php bin/console importmap:require chart.js
```

Or, use a CDN fallback in your `base.html.twig`:

```html
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
```

---

## 🧾 Data Sources

These charts pull their data from  **Doctrine Repositories** :

| Metric         | Repository Method                        | Example Output                         |
| -------------- | ---------------------------------------- | -------------------------------------- |
| Total Orders   | `OrderRepository::countAll()`          | `154`                                |
| Total Revenue  | `OrderRepository::getTotalRevenue()`   | `12450.50`                           |
| Sales by Month | `OrderRepository::getMonthlyRevenue()` | `{ 'Jan': 2400, 'Feb': 3200 }`       |
| Top Products   | `ProductRepository::findTopSelling(5)` | `[ {name:'Laptop', sales:120}, … ]` |

---

## 🧭 Example Controller Snippet

```php
#[Route('/admin/dashboard', name: 'app_admin_dashboard')]
public function dashboard(
    OrderRepository $orders,
    ProductRepository $products,
    UserRepository $users
): Response {
    return $this->render('admin/dashboard.html.twig', [
        'totalOrders' => $orders->countAll(),
        'totalUsers' => $users->countAll(),
        'revenueData' => $orders->getMonthlyRevenue(),
        'topProducts' => $products->findTopSelling(5),
    ]);
}
```

---

### ✅ Summary

| Area            | Chart Type | Goal                |
| --------------- | ---------- | ------------------- |
| Daily Sales     | Bar        | Show order volume   |
| Monthly Revenue | Line       | Show growth trend   |
| Category Split  | Doughnut   | Category popularity |
| Top Products    | List / Bar | Best sellers        |

---
