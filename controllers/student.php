<?php
$qrCode = $_GET['code'] ?? null;

if (!$qrCode) {
    echo '<!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ошибка</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                background: #f5f5f5;
                margin: 0;
                padding: 20px;
            }
            .error {
                background: white;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                text-align: center;
                max-width: 400px;
            }
            h1 {
                color: ' . BRAND_COLOR . ';
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body>
        <div class="error">
            <h1>Ошибка</h1>
            <p>Неверная ссылка для отметки посещаемости</p>
        </div>
    </body>
    </html>';
    exit;
}

$db = Database::getConnection();

// Проверяем существование занятия и его актуальность
$stmt = $db->prepare("
    SELECT l.*, g.name as group_name, s.name as subject_name
    FROM lessons l
    JOIN groups g ON l.group_id = g.id
    JOIN subjects s ON l.subject_id = s.id
    WHERE l.qr_code = ?
");
$stmt->execute([$qrCode]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lesson) {
    echo '<!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ошибка</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                background: #f5f5f5;
                margin: 0;
                padding: 20px;
            }
            .error {
                background: white;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                text-align: center;
                max-width: 400px;
            }
            h1 {
                color: ' . BRAND_COLOR . ';
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body>
        <div class="error">
            <h1>Ошибка</h1>
            <p>Занятие не найдено</p>
        </div>
    </body>
    </html>';
    exit;
}

// Проверяем, не истекло ли время
$createdTime = strtotime($lesson['created_at']);
$currentTime = time();
$elapsed = ($currentTime - $createdTime) / 60; // в минутах

if ($elapsed > QR_LIFETIME) {
    echo '<!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Время истекло</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                background: #f5f5f5;
                margin: 0;
                padding: 20px;
            }
            .error {
                background: white;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                text-align: center;
                max-width: 400px;
            }
            h1 {
                color: ' . BRAND_COLOR . ';
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body>
        <div class="error">
            <h1>Время истекло</h1>
            <p>Время для отметки на этом занятии истекло</p>
        </div>
    </body>
    </html>';
    exit;
}

// Получаем студентов группы
$stmt = $db->prepare("SELECT id, full_name FROM students WHERE group_id = ? ORDER BY full_name");
$stmt->execute([$lesson['group_id']]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отметка посещаемости</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
        }
        h1 {
            color: <?php echo BRAND_COLOR; ?>;
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
        }
        .lesson-info {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .lesson-info p {
            margin: 5px 0;
            color: #333;
        }
        .lesson-info strong {
            color: <?php echo BRAND_COLOR; ?>;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            width: 100%;
            padding: 14px;
            background: <?php echo BRAND_COLOR; ?>;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.3s;
        }
        button:hover {
            opacity: 0.9;
        }
        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .message {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            display: none;
        }
        .success {
            background: #d4edda;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Отметка посещаемости</h1>
        
        <div class="lesson-info">
            <p><strong>Предмет:</strong> <?php echo htmlspecialchars($lesson['subject_name']); ?></p>
            <p><strong>Группа:</strong> <?php echo htmlspecialchars($lesson['group_name']); ?></p>
            <p><strong>Тема:</strong> <?php echo htmlspecialchars($lesson['topic']); ?></p>
            <p><strong>Дата:</strong> <?php echo $lesson['date']; ?></p>
        </div>

        <div id="message" class="message"></div>

        <form id="attendance-form">
            <div class="form-group">
                <label for="student">Выберите ваше ФИО:</label>
                <select id="student" required>
                    <option value="">-- Выберите --</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo $student['id']; ?>">
                            <?php echo htmlspecialchars($student['full_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" id="submit-btn">Отметиться</button>
        </form>
    </div>

    <script>
        const lessonId = <?php echo $lesson['id']; ?>;
        const qrCode = '<?php echo $qrCode; ?>';
        
        // Проверяем, не отмечался ли уже с этого устройства
        const deviceId = localStorage.getItem('device_id') || generateDeviceId();
        localStorage.setItem('device_id', deviceId);
        
        const markedKey = `marked_${lessonId}`;
        if (localStorage.getItem(markedKey)) {
            showMessage('Вы уже отметились на этом занятии с этого устройства', 'error');
            document.getElementById('submit-btn').disabled = true;
        }
        
        function generateDeviceId() {
            return 'device_' + Math.random().toString(36).substr(2, 9) + Date.now();
        }
        
        function showMessage(text, type) {
            const msg = document.getElementById('message');
            msg.textContent = text;
            msg.className = 'message ' + type;
            msg.style.display = 'block';
        }
        
        document.getElementById('attendance-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const studentId = document.getElementById('student').value;
            if (!studentId) {
                showMessage('Выберите ваше ФИО', 'error');
                return;
            }
            
            const btn = document.getElementById('submit-btn');
            btn.disabled = true;
            btn.textContent = 'Отмечаем...';
            
            try {
                const response = await fetch('/api/student/mark', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        lesson_id: lessonId,
                        student_id: studentId,
                        device_id: deviceId,
                        qr_code: qrCode
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    localStorage.setItem(markedKey, 'true');
                    showMessage('Вы успешно отметились!', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showMessage(result.message, 'error');
                    btn.disabled = false;
                    btn.textContent = 'Отметиться';
                }
            } catch (error) {
                showMessage('Ошибка при отметке. Попробуйте снова', 'error');
                btn.disabled = false;
                btn.textContent = 'Отметиться';
            }
        });
    </script>
</body>
</html>