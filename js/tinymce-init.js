    // TinyMCE init quando DOM è pronto
    document.addEventListener('DOMContentLoaded', function() {
      const BASE = (window.BASE_URL || '/');
      tinymce.init({
        license_key: 'gpl',
        base_url: '/libs/tinymce',
        suffix: '.min',
        selector: '#contenuto',
        height: 600,
        menubar: 'edit view insert format tools table',
        // 👇 aggiungi 'emoticons'
        plugins: 'lists advlist link image media code table fullscreen charmap emoticons',
        // 👇 aggiungi 'emoticons' in toolbar
        toolbar: 'undo redo | styles | fontselect fontsizeselect | bold italic underline | forecolor backcolor | ' +
          'alignleft aligncenter alignright | bullist numlist | link image media | emoticons | sfondoSelect | code fullscreen',
        // Usa il database nativo di emoji (non le faccine ascii)
        emoticons_database: 'emoji',
        // 👇 forza TinyMCE a usare il file che hai davvero sul disco
        emoticons_database_url: '/libs/tinymce/plugins/emoticons/js/emojis.min.js',
        // Se vuoi permettere il paste di emoji da altrove senza sorprese:
        paste_data_images: true, // opzionale
        content_css: "/vittrosviaggi_1.1/css/content.css",

        setup(editor) {
          const sfondi = {
            '': 'Predefinito',
            'bg-azzurro': 'Azzurro',
            'bg-giallo': 'Giallo',
            'bg-verde': 'Verde',
            'bg-rosa': 'Rosa',
            'bg-arancio': 'Arancio'
          };

          editor.ui.registry.addMenuButton('sfondoSelect', {
            text: 'Sfondo',
            fetch(callback) {
              const items = Object.entries(sfondi).map(([val, label]) => ({
                type: 'menuitem',
                text: label,
                onAction() {
                  document.body.className = val;
                  document.getElementById('sfondo').value = val;
                  fetch('ajax/update_sfondo.php', {
                    method: 'POST',
                    headers: {
                      'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `post_id=${POST_ID}&sfondo=${encodeURIComponent(val)}`
                  }).then(r => r.json()).then(d => console.debug('Sfondo aggiornato', d));
                }
              }));
              callback(items);
            }
          });

          // autosave su variazioni dell’editor
          editor.on('change input keyup paste SetContent', doAutosave);
        }
      });

      hookAutosave(); // per titolo/textarea “non Tiny”
      document.getElementById('btn-pubblica')?.addEventListener('click', pubblicaPost);

      // autosave “di sicurezza” ogni 60s
      setInterval(() => {
        lastPayload = '';
        doAutosave();
      }, 60000);

      // best-effort alla chiusura scheda
      window.addEventListener('beforeunload', () => {
        const body = new URLSearchParams({
          post_id: POST_ID,
          titolo: readTitolo(),
          contenuto: readContenuto(),
          sfondo: readSfondo()
        });
        if (navigator.sendBeacon) navigator.sendBeacon('ajax/autosave_post.php', body);
      });
    });