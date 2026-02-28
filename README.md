This repository is used for end-to-end GRIND validation.

## Local Setup

Prerequisites:

- PHP `8.2+`.
- PHP extensions: `pdo_sqlite`, `sqlite3`, and `session`.
- Write access to the `storage/` directory.

Run locally:

```bash
php -S 127.0.0.1:8000 -t public
```

Open `http://127.0.0.1:8000`, register an account, and sign in.

Optional demo seed:

```bash
php scripts/seed_demo.php
```

Creates a demo user (`demo@example.com`) and sample dashboard activity records.

## Troubleshooting

- `could not find driver` when loading pages:
  Install/enable SQLite extensions for PHP (`pdo_sqlite` and `sqlite3`).
- Database or session files are not created:
  Ensure the process user can write to `storage/`.
- `Invalid form submission.` on POST actions:
  Refresh the form page and retry to get a fresh CSRF token.
- Session cookie not marked `Secure` in local HTTP:
  Expected on plain HTTP; use HTTPS in production.

## Implemented Auth Routes

- `GET /register`, `POST /register`
- `GET /login`, `POST /login`
- `POST /logout`
- `GET /dashboard` (requires authenticated session)

## Security Threat Model

### Auth Risks

- Credential theft from database exposure:
  Mitigated with `password_hash`/`password_verify` and rehash support.
- Account enumeration:
  Mitigated with generic login failure messaging.
- Brute-force login attempts:
  Not implemented in-app; apply rate limiting and IP controls at the reverse proxy/WAF layer.

### Session Risks

- Session fixation:
  Mitigated with `session_regenerate_id(true)` on login and after logout.
- Session theft via client-side script access:
  Mitigated with `HttpOnly` cookies and strict session settings.
- Cross-site request forgery:
  Mitigated with CSRF tokens validated on all POST routes.
- Cookie interception:
  `Secure` is only enabled under HTTPS; production must terminate TLS.

### Data Risks

- SQL injection:
  Mitigated with prepared statements for user and dashboard queries.
- Data confidentiality:
  SQLite file at `storage/app.sqlite` is sensitive and must be protected by filesystem permissions and backup controls.
- Data availability:
  Single-file SQLite requires routine backups and restore testing to avoid single-point data loss.

## Secrets Handling Rules

This app currently does not require runtime secrets to boot. If deployment adds env-based configuration, use `.env.example` as the template and keep real values only in untracked `.env` files.

- Never commit secrets, API keys, or credentials to git.
- Keep secret files readable only by the service account (for example `chmod 600`).
- Avoid printing secrets in logs, error pages, or process listings.
- Rotate secrets immediately if exposure is suspected.

## Deployment Notes (Basic Linux VM)

1. Install runtime packages (`php8.2`, `php8.2-fpm` or CLI runtime, `php8.2-sqlite3`, and `nginx` or `apache2`).
2. Deploy code under a dedicated service account (for example `/var/www/grind-e2e`).
3. Point the web server document root to the `public/` directory only.
4. Ensure `storage/` is writable by the service account and not web-browsable.
5. Configure HTTPS (LetsEncrypt or equivalent) so session cookies can be `Secure`.
6. Provide real environment values in `.env` (not committed), if used by your deployment.
7. Back up `storage/app.sqlite` regularly and test restore.
8. Smoke test: register, login, open `/dashboard`, and logout.

## Data Storage

- SQLite database file: `storage/app.sqlite`.
- Schema is created automatically at boot.
- Tables: `users` and `dashboard_records`.
