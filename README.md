

# PehliONE – Symfony 7.3 E-Commerce

> Beispiel-Onlineshop auf Basis von  **Symfony 7.3** : vom Stöbern über den **Warenkorb** bis zur **Bestellung** – inkl.  **Rollenverwaltung** , **Adressverwaltung** und  **transaktionalen E-Mails** .

---

## Inhaltsverzeichnis

* [Funktionsumfang](https://chatgpt.com/c/690666ba-a3c4-8330-8196-5067f1b03c12#funktionsumfang)
* [Technologie-Stack](https://chatgpt.com/c/690666ba-a3c4-8330-8196-5067f1b03c12#technologie-stack)
* [Voraussetzungen](https://chatgpt.com/c/690666ba-a3c4-8330-8196-5067f1b03c12#voraussetzungen)
* [Installation &amp; Ersteinrichtung](https://chatgpt.com/c/690666ba-a3c4-8330-8196-5067f1b03c12#installation--ersteinrichtung)
* [Entwicklung starten](https://chatgpt.com/c/690666ba-a3c4-8330-8196-5067f1b03c12#entwicklung-starten)
* [Demo-Zugänge](https://chatgpt.com/c/690666ba-a3c4-8330-8196-5067f1b03c12#demo-zug%C3%A4nge)
* [Projektstruktur](https://chatgpt.com/c/690666ba-a3c4-8330-8196-5067f1b03c12#projektstruktur)
* [Nützliche Befehle](https://chatgpt.com/c/690666ba-a3c4-8330-8196-5067f1b03c12#n%C3%BCtzliche-befehle)
* [Hinweise für Produktion](https://chatgpt.com/c/690666ba-a3c4-8330-8196-5067f1b03c12#hinweise-f%C3%BCr-produktion)
* [Troubleshooting](https://chatgpt.com/c/690666ba-a3c4-8330-8196-5067f1b03c12#troubleshooting)

---

## Funktionsumfang

* Startseite mit **Kategoriefilter** und **dynamischer Produktliste**
* Produktdetailseiten mit **Warenkorb-Integration** (AJAX-Feedback, Mengensteuerung)
* Sitzungsgestützter  **Warenkorb** : Aktualisieren, Entfernen, Leeren
* **Checkout** : Adressauswahl, Bestellübersicht,  **simulierter Bezahlvorgang** , E-Mail-Bestätigung
* **Benutzerregistrierung** inkl. E-Mail-Verifizierung & Willkommensmail
* **Rollen- & Nutzerverwaltung** (Admin / Employee / Customer)
* **Produktverwaltung (CRUD)** inkl. Kategoriezuordnung
* **Persönliche Adressverwaltung** und **Bestellübersicht** für eingeloggte Kund:innen

---

## Technologie-Stack

* **Backend:** PHP ≥ 8.2,  **Symfony 7.3** , Doctrine ORM, Twig
* **Frontend:** Symfony **AssetMapper** & **Stimulus** (für Interaktionen)
* **Datenbank:** MySQL 8 / PostgreSQL (per Docker verfügbar), Doctrine Migrations & Fixtures
* **Mailing:** Symfony Mailer mit **Mailtrap** (vorkonfigurierbar)
* **Tests:** PHPUnit 12, Faker für Seed-Daten

---

## Voraussetzungen

* PHP 8.2 (CLI) inkl. Extensions: `ctype`, `iconv`, `pdo_mysql` **oder** `pdo_pgsql`
* Composer
* Relationale Datenbank (Standard:  **MySQL 8** ; optional Docker Compose für PostgreSQL)
* Symfony CLI (empfohlen) oder alternativer Webserver
* Optional: Node.js/NPM (falls zusätzliche Tools benötigt werden)

> **Hinweis:** Der Symfony-Code liegt unter `my_app/`. Befehle unten gehen von diesem Verzeichnis aus.

---

## Installation & Ersteinrichtung

1. **Abhängigkeiten installieren**

```bash
cd my_app
composer install
```

2. **Umgebungsvariablen setzen** (`.env.local` erstellen)

```bash
cp .env .env.local
# In .env.local: DATABASE_URL, MAILER_DSN etc. anpassen
```

Beispiele:

```dotenv
# MySQL
DATABASE_URL="mysql://user:pass@127.0.0.1:3306/app?serverVersion=8.0.32&charset=utf8mb4"

# Mailtrap (SMTP)
MAILER_DSN="smtp://<MAILTRAP_USER>:<MAILTRAP_PASS>@sandbox.smtp.mailtrap.io:2525"
```

3. **Datenbank vorbereiten**

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate -n
```

4. **Beispieldaten laden** (Kategorien, Produkte, Demo-User)

```bash
php bin/console doctrine:fixtures:load -n
```

5. **(Optional) Datenbank via Docker starten**

```bash
docker compose up -d database
```

---

## Entwicklung starten

* **Webserver:**
  ```bash
  symfony server:start -d
  # oder
  php -S localhost:8000 -t public
  ```
* **Assets kompilieren:**
  ```bash
  php bin/console asset-map:compile      # --watch für Live-Reload
  ```
* **E-Mails:** Standardmäßig über **Mailtrap** (per `MAILER_DSN`)

---

## Demo-Zugänge

| E-Mail                                     | Passwort    | Rolle         |
| ------------------------------------------ | ----------- | ------------- |
| [admin@shop.com](mailto:admin@shop.com)       | admin123    | ROLE_ADMIN    |
| [employee@shop.com](mailto:employee@shop.com) | employee123 | ROLE_EMPLOYEE |
| [customer@shop.com](mailto:customer@shop.com) | customer123 | ROLE_CUSTOMER |

---

## Projektstruktur

```
my_app/
├─ assets/        # AssetMapper, Stimulus-Controller, Styles
├─ migrations/    # Doctrine-Migrationen
├─ public/        # Webroot (index.php), ausgelieferte Assets
├─ src/           # Controller, Services, Entities, Repos, Forms
├─ templates/     # Twig (Seiten, Partials, E-Mails)
├─ tests/         # PHPUnit-Tests (Cart/Category etc.)
└─ composer.json
```

---

## Nützliche Befehle

```bash
# Routen ansehen
php bin/console debug:router

# DB-Schema prüfen
php bin/console doctrine:schema:validate

# Cache leeren
php bin/console cache:clear

# Tests ausführen
php bin/phpunit
```

---

## Hinweise für Produktion

* In `.env.local` mindestens setzen:
  ```dotenv
  APP_ENV=prod
  APP_SECRET=<zufälliger_geheimer_wert>
  ```
* **Echte SMTP/API-Zugangsdaten** hinterlegen (`MAILER_DSN`)
* **Assets** bündeln:
  ```bash
  php bin/console asset-map:compile --env=prod
  ```
* **SSL/TLS** aktivieren und **Backups** (DB, Assets) einrichten

---

## Troubleshooting

* **DB-Loginfehler (SQLSTATE[HY000] [1045])**

  → `DATABASE_URL` prüfen (User/Pass/Host/Port), DB-User-Rechte anpassen.
* **Spalten dürfen nicht NULL sein (Fixtures)**

  → In Entities Timestamps (z. B. `createdAt`, `updatedAt`) im Konstruktor setzen oder in Fixtures füllen.
* **`decimal(10,2)` Fehler beim Maker**

  → Typ nur `decimal` wählen; **Precision** `10`, **Scale** `2` im Prompt angeben.
* **Kein `make:service`**

  → Services einfach unter `src/Service/` anlegen; Symfony entdeckt sie automatisch (DI).
* **Mail kommt nicht an**

  → `MAILER_DSN` (Mailtrap) prüfen; in der Mailtrap-Inbox nachsehen.

---

### Zahlungsfluss (aktuell *fake* Simulation)

* Button **“Pay Now”** → Route `app_checkout_pay`
* Controller prüft Besitz, simuliert Zahlung (`sleep(2)`), setzt Status  **Paid** , sendet  **Bestätigungs-E-Mail** , leitet zur **Success-Seite** weiter
* Austauschbar gegen Stripe/iyzico – API-Calls können an derselben Stelle integriert werden

---
