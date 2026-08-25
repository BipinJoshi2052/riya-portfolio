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

Most cPanel accounts won't let you repoint the **primary domain's** document
root away from `public_html` via the Domains UI (only addon domains /
subdomains support that). So instead of changing the document root, this repo
ships a [`.cpanel.yml`](.cpanel.yml) deployment file that copies the right
pieces into place automatically:

- `public/*` → `public_html/`
- `src/`, `config/`, `vendor/`, `database/` → your home directory, one level
  above `public_html` — exactly where the app already expects them via
  `../vendor`, `../config`, `../src`, so **no code changes are needed**.

`.cpanel.yml` currently hardcodes `DEPLOYPATH=/home/riyaprad` — update that if
the cPanel username ever changes.

Steps:

1. **Database**: cPanel → *MySQL® Databases* → create a database and a user,
   add the user to the database with all privileges. You'll put these
   credentials in `.env` in step 5 (`DB_HOST` is almost always `localhost`).
2. **Import schema**: cPanel → *phpMyAdmin* → select your database → *Import*
   → upload `database/schema.sql`.
3. **Set up Git Version Control** (cPanel → *Git™ Version Control*):
   - If you already cloned into `/home/riyaprad/repositories/riya-portfolio`,
     you're set — that's the working copy `.cpanel.yml` deploys *from*.
   - Open the repo → **Pull or Deploy** tab → **Update from Remote** to pull
     the latest commit (which now includes `.cpanel.yml`).
4. **Deploy**: still on the **Pull or Deploy** tab, click **Deploy HEAD
   Commit**. This runs the tasks in `.cpanel.yml`, copying `public/*` into
   `public_html/` and `src/`, `config/`, `vendor/`, `database/` into
   `/home/riyaprad/`. Re-run this any time you pull new commits.
5. **Create `.env` on the server**: this file is intentionally never in git.
   Use cPanel *File Manager* (or Terminal, if available) to create
   `/home/riyaprad/.env` — i.e. in the home directory, as a sibling of the
   `config/`/`src/`/`vendor/` folders that step 4 just created, **not** inside
   `public_html/`. Copy the contents of `.env.example` and fill in the real
   DB credentials from step 1 plus SMTP values from step 6.
6. **Gmail SMTP**: use a Google **App Password** (Google Account → Security →
   2-Step Verification → App passwords), not your normal Gmail password.
   `SMTP_HOST=smtp.gmail.com`, `SMTP_PORT=587`, `SMTP_SECURE=tls`.
7. Visit `https://yourdomain/setup` once to create the admin login, then
   `https://yourdomain/login`.
8. `vendor/` is committed to git, so no Composer access is required on the
   server. If you do update dependencies, run `composer install` locally,
   commit the updated `vendor/` folder, then repeat steps 3–4.

If you'd rather avoid `.cpanel.yml` entirely, the alternative is creating a
subdomain (e.g. `app.yourdomain.com`) pointed at a folder whose document root
you set directly to `.../riya-portfolio/public` — cPanel does allow custom
document roots for subdomains/addon domains, just not the primary domain.

## Page-view tracking behavior

Each request to `/` checks the most recent `page_views` row for that visitor's
IP. If there isn't one, or it's older than `PAGE_VIEW_COOLDOWN_MINUTES`
(default 10), a new view is recorded; otherwise the refresh is ignored. This
is IP-based (not session/cookie-based), matching "same IP within 10 minutes
doesn't count again."
