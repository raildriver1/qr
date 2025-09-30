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

// Генерируем уникальный код для QR
$qrCode = bin2hex(random_bytes(16));
$createdAt = date('Y-m-d H:i:s');
$date = date('Y-m-d');

try {
    $stmt = $db->prepare("
        INSERT INTO lessons (teacher_id, subject_id, group_id, topic, date, qr_code, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $_SESSION['user_id'],
        $data['subject_id'],
        $data['group_id'],
        $data['topic'],
        $date,
        $qrCode,
        $createdAt
    ]);
    
    $lessonId = $db->lastInsertId();
    
    // Получаем названия для ответа
    $stmt = $db->prepare("SELECT name FROM subjects WHERE id = ?");
    $stmt->execute([$data['subject_id']]);
    $subject = $stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT name FROM groups WHERE id = ?");
    $stmt->execute([$data['group_id']]);
    $group = $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'lesson_id' => $lessonId,
        'qr_code' => $qrCode,
        'subject' => $subject,
        'group' => $group,
        'topic' => $data['topic'],
        'created_at' => $createdAt
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
}