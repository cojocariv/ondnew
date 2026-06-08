<?php
$supported_langs = ['ro', 'en', 'ru'];
$lang = 'ro';

if (isset($_GET['lang']) && in_array($_GET['lang'], $supported_langs, true)) {
    $lang = $_GET['lang'];
    setcookie('lang', $lang, ['expires' => time() + 86400 * 365, 'path' => '/', 'samesite' => 'Lax']);
} elseif (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], $supported_langs, true)) {
    $lang = $_COOKIE['lang'];
}

$lang_file = dirname(__DIR__) . '/lang/' . $lang . '.php';
$L = is_file($lang_file) ? require $lang_file : require dirname(__DIR__) . '/lang/ro.php';

function t(string $key, ...$args): string
{
    global $L;
    $value = $L[$key] ?? $key;
    return $args ? sprintf($value, ...$args) : $value;
}

function lang_url(string $code): string
{
    $path = strtok($_SERVER['REQUEST_URI'] ?? '/', '?') ?: '/';
    return $path . '?lang=' . urlencode($code);
}

function lang_html(): string
{
    global $lang;
    return ['ro' => 'ro', 'en' => 'en', 'ru' => 'ru'][$lang] ?? 'ro';
}

function lang_og_locale(): string
{
    global $lang;
    return ['ro' => 'ro_MD', 'en' => 'en_US', 'ru' => 'ru_RU'][$lang] ?? 'ro_MD';
}

function lang_schema(): string
{
    global $lang;
    return ['ro' => 'ro-MD', 'en' => 'en', 'ru' => 'ru'][$lang] ?? 'ro-MD';
}
