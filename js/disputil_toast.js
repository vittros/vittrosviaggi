// Dipendenze: Bootstrap 5 bundle (per .Toast)

window.DispUtil = window.DispUtil || {};
(function(NS){
  // Assicura il contenitore una volta sola
  function ensureContainer(){
    let c = document.getElementById('disputil-toast-container');
    if (!c) {
      c = document.createElement('div');
      c.id = 'disputil-toast-container';
      c.className = 'position-fixed top-0 end-0 p-3';
      c.style.zIndex = 1080; // sopra modali standard
      document.body.appendChild(c);
    }
    return c;
  }

  /**
   * Mostra un toast Bootstrap
   * @param {string} msg - testo (accetta html semplice)
   * @param {'success'|'danger'|'warning'|'info'|'primary'|'secondary'|'light'|'dark'} variant
   * @param {number} delayMs
   */
  NS.toast = function(msg, variant='primary', delayMs=2200){
    const c = ensureContainer();
    const el = document.createElement('div');
    el.className = `toast align-items-center text-bg-${variant} border-0 mb-2`;
    el.setAttribute('role','alert');
    el.setAttribute('aria-live','assertive');
    el.setAttribute('aria-atomic','true');
    el.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">${msg}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Chiudi"></button>
      </div>`;
    c.appendChild(el);
    const t = new bootstrap.Toast(el, { delay: delayMs });
    t.show();
    el.addEventListener('hidden.bs.toast', () => el.remove());
    return t;
  };
})(window.DispUtil);

