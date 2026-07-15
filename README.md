# Digital Invoicing (Laravel)

Multi-tenant SaaS for creating digital invoice **drafts**, reviewing them, and posting to **FBR sandbox only** (DI API v1.12).

## Stack

- Laravel 13 + Breeze (Blade)
- SQLite by default (swap to MySQL/Postgres via `.env`)
- Tailwind + Day / Night / Auto theme
- Server-side FBR sandbox client (`validateinvoicedata_sb` + `postinvoicedata_sb`)

## Quick start

```bash
composer install
cp .env.example .env   # if needed
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install && npm run build
php artisan serve
```

Open http://127.0.0.1:8000 → **Register** (creates user + organization).

## Sandbox setup (per organization)

1. Log in as the org **owner**
2. Open **Org & FBR**
3. Fill seller NTN/CNIC, business name, province, address
4. Paste your **FBR sandbox Bearer token** (from PRAL)
5. Create a draft invoice with scenario **SN001** or **SN002**
6. **Validate (sandbox)** → fix errors → **Post to FBR sandbox**

Production FBR URLs are **not** wired in this version.

## Useful paths

| Path | Purpose |
|------|---------|
| `/dashboard` | Stats + recent invoices |
| `/invoices` | List / filter drafts & posts |
| `/invoices/create` | New draft |
| `/settings/organization` | Seller + sandbox token |

## Config

See `config/fbr.php` for sandbox URLs and `scenarioId` list.

Docs: [FBR DI API v1.12 PDF](https://download1.fbr.gov.pk/Docs/20257301172130815TechnicalDocumentationforDIAPIV1.12.pdf)

## Railway deploy

Set these **Variables** on the Railway service (required):

| Variable | Example |
|----------|---------|
| `APP_KEY` | Output of `php artisan key:generate --show` |
| `APP_URL` | `https://your-app.up.railway.app` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `true` temporarily to see errors, then `false` |
| `LOG_CHANNEL` | `stderr` |
| `NIXPACKS_PHP_VERSION` | `8.4` |
| `NPM_CONFIG_PRODUCTION` | `false` |

Optional for SQLite (default on this app): leave `DB_CONNECTION` unset or `sqlite`.

For Postgres on Railway, set on the **app** service:

| Variable | Value |
|----------|--------|
| `DB_CONNECTION` | `pgsql` |
| `DATABASE_URL` | copy from the Postgres service *(Laravel also accepts this; maps to `DB_URL`)* |

Do **not** set `DB_HOST=127.0.0.1` or `DB_DATABASE=database/database.sqlite` on the app.
