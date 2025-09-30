<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit;
}

$login = $_POST['login'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($login) || empty($password)) {
    $_SESSION['error'] = 'Заполните все поля';
    header('Location: /');
    exit;
}

$db = Database::getConnection();

// Проверяем администратора
$stmt = $db->prepare("SELECT * FROM admins WHERE login = ?");
$stmt->execute([$login]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin && password_verify($password, $admin['password'])) {
    $_SESSION['user_id'] = $admin['id'];
    $_SESSION['user_type'] = 'admin';
    $_SESSION['user_name'] = 'Администратор';
    header('Location: /admin');
    exit;
}

// Проверяем преподавателя
$stmt = $db->prepare("SELECT * FROM teachers WHERE login = ?");
$stmt->execute([$login]);
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);

if ($teacher && $teacher['password'] === $password) {
    $_SESSION['user_id'] = $teacher['id'];
    $_SESSION['user_type'] = 'teacher';
    $_SESSION['user_name'] = $teacher['full_name'];
    header('Location: /teacher');
    exit;
}

$_SESSION['error'] = 'Неверный логин или пароль';
header('Location: /');
exit;