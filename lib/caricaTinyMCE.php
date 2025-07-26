<!-- [// lib/caricaTinyMCE.php] -->
<script src="/libs/tinymce/tinymce.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  tinymce.init({
    selector: '#contenuto',
    menubar: 'edit view insert format tools table',
    plugins: 'lists link image media code table fullscreen',
    toolbar: 'undo redo | styleselect fontfamily fontsize | bold italic underline | forecolor backcolor | align | bullist numlist | link image media | sfondoSelect | code fullscreen',
    height: 600,
    content_css: '/vittrosviaggi/css/content.css',

    setup: function(editor) {
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
        fetch: function(callback) {
          const items = Object.entries(sfondi).map(([val, label]) => {
            return {
              type: 'menuitem',
              text: label,
              onAction: function() {
                document.body.className = val;
                document.getElementById('sfondo').value = val;
                // Salva lo sfondo via AJAX
                fetch('ajax/update_sfondo.php', {
                  method: 'POST',
                  headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                  body: `post_id=${encodeURIComponent(document.getElementById('id_post').value)}&sfondo=${encodeURIComponent(val)}`
                })
                .then(r => r.json())
                .then(d => console.log('Sfondo aggiornato', d));
              }
            }
          });
          callback(items);
        }
      });
    }
  });
});
</script>
