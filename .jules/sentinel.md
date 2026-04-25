## 2024-05-24 - [Stored XSS & Insecure Session Cookies]
**Vulnerability:**
1. The JSON variable `$loadedPositions` was echoed directly into a `<script>` tag in `main/pizarra.php` without encoding.
2. The session cookies were missing `HttpOnly` and `SameSite` flags globally across all PHP entry points.
**Learning:**
1. Rendering dynamic variables in a JavaScript context requires specific JSON encoding flags to prevent attackers from escaping the context.
2. PHP's default session cookie configurations do not enforce `HttpOnly` or `SameSite`, which leaves sessions vulnerable to XSS theft and CSRF.
**Prevention:**
1. Use `json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)` when embedding PHP data into JavaScript.
2. Configure `session_set_cookie_params` with `['httponly' => true, 'samesite' => 'Lax']` in all entry points.
