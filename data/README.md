# Folderul data/

- **site_data.json** – datele editate din **admin panel** (prețuri, contact, texte). Acest fișier **nu este în Git** (e în `.gitignore`).
- **site_data.json.example** – copie implicită, folosită doar dacă `site_data.json` lipsește.

## ⚠️ La deploy (upload / commit pe server)

**NU suprascrie** `site_data.json` pe serverul de producție. Dacă uploadezi tot proiectul din PC sau faci un deploy care șterge/înlocuiește fișierele:

1. **Exclude** folderul `data/` din upload (sau exclude doar `data/site_data.json`), **sau**
2. Fă **backup** la `data/site_data.json` de pe server înainte de deploy și restaurează-l după.

Altfel, modificările făcute în admin panel se pierd la următorul deploy.
