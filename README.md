# 🛍️ PehliONE – Symfony 7.3 E-Commerce Platform

> A full-featured **Symfony 7.3** e-commerce demo – from browsing and cart to checkout and order confirmation.
>
> Includes **user roles** , **address management** , and **transactional emails** — designed for modularity and scalability.

---

## 🚀 Features

- 🏠 **Homepage** with category filters and dynamic product listings
- 🛒 **Cart system** (session-based): add, update, remove, and clear items
- 💳 **Checkout flow** with address selection, order summary, and _simulated payment_
- ✉️ **Email verification** & welcome email on registration
- 👥 **Role-based access control:** Admin / Employee / Customer
- ⚙️ **Product CRUD management** with category relations (admin only)
- 📦 **Customer area:** address book & order history
- 🔔 **AJAX interactions** (cart badge, product add, modal confirmation)
- 📈 **Admin dashboard** with live statistics (via Chart.js)

---

## 🧰 Tech Stack

| Layer        | Technology                                    |
| ------------ | --------------------------------------------- |
| **Backend**  | PHP ≥ 8.2 · Symfony 7.3 · Doctrine ORM · Twig |
| **Frontend** | Symfony AssetMapper · Stimulus · Bootstrap 5  |
| **Database** | MySQL 8 / PostgreSQL (via Docker)             |
| **Mailing**  | Symfony Mailer · Mailtrap (sandbox)           |
| **Testing**  | PHPUnit 12 · Faker (for seed data)            |

---

## ⚙️ Prerequisites

- PHP 8.2 + extensions: `ctype`, `iconv`, `pdo_mysql` or `pdo_pgsql`
- Composer
- Relational database (MySQL 8 by default)
- Symfony CLI (recommended)
- _(optional)_ Node.js / NPM for advanced front-end builds

> 💡 The Symfony source lives in `my_app/`.
>
> All commands below assume you are inside that directory.

---

## 🏗️ Installation & Setup

### 1️⃣ Install dependencies

```bash
cd my_app
composer install
```

### 2️⃣ Configure environment

Create your local environment file:

```bash
cp .env .env.local
```

Then edit `.env.local`:

```dotenv
# Database (MySQL)
DATABASE_URL="mysql://user:pass@127.0.0.1:3306/app?serverVersion=8.0.32&charset=utf8mb4"

# Mailtrap sandbox (SMTP)
MAILER_DSN="smtp://<MAILTRAP_USER>:<MAILTRAP_PASS>@sandbox.smtp.mailtrap.io:2525"
```

### 3️⃣ Create and migrate the database

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate -n
```

### 4️⃣ Load demo fixtures

```bash
php bin/console doctrine:fixtures:load -n
```

### 5️⃣ _(Optional)_ Start via Docker

```bash
docker compose up -d database
```

---

## 🧑‍💻 Development

### Local web server

```bash
symfony server:start -d
# or
php -S localhost:8000 -t public
```

### Asset compilation

```bash
php bin/console asset-map:compile      # use --watch for live reload
```

### Mail testing

E-mails are sent through **Mailtrap** — customize the `MAILER_DSN` if needed.

---

## 🔑 Demo Accounts

| E-Mail              | Password      | Role          |
| ------------------- | ------------- | ------------- |
| `admin@shop.com`    | `admin123`    | ROLE_ADMIN    |
| `employee@shop.com` | `employee123` | ROLE_EMPLOYEE |
| `customer@shop.com` | `customer123` | ROLE_CUSTOMER |

---

## 🧭 Project Structure

```
my_app/
├─ assets/        # Stimulus controllers, styles, asset mapper
├─ migrations/    # Doctrine migrations
├─ public/        # Web root (index.php), static assets
├─ src/           # Controllers, Services, Entities, Repositories, Forms
├─ templates/     # Twig templates (pages, layouts, emails)
├─ tests/         # PHPUnit tests (Cart, Category, etc.)
└─ composer.json
```

---

## 🧩 Common Commands

```bash
# List routes
php bin/console debug:router

# Validate database schema
php bin/console doctrine:schema:validate

# Clear cache
php bin/console cache:clear

# Run test suite
php bin/phpunit
```

---

## 🏁 Production Notes

- Update `.env.local`:
  ```dotenv
  APP_ENV=prod
  APP_SECRET=<your_random_secret>
  ```
- Provide **real SMTP credentials** (`MAILER_DSN`)
- Compile optimized assets:
  ```bash
  php bin/console asset-map:compile --env=prod
  ```
- Enable **HTTPS (TLS)** and regular **database backups**

---

## 🧠 Troubleshooting

| Problem                                | Ursache / Lösung                                                            |
| -------------------------------------- | --------------------------------------------------------------------------- |
| ❌*SQLSTATE[HY000] [1045]*             | DB-URL oder Benutzerrechte prüfen                                           |
| ⚠️*Column ‘created_at’ cannot be null* | `createdAt`/`updatedAt`im Entity-Konstruktor initialisieren                 |
| 🧮*Invalid type decimal(10,2)*         | Nur `decimal`eingeben → Precision: 10, Scale: 2                             |
| 🧩*No command: make:service*           | Service manuell unter `src/Service/`anlegen – Symfony autowired automatisch |
| 📧*Mail not arriving*                  | `MAILER_DSN`in `.env.local`und Mailtrap-Inbox prüfen                        |

---

## 💳 Payment Flow (Simulated)

1. 🖱️ User clicks **Pay Now** → triggers `app_checkout_pay`
2. 🔒 Controller verifies ownership, simulates a 2-second delay
3. 💰 Order status → **Paid** , timestamps set, confirmation email sent
4. ✅ Redirects to success page with flash message
5. _(Ready to swap with Stripe / iyzico API integration later)_

---

## 📊 Admin Dashboard (optional extension)

- Visualized via **Chart.js (CDN)**
- Shows total sales, orders, and user activity
- Replace the fake data provider with real queries using Doctrine repositories

---

## 🧾 License

MIT License — free for personal & commercial use.

---

### ✨ Authors & Credits

Created with ❤️ using **Symfony 7.3**

Built for learning, prototyping, and demonstration purposes.

---

## Customer Homepage

![1762118383674](image/README/1762118383674.png)

---

## Admin Dashboard

![1762118485065](image/README/1762118485065.png)

---

## Admin Dashboard

![1762118530736](image/README/1762118530736.png)

![1762118567362](image/README/1762118567362.png)

![1762120175036](image/README/1762120175036.png)

---

## User Management

![1762118598664](image/README/1762118598664.png)

---

## Products

![1762118674552](image/README/1762118674552.png)

---

## Product Details

![1762118799366](image/README/1762118799366.png)

![1762118824864](image/README/1762118824864.png)

![1762118844087](image/README/1762118844087.png)

![1762118959437](image/README/1762118959437.png)
