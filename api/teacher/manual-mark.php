<?php
header('Content-Type: application/json');

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Неверный метод']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$db = Database::getConnection();

try {
    $stmt = $db->prepare("
        INSERT INTO attendance (lesson_id, student_id, marked_at, marked_by)
        VALUES (?, ?, ?, 'manual')
    ");
    
    $stmt->execute([
        $data['lesson_id'],
        $data['student_id'],
        date('Y-m-d H:i:s')
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Студент отмечен']);
    
} catch (PDOException $e) {
    // Если студент уже отмечен
    if ($e->getCode() == 23000) {
        echo json_encode(['success' => false, 'message' => 'Студент уже отмечен']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
    }
}