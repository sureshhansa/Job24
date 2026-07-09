# JobPortal — PHP 8.2 + MySQL Job Board

A complete, production-ready, **Indeed-style job portal** built in **core PHP (no framework)**.
Bootstrap 5 frontend, AdminLTE 3 admin dashboard, SEO-friendly URLs, shared-hosting compatible.

---

## Features

- Modern Indeed-style responsive UI (Bootstrap 5)
- AdminLTE 3 admin dashboard
- Admin login (separate session from candidates)
- Candidate registration / login / logout
- Resume upload (PDF / DOC / DOCX)
- **Internal apply** (login required, stores application + resume)
- **External apply** (direct redirect to employer URL)
- Job CRUD with featured flag, draft/publish toggle, deadlines, salary range
- Categories management (with icons)
- Companies management (with logo upload)
- Candidate dashboard + application tracking
- Applications management with status workflow (pending → reviewed → shortlisted → rejected → hired)
- SEO-friendly URLs (`/job/senior-php-developer`, `/category/software-development`, `/company/acme-technologies`)
- CSRF protection on all forms, prepared statements everywhere, secure file uploads
- Zero Composer dependencies — just upload and go

---

## Requirements

- PHP **8.2+** (uses typed properties, `never` return type, `str_starts_with`)
- MySQL 5.7+ / MariaDB 10.3+
- Apache with `mod_rewrite` (for clean URLs)

---

## Folder structure

```
job-portal/
├── .htaccess              Front-controller rewrite + hardening
├── index.php              Router / front controller (public + candidate)
├── admin.php              Admin controllers (included by index.php)
├── database.sql           Schema + seed data
├── reset_passwords.php    One-time demo password setup (DELETE after use)
├── config/
│   ├── config.php         Constants, paths, session bootstrap
│   └── database.php        PDO connection + query helpers
├── includes/
│   ├── helpers.php         e(), url(), slugify(), pagination, formatters
│   ├── csrf.php            CSRF token + verification
│   ├── auth.php            User/admin guards + secure file upload
│   └── flash.php           Flash messages
├── views/
│   ├── partials/           header, footer, plain layout, job_card
│   ├── home.php jobs.php job_single.php category.php companies.php company.php errors.php
│   ├── auth/               login, register
│   ├── candidate/          dashboard, profile, applications, _nav
│   └── admin/
│       ├── partials/       header, sidebar, footer, auth
│       ├── login.php dashboard.php
│       ├── jobs/           index, form
│       ├── categories/     index
│       ├── companies/      index, form
│       ├── applications/   index
│       └── users/          index
├── uploads/
│   ├── .htaccess           Blocks script execution
│   ├── resumes/            Uploaded resumes
│   └── logos/              Company logos
└── assets/
    ├── css/style.css
    └── js/main.js
```

---

## Installation (local — XAMPP / WAMP / MAMP)

1. Copy the `job-portal` folder into your web root (e.g. `htdocs/job-portal`).
2. Create a database and import the schema:
   - Open **phpMyAdmin** → create database `job_portal` (utf8mb4).
   - Import `database.sql`.
3. Edit `config/config.php` and set your DB credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'job_portal');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```
4. Make `uploads/resumes` and `uploads/logos` writable (already 0755).
5. **Set the demo passwords**: visit
   `http://localhost/job-portal/reset_passwords.php` once, then **delete that file**.
6. Open `http://localhost/job-portal/`.

---

## Installation (shared hosting / cPanel)

1. Upload the contents of `job-portal/` to `public_html/` (or a subfolder).
2. In cPanel → **MySQL Databases**, create a database + user, assign privileges.
3. cPanel → **phpMyAdmin** → select the DB → **Import** `database.sql`.
4. Edit `config/config.php` with the cPanel DB name/user/password.
5. Visit `https://yourdomain.com/reset_passwords.php` once, then delete it.
6. Ensure `mod_rewrite` is enabled (it is on most cPanel hosts). The included
   `.htaccess` handles clean URLs and auto-detects subfolder installs.
7. For production, set `APP_ENV` to `'production'` in `config/config.php`.

---

## Default logins

| Role       | URL              | Email                  | Password   |
|------------|------------------|------------------------|------------|
| Admin      | `/admin/login`   | admin@jobportal.test   | `Admin@123`|
| Candidate  | `/login`         | john@example.com       | `User@123` |

> `database.sql` now ships with **real bcrypt hashes**, so these work immediately
> after import. If you imported an older copy and get "Invalid credentials",
> run **`fix_login.sql`** once in phpMyAdmin (or visit `/reset_passwords.php`).

---

## URL map (SEO friendly)

| Path                              | Purpose                          |
|-----------------------------------|----------------------------------|
| `/`                               | Home + search                    |
| `/jobs?q=&location=&type=&category=` | Job search / listing          |
| `/job/{slug}`                     | Single job + apply               |
| `/category/{slug}`                | Jobs in a category               |
| `/companies`                      | Company directory                |
| `/company/{slug}`                 | Single company                   |
| `/register`, `/login`, `/logout`  | Candidate auth                   |
| `/apply/{jobId}` (POST)           | Internal application             |
| `/go/{jobId}`                     | External apply redirect          |
| `/dashboard`, `/profile`, `/applications` | Candidate area           |
| `/admin/...`                      | Admin dashboard + CRUD           |

---

## Security notes

- All queries use **PDO prepared statements**.
- All output escaped via `e()` (htmlspecialchars).
- **CSRF tokens** on every POST form, verified in the front controller.
- Uploads validated by extension + size; `uploads/.htaccess` disables script execution.
- Passwords hashed with `password_hash()` (bcrypt).
- Sessions use `HttpOnly`, `SameSite=Lax`, and `Secure` over HTTPS; ID regenerated on login.
- **Delete `reset_passwords.php` after first use.**

---

## Customization

- Colors/branding: `assets/css/style.css` (`--jp-primary`) and `SITE_NAME` in `config/config.php`.
- Jobs per page: `PER_PAGE` in `config/config.php`.
- Upload limits / allowed types: `MAX_RESUME_SIZE`, `ALLOWED_RESUME_EXT`, etc. in `config/config.php`.
