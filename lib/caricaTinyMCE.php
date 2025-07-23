<?php
// TinyMCE: script e inizializzazione
function caricaTinyMCE() {
    echo <<<EOT
<script src="/libs/tinymce/tinymce.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  tinymce.on('AddEditor', function (e) {
    const editor = e.editor;

    const originalNotificationManager = editor.notificationManager;

    // 🧠 Patch silenziosa
    editor.notificationManager = {
      open: function () {
        console.log('🧯 Soppressione popup TinyMCE attivata!');
        return {
          close: () => {}
        };
      },
      close: function () {}
    };

    console.log('🔇 Patch al notificationManager applicata');
  });
});
</script>

<script>
tinymce.init({
    selector: '#contenuto',
    license_key: 'gpl',
    menubar: 'edit view insert format tools table',
    plugins: 'lists link image media code table fullscreen',
    toolbar: 'undo redo | styleselect fontfamily fontsize | bold italic underline | forecolor backcolor | align | bullist numlist | link image media | sfondoSelect | code fullscreen',
    content_css: '/css/content.css',
    height: 600,

    font_family_formats: 'Arial=arial,helvetica,sans-serif; Courier New=courier new,courier; Georgia=georgia,palatino; Tahoma=tahoma,arial,helvetica; Verdana=verdana,geneva; Comic Sans MS=comic sans ms,sans-serif; Impact=impact,sans-serif; Trebuchet MS=trebuchet ms,sans-serif',
    font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt 48pt',

    style_formats: [
        { title: 'Titolo 1', block: 'h1' },
        { title: 'Titolo 2', block: 'h2' },
        { title: 'Titolo 3', block: 'h3' },
        { title: 'Paragrafo', block: 'p' },
        { title: 'Citazione', block: 'blockquote' },
        { title: 'Codice', block: 'code' }
    ],

    images_upload_url: '/vittrosviaggi/lib/upload_tinymce.php',

    images_upload_handler: function (blobInfo, success, failure) {
  console.log('Inizio upload immagine...');
  const xhr = new XMLHttpRequest();
  xhr.open('POST', '/vittrosviaggi/lib/upload_tinymce.php');

  xhr.onload = function () {
    try {
      const json = JSON.parse(xhr.responseText);
      console.log('Risposta JSON ricevuta dal server:', json);

      if (json?.location) {
        console.log('📸 inserisciImmagineDiretta:', json.location);
        inserisciImmagineDiretta(json.location);
      }
    } catch (e) {
      console.log('Errore JSON (ignorato):', e);
    }

    // 👉 Risolviamo comunque la Promise per non far arrabbiare TinyMCE
    return Promise.resolve(null); // zittisce tutto
  };

  const formData = new FormData();
  formData.append('file', blobInfo.blob(), blobInfo.filename());
  formData.append('id_post', document.getElementById('id_post')?.value || '0');
  xhr.send(formData);
},

    images_upload_handler3: function (blobInfo, success, failure) {
  console.log('Inizio upload immagine...');
  const xhr = new XMLHttpRequest();
  xhr.open('POST', '/vittrosviaggi/lib/upload_tinymce.php');

  xhr.onload = function () {
    try {
      const json = JSON.parse(xhr.responseText);
      console.log('Risposta JSON ricevuta dal server:', json);

      if (json?.location) {
        console.log('📸 inserisciImmagineDiretta:', json.location);
        inserisciImmagineDiretta(json.location);

        // NIENTE success()
        // NIENTE failure()
        // TinyMCE resta zitto
      }
    } catch (e) {
      console.log('Errore JSON (ma non lo diciamo a TinyMCE):', e);
    }
    console.log('Errore o non errore, fuori dai piedi!');
  };

  const formData = new FormData();
  formData.append('file', blobInfo.blob(), blobInfo.filename());
  formData.append('id_post', document.getElementById('id_post')?.value || '0');
  xhr.send(formData);
},

    images_upload_handler2: function (blobInfo, success, failure) {
  console.log('Inizio upload immagine...');
  var xhr = new XMLHttpRequest();
  xhr.withCredentials = false;
  xhr.open('POST', '/vittrosviaggi/lib/upload_tinymce.php');

  xhr.onload = function () {
    if (xhr.status !== 200) {
      console.log('Errore durante l\'upload dell\'immagine: ' + xhr.status);
      if (typeof failure === 'function') failure('Errore HTTP: ' + xhr.status);
      return;
    }

    try {
      const json = JSON.parse(xhr.responseText);
      console.log('Risposta JSON ricevuta dal server:', json);

      if (json?.location) {
        console.log('📸 inserisciImmagineDiretta:', json.location);
        inserisciImmagineDiretta(json.location);

        if (typeof failure === 'function') failure(null);  // 🔇 niente popup
      } else {
        failure('URL mancante');
      }
    } catch (e) {
      console.log('Errore nel parsing della risposta JSON:', e);
      if (typeof failure === 'function') failure('Risposta non valida');
    }
  };

  const formData = new FormData();
  formData.append('file', blobInfo.blob(), blobInfo.filename());
  formData.append('id_post', document.getElementById('id_post')?.value || '0');
  xhr.send(formData);
},

images_upload_handler1: function (blobInfo, success, failure) {
  return new Promise((resolve, reject) => {
    console.log('Inizio upload immagine...');
    var xhr = new XMLHttpRequest();
    xhr.withCredentials = false;
    xhr.open('POST', '/vittrosviaggi/lib/upload_tinymce.php');

    xhr.onload = function () {
      if (xhr.status !== 200) {
        console.log('Errore durante l\'upload dell\'immagine: ' + xhr.status);
        if (typeof failure === 'function') failure('Errore HTTP: ' + xhr.status);
        reject();
        return;
      }

      var json;
      try {
        json = JSON.parse(xhr.responseText);
        console.log('Risposta JSON ricevuta dal server:', json);
      } catch (e) {
        console.log('Errore nel parsing della risposta JSON:', e);
        if (typeof failure === 'function') failure('Risposta non valida dal server');
        reject();
        return;
      }

      if (!json || typeof json.location !== 'string') {
        console.log('Manca il campo location nella risposta JSON');
        if (typeof failure === 'function') failure('Manca il campo location');
        reject();
        return;
      }

      console.log('📸 inserisciImmagineDiretta:', json.location);
      inserisciImmagineDiretta(json.location);

      // NON dire success(), TinyMCE non deve fare nulla
      console.log('NON dire success(), TinyMCE non deve fare nulla');
      resolve();
    };

    var formData = new FormData();
    formData.append('file', blobInfo.blob(), blobInfo.filename());
    formData.append('id_post', document.getElementById('id_post')?.value || '0');
    xhr.send(formData);
  });
},

    setup: function(editor) {
        const CLASSI_SFONDO = {
            '': 'Default (nessuno)',
            'bg-azzurro': 'Sfondo azzurro',
            'bg-giallo': 'Sfondo giallo',
            'bg-verde': 'Sfondo verde',
            'bg-rosa': 'Sfondo rosa',
            'bg-arancio': 'Sfondo arancio'
        };

        editor.ui.registry.addMenuButton('sfondoSelect', {
            text: 'Sfondo',
            fetch: function(callback) {
                const items = Object.entries(CLASSI_SFONDO).map(([classe, label]) => {
                    return {
                        type: 'menuitem',
                        text: label,
                        onAction: function() {
                            editor.getBody().className = classe;
                            const inputSfondo = document.getElementById('sfondo');
                            if (inputSfondo) inputSfondo.value = classe;
                        }
                    };
                });
                callback(items);
            }
        });

        editor.on('init', function () {
            const sfondoSalvato = document.getElementById('sfondo')?.value;
            if (sfondoSalvato) {
                editor.getBody().className = sfondoSalvato;
            }
        });
    }
});
</script>
EOT;
}