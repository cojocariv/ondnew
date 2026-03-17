<?php
/**
 * Limbă site: ro | ru. Setare prin ?lang=ro sau ?lang=ru, salvată în cookie.
 */
$allowed_langs = ['ro', 'ru'];
$lang = null;
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_GET['lang']) && in_array($_GET['lang'], $allowed_langs, true)) {
    $lang = $_GET['lang'];
    setcookie('site_lang', $lang, time() + 86400 * 365, '/', '', false, true);
    if (!headers_sent()) {
        header('Location: ' . strtok($_SERVER['REQUEST_URI'] ?? '/', '?'));
        exit;
    }
}
if (empty($lang)) {
    $lang = isset($_COOKIE['site_lang']) && in_array($_COOKIE['site_lang'], $allowed_langs, true) ? $_COOKIE['site_lang'] : 'ro';
}
$lang_file = __DIR__ . '/../lang/' . $lang . '.php';
$L = is_file($lang_file) ? require $lang_file : [];

function t($key, $fallback = '') {
    global $L;
    return isset($L[$key]) ? $L[$key] : $fallback;
}
