# Temă Smart Solutions pentru OpenCart 4

Aliniază magazinul `shop.smartsolutions.md` cu designul site-ului principal.

## Ce include

- Culori: verde `#185649`, accent `#FF5938`, font Inter
- Header sticky, meniu verde, butoane rotunde ca pe site-ul principal
- Footer închis ca pe smartsolutions.md
- Bară sus cu link „← Smart Solutions” către site principal
- Logo Smart Solutions (încărcat de pe smartsolutions.md)

---

## Instalare (5 minute)

### Pasul 1 — Publică fișierele pe site-ul principal

Asigură-te că pe **smartsolutions.md** există (după deploy Git):

- `assets/shop/opencart.css`
- `assets/shop/opencart.js`

### Pasul 2 — Editează header-ul OpenCart

Pe server, în **shop.smartsolutions.md**, deschide:

```
catalog/view/template/common/header.twig
```

**Înainte de `</head>`**, adaugă:

```twig
<link href="https://smartsolutions.md/assets/shop/opencart.css" type="text/css" rel="stylesheet"/>
<script src="https://smartsolutions.md/assets/shop/opencart.js" defer></script>
```

Salvează fișierul.

### Pasul 3 — Șterge cache OpenCart

**Dashboard → Developer Settings** (sau System) → șterge:

- Theme cache  
- SASS cache  

Reîncarcă magazinul cu **Ctrl+F5**.

---

## Variantă locală (fără dependență de domeniul principal)

Copiază pe shop:

- `opencart-theme/catalog/view/stylesheet/smartsolutions.css` → `catalog/view/stylesheet/smartsolutions.css`
- `opencart-theme/catalog/view/javascript/smartsolutions.js` → `catalog/view/javascript/smartsolutions.js`

În `header.twig`:

```twig
<link href="catalog/view/stylesheet/smartsolutions.css" type="text/css" rel="stylesheet"/>
<script src="catalog/view/javascript/smartsolutions.js" defer></script>
```

În `smartsolutions.js` schimbă `MAIN_SITE` și `LOGO_URL` dacă e nevoie, sau pune logo-ul în `image/catalog/`.

---

## Personalizare din admin

- **System → Settings → Edit store** — nume magazin, email
- **Design → Layouts** — module homepage
- **Design → Banners** — imagini promo

---

## Notă

Aceasta este o **suprapunere CSS/JS** peste tema default OpenCart 4. Pentru control 100% asupra HTML (header/footer custom), e nevoie de extensie temă OpenCart 4 cu evenimente — contactează-ne dacă vrei varianta completă.
