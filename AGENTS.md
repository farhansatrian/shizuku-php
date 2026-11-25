# SHIZUKU Ecosystem Contract (PHP Edition — Shared with `shikas` & Future Apps)

This repository (`shizuku-php`) is the **Native PHP 8.1+ + SQLite** central identity, portfolio CMS, and database hub for the SHIZUKU ecosystem (`https://shizuku.sitezet.com` + `https://shikas.sitezet.com`).

## Stable API Endpoints (100% Parity with Next.js Edition)
- `POST /api/ecosystem/register` → `{ email, displayName?, password }` ⇒ `201 { user, token }` (`INVALID_EMAIL`, `SHORT_PASSWORD`, `EMAIL_EXISTS`)
- `POST /api/ecosystem/login` → `{ email, password }` ⇒ `200 { user, token }` (`ACCOUNT_NOT_FOUND`, `INVALID_CREDENTIALS`)
- `POST /api/ecosystem/logout` ⇒ `200 { ok: true }`
- `GET /api/ecosystem/me` ⇒ `200 { user | null }`
- `GET /api/ecosystem/google?redirect=<internal_path_or_allowed_origin_url>` & `GET /api/ecosystem/google/callback` → Google OAuth 2.0 + PKCE SSO; sets `shizuku_eco_session` cookie (with optional `ECOSYSTEM_COOKIE_DOMAIN=".sitezet.com"`) and appends `#shizuku_token=<jwt>` when redirecting to an allowlisted external app (`shikas`).
- `GET /api/shikas/store` & `PUT /api/shikas/store` → strict per-`userId` isolation (`401` if unauthenticated); v5 payload stores `accounts[]` (`ShikasSettings.accountsJson`), `settings.cycleStartDay` (`1..28`), and `transactions[]` supporting `income | expense | transfer`, `accountId`, `toAccountId`, `feeIdr`, and optional expense `reimburse` (`ShikasTransaction.reimburseJson`).
