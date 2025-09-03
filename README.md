# GLX Secure Contact

A minimal **WordPress contact form plugin** built with:

- AJAX (no page reloads)
- Nonce verification
- Honeypot field (anti-bot)
- Simple IP-based rate limiting
- Optional **Google reCAPTCHA v2 ("I'm not a robot" checkbox)**

Use the `[glx_secure_contact]` shortcode to add the form anywhere on your site.

---

## Features

- Lightweight: no external dependencies beyond WordPress core
- Spam protection:
  - Nonce check
  - Honeypot field
  - Optional Google reCAPTCHA v2
  - Basic rate-limiting (1 submission per IP per 60s)
- Accessible: ARIA live region for messages
- Easy configuration via `config.local.php` (ignored by Git)

---

## Installation

1. Download or clone this repository into your WordPress plugins folder:

   ```bash
   wp-content/plugins/glx-secure-contact/

2. Activate GLX Secure Contact in the WordPress admin (Plugins → Installed Plugins).

3. Place the form shortcode on a page:
   
   [glx_secure_contact]

## Configuration

Secrets (like your reCAPTCHA keys) are not committed to Git.

Instead, copy the provided sample config:

cp wp-content/plugins/glx-secure-contact/config.sample.php \
   wp-content/plugins/glx-secure-contact/config.local.php

Edit config.local.php:

<?php
define('GLX_CONTACT_TO', 'you@example.com'); // Destination email
define('GLX_RECAPTCHA_SITE_KEY', 'your-site-key-here');
define('GLX_RECAPTCHA_SECRET', 'your-secret-key-here');

⚠️ config.local.php is ignored by Git (see .gitignore).

## reCAPTCHA Setup

1. Go to the Google reCAPTCHA Admin Console
2. Register your domain (example.com, www.example.com).
3. Select reCAPTCHA v2 → "I'm not a robot" Checkbox.
4. Copy the Site key and Secret key.
5. Paste them into config.local.php (or wp-config.php).

## Styling

The plugin includes minimal styles.
Override them in your theme’s CSS as needed.

## Development

PHP 7.4+ recommended

WordPress 5.8+

Code is MIT licensed

## Useful commands

Package for upload:

zip -r glx-secure-contact.zip glx-secure-contact/ -x "*.git*" -x "*/node_modules/*"

## License

[MIT LICENSE](https://opensource.org/license/mit)

## Credits

Created by [Great Lynx Designs](https://greatlynxdesigns.com/)


---
