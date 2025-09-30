<?php
header('Content-Type: application/json');

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
    exit;
}

$db = Database::getConnection();
$teacherId = $_SESSION['user_id'];

$stmt = $db->prepare("
    SELECT 
        l.*,
        s.name as subject,
        g.name as group_name,
        (SELECT COUNT(*) FROM attendance WHERE lesson_id = l.id) as present_count
    FROM lessons l
    JOIN subjects s ON l.subject_id = s.id
    JOIN groups g ON l.group_id = g.id
    WHERE l.teacher_id = ?
    ORDER BY l.date DESC, l.created_at DESC
");

$stmt->execute([$teacherId]);
$lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($lessons);