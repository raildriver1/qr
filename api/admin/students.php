<?php
header('Content-Type: application/json');

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
    exit;
}

$db = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = $_GET['id'] ?? null;
    
    if ($id) {
        $stmt = $db->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    } else {
        $stmt = $db->query("
            SELECT s.*, g.name as group_name
            FROM students s
            LEFT JOIN groups g ON s.group_id = g.id
            ORDER BY g.name, s.full_name
        ");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}

elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        $stmt = $db->prepare("INSERT INTO students (full_name, group_id) VALUES (?, ?)");
        $stmt->execute([$data['full_name'], $data['group_id']]);
        echo json_encode(['success' => true, 'message' => 'Студент добавлен']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
    }
}

elseif ($method === 'PUT') {
    $id = $_GET['id'];
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        $stmt = $db->prepare("UPDATE students SET full_name = ?, group_id = ? WHERE id = ?");
        $stmt->execute([$data['full_name'], $data['group_id'], $id]);
        echo json_encode(['success' => true, 'message' => 'Студент обновлен']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
    }
}

elseif ($method === 'DELETE') {
    $id = $_GET['id'];
    
    try {
        $stmt = $db->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Студент удален']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
    }
}