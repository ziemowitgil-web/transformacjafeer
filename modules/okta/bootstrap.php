<?php
// Wczytanie pliku .env
if (!file_exists(__DIR__ . '/.env')) {
    die('Brak pliku .env w module Okta.');
}

$env = parse_ini_file(__DIR__ . '/.env');

define('OKTA_DOMAIN', $env['OKTA_DOMAIN'] ?? '');
define('OKTA_API_TOKEN', $env['OKTA_API_TOKEN'] ?? '');
define('API_AUTH_TOKEN', $env['API_AUTH_TOKEN'] ?? '');

if (!OKTA_DOMAIN || !OKTA_API_TOKEN || !API_AUTH_TOKEN) {
    die('Brak wymaganych zmiennych środowiskowych w module Okta.');
}
