<?php
session_start();
require_once 'config.php';
require_once 'database.php';

// Инициализация БД
Database::init();

// Простой роутинг
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

// Удаляем query string из пути
$route = strtok($path, '?');

// Роутинг с поддержкой вложенных путей
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
    // Статические файлы обрабатываются встроенным сервером
    return false;
} else {
    http_response_code(404);
    echo "404 - Страница не найдена: " . htmlspecialchars($route);
}