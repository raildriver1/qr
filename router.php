<?php
// Роутер для встроенного PHP сервера и Apache

// Для встроенного сервера PHP
if (php_sapi_name() === 'cli-server') {
    $uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    
    // Если это файл статических ресурсов - отдаём его
    if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
        return false;
    }
}

// Запускаем основное приложение
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/index.php';