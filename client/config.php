<?php
/**
 * Config Cabinet Client (Google OAuth).
 *
 * Setează în Google Cloud Console un OAuth Client de tip "Web application".
 * Redirect URI recomandat:
 *   https://ondsolutions.md/client/oauth_callback.php
 */

// IMPORTANT: completează cu valorile tale reale
define('GOOGLE_CLIENT_ID', 'id');
define('GOOGLE_CLIENT_SECRET', 'secret');

// URL absolut către callback (trebuie să fie exact ca în Google Console)
define('GOOGLE_REDIRECT_URI', 'https://ondsolutions.md/client/oauth_callback.php');

// Permite numai emailuri din aceste domenii (gol = orice). Exemplu: ['ondsolutions.md']
define('CLIENT_ALLOWED_EMAIL_DOMAINS', []);

