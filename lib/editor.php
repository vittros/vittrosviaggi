<?php
// lib/editor.php
function caricaTinyMCE() {
    echo <<<EOT
<script src="/libs/tinymce/tinymce.min.js"></script>
<script>
tinymce.init({
    selector: '#contenuto',
    plugins: 'lists link image media code table textcolor',
    toolbar: 'undo redo | styleselect | fontfamily fontsize | bold italic underline | forecolor backcolor | alignleft aligncenter alignright | bullist numlist | link image media | code',
    images_upload_url: '/vittrosviaggi/upload_tinymce.php',
    automatic_uploads: true,
    images_reuse_filename: true,
    font_family_formats: 'Arial=arial,helvetica,sans-serif; Courier New=courier new,courier; Georgia=georgia,palatino; Tahoma=tahoma,arial,helvetica; Verdana=verdana,geneva',
    font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt 48pt',
    style_formats: [
      { title: 'Sfondo azzurro', block: 'p', classes: 'bg-azzurro' },
      { title: 'Sfondo giallo', block: 'p', classes: 'bg-giallo' },
      { title: 'Sfondo Parlasco', block: 'div', classes: 'bg-sfondo-parlasco' },
      { title: 'Sfondo immagine KDE', block: 'div', classes: 'sfondo-kde' }
    ],
    content_css: '/vittrosviaggi/css/content.css',
    height: 400,
    language: 'it'
});
</script>
EOT;
}
?>

