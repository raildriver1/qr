<?php
class Database {
    private static $connection = null;
    
    public static function getConnection() {
        if (self::$connection === null) {
            try {
                self::$connection = new PDO('sqlite:' . DB_PATH);
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$connection->exec('PRAGMA foreign_keys = ON');
            } catch (PDOException $e) {
                die("Ошибка подключения к БД: " . $e->getMessage());
            }
        }
        return self::$connection;
    }
    
    public static function init() {
        $db = self::getConnection();
        
        // Таблица администраторов
        $db->exec("CREATE TABLE IF NOT EXISTS admins (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            login TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL
        )");
        
        // Таблица преподавателей
        $db->exec("CREATE TABLE IF NOT EXISTS teachers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            full_name TEXT NOT NULL,
            login TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL
        )");
        
        // Таблица групп
        $db->exec("CREATE TABLE IF NOT EXISTS groups (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT UNIQUE NOT NULL
        )");
        
        // Таблица предметов
        $db->exec("CREATE TABLE IF NOT EXISTS subjects (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT UNIQUE NOT NULL
        )");
        
        // Связь преподаватель-предмет (многие ко многим)
        $db->exec("CREATE TABLE IF NOT EXISTS teacher_subjects (
            teacher_id INTEGER,
            subject_id INTEGER,
            FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
            FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
            PRIMARY KEY (teacher_id, subject_id)
        )");
        
        // Таблица студентов
        $db->exec("CREATE TABLE IF NOT EXISTS students (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            full_name TEXT NOT NULL,
            group_id INTEGER,
            FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE
        )");
        
        // Таблица занятий
        $db->exec("CREATE TABLE IF NOT EXISTS lessons (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            teacher_id INTEGER,
            subject_id INTEGER,
            group_id INTEGER,
            topic TEXT NOT NULL,
            date DATE NOT NULL,
            qr_code TEXT UNIQUE NOT NULL,
            created_at DATETIME NOT NULL,
            FOREIGN KEY (teacher_id) REFERENCES teachers(id),
            FOREIGN KEY (subject_id) REFERENCES subjects(id),
            FOREIGN KEY (group_id) REFERENCES groups(id)
        )");
        
        // Таблица посещаемости
        $db->exec("CREATE TABLE IF NOT EXISTS attendance (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            lesson_id INTEGER,
            student_id INTEGER,
            marked_at DATETIME NOT NULL,
            marked_by TEXT DEFAULT 'qr',
            FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
            UNIQUE (lesson_id, student_id)
        )");
        
        // Создаём админа по умолчанию, если его нет
        $stmt = $db->query("SELECT COUNT(*) FROM admins");
        if ($stmt->fetchColumn() == 0) {
            $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $db->exec("INSERT INTO admins (login, password) VALUES ('admin', '$hashedPassword')");
        }
    }
}