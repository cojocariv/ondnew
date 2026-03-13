# Cum să păstrezi modificările din Plesk și cum să faci push către GitHub

## Problema

- Modificările făcute în **admin panel** se salvează în **Plesk** (în `data/site_data.json`, eventual `admin/config.php`).
- Aceste fișiere **nu sunt în GitHub**. La următorul deploy (upload sau `git pull` din repo), ele pot fi suprascrise și modificările se pierd.

---

## Soluția 1: Nu mai suprascrie datele la deploy (recomandat)

Am adăugat **`.gitignore`** în proiect. În el sunt excluse:

- `data/site_data.json` – prețurile și textele editate din admin
- `admin/config.php` – parola de admin (fiecare server poate avea altă parolă)

**Ce înseamnă în practică:**

1. **În GitHub** nu vei comite niciodată `site_data.json` și `config.php` (sau le lași ignorate).
2. **Când faci deploy** (upload sau `git pull` pe Plesk), **nu șterge / nu suprascrie** folderele `data/` și fișierul `admin/config.php` pe server. Deploy doar: `index.php`, `admin/index.php`, `includes/`, `phpmailer/`, `.htaccess`, etc.
3. Modificările din admin rămân pe server; la fiecare deploy păstrezi conținutul din Plesk pentru aceste fișiere.

Dacă folosești **Git pe Plesk** și faci `git pull origin main` în directorul site-ului, fișierele din `.gitignore` nu sunt portate din GitHub, deci **nu se suprascriu** cele de pe server. Modificările din admin rămân.

---

## Soluția 2: Push din Plesk către GitHub

Dacă vrei ca modificările de pe Plesk (inclusiv `site_data.json`) să ajungă în GitHub, trebuie să faci **commit și push direct de pe server**.

### Pasul 1: Verifică dacă Git este instalat pe Plesk

- Conectează-te la server prin **SSH** (utilizatorul de hosting / SSH din Plesk).
- Rulează: `git --version`. Dacă nu există, activează/instalează Git din Plesk sau ceri providerului.

### Pasul 2: În Plesk, nu ignora fișierele de date (opțional)

- Dacă vrei ca `data/site_data.json` să fie și în GitHub, **pe server** șterge sau comentează din `.gitignore` linia cu `data/site_data.json` (sau nu copia `.gitignore` pe server dacă nu vrei ignorare acolo).  
- **Atentie:** dacă la deploy faci `git pull`, atunci trebuie ales clar: fie ignori `site_data.json` la pull (Soluția 1), fie îl incluzi în repo și faci push din Plesk (Soluția 2).

### Pasul 3: Configurare Git pe server (o singură dată)

În directorul site-ului pe Plesk (ex: `~/httpdocs` sau calea ta):

```bash
cd ~/httpdocs
# sau: cd /var/www/vhosts/ondsolutions.md/httpdocs

git config user.email "tu@email.com"
git config user.name "Numele tau"
```

### Pasul 4: Conectare la GitHub (SSH sau token)

**Variantă A – SSH (recomandat):**

1. Pe server, generezi o cheie SSH:  
   `ssh-keygen -t ed25519 -C "plesk-ondsolutions" -f ~/.ssh/github_plesk -N ""`
2. Afișezi cheia publică:  
   `cat ~/.ssh/github_plesk.pub`  
   Copiezi conținutul.
3. În GitHub: **Repository → Settings → Deploy keys** (sau **SSH and GPG keys** la cont) → Add → lipești cheia.
4. Schimbi URL-ul remote la repo (dacă acum folosești HTTPS):  
   `git remote set-url origin git@github.com:USERNAME/REPO.git`

**Variantă B – HTTPS cu Personal Access Token:**

1. În GitHub: **Settings → Developer settings → Personal access tokens** → generezi un token cu drept **repo**.
2. Pe server:  
   `git remote set-url origin https://TOKEN@github.com/USERNAME/REPO.git`  
   (înlocuiești TOKEN, USERNAME, REPO).

### Pasul 5: Commit și push de pe Plesk

După ce ai făcut modificări în admin (deci `data/site_data.json` s-a schimbat pe server):

```bash
cd ~/httpdocs

git status
git add data/site_data.json
# sau: git add -A   (dacă vrei să incluzi toate modificările)

git commit -m "Actualizare date din admin panel"
git push -u origin main
```

(Branch-ul poate fi `main` sau `master` – verifici cu `git branch`.)

După acest push, modificările sunt și în GitHub.

---

## Rezumat

| Scop | Ce să faci |
|------|-------------|
| Modificările din admin să **nu se piardă** la deploy | Folosești `.gitignore` și **nu suprascrii** `data/` și `admin/config.php` la deploy (Soluția 1). |
| Modificările de pe Plesk să **ajungă în GitHub** | Configurezi Git + SSH/token pe Plesk și faci **commit + push** din directorul site-ului (Soluția 2). |

Recomandare: **Soluția 1** (ignorare la deploy) e mai simplă și evita conflicte între medii. **Soluția 2** are sens dacă vrei un singur sursă de adevăr în GitHub, inclusiv conținutul din admin.
