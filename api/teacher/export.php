<?php
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'teacher') {
    echo 'Доступ запрещен';
    exit;
}

$groupId = $_GET['group_id'] ?? null;

if (!$groupId) {
    echo 'Не указана группа';
    exit;
}

$db = Database::getConnection();
$teacherId = $_SESSION['user_id'];

// Получаем название группы
$stmt = $db->prepare("SELECT name FROM groups WHERE id = ?");
$stmt->execute([$groupId]);
$groupName = $stmt->fetchColumn();

// Получаем студентов группы
$stmt = $db->prepare("SELECT id, full_name FROM students WHERE group_id = ? ORDER BY full_name");
$stmt->execute([$groupId]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем занятия преподавателя для этой группы
$stmt = $db->prepare("
    SELECT l.id, l.date, l.topic, s.name as subject
    FROM lessons l
    JOIN subjects s ON l.subject_id = s.id
    WHERE l.teacher_id = ? AND l.group_id = ?
    ORDER BY l.date, l.created_at
");
$stmt->execute([$teacherId, $groupId]);
$lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем все отметки для этих занятий
$lessonIds = array_column($lessons, 'id');
$attendance = [];

if (!empty($lessonIds)) {
    $placeholders = str_repeat('?,', count($lessonIds) - 1) . '?';
    $stmt = $db->prepare("SELECT lesson_id, student_id FROM attendance WHERE lesson_id IN ($placeholders)");
    $stmt->execute($lessonIds);
    
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $attendance[$row['lesson_id']][$row['student_id']] = true;
    }
}

// Формируем CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="attendance_' . $groupName . '_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

// Добавляем BOM для корректного отображения кириллицы в Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Заголовки
$headers = ['ФИО студента'];
foreach ($lessons as $lesson) {
    $headers[] = $lesson['date'] . ' - ' . $lesson['subject'] . ' (' . $lesson['topic'] . ')';
}
fputcsv($output, $headers, ';', '"', '\\');


// Данные по студентам
foreach ($students as $student) {
    $row = [$student['full_name']];
    
    foreach ($lessons as $lesson) {
        $present = isset($attendance[$lesson['id']][$student['id']]);
        $row[] = $present ? '+' : '-';
    }
    
    fputcsv($output, $row, ';', '"', '\\');
}

fclose($output);
exit;