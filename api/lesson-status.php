<?php
header('Content-Type: application/json');

$lessonId = $_GET['lesson_id'] ?? null;

if (!$lessonId) {
    echo json_encode(['success' => false, 'message' => 'Не указан ID занятия']);
    exit;
}

$db = Database::getConnection();

// Получаем информацию о занятии
$stmt = $db->prepare("SELECT group_id FROM lessons WHERE id = ?");
$stmt->execute([$lessonId]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lesson) {
    echo json_encode(['success' => false, 'message' => 'Занятие не найдено']);
    exit;
}

// Получаем список студентов группы с отметками
$stmt = $db->prepare("
    SELECT 
        s.id,
        s.full_name,
        CASE WHEN a.id IS NOT NULL THEN 1 ELSE 0 END as present
    FROM students s
    LEFT JOIN attendance a ON s.id = a.student_id AND a.lesson_id = ?
    WHERE s.group_id = ?
    ORDER BY s.full_name
");

$stmt->execute([$lessonId, $lesson['group_id']]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'students' => $students
]);