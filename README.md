# OKFKS_LabWork2

# Создание новой базы данных

СУБД — MySQL Workbench

- Скрипт для создания базы данных

```
CREATE DATABASE lab_security;

USE lab_security;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL
);

CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    comment TEXT NOT NULL
);

-- Добавляем пользователя для тестирования
INSERT INTO users (username, password, email) VALUES ('admin', 'password', 'example@mail.ru');

INSERT INTO users (username, password, email) VALUES ('user', 'password', 'other@mail.ru');


CREATE USER 'lab_security'@'localhost' identified with mysql_native_password BY 'lab_security';

GRANT ALL PRIVILEGES ON lab_security.* TO 'lab_security'@'localhost';
```

Скриншот созданной базы данных

<img width="165" height="115" alt="image" src="https://github.com/user-attachments/assets/55ba1ab2-2fd2-4ad6-90c4-0f1f41e66be6" />

# Код с уязвимостями к SQL-инъекцией

```
<?php
session_start();

$host = 'localhost';
$user = 'lab_security';
$pass = 'lab_security';
$dbname = 'lab_security';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_errno) {
    die("Не удалось подключиться к БД: " . $conn->connect_error);
}

// Маршрутизация: ?page=login | comments | update-email | logout | home
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($page === 'login') {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            $_SESSION['username'] = $username;
            $login_message = "Добро пожаловать, $username!";
        } else {
            $login_message = "Неверное имя пользователя или пароль.";
        }
    }

    if ($page === 'comments') {
        $username = $_POST['username'];
        $comment = $_POST['comment'];

        $query = "INSERT INTO comments (username, comment) VALUES ('$username', '$comment')";
        $conn->query($query);
		
        header('Location: ?page=comments');
        exit;
    }

    if ($page === 'update-email') {
        $username = $_POST['username'];
        $new_email = $_POST['new_email'];
        $query = "UPDATE users SET email = '$new_email' WHERE username = '$username'";
        $result = $conn->query($query);

        if ($result) {
            $update_message = "Email for user $username updated to $new_email"; // XSS possible
        }
    }
}

$comments_result = $conn->query("SELECT * FROM comments");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lab Security — Unified App</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        nav a { margin-right: 10px; }
        form { margin-bottom: 20px; }
        .message { padding: 8px; background: #efefef; border-radius: 4px; margin-bottom: 12px; }
    </style>
</head>
<body>
    <h1>Lab Security</h1>
    <nav>
        <a href="?page=home">Главная</a>
        <a href="?page=login">Вход</a>
		<?php if (isset($_SESSION['username'])): ?>
			<a href="?page=comments">Комментарии</a>
			<a href="?page=update-email">Обновить email</a>
            <a href="?page=logout">Выйти (<?php echo $_SESSION['username']; ?>)</a>
        <?php endif; ?>
    </nav>

    <hr>

    <?php if ($page === 'home'): ?>
        <h2>Главная</h2>
        <p>Данное приложение демонстрирует применение SQL-инъекций, XSS-уязвимостей и уязвимостей авторизации</p>
        <?php if (isset($login_message)): ?><div class="message"><?php echo $login_message; ?></div><?php endif; ?>
        <?php if (isset($update_message)): ?><div class="message"><?php echo $update_message; ?></div><?php endif; ?>

        <h3>Текущий пользователь</h3>
        <p><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Гость'; ?></p>

    <?php elseif ($page === 'login'): ?>
        <h2>Вход</h2>
        <?php if (isset($login_message)): ?><div class="message"><?php echo $login_message; ?></div><?php endif; ?>
        <form method="POST" action="?page=login">
            Логин: <input type="text" name="username"><br>
            Пароль: <input type="password" name="password"><br>
            <input type="submit" value="Login">
        </form>

    <?php elseif ($page === 'comments'): ?>
        <h2>Комментарии</h2>
        <form method="POST" action="?page=comments">
            Логин: <input type="text" name="username" value="<?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?>"><br>
            Комментарий: <textarea name="comment"></textarea><br>
            <input type="submit" value="Отправить">
        </form>

        <h3>Все комментарии:</h3>
        <ul>
            <?php while ($row = $comments_result->fetch_assoc()): ?>
                <li><strong><?php echo $row['username']; ?>:</strong> <?php echo $row['comment']; ?></li>
            <?php endwhile; ?>
        </ul>

    <?php elseif ($page === 'update-email'): ?>
        <h2>Сменить email</h2>
        <form method="POST" action="?page=update-email">
            <label for="username">Логин</label>
            <input type="text" id="username" name="username" required value="<?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?>"><br>
            <label for="new_email">Новый Email:</label>
            <input type="email" id="new_email" name="new_email" required><br>
            <input type="submit" value="Update Email">
        </form>

        <?php if (isset($update_message)): ?><div class="message"><?php echo $update_message; ?></div><?php endif; ?>

    <?php elseif ($page === 'logout'): ?>
        <?php session_destroy(); header('Location: ?page=home'); exit; ?>

    <?php else: ?>
        <h2>Not Found</h2>
        <p>Unknown page.</p>
    <?php endif; ?>

</body>
</html>
```
# Скриншот приложения

<img width="1437" height="574" alt="image" src="https://github.com/user-attachments/assets/a3509f06-85b4-405b-ac67-b474e258b717" />

# Тестирование приложения
| Действие | Результат |
|----------|-----------|
| Выполнить авторизацию без учетных данных ``` Логин: ' OR '1'='1 Пароль: ' OR '1'='1 ```| <img width="853" height="480" alt="image" src="https://github.com/user-attachments/assets/39c3b71c-c822-4ada-b73c-b68089e2597a" />|
|Вход по учётной записью админа без пароля ``` Логин: admin'-- Пароль: 111 `` |<img width="590" height="309" alt="image" src="https://github.com/user-attachments/assets/dff791e2-c734-47cb-b201-3c28b298ef66" /> |
 

