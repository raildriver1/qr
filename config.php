<?php
// Конфигурация системы
define('DB_PATH', __DIR__ . '/data/attendance.db');
define('QR_LIFETIME', 15); // минут
define('BRAND_COLOR', 'rgb(149, 37, 32)');

// Создаём папку data если её нет
if (!file_exists(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0777, true);
}

// Timezone
date_default_timezone_set('Europe/Moscow');