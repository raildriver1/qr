<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Неверный метод']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$db = Database::getConnection();

$lessonId = $data['lesson_id'] ?? null;
$studentId = $data['student_id'] ?? null;
$deviceId = $data['device_id'] ?? null;
$qrCode = $data['qr_code'] ?? null;

if (!$lessonId || !$studentId || !$deviceId || !$qrCode) {
    echo json_encode(['success' => false, 'message' => 'Не все данные переданы']);
    exit;
}

// Проверяем, что QR-код соответствует занятию
$stmt = $db->prepare("SELECT created_at FROM lessons WHERE id = ? AND qr_code = ?");
$stmt->execute([$lessonId, $qrCode]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lesson) {
    echo json_encode(['success' => false, 'message' => 'Неверный код занятия']);
    exit;
}

// Проверяем время (15 минут)
$createdTime = strtotime($lesson['created_at']);
$currentTime = time();
$elapsed = ($currentTime - $createdTime) / 60;

if ($elapsed > QR_LIFETIME) {
    echo json_encode(['success' => false, 'message' => 'Время для отметки истекло']);
    exit;
}

// Проверяем, не отмечен ли уже этот студент
$stmt = $db->prepare("SELECT id FROM attendance WHERE lesson_id = ? AND student_id = ?");
$stmt->execute([$lessonId, $studentId]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Этот студент уже отмечен на данном занятии']);
    exit;
}

// Отмечаем студента
try {
    $stmt = $db->prepare("
        INSERT INTO attendance (lesson_id, student_id, marked_at, marked_by)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $lessonId,
        $studentId,
        date('Y-m-d H:i:s'),
        'qr'
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Вы успешно отметились!']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка при отметке: ' . $e->getMessage()]);
}