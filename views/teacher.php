<?php
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: /');
    exit;
}

$db = Database::getConnection();
$teacherId = $_SESSION['user_id'];

// Получаем предметы преподавателя
$stmt = $db->prepare("
    SELECT s.id, s.name
    FROM subjects s
    JOIN teacher_subjects ts ON s.id = ts.subject_id
    WHERE ts.teacher_id = ?
");
$stmt->execute([$teacherId]);
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем все группы
$groups = $db->query("SELECT * FROM groups ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Кабинет преподавателя</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f5f5;
        }
        .header {
            background: white;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            color: <?php echo BRAND_COLOR; ?>;
            font-size: 24px;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .logout-btn {
            padding: 10px 20px;
            background: <?php echo BRAND_COLOR; ?>;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: opacity 0.3s;
        }
        .logout-btn:hover {
            opacity: 0.9;
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
        }
        .tab {
            padding: 12px 24px;
            background: white;
            border: 2px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .tab.active {
            background: <?php echo BRAND_COLOR; ?>;
            color: white;
            border-color: <?php echo BRAND_COLOR; ?>;
        }
        .tab-content {
            display: none;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .tab-content.active {
            display: block;
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
        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        button {
            padding: 12px 24px;
            background: <?php echo BRAND_COLOR; ?>;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: opacity 0.3s;
        }
        button:hover {
            opacity: 0.9;
        }
        .qr-container {
            text-align: center;
            padding: 30px;
            background: #f9f9f9;
            border-radius: 10px;
            margin: 20px 0;
        }
        .qr-code {
            margin: 20px 0;
        }
        .timer {
            font-size: 24px;
            font-weight: bold;
            color: <?php echo BRAND_COLOR; ?>;
            margin: 10px 0;
        }
        .students-list {
            margin-top: 30px;
        }
        .student-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .student-item.present {
            background: #d4edda;
        }
        .status {
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
        }
        .status.present {
            background: #28a745;
            color: white;
        }
        .status.absent {
            background: #dc3545;
            color: white;
        }
        .mark-btn {
            padding: 8px 16px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f8f8;
            font-weight: 600;
        }
        .export-btn {
            background: #28a745;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .container {
                padding: 0 10px;
            }
            .tab-content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Кабинет преподавателя</h1>
        <div class="user-info">
            <span><?php echo $_SESSION['user_name']; ?></span>
            <a href="/logout" class="logout-btn">Выход</a>
        </div>
    </div>

    <div class="container">
        <div class="tabs">
            <div class="tab active" onclick="switchTab('create')">Создать занятие</div>
            <div class="tab" onclick="switchTab('history')">История занятий</div>
        </div>

        <!-- Создание занятия -->
        <div id="create" class="tab-content active">
            <h2>Создать новое занятие</h2>
            
            <div id="create-form">
                <form id="lesson-form">
                    <div class="form-group">
                        <label>Предмет</label>
                        <select id="subject" required>
                            <option value="">Выберите предмет</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?php echo $subject['id']; ?>"><?php echo $subject['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Группа</label>
                        <select id="group" required>
                            <option value="">Выберите группу</option>
                            <?php foreach ($groups as $group): ?>
                                <option value="<?php echo $group['id']; ?>"><?php echo $group['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Тема занятия</label>
                        <input type="text" id="topic" required>
                    </div>
                    
                    <button type="submit">Создать занятие</button>
                </form>
            </div>

            <div id="active-lesson" style="display: none;">
                <h2>Активное занятие</h2>
                <p id="lesson-info"></p>
                
                <div class="qr-container">
                    <h3>QR-код для студентов</h3>
                    <div class="timer" id="timer">15:00</div>
                    <div id="qr-code" class="qr-code"></div>
                    <p>Студенты должны отсканировать этот код и выбрать свое ФИО</p>
                </div>

                <div class="students-list">
                    <h3>Список студентов группы</h3>
                    <div id="students-container"></div>
                </div>

                <button onclick="endLesson()">Завершить занятие</button>
            </div>
        </div>

        <!-- История занятий -->
        <div id="history" class="tab-content">
            <h2>История занятий</h2>
            <button class="export-btn" onclick="exportReport()">Выгрузить отчет (Excel)</button>
            
            <div id="export-form" style="display: none; margin-bottom: 20px;">
                <select id="export-group">
                    <option value="">Выберите группу</option>
                    <?php foreach ($groups as $group): ?>
                        <option value="<?php echo $group['id']; ?>"><?php echo $group['name']; ?></option>
                    <?php endforeach; ?>
                </select>
                <button onclick="doExport()">Скачать</button>
            </div>

            <table id="lessons-table">
                <thead>
                    <tr>
                        <th>Дата</th>
                        <th>Предмет</th>
                        <th>Группа</th>
                        <th>Тема</th>
                        <th>Присутствовало</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="/assets/teacher.js"></script>
</body>
</html>