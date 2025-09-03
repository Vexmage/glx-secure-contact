(function(){
  document.addEventListener('DOMContentLoaded', function(){
    var form = document.getElementById('glx-contact-form');
    if (!form) return;

    var btn = document.getElementById('glx_submit_btn');
    var notice = document.getElementById('glx_form_notice');

    function show(msg, ok){
      if (!notice) return;
      notice.textContent = msg;
      notice.className = 'glx-contact-notice ' + (ok ? 'glx-ok' : 'glx-error');
      notice.style.display = 'block';
    }

    form.addEventListener('submit', function(e){
      e.preventDefault();
      if (btn) { btn.disabled = true; btn.textContent = 'Sending...'; }

      var fd = new FormData(form);
      // Nonce from localized data (backup if hidden input missing)
      if (typeof GLX_CONTACT !== 'undefined' && GLX_CONTACT.nonce && !fd.get('glx_nonce')) {
        fd.append('glx_nonce', GLX_CONTACT.nonce);
      }
      // Add action if missing
      if (!fd.get('action')) fd.append('action', 'glx_contact_submit');

      fetch((GLX_CONTACT && GLX_CONTACT.ajaxUrl) ? GLX_CONTACT.ajaxUrl : '/wp-admin/admin-ajax.php', {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
      })
      .then(function(r){ return r.json(); })
      .then(function(data){
        show(data.msg || 'Done.', !!data.ok);
        if (data.ok) {
          form.reset();
          // reset captcha if present
          if (typeof grecaptcha !== 'undefined') { try { grecaptcha.reset(); } catch(e){} }
        }
      })
      .catch(function(){
        show('Network error. Please try again.', false);
      })
      .finally(function(){
        if (btn) { btn.disabled = false; btn.textContent = 'Send'; }
      });
    });
  });
})();
