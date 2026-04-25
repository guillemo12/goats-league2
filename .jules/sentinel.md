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

## 2024-04-25 - Secure Session Cookies against XSS and CSRF
**Vulnerability:** Session cookies did not have the `HttpOnly` or `SameSite` flags configured, leaving the application highly vulnerable to Cross-Site Scripting (XSS) and Cross-Site Request Forgery (CSRF). Since many state-changing requests use HTTP POST without CSRF tokens, this makes it easier for an attacker to craft a CSRF attack.
**Learning:** Proper session cookie attributes are a critical defense-in-depth mechanism. Without `HttpOnly`, XSS can easily steal session tokens. Without `SameSite`, CSRF attacks become significantly easier to execute.
**Prevention:** Always configure `session_set_cookie_params` with `'httponly' => true` and `'samesite' => 'Lax'` globally before calling `session_start()`.
