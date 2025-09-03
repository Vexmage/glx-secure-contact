<?php
/**
 * GLX Secure Contact - Sample Config
 *
 * Copy this file to config.local.php and fill in your own values.
 * IMPORTANT: Do not commit config.local.php to version control.
 */

// Email address where contact form submissions should be sent.
if (!defined('GLX_CONTACT_TO')) {
    define('GLX_CONTACT_TO', 'you@example.com');
}

// Google reCAPTCHA v2 Site Key
if (!defined('GLX_RECAPTCHA_SITE_KEY')) {
    define('GLX_RECAPTCHA_SITE_KEY', 'your-site-key-here');
}

// Google reCAPTCHA v2 Secret Key
if (!defined('GLX_RECAPTCHA_SECRET')) {
    define('GLX_RECAPTCHA_SECRET', 'your-secret-key-here');
}
