# Objective
Build a production-ready vanilla PHP web application with authentication and a dashboard.
All work should be implemented by autonomous Codex workers launched by GRIND.

## Project bootstrap and routing foundation
Labels: e2e,php,foundation
State: open

Create a minimal vanilla PHP app structure with a front controller and route handling.
Requirements:
- PHP 8.2+ compatibility.
- `public/` web root with `index.php`.
- `app/` for core logic.
- `storage/` for runtime files (ignored in git where needed).
- Clean separation for auth, database, and dashboard modules.
- Add a concise `README.md` with local run instructions.

## Authentication and session hardening
Labels: e2e,php,auth,security
State: open

Implement auth system with register/login/logout and secure session practices.
Requirements:
- Password hashing with `password_hash`/`password_verify`.
- CSRF token validation on all POST forms.
- Session fixation mitigation on login.
- Basic input validation and safe error messages.
- Access control middleware for protected routes.

## Data layer and schema
Labels: e2e,php,database
State: open

Implement SQLite-backed data layer for users and dashboard records.
Requirements:
- Migrations/init setup script or boot-time table creation.
- Repository/helper functions for CRUD.
- No SQL injection vulnerabilities (prepared statements only).
- Add one seed workflow for local demo data.

## Dashboard features and UX
Labels: e2e,php,dashboard,ui
State: open

Build authenticated dashboard screens.
Requirements:
- Dashboard home with summary cards.
- Profile view/update page.
- Recent activity panel from DB records.
- Responsive styles using plain CSS (no frameworks).
- Error/success flash messaging.

## Testing, linting, and CI workflow
Labels: e2e,php,quality,ci
State: open

Add deterministic quality checks and CI workflow.
Requirements:
- Add PHP syntax lint check script.
- Add at least one lightweight integration test script (CLI-driven is acceptable).
- GitHub Actions workflow named exactly `lint` and `test`.
- CI must run on pull requests and pushes to main.

## Security and operational docs
Labels: e2e,php,docs,security
State: open

Document operating and security practices.
Requirements:
- Threat model section in docs for auth/session/data risks.
- Local setup + troubleshooting steps.
- Secrets handling rules and `.env.example` if needed.
- Deployment notes for a basic Linux VM.
