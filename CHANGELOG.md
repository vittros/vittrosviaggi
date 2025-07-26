# VittRosViaggi - Changelog

## 📦 Versione 4.2 - "natura-emoji" (2025-07-26)

### ✨ Novità principali

- **Visibilità post**: aggiunto campo `visibilita` (`pubblico`, `nat`, `privato`) al posto di `privato`.
- **Ruoli utente**: introdotto ruolo `amik_nat` con permessi specifici per visualizzare post "nat".
- **Emoji di ruolo**: ogni utente visualizza il proprio ruolo con emoji nel `header` (👑 admin, ✏️ editor, 🧭 amico, 🌿 amik_nat, 👤 ospite).
- **Salvataggio post**: completamente rivisto e ora funzionante via AJAX (con `handle_salva_post.php`).
- **Pulizia codice**: eliminati file inutili e vecchie versioni (`lib/mostra_form.php`, `scripts/autosave_titolo.php`, etc).
- **Interfaccia admin migliorata**: `admin.php` più chiara, pronta per estensioni future.
- **Footer dinamico**: ora meno invadente, appare al passaggio del mouse con ritardo, si nasconde solo dopo 2 secondi.

### ✅ Tecnico

- `ALTER TABLE post ADD COLUMN visibilita ENUM('pubblico','nat','privato') NOT NULL DEFAULT 'pubblico';`
- Gestione dei ruoli e visibilità centralizzata nella funzione `può_visualizzare_post($post, $ruolo, $utente_id)`.

### 🧰 Riferimenti file coinvolti

- `modifica_post.php`, `lib/bootstrap.php`, `lib/functions.php`, `ajax/handle_salva_post.php`
- `admin.php`, `admin_utenti.php`, `admin_nuovo_utente.php`
- `login.php`, `logout.php`, `lib/header.php`, `footer.php`
- `media_popup.php`, `lib/caricaTinyMCE.php`

---


