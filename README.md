# Riya Portfolio

Plain PHP site with a landing page, IP-based page-view tracking, a contact form
that emails a notification and saves to the database, and an admin panel
(`/login`) to review page visits (with resolved location) and messages.

## Stack

- Plain PHP 8.1+, PDO/MySQL — no framework
- [phpmailer/phpmailer](https://github.com/PHPMailer/PHPMailer) for SMTP email
- [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) for `.env` loading
- IP → location: [ip-api.com](https://ip-api.com) resolves the IP to
  coordinates, then [Nominatim](https://nominatim.org) reverse-geocodes those
  coordinates into a readable address. (Nominatim itself has no IP lookup —
  it only geocodes addresses/coordinates.) Every IP is resolved **once** and
  cached in `ip_locations`; lookups happen lazily when you view **Page
  Visits** in the admin panel, never on real visitor traffic.

## Project layout

```
public/         <- set this folder as the web/document root
  index.php     <- landing page (records page views, has the contact form)
  contact.php   <- handles the contact form POST
  login.php     <- /login
  setup.php     <- /setup — one-time admin account creation
  admin/        <- page-visits.php, messages.php (require login)
  assets/       <- css/js (swap in the real design here)
config/         <- config.php (loads .env)
src/            <- Database, Auth, Mailer, GeoLocation, PageViewTracker, helpers
database/       <- schema.sql
vendor/         <- committed, so no Composer/SSH access is needed on the server
```

`config/`, `src/`, `database/`, and the project root all carry a `.htaccess`
that denies direct access, in case the whole repo (not just `public/`) ends
up as the web root.

## Local setup

1. `composer install` (already done if you're reading this from the repo as-is)
2. Copy `.env.example` to `.env` and fill in real DB + SMTP values
3. Create the database and import the schema:
   ```
   mysql -u youruser -p yourdb < database/schema.sql
   ```
4. Point your web server's document root at `public/` (or use PHP's built-in
   server for quick testing: `php -S localhost:8000 -t public`)
5. Visit `/setup` to create the first admin account, then `/login`

## Deploying to cPanel

1. **Database**: cPanel → *MySQL® Databases* → create a database and a user,
   add the user to the database with all privileges. Put those credentials
   in `.env` (`DB_HOST` is almost always `localhost` on cPanel).
2. **Import schema**: cPanel → *phpMyAdmin* → select your database → *Import*
   → upload `database/schema.sql`.
3. **Upload files**: upload the whole project (e.g. via Git Version Control
   in cPanel, or File Manager/FTP) to a folder such as
   `/home/youruser/riya-portfolio` — **not** directly into `public_html`.
4. **Set the document root to `public/`**:
   - If this is an addon domain or subdomain: cPanel → *Domains* → set its
     *Document Root* to `riya-portfolio/public`.
   - If it must live at the account's main domain and you can't change the
     document root, ask your host to point it at the `public/` subfolder, or
     use a subdomain instead — don't serve the project root directly, since
     that would (defense-in-depth `.htaccess` aside) put `.env` and app code
     on a real web-accessible path.
5. **Create `.env`**: copy `.env.example` to `.env` in the project root (next
   to `composer.json`, not inside `public/`) and fill in real DB and SMTP
   values.
6. **Gmail SMTP**: use a Google **App Password** (Google Account → Security →
   2-Step Verification → App passwords), not your normal Gmail password.
   `SMTP_HOST=smtp.gmail.com`, `SMTP_PORT=587`, `SMTP_SECURE=tls`.
7. Visit `https://yourdomain/setup` once to create the admin login, then
   `https://yourdomain/login`.
8. `vendor/` is already committed, so no Composer access is required on the
   server. If you do update dependencies, run `composer install` locally and
   commit the updated `vendor/` folder.

## Page-view tracking behavior

Each request to `/` checks the most recent `page_views` row for that visitor's
IP. If there isn't one, or it's older than `PAGE_VIEW_COOLDOWN_MINUTES`
(default 10), a new view is recorded; otherwise the refresh is ignored. This
is IP-based (not session/cookie-based), matching "same IP within 10 minutes
doesn't count again."
