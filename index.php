<?php
session_start();
require_once 'config.php';
require_once 'database.php';

// Инициализация БД
Database::init();

// Определяем базовый путь
define('BASE_PATH', '/test/qr-main');

// Получаем URI и убираем базовый путь
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

// Убираем базовый путь из URL
if (strpos($path, BASE_PATH) === 0) {
    $path = substr($path, strlen(BASE_PATH));
}

// Если путь пустой, ставим /
if (empty($path) || $path === '') {
    $path = '/';
}

// Убираем query string
$route = strtok($path, '?');

// Роутинг
if ($route === '/' || $route === '/index.php') {
    require 'views/login.php';
} elseif ($route === '/login') {
    require 'controllers/auth.php';
} elseif ($route === '/logout') {
    require 'controllers/logout.php';
} elseif ($route === '/admin') {
    require 'views/admin.php';
} elseif ($route === '/teacher') {
    require 'views/teacher.php';
} elseif ($route === '/student') {
    require 'controllers/student.php';
} elseif (strpos($route, '/api/admin/teachers') === 0) {
    require 'api/admin/teachers.php';
} elseif (strpos($route, '/api/admin/groups') === 0) {
    require 'api/admin/groups.php';
} elseif (strpos($route, '/api/admin/subjects') === 0) {
    require 'api/admin/subjects.php';
} elseif (strpos($route, '/api/admin/students') === 0) {
    require 'api/admin/students.php';
} elseif (strpos($route, '/api/teacher/create-lesson') === 0) {
    require 'api/teacher/create-lesson.php';
} elseif (strpos($route, '/api/teacher/lessons') === 0) {
    require 'api/teacher/lessons.php';
} elseif (strpos($route, '/api/teacher/manual-mark') === 0) {
    require 'api/teacher/manual-mark.php';
} elseif (strpos($route, '/api/teacher/export') === 0) {
    require 'api/teacher/export.php';
} elseif (strpos($route, '/api/student/mark') === 0) {
    require 'api/student/mark.php';
} elseif (strpos($route, '/api/lesson-status') === 0) {
    require 'api/lesson-status.php';
} elseif (strpos($route, '/assets/') === 0) {
    return false;
} else {
    http_response_code(404);
    echo "404 - Страница не найдена: " . htmlspecialchars($route);
}