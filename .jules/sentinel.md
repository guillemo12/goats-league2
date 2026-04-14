## 2024-05-24 - Remove Hardcoded Cloudinary API Credentials
**Vulnerability:** Hardcoded API keys for Cloudinary were present as fallback values in `main/cloudinary.php`.
**Learning:** Even fallback values for credentials should not be committed to version control, as they can inadvertently expose sensitive production or development keys.
**Prevention:** Rely strictly on securely injected environment variables (e.g., via `getenv()`) and ensure developers configure their local `.env` files appropriately instead of placing raw secrets in code.
