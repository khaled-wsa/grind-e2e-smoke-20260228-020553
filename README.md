This repository is used for end-to-end GRIND validation.

## Local run

Requires PHP 8.2+.

```bash
php -S 127.0.0.1:8000 -t public
```

Open `http://127.0.0.1:8000`.

## Implemented auth routes

- `GET /register`, `POST /register`
- `GET /login`, `POST /login`
- `POST /logout`
- `GET /dashboard` (requires authenticated session)

Security baseline included:

- `password_hash`/`password_verify` for stored credentials.
- CSRF token validation on all POST forms.
- `session_regenerate_id(true)` on login (session fixation mitigation).
- Basic input validation and generic auth failure messages.

## Data storage

- SQLite database file: `storage/app.sqlite`.
- Schema is created automatically at boot.
- Tables: `users` and `dashboard_records`.

## Seed demo data

```bash
php scripts/seed_demo.php
```

Creates a demo user (`demo@example.com`) and sample dashboard activity records.
