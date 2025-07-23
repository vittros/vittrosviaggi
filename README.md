# vittrosviaggi

**vittrosviaggi** è un progetto per la gestione di un diario di viaggio, sviluppato utilizzando PHP 8.2 e Bootstrap.  
Permette la creazione di articoli estetici e multimediali, con supporto a immagini, slideshow e temi personalizzati.  
Il progetto è pensato per essere **semplice, funzionale e visualmente gradevole**, con un editor WYSIWYG integrato per facilitare la creazione dei contenuti.

---

## ✨ Funzionalità principali

1. **Editor per la creazione di post**  
   Interfaccia visuale basata su **TinyMCE**, con supporto a font, colori, sfondi (anche fotografici) e stili personalizzati.

2. **Gestione immagini**  
   - Upload da PC o selezione da archivio fotografico esistente (`leNostre/`)
   - Ridimensionamento automatico
   - Galleria popup con anteprime e selezione visiva

3. **Slideshow fotografico con musica**  
   Possibilità di creare slideshow associati agli articoli, con supporto audio. In futuro sarà disponibile l'audio multiplo per slideshow lunghi.

4. **Temi personalizzabili per ogni articolo**  
   Il sistema supporta temi CSS differenti per ogni post, selezionabili e salvabili.

5. **Gestione utenti**  
   Sistema di login con permessi (lettura/scrittura), sessioni e log.

6. **Backup manuale con rotazione**  
   Script Bash dedicato (`backup_web_project.sh`) per salvare e ruotare fino a 5 versioni del progetto.

---

## 🚧 In sviluppo / Da completare

- Mini-editor immagini: ritaglio, rotazione (parzialmente presente)
- Sezione “amici” con accesso privato
- Selezione multipla di immagini nei post
- Slideshow con supporto audio multiplo e sottocartelle

---

## 🚀 Come iniziare

### a) Clona il repository

```bash
git clone https://github.com/tuo-utente/vittrosviaggi.git
```

### b) Configura l’ambiente

- PHP 8.2 o superiore
- Server web (Apache o Nginx)
- MariaDB o MySQL
- Consigliato: NAS Synology o server Linux (es. Manjaro) con accesso SSH

### c) Configura il database

- Crea un database `vittrosviaggi`
- Importa lo schema dal file `setup.sql` (da aggiungere)

### d) Prepara la configurazione

```bash
cp lib/config.example.php lib/config.php
```
Modifica `config.php` inserendo le tue credenziali del database.

### e) Carica le foto

- Le immagini dei post vengono salvate in `foto/post_xx/`
- Puoi importarli da archivio o caricarli direttamente

### f) Accedi all’interfaccia

Visita `http://localhost/vittrosviaggi/` e inizia a creare post, slideshow e contenuti multimediali.

---

## 🗂️ Struttura del progetto

```plaintext
/vittrosviaggi/
├── ajax/               # Script PHP per chiamate AJAX (upload, ridimensiona, log selezioni)
├── css/                # Temi e stili personalizzati
├── foto/               # Immagini associate ai post
├── lib/                # Funzioni PHP, configurazioni, gestori upload, TinyMCE, ecc.
├── media_popup.php     # Finestra popup per selezione immagini
├── modifica_post.php   # Pagina principale per l'editing dei post
├── salva_tema.php      # Salva le scelte grafiche dell’utente
├── backup_web_project.sh # Script per backup e rotazione
└── index.php           # Home page del progetto
```

---

## 🙋 Contribuire

Se vuoi contribuire al progetto:

1. Fai un fork del repository
2. Crea un nuovo branch:
   ```bash
   git checkout -b feature-nome
   ```
3. Fai le tue modifiche e crea un commit:
   ```bash
   git commit -am "Aggiunta nuova funzionalità"
   ```
4. Pusha sul tuo repository:
   ```bash
   git push origin feature-nome
   ```
5. Crea una pull request su GitHub

---

## ⚖️ Licenza

Questo progetto è distribuito sotto licenza **MIT**.  
Puoi usarlo, modificarlo e ridistribuirlo liberamente.

---

> ✍️ Progetto creato da viaggiatori, per viaggiatori.  
> Per raccontare esperienze, condividere emozioni e custodire ricordi.

