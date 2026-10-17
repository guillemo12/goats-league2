## 2024-05-24 - [Information Disclosure] Fix sensitive data exposure in upload_db.php
**Vulnerability:** The application was outputting internal server information (`print_r($_FILES)`) directly to the frontend upon a file upload failure. This leaks details about the temporary upload directory structure and internal file names.
**Learning:** Using `print_r` or `var_dump` on superglobals for debugging is dangerous if left in production, especially when it dumps sensitive information directly to the client rather than server logs.
**Prevention:** Use `error_log(print_r($var, true))` to write debugging arrays to server logs instead of displaying them on the UI.
