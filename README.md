# shizuku-php (雫)

Zero-dependency **Native PHP 8.1+ + SQLite** edition of **shizuku (雫)** — designed to run effortlessly on standard **cPanel / Apache Shared Hosting** (`~10–25 MB RAM` per request, `0 MB` idle, zero Node.js, zero Composer, zero MySQL setup required) while keeping **100% identical UI/UX**, **5-tab Admin Studio**, **Google OAuth 2.0 + PKCE**, and the **exact v5 Ecosystem API contract** shared with **`shikas`**.

## Production Subdomain Setup (`shizuku.sitezet.com` + `shikas.sitezet.com`)

### 1. Upload `shizuku-php` to `shizuku.sitezet.com`
1. Create subdomain `shizuku.sitezet.com` in cPanel (PHP **8.1+** with `pdo_sqlite`, `curl`, `openssl`, `fileinfo`, `mbstring` enabled — all standard defaults).
2. Upload all files inside `shizuku-php/` to the document root of `shizuku.sitezet.com` (make sure hidden file `.htaccess` is uploaded!).
3. Copy `.env.example` to `.env` and fill in your production values:
   ```dotenv
   PUBLIC_BASE_URL="https://shizuku.sitezet.com"
   DATABASE_PATH="data/shizuku.db"
   UPLOAD_DIR="data/uploads"
   AUTH_SECRET="<output of openssl rand -hex 32>"
   ADMIN_USERNAME="farhan"
   ADMIN_EMAIL="farhansatria.07@gmail.com"
   ADMIN_PASSWORD="<strong-admin-password>"
   COOKIE_SECURE="true"
   ECOSYSTEM_ALLOWED_ORIGINS="https://shikas.sitezet.com"
   ECOSYSTEM_COOKIE_DOMAIN=".sitezet.com"
   GOOGLE_CLIENT_ID="<your-google-client-id>"
   GOOGLE_CLIENT_SECRET="<your-google-client-secret>"
   APP_ENV="production"
   ```
4. Ensure `data/` and `data/uploads/` are writable (`chmod 755` or `775`). Upload your media files (`data/uploads/*` including `bg-watercolor-pc.jpg` and `IMG_20210913_0002.webp`) — and if migrating local `data/shizuku.db`, flush WAL first via `php -r '$p=new PDO("sqlite:data/shizuku.db");$p->exec("PRAGMA wal_checkpoint(TRUNCATE);");'` before uploading so all latest rows are inside the single `.db` file!
5. Verify health endpoint: `https://shizuku.sitezet.com/api/health` ⇒ `{"status":"ok","app":"shizuku-php",...}`.

### 2. Deploy `shikas` to `shikas.sitezet.com` (Static SPA — Zero PHP/Node Needed!)
1. Build `shikas` (`../personal-cashflow`) with:
   ```bash
   VITE_SHIZUKU_URL="https://shizuku.sitezet.com" \
   VITE_SHIZUKU_API_URL="https://shizuku.sitezet.com" \
   npm run build
   ```
2. Upload the contents of `dist/` into `shikas.sitezet.com`'s document root, along with a tiny SPA `.htaccess`:
   ```apache
   RewriteEngine On
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^ index.html [L]
   ```

### 3. Google Cloud Console (Production OAuth)
1. In **Google Auth Platform → Clients → SHIZUKU ID**, add:
   - **Authorized JavaScript origins**: `https://shizuku.sitezet.com`
   - **Authorized redirect URIs**: `https://shizuku.sitezet.com/api/ecosystem/google/callback`
2. In **Google Auth Platform → Audience**, click **Publish App** so any Google user can sign in.

### 4. Update Work Card URL
Sign in to `https://shizuku.sitezet.com/admin` → **Work** tab → edit **SHIKAS** URL to `https://shikas.sitezet.com`.

## Local Development (Mac / Linux)
```bash
php -S 127.0.0.1:8088 index.php
```
Open `http://localhost:8088`.
