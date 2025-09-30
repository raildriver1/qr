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
        $stmt = $db->prepare("SELECT * FROM groups WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    } else {
        $stmt = $db->query("SELECT * FROM groups ORDER BY name");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}

elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        $stmt = $db->prepare("INSERT INTO groups (name) VALUES (?)");
        $stmt->execute([$data['name']]);
        echo json_encode(['success' => true, 'message' => 'Группа добавлена']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
    }
}

elseif ($method === 'PUT') {
    $id = $_GET['id'];
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        $stmt = $db->prepare("UPDATE groups SET name = ? WHERE id = ?");
        $stmt->execute([$data['name'], $id]);
        echo json_encode(['success' => true, 'message' => 'Группа обновлена']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
    }
}

elseif ($method === 'DELETE') {
    $id = $_GET['id'];
    
    try {
        $stmt = $db->prepare("DELETE FROM groups WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Группа удалена']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
    }
}