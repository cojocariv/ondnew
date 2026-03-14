# Admin Contact Requests (/db)

## 1. Setează user și parola MySQL

Deschide **`db/config.php`** și înlocuiește:

- `SETEAZĂ_USER` → utilizatorul tău MySQL (ex: `root` sau user-ul pentru `smartdb`)
- `SETEAZĂ_PAROLA` → parola pentru acel utilizator

## 2. Tabel în baza de date

Dacă tabelul **`contact_requests`** nu există în baza **smartdb**, rulează scriptul:

**`db/contact_requests.sql`**

(sau copiază conținutul în phpMyAdmin / MySQL și execută).

## 3. Acces

- **Login:** `https://ondsolutions.md/db/` sau `.../db/index.php`  
  Utilizator: **admin**  
  Parolă: **ondsecure2026**

- După login ești redirecționat la **dashboard.php** (lista cererilor de contact).

- **Deconectare:** buton „Deconectare” sau `.../db/logout.php`

## 4. Formularul de contact (site)

La trimitere, datele se salvează în **contact_requests** (first_name, last_name, email, message) și se trimite și emailul ca înainte.
