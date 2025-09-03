<?php
/**
 * Plugin Name: GLX Secure Contact (AJAX + reCAPTCHA)
 * Description: Minimal secure contact form with shortcode [glx_secure_contact]. Uses AJAX, nonce, honeypot, rate limit, and Google reCAPTCHA v2.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) exit;

// ===== CONFIG =====
const GLX_CONTACT_TO         = 'REPLACE_WITH_YOUR_DESTINATION_EMAIL'; 
const GLX_RECAPTCHA_SITE_KEY = 'REPLACE_WITH_YOUR_SITE_KEY';
const GLX_RECAPTCHA_SECRET   = 'REPLACE_WITH_YOUR_SECRET_KEY';

// ---------- Shortcode renders the form ----------
add_shortcode('glx_secure_contact', function () {
    // enqueue reCAPTCHA and our JS at render time
    add_action('wp_enqueue_scripts', function () {
        if (GLX_RECAPTCHA_SITE_KEY && GLX_RECAPTCHA_SITE_KEY !== 'REPLACE_WITH_YOUR_SITE_KEY') {
            wp_enqueue_script('glx-recaptcha', 'https://www.google.com/recaptcha/api.js', [], null, true);
        }
        wp_register_script('glx-contact-js', plugins_url('glx-contact.js', __FILE__), [], '1.0.0', true);
        wp_localize_script('glx-contact-js', 'GLX_CONTACT', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('glx_contact_nonce'),
        ]);
        wp_enqueue_script('glx-contact-js');
    });

    ob_start(); ?>
    <form id="glx-contact-form" class="glx-secure-contact" novalidate>
      <input type="hidden" name="action" value="glx_contact_submit">
      <input type="hidden" name="glx_nonce" value="<?php echo esc_attr(wp_create_nonce('glx_contact_nonce')); ?>">

      <!-- Honeypot -->
      <div style="position:absolute;left:-9999px;">
        <label>Leave this field empty</label>
        <input type="text" name="company" tabindex="-1" autocomplete="off">
      </div>

      <label for="glx_name">Name*</label>
      <input id="glx_name" name="name" type="text" required>

      <label for="glx_email">Email*</label>
      <input id="glx_email" name="email" type="email" required>

      <label for="glx_message">Message*</label>
      <textarea id="glx_message" name="message" rows="6" required></textarea>

      <?php if (GLX_RECAPTCHA_SITE_KEY && GLX_RECAPTCHA_SITE_KEY !== 'REPLACE_WITH_YOUR_SITE_KEY'): ?>
        <div class="g-recaptcha" data-sitekey="<?php echo esc_attr(GLX_RECAPTCHA_SITE_KEY); ?>"></div>
      <?php endif; ?>

      <button id="glx_submit_btn" type="submit">Send</button>

      <div id="glx_form_notice" class="glx-contact-notice" aria-live="polite" style="display:none;"></div>
    </form>
    <style>
      .glx-secure-contact { max-width: 640px; display:grid; gap:.75rem; }
      .glx-secure-contact input, .glx-secure-contact textarea { width:100%; padding:.6rem; }
      .glx-secure-contact button { padding:.65rem 1.1rem; cursor:pointer; }
      .glx-contact-notice { margin-top:.5rem; padding:.75rem 1rem; border-radius:.5rem; border:1px solid #ddd; }
      .glx-ok    { background:#f0fff4; border-color:#c6f6d5; }
      .glx-error { background:#fff5f5; border-color:#fed7d7; }
    </style>
    <?php
    return ob_get_clean();
});

// ---------- AJAX handler (front + back) ----------
add_action('wp_ajax_nopriv_glx_contact_submit', 'glx_handle_contact_ajax');
add_action('wp_ajax_glx_contact_submit',        'glx_handle_contact_ajax');

function glx_handle_contact_ajax() {
    // JSON header
    nocache_headers();
    header('Content-Type: application/json; charset=utf-8');

    // Simple rate limit by IP (1 per 60s)
    $ip  = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = 'glx_contact_rate_' . md5($ip);
    if (get_transient($key)) {
        echo json_encode(['ok'=>false, 'msg'=>'Too many attempts. Please wait a minute and try again.']); exit;
    }

    // Honeypot
    if (!empty($_POST['company'])) {
        set_transient($key, 1, 60);
        echo json_encode(['ok'=>true, 'msg'=>'Thanks! Your message was sent.']); exit;
    }

    // Nonce
    $nonce = isset($_POST['glx_nonce']) ? sanitize_text_field($_POST['glx_nonce']) : '';
    if (!$nonce || !wp_verify_nonce($nonce, 'glx_contact_nonce')) {
        echo json_encode(['ok'=>false, 'msg'=>'Security check failed. Reload and try again.']); exit;
    }

    // Validate fields
    $name    = isset($_POST['name'])    ? sanitize_text_field($_POST['name'])  : '';
    $email   = isset($_POST['email'])   ? sanitize_email($_POST['email'])      : '';
    $message = isset($_POST['message']) ? wp_strip_all_tags($_POST['message']) : '';
    if (!$name || !$email || !is_email($email) || !$message) {
        echo json_encode(['ok'=>false, 'msg'=>'Please complete all fields with a valid email.']); exit;
    }

    // reCAPTCHA v2 verify (if keys present)
    if (GLX_RECAPTCHA_SECRET && GLX_RECAPTCHA_SECRET !== 'REPLACE_WITH_YOUR_SECRET_KEY') {
        $captcha = isset($_POST['g-recaptcha-response']) ? sanitize_text_field($_POST['g-recaptcha-response']) : '';
        if (!$captcha) {
            echo json_encode(['ok'=>false, 'msg'=>'Captcha required.']); exit;
        }
        $resp = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
            'timeout' => 10,
            'body' => [
                'secret'   => GLX_RECAPTCHA_SECRET,
                'response' => $captcha,
                'remoteip' => $ip,
            ]
        ]);
        if (is_wp_error($resp)) {
            echo json_encode(['ok'=>false, 'msg'=>'Captcha check failed. Try again.']); exit;
        }
        $data = json_decode(wp_remote_retrieve_body($resp), true);
        if (empty($data['success'])) {
            echo json_encode(['ok'=>false, 'msg'=>'Captcha failed. Try again.']); exit;
        }
    }

    // Build & send email
    $to      = GLX_CONTACT_TO;
    $subject = 'Website contact form';
    $headers = [
        'Reply-To: ' . $name . ' <' . $email . '>',
        'Content-Type: text/plain; charset=UTF-8'
    ];
    $body = "Name: $name\nEmail: $email\n\nMessage:\n$message";

    $sent = wp_mail($to, $subject, $body, $headers);

    // Set rate limit after processing
    set_transient($key, 1, 60);

    if ($sent) {
        echo json_encode(['ok'=>true, 'msg'=>'Thanks! Your message was sent.']);
    } else {
        echo json_encode(['ok'=>false, 'msg'=>'Sorry, something went wrong sending email.']);
    }
    exit;
}

// ---------- Inline JS file output ----------
add_action('init', function () {
    // Provide a virtual JS file so we keep a single-plugin-file install optional, but we registered a real file path above.
    // If the physical file exists (glx-contact.js), WP will serve that. This is just a fallback no-op.
});
