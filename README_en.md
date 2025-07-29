# vittrosviaggi

**vittrosviaggi** is a project to manage a multimedia travel diary, developed using PHP 8.2 and Bootstrap.  
It allows the creation of visually appealing posts with support for images, slideshows, themes and personalized styles.  
The project aims to be **simple, functional and aesthetically pleasing**, with an integrated WYSIWYG editor for easy content creation.

---

## ✨ Main Features

1. **Visual post editor**  
   Visual interface powered by **TinyMCE**, with support for fonts, colors, backgrounds (including photos), and custom styles.

2. **Image management**  
   - Upload from PC or select from existing archive (`leNostre/`)
   - Automatic resizing
   - Popup gallery with thumbnails and visual selection

3. **Photo slideshow with music**  
   Ability to create slideshows linked to posts, with optional audio. Multi-track support is planned for longer slideshows.

4. **Customizable themes per article**  
   CSS themes can be selected and saved for each individual post.

5. **User management**  
   Login system with roles (read/write), session tracking and logs.

6. **Manual backup with rotation**  
   Bash script (`backup_web_project.sh`) for manual backups, maintaining up to 5 rotated versions.

---

## 👥 User Roles

| Role            | Description                                      | Emoji |
|-----------------|--------------------------------------------------|-------|
| **admin**       | Can do anything, on any post                     | 👑    |
| **editor**      | Can write, edit, and delete their own posts      | ✍️    |
| **amico**       | Trusted friend: can read all public posts        | 🌞    |
| **amik_nat**    | Naturist friend: can also read "nat" posts       | 🌿    |
| **ospite**      | Temporary guest author: can manage only their own posts | 🎒 |

---

## 🚧 In development / To be completed

- Image mini-editor: crop, rotate (partially present)
- Private “friends only” section
- Multi-image selection per post
- Slideshow with multiple audio tracks and subfolders

---

## 🚀 Getting Started

### a) Clone the repository

```bash
git clone https://github.com/tuo-utente/vittrosviaggi.git
```

### b) Set up the environment

- PHP 8.2 or higher
- Web server (Apache or Nginx)
- MariaDB or MySQL
- Recommended: Synology NAS or Linux server (e.g. Manjaro) with SSH access

### c) Configure the database

- Create a database named `vittrosviaggi`
- Import schema from `setup.sql` (to be added)

### d) Prepare the configuration

```bash
cp lib/config.example.php lib/config.php
```
Edit `config.php` with your database credentials.

### e) Upload photos

- Images for posts are stored in `foto/post_xx/`
- You can import them from your archive or upload directly

### f) Open the web interface

Visit `http://localhost/vittrosviaggi/` and start writing posts and creating slideshows.

---

## 🗂 Project structure

```plaintext
/vittrosviaggi/
├── ajax/               # PHP scripts for AJAX (upload, resize, logs)
├── css/                # Themes and styles
├── foto/               # Post-related images
├── lib/                # PHP functions, config, TinyMCE handlers, etc.
├── media_popup.php     # Popup window to select images
├── modifica_post.php   # Main editing page
├── salva_tema.php      # Save graphic theme selection
├── backup_web_project.sh # Bash backup script
└── index.php           # Project homepage
```

---

## 🙋 Contributing

To contribute:

1. Fork the repository
2. Create a feature branch:
   ```bash
   git checkout -b feature-name
   ```
3. Make your changes and commit:
   ```bash
   git commit -am "Added feature"
   ```
4. Push to your fork:
   ```bash
   git push origin feature-name
   ```
5. Open a pull request on GitHub

---

## ⚖ License

This project is released under the **MIT License**.  
You are free to use, modify and redistribute it.

---

> ✍ Created by travelers, for travelers.  
> To share stories, preserve memories and celebrate adventures.
