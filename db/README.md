# Admin Contact Requests (/db)

Baza de date folosită este **PostgreSQL**.

## 1. Setează user și parola PostgreSQL

Deschide **`db/config.php`** și înlocuiește:

- `$DB_HOST` – de obicei `localhost`
- `$DB_PORT` – de obicei `5432`
- `$DB_NAME` – numele bazei tale PostgreSQL
- `$DB_USER` → utilizatorul PostgreSQL
- `$DB_PASS` → parola pentru acel utilizator

## 2. Tabel în baza de date

Dacă tabelul **`contact_requests`** nu există:

- **Variantă 1:** După login la `/db/`, deschide **`/db/install_table.php`** și apasă „Creează tabelul”. Șterge apoi fișierul `install_table.php` de pe server.
- **Variantă 2:** Rulează manual în clientul PostgreSQL (psql, pgAdmin etc.) conținutul fișierului **`db/contact_requests.sql`**.

## 3. Acces

- **Login:** `https://ondsolutions.md/db/` sau `.../db/index.php`  
  Utilizator: **admin**  
  Parolă: **ondsecure2026**

- După login ești redirecționat la **dashboard.php** (lista cererilor de contact).

- **Deconectare:** buton „Deconectare” sau `.../db/logout.php`

## 4. Formularul de contact (site)

La trimitere, datele se salvează în **contact_requests** (first_name, last_name, email, message) și se trimite și emailul ca înainte.

---

## 5. Mini-chat și Cabinet

- **Pe site:** vizitatorii văd un buton de chat (colț dreapta jos). Introdu numele și emailul, apoi pot trimite mesaje. Conversațiile se salvează în baza de date.
- **Cabinet (admin):** după login la `/db/`, apasă **„Cabinet chat”** sau mergi la **`/db/cabinet/conversations.php`**. Acolo vezi toate conversațiile și poți răspunde clienților.
- **Instalare tabele chat:** dacă nu există tabelele pentru chat, rulează o dată **`/db/install_chat.php`** (după autentificare). Creează `chat_conversations` și `chat_messages`.
