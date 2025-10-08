<?php
if (isset($_SESSION['user_type'])) {
    header('Location: ' . BASE_PATH . '/' . $_SESSION['user_type']);
    exit;
}
?>
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QRПосещение - Вход</title>
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
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h1 {
            color: <?php echo BRAND_COLOR; ?>;
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        input:focus {
            outline: none;
            border-color: <?php echo BRAND_COLOR; ?>;
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
        .error {
            background: #ffe6e6;
            color: #d63031;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .info {
            margin-top: 20px;
            padding: 15px;
            background: #f0f0f0;
            border-radius: 5px;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>QRПосещение</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error">
                <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo BASE_PATH; ?>/login">
            <div class="form-group">
                <label for="login">Логин</label>
                <input type="text" id="login" name="login" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit">Войти</button>
        </form>
        
        <div class="info">
            <strong>Данные для входа по умолчанию:</strong><br>
            Логин: admin<br>
            Пароль: admin123
        </div>
    </div>
</body>
</html>