## 2026-04-16 - Unauthenticated Database Upload
**Vulnerability:** The `main/upload_db.php` script allowed unauthenticated users to upload and overwrite the entire SQLite database file (`database.sqlite`).
**Learning:** Admin endpoints and critical data manipulation scripts must be guarded with session validation and role checks. Leaving maintenance scripts open is a critical threat.
**Prevention:** Always verify `$_SESSION['user_id']` and confirm the user's role against the database before executing any administrative action or file upload processing.

## 2024-04-16 - Hardcoded Secrets
**Vulnerability:** Hardcoded API keys in `main/cloudinary.php` and plaintext passwords in `main/gensql.php`.
**Learning:** Hardcoded credentials can easily leak and compromise the entire system.
**Prevention:** Rely on environment variables for API keys and remove setup scripts containing sensitive test/init data from the production codebase.

## 2024-04-15 - Unauthenticated Database Upload Vulnerability
**Vulnerability:** Found an unauthenticated file upload vulnerability in `main/upload_db.php`. Anyone could upload an arbitrary `.sqlite` file to overwrite the application's entire database, allowing complete takeover of application data.
**Learning:** Admin tools or developer scripts intended for convenience (like `upload_db.php`) are sometimes left completely unprotected in production or without any authentication checks, representing a severe security risk. This application relies on a session-based role check for other admin pages, but missed it here.
**Prevention:** Ensure all files accessible via the web server enforce proper authentication and authorization checks. Centralize admin routing if possible to prevent individual files from being exposed without checks.
## 2025-05-18 - Unauthenticated Maintenance Scripts\n**Vulnerability:** Standalone maintenance scripts ( and ) were left unprotected in the public web root.\n**Learning:** Maintenance scripts that alter code () or database structures () pose a critical threat if accessible to unauthenticated users, potentially leading to arbitrary code execution or data corruption.\n**Prevention:** Remove one-off maintenance scripts from the production codebase immediately after use, or strictly secure them with admin authentication checks similar to .
## 2025-05-18 - Unauthenticated Maintenance Scripts
**Vulnerability:** Standalone maintenance scripts (`main/fix_nav.php` and `main/market_db.php`) were left unprotected in the public web root.
**Learning:** Maintenance scripts that alter code (`fix_nav.php`) or database structures (`market_db.php`) pose a critical threat if accessible to unauthenticated users, potentially leading to arbitrary code execution or data corruption.
**Prevention:** Remove one-off maintenance scripts from the production codebase immediately after use, or strictly secure them with admin authentication checks similar to `upload_db.php`.
