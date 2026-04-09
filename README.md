# GLX Secure Contact

A minimal **WordPress contact form plugin** focused on simplicity, security, and clean architecture.

Built to avoid bloated form plugins while still providing essential protections and a modern AJAX-based user experience.

---

## Features

* Shortcode: `[glx_secure_contact]`
* AJAX submission (no page reloads)
* Nonce verification
* Honeypot field (anti-bot)
* Simple IP-based rate limiting (1 submission per IP per 60s)
* Optional **Google reCAPTCHA v2 ("I'm not a robot" checkbox)**
* Accessible UI (ARIA live region for feedback)

---

## Architecture

This plugin follows a simple separation of concerns:

* **PHP (WordPress layer)**

  * Registers shortcode
  * Handles AJAX endpoint (`admin-ajax.php`)
  * Validates and sanitizes input
  * Sends email via `wp_mail`

* **JavaScript (client layer)**

  * Intercepts form submission
  * Sends data via `fetch`
  * Handles success/error UI states

This keeps WordPress as a lightweight integration layer while allowing modern frontend behavior.

---

## Installation

1. Clone or download into your plugins directory:

```bash
wp-content/plugins/glx-secure-contact/
```

2. Activate **GLX Secure Contact** in WordPress Admin
   *(Plugins → Installed Plugins)*

3. Add the shortcode to any page:

```
[glx_secure_contact]
```

---

## Configuration

Secrets (like email and reCAPTCHA keys) are not committed to Git.

Copy the sample config:

```bash
cp wp-content/plugins/glx-secure-contact/config.sample.php \
   wp-content/plugins/glx-secure-contact/config.local.php
```

Edit `config.local.php`:

```php
define('GLX_CONTACT_TO', 'you@example.com');
define('GLX_RECAPTCHA_SITE_KEY', 'your-site-key-here');
define('GLX_RECAPTCHA_SECRET', 'your-secret-key-here');
```

 `config.local.php` is ignored by Git (see `.gitignore`)

---

## reCAPTCHA Setup

1. Go to the Google reCAPTCHA Admin Console
2. Register your domain
3. Select **reCAPTCHA v2 → "I'm not a robot" Checkbox**
4. Copy your Site Key and Secret Key
5. Add them to `config.local.php`

---

## Styling

The plugin includes minimal default styles.

Override in your theme’s CSS as needed.

---

## Development

* PHP 7.4+
* WordPress 5.8+
* MIT Licensed

---

## Packaging

Create a zip for upload:

```bash
zip -r glx-secure-contact.zip glx-secure-contact/ -x "*.git*" -x "*/node_modules/*"
```

---

## Notes

This plugin is intentionally minimal and avoids heavy dependencies.
It can be extended to integrate with external services (APIs, databases, or other backends) while keeping the WordPress layer lightweight.

---

## License

[MIT License](https://opensource.org/license/mit)

---

## Credits

Created by [Great Lynx Designs](https://greatlynxdesigns.com/)
