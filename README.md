# FreshMart — Premium Grocery & Fresh Produce E-Commerce (PHP / CodeIgniter 4)

An enquiry-based wholesale & retail grocery platform. Customers browse a premium
storefront and submit purchase enquiries instead of paying online; every enquiry
is emailed to the business and manageable from a full admin panel.

This is a 1:1 PHP port of the original Next.js application — identical UI and
functionality — built for cheap, dependency-free shared hosting (hPanel /
cPanel), using **CodeIgniter 4.6** and **MySQL**. No Composer, no Node.js and
no SSH are required on the server.

## Tech Stack

- **CodeIgniter 4.6** (framework bundled in `system/` — no Composer needed)
- **PHP 8.1+** · **MySQL / MariaDB**
- **Tailwind CSS v4** — precompiled to a static `public/assets/css/app.css`
- Vanilla-JS ports of all client interactivity (`public/assets/js/site.js`, `admin.js`)
- CodeIgniter Email library — SMTP enquiry emails (admin notification + customer acknowledgement)

## Features (identical to the original)

- Storefront: home (hero slider, categories, featured products, collection
  tabs, testimonials, animated counters), product listing with filters /
  sorting / grid-list view / pagination, category pages, product detail
  (gallery with hover zoom, weight options, related products, recently
  viewed), offers, about, contact, privacy, terms, 404.
- Enquiry modal ("Buy Now") with validation, quick-view dialog, wishlist
  (localStorage), live search suggestions, newsletter signup.
- Admin panel: dashboard analytics (14-day enquiry chart, top products),
  product CRUD (multi-image, badges, SEO fields), category & subcategory
  management, hero banner management, enquiry inbox (search, product/status/
  date filters, CSV export), newsletter subscribers, and settings (site
  identity, SEO defaults, SMTP, socials).
- SEO: per-page metadata & canonical URLs, Product JSON-LD, Open Graph &
  Twitter cards, `sitemap.xml`, `robots.txt`.

## Local Development

```bash
# 1. Create the database and point .env at it (defaults: root@localhost/freshmart)
mysql -u root -e "CREATE DATABASE freshmart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

# 2. Create the schema and seed it (categories, 42 products, banners, admin user)
php spark migrate
php spark db:seed FreshmartSeeder

# 3. Run
php spark serve --port 8080     # http://localhost:8080
```

### Admin Panel

- URL: `/admin`
- Login: `admin@freshmart.com` / `Admin@123` (change via `ADMIN_EMAIL` /
  `ADMIN_PASSWORD` in `.env` before seeding — and change it in production!)

### Enquiry Emails (SMTP)

Set SMTP credentials either in `.env` or in **Admin → Settings → Email (SMTP)**
(DB settings take precedence). With no SMTP host configured, emails are written
to the log (`writable/logs/`) and the enquiry is still stored — the flow never
breaks in dev.

### WhatsApp Order Alerts (Meta Cloud API)

Every new order enquiry can be sent to the owner's WhatsApp. Configure in
**Admin → Settings → WhatsApp Order Alerts** (or via `.env` fallbacks:
`WHATSAPP_ACCESS_TOKEN`, `PHONE_NUMBER_ID`, `WHATSAPP_NOTIFY_TO`,
`WHATSAPP_TEMPLATE_NAME`, `WHATSAPP_TEMPLATE_LANG`).

1. **Access token (required)** — In [Meta Business Settings](https://business.facebook.com/settings)
   → Users → System Users, create a system user, assign your WhatsApp app with
   the `whatsapp_business_messaging` permission, and generate a **permanent**
   token. (The app secret cannot send messages — it is only for webhooks.)

2. **Template (required for reliable delivery)** — WhatsApp only delivers
   business-initiated messages as pre-approved templates. In
   [WhatsApp Manager → Message templates](https://business.facebook.com/wa/manage/message-templates/)
   create a **Utility** template named `freshmart_order_alert`, language
   **English (en)**, with this body, then wait for approval (usually minutes):

   ```
   🛒 New FreshMart order enquiry from {{1}} (phone {{2}}). Product: {{3}}, quantity: {{4}}. Location: {{5}}. Delivery address: {{6}}. Preferred date: {{7}}. Notes: {{8}}
   ```

   If the template name is left empty, the alert is sent as plain text — which
   WhatsApp only delivers while you have an open 24-hour session (i.e. you
   messaged the business number from your phone within the last 24 hours).

3. **Test it** — `php spark whatsapp:test` sends a sample alert and prints the
   API response. Failures are also logged to `writable/logs/`; a failed
   WhatsApp send never blocks the customer's enquiry.

## Deployment on Shared Hosting (hPanel)

1. **Database** — In hPanel create a MySQL database + user, then open
   **phpMyAdmin** and import `database/freshmart.sql` (schema + full seed
   data, admin user included). Alternatively run `php spark migrate` +
   `php spark db:seed FreshmartSeeder` over SSH if your plan has it.

2. **Files** — Upload the whole project folder into `public_html/` (or use the
   File Manager and extract a zip). The root `.htaccess` routes all traffic
   into `public/` automatically, so no docroot change is needed.
   *(If you prefer, you can instead point the domain's document root at the
   `public/` folder — both layouts work.)*

3. **Configuration** — Edit `.env` on the server:

   ```
   CI_ENVIRONMENT = production
   app.baseURL = 'https://yourdomain.com/'
   database.default.hostname = localhost
   database.default.database = <your db name>
   database.default.username = <your db user>
   database.default.password = <your db password>
   ```

4. **PHP version** — Select PHP 8.1+ (8.2 recommended) in hPanel, with the
   `intl` and `mysqli` extensions enabled (they are by default on Hostinger).

5. **Permissions** — Make sure `writable/` is writable by PHP (755 usually
   suffices on hPanel; sessions, logs and cache live there).

6. **Go live** — Log in at `/admin`, change the admin password data if needed,
   and configure SMTP under **Admin → Settings** so enquiry emails are sent.

### Environment Variables (`.env`)

| Variable | Purpose |
| --- | --- |
| `CI_ENVIRONMENT` | `development` or `production` |
| `app.baseURL` | Canonical site URL (SEO, sitemap, emails) |
| `database.default.*` | MySQL connection settings |
| `SMTP_HOST/PORT/USER/PASS/FROM` | Fallback SMTP config (Admin → Settings takes precedence) |
| `ADMIN_NOTIFY_EMAIL` | Fallback enquiry notification recipient |
| `ADMIN_EMAIL` / `ADMIN_PASSWORD` | Seeded admin credentials (used by the seeder) |

## Rebuilding the CSS (only after editing views)

The stylesheet is precompiled — the server never needs Node. If you change
Tailwind classes in `app/Views/`, rebuild locally:

```bash
npm install          # once — installs the Tailwind CLI locally
npm run build:css    # writes public/assets/css/app.css
```

## Project Structure

```
.htaccess                     routes all traffic into public/ (shared hosting)
database/freshmart.sql        full schema + seed dump for phpMyAdmin import
assets-src/app.css            Tailwind v4 source (theme copied from the original)
app/
  Config/Routes.php           storefront, API and admin routes
  Config/LucideIcons.php      inline SVG data extracted from lucide-react
  Controllers/Site.php        storefront pages
  Controllers/Api.php         search, by-slugs, enquiry, contact, newsletter
  Controllers/Seo.php         sitemap.xml, robots.txt
  Controllers/Admin/          auth, dashboard, products, categories, banners,
                              enquiries (+CSV export), subscribers, settings
  Database/Migrations/        MySQL schema (mirrors the Prisma schema)
  Database/Seeds/             FreshmartSeeder + seed-data.json (42 products…)
  Filters/AdminAuth.php       session guard for /admin and admin APIs
  Helpers/site_helper.php     format_price, slugify, badges, buttons, icons…
  Libraries/                  Settings, Mailer (SMTP), ProductQuery
  Views/layouts/              site + admin shells (header, nav, footer, modals)
  Views/site/                 home, products, category, detail, offers, about…
  Views/partials/             product card, listing (filters/pagination), search
  Views/admin/                login, dashboard, products, categories, banners…
public/
  index.php                   front controller
  assets/css/app.css          compiled Tailwind stylesheet
  assets/js/site.js           storefront interactivity (vanilla JS)
  assets/js/admin.js          admin interactivity (vanilla JS)
system/                       CodeIgniter 4.6 framework (bundled)
writable/                     logs, cache, sessions (must be writable)
```
