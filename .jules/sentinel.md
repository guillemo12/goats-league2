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

## 2024-05-18 - [HIGH] Session Fixation in Authentication
**Vulnerability:** The application was vulnerable to Session Fixation attacks. Upon successful user authentication in `main/login/auth.php`, the session ID was not regenerated.
**Learning:** This could allow an attacker to hijack a user's session if they could set the session ID before the user logs in (e.g., via XSS or by forcing the user to visit a crafted link). The application is stateful and relies entirely on sessions, making this a critical area to protect.
**Prevention:** Always call `session_regenerate_id(true);` immediately upon successful authentication, before setting any new session state. This ensures that the post-login session is fundamentally distinct and unknown to any potential attacker who may have seeded a pre-login session ID.
