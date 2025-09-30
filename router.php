<?php
// Роутер для встроенного PHP сервера

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Если это файл статических ресурсов - отдаём его
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Все остальные запросы идут через index.php
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/index.php';