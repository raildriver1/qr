<?php
header('Content-Type: application/json');

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
    exit;
}

$db = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'];

// GET - Получить список или одного преподавателя
if ($method === 'GET') {
    $id = $_GET['id'] ?? null;
    
    if ($id) {
        // Получить одного преподавателя
        $stmt = $db->prepare("SELECT * FROM teachers WHERE id = ?");
        $stmt->execute([$id]);
        $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Получить его предметы
        $stmt = $db->prepare("SELECT subject_id FROM teacher_subjects WHERE teacher_id = ?");
        $stmt->execute([$id]);
        $teacher['subject_ids'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode($teacher);
    } else {
        // Получить всех преподавателей
        $stmt = $db->query("
            SELECT t.*, GROUP_CONCAT(s.name, ', ') as subjects
            FROM teachers t
            LEFT JOIN teacher_subjects ts ON t.id = ts.teacher_id
            LEFT JOIN subjects s ON ts.subject_id = s.id
            GROUP BY t.id
        ");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}

// POST - Создать преподавателя
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        $stmt = $db->prepare("INSERT INTO teachers (full_name, login, password) VALUES (?, ?, ?)");
        $stmt->execute([$data['full_name'], $data['login'], $data['password']]);
        $teacherId = $db->lastInsertId();
        
        // Добавляем предметы
        if (!empty($data['subjects'])) {
            $stmt = $db->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id) VALUES (?, ?)");
            foreach ($data['subjects'] as $subjectId) {
                $stmt->execute([$teacherId, $subjectId]);
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Преподаватель добавлен']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
    }
}

// PUT - Обновить преподавателя
elseif ($method === 'PUT') {
    $id = $_GET['id'];
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        $stmt = $db->prepare("UPDATE teachers SET full_name = ?, login = ?, password = ? WHERE id = ?");
        $stmt->execute([$data['full_name'], $data['login'], $data['password'], $id]);
        
        // Удаляем старые связи с предметами
        $db->prepare("DELETE FROM teacher_subjects WHERE teacher_id = ?")->execute([$id]);
        
        // Добавляем новые
        if (!empty($data['subjects'])) {
            $stmt = $db->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id) VALUES (?, ?)");
            foreach ($data['subjects'] as $subjectId) {
                $stmt->execute([$id, $subjectId]);
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Преподаватель обновлен']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
    }
}

// DELETE - Удалить преподавателя
elseif ($method === 'DELETE') {
    $id = $_GET['id'];
    
    try {
        $stmt = $db->prepare("DELETE FROM teachers WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Преподаватель удален']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
    }
}