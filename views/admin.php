<?php
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ' . BASE_PATH . '/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
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
            overflow-x: auto;
        }
        .tab {
            padding: 12px 24px;
            background: white;
            border: 2px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            white-space: nowrap;
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
            color: #333;
        }
        .action-btn {
            padding: 6px 12px;
            margin-right: 5px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }
        .edit-btn {
            background: #3498db;
            color: white;
        }
        .delete-btn {
            background: #e74c3c;
            color: white;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .checkbox-list {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
        }
        .checkbox-item {
            padding: 5px 0;
        }
        @media (max-width: 768px) {
            .container {
                padding: 0 10px;
            }
            .tab-content {
                padding: 20px;
            }
            table {
                font-size: 14px;
            }
            th, td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Панель администратора</h1>
        <a href="<?php echo BASE_PATH; ?>/logout" class="logout-btn">Выход</a>
    </div>

    <div class="container">
        <div class="tabs">
            <div class="tab active" onclick="switchTab('teachers')">Преподаватели</div>
            <div class="tab" onclick="switchTab('groups')">Группы</div>
            <div class="tab" onclick="switchTab('subjects')">Предметы</div>
            <div class="tab" onclick="switchTab('students')">Студенты</div>
        </div>

        <!-- Преподаватели -->
        <div id="teachers" class="tab-content active">
            <h2>Управление преподавателями</h2>
            <div id="teachers-message"></div>
            
            <form id="teacher-form">
                <input type="hidden" id="teacher-id">
                <div class="form-group">
                    <label>ФИО</label>
                    <input type="text" id="teacher-name" required>
                </div>
                <div class="form-group">
                    <label>Логин</label>
                    <input type="text" id="teacher-login" required>
                </div>
                <div class="form-group">
                    <label>Пароль</label>
                    <input type="text" id="teacher-password" required>
                </div>
                <div class="form-group">
                    <label>Предметы</label>
                    <div id="teacher-subjects" class="checkbox-list"></div>
                </div>
                <button type="submit">Сохранить</button>
                <button type="button" onclick="resetTeacherForm()">Отменить</button>
            </form>

            <table id="teachers-table">
                <thead>
                    <tr>
                        <th>ФИО</th>
                        <th>Логин</th>
                        <th>Пароль</th>
                        <th>Предметы</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <!-- Группы -->
        <div id="groups" class="tab-content">
            <h2>Управление группами</h2>
            <div id="groups-message"></div>
            
            <form id="group-form">
                <input type="hidden" id="group-id">
                <div class="form-group">
                    <label>Название группы</label>
                    <input type="text" id="group-name" required>
                </div>
                <button type="submit">Сохранить</button>
                <button type="button" onclick="resetGroupForm()">Отменить</button>
            </form>

            <table id="groups-table">
                <thead>
                    <tr>
                        <th>Название</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <!-- Предметы -->
        <div id="subjects" class="tab-content">
            <h2>Управление предметами</h2>
            <div id="subjects-message"></div>
            
            <form id="subject-form">
                <input type="hidden" id="subject-id">
                <div class="form-group">
                    <label>Название предмета</label>
                    <input type="text" id="subject-name" required>
                </div>
                <button type="submit">Сохранить</button>
                <button type="button" onclick="resetSubjectForm()">Отменить</button>
            </form>

            <table id="subjects-table">
                <thead>
                    <tr>
                        <th>Название</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <!-- Студенты -->
        <div id="students" class="tab-content">
            <h2>Управление студентами</h2>
            <div id="students-message"></div>
            
            <form id="student-form">
                <input type="hidden" id="student-id">
                <div class="form-group">
                    <label>ФИО студента</label>
                    <input type="text" id="student-name" required>
                </div>
                <div class="form-group">
                    <label>Группа</label>
                    <select id="student-group" required></select>
                </div>
                <button type="submit">Сохранить</button>
                <button type="button" onclick="resetStudentForm()">Отменить</button>
            </form>

            <table id="students-table">
                <thead>
                    <tr>
                        <th>ФИО</th>
                        <th>Группа</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
<script>
        window.BASE_PATH = '<?php echo BASE_PATH; ?>';
    </script>
    <script src="<?php echo BASE_PATH; ?>/assets/admin.js"></script>
</body>
</html>