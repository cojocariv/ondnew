# Cum schimbi parola de administrator

Parola de admin se verifică în **`admin/config.php`** sub forma unui **hash** (nu parola în clar). Poți schimba parola în două moduri.

---

## Metoda 1: Din panel (recomandat)

1. Intră în panel: **https://ondsolutions.md/admin/** (sau adresa ta de site + `/admin/`).
2. Autentifică-te cu parola actuală (implicit: **admin123**).
3. În partea de sus a paginii, dă click pe **„Schimbă parola”**.
4. Completează:
   - **Parola actuală** – parola cu care te-ai logat acum
   - **Parola nouă** – parola dorită (minim 8 caractere)
   - **Confirmă parola nouă** – aceeași parolă ca mai sus
5. Apasă **„Salvează parola nouă”**.
6. Dacă apare mesajul de succes, parola s-a schimbat. La următoarea logare folosești **parola nouă**.

**Notă:** Dacă pe server nu se poate scrie în `admin/config.php`, va apărea o eroare; atunci folosește Metoda 2.

---

## Metoda 2: Manual (prin fișiere)

### Pasul 1: Generează hash-ul pentru parola nouă

Alege una dintre variante:

**A) Din linia de comandă (calculatorul tău):**

Deschide PowerShell sau CMD în folderul site-ului și rulează:

```bash
php -r "echo password_hash('PAROLA_TA_NOUA', PASSWORD_DEFAULT);"
```

Înlocuiește **PAROLA_TA_NOUA** cu parola dorită, fără spații în plus.  
Exemplu pentru parola `MeaParola2024`:

```bash
php -r "echo password_hash('MeaParola2024', PASSWORD_DEFAULT);"
```

Vei obține un șir lung, de forma:

```
$2y$10$uSRzfOof0c1ycc4Nrclkb.q5.qhwS167RcfXjUtyzwqxVLzGs65Y2
```

Copiază **întregul** șir (fără spații la început/sfârșit).

**B) Cu un fișier PHP pe server (Plesk / hosting):**

1. Creează în `httpdocs` un fișier, de exemplu **`gen_hash.php`**, cu conținutul:

```php
<?php
$parola = 'PAROLA_TA_NOUA';  // pune aici parola dorită
echo password_hash($parola, PASSWORD_DEFAULT);
```

2. Înlocuiește `PAROLA_TA_NOUA` cu parola ta.
3. Deschide în browser: **https://ondsolutions.md/gen_hash.php**
4. Pe pagină va apărea doar hash-ul. **Copiază tot șirul** (de la `$2y$` până la ultimul caracter).
5. Șterge imediat fișierul **`gen_hash.php`** de pe server (din motive de securitate).

---

### Pasul 2: Actualizează `admin/config.php`

1. Conectează-te la server (FTP, SFTP sau File Manager din Plesk).
2. Deschide fișierul **`admin/config.php`** (cale: `httpdocs/admin/config.php` sau echivalent).
3. Găsești o linie de forma:

```php
$admin_password_hash = '$2y$10$uSRzfOof0c1ycc4Nrclkb...';
```

4. **Înlocuiește** valoarea dintre ghilimele (hash-ul vechi) cu **hash-ul nou** copiat la Pasul 1. Păstrează ghilimelele și punctul și virgula.

Exemplu înainte:

```php
$admin_password_hash = '$2y$10$uSRzfOof0c1ycc4Nrclkb.q5.qhwS167RcfXjUtyzwqxVLzGs65Y2';
```

Exemplu după (cu un hash nou fictiv):

```php
$admin_password_hash = '$2y$10$AbCdEfGhIjKlMnOpQrStUvWxYz1234567890abcdefghij';
```

5. Salvează fișierul și încarcă-l pe server (dacă l-ai editat local).

---

### Pasul 3: Verificare

1. Deschide **https://ondsolutions.md/admin/**
2. Deloghează-te dacă ești deja logat (link „Deconectare”).
3. Autentifică-te cu **parola nouă**. Dacă merge, schimbarea e făcută corect.

---

## Rezumat

| Ce vrei să faci | Unde |
|-----------------|------|
| Schimb parola din browser | Panel Admin → Schimbă parola |
| Generez hash (local) | `php -r "echo password_hash('parola', PASSWORD_DEFAULT);"` |
| Pun hash-ul în cod | `admin/config.php` → linia `$admin_password_hash = '...';` |

Parola implicită la prima instalare este **admin123**. Este bine să o schimbi după prima autentificare.
