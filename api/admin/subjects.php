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
        $stmt = $db->prepare("SELECT * FROM subjects WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    } else {
        $stmt = $db->query("SELECT * FROM subjects ORDER BY name");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}

elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        $stmt = $db->prepare("INSERT INTO subjects (name) VALUES (?)");
        $stmt->execute([$data['name']]);
        echo json_encode(['success' => true, 'message' => 'Предмет добавлен']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
    }
}

elseif ($method === 'PUT') {
    $id = $_GET['id'];
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        $stmt = $db->prepare("UPDATE subjects SET name = ? WHERE id = ?");
        $stmt->execute([$data['name'], $id]);
        echo json_encode(['success' => true, 'message' => 'Предмет обновлен']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
    }
}

elseif ($method === 'DELETE') {
    $id = $_GET['id'];
    
    try {
        $stmt = $db->prepare("DELETE FROM subjects WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Предмет удален']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
    }
}