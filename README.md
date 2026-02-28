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
