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

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($page === 'login') {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE username=? AND password=?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $_SESSION['username'] = $username;
            $login_message = "Добро пожаловать, " . htmlspecialchars($username);
        } else {
            $login_message = "Неверное имя пользователя или пароль.";
        }

        $stmt->close();
    }

    if ($page === 'comments' && isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
        $comment = $_POST['comment'];

        $stmt = $conn->prepare("INSERT INTO comments (username, comment) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $comment);
        $stmt->execute();
        $stmt->close();

        header('Location: ?page=comments');
        exit;
    }

    if ($page === 'update-email' && isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
        $new_email = $_POST['new_email'];

        $stmt = $conn->prepare("UPDATE users SET email=? WHERE username=?");
        $stmt->bind_param("ss", $new_email, $username);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $update_message = "Email for user " . htmlspecialchars($username) . " updated to " . htmlspecialchars($new_email);
        }
        $stmt->close();
    }
}

$comments_result = $conn->query("SELECT username, comment FROM comments");
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
            <a href="?page=logout">Выйти (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
        <?php endif; ?>
    </nav>

    <hr>

    <?php if ($page === 'home'): ?>
        <h2>Главная</h2>
        <p>Данное приложение демонстрирует безопасное использование SQL, XSS и авторизации</p>
        <?php if (isset($login_message)): ?><div class="message"><?php echo $login_message; ?></div><?php endif; ?>
        <?php if (isset($update_message)): ?><div class="message"><?php echo $update_message; ?></div><?php endif; ?>

        <h3>Текущий пользователь</h3>
        <p><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Гость'; ?></p>

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
        <?php if (isset($_SESSION['username'])): ?>
        <form method="POST" action="?page=comments">
            Комментарий: <textarea name="comment"></textarea><br>
            <input type="submit" value="Отправить">
        </form>
        <?php endif; ?>

        <h3>Все комментарии:</h3>
        <ul>
            <?php while ($row = $comments_result->fetch_assoc()): ?>
                <li><strong><?php echo htmlspecialchars($row['username']); ?>:</strong> <?php echo htmlspecialchars($row['comment']); ?></li>
            <?php endwhile; ?>
        </ul>

    <?php elseif ($page === 'update-email'): ?>
        <?php if (isset($_SESSION['username'])): ?>
        <h2>Сменить email</h2>
        <form method="POST" action="?page=update-email">
            Новый Email: <input type="email" name="new_email" required><br>
            <input type="submit" value="Update Email">
        </form>
        <?php endif; ?>
        <?php if (isset($update_message)): ?><div class="message"><?php echo $update_message; ?></div><?php endif; ?>

    <?php elseif ($page === 'logout'): ?>
        <?php session_destroy(); header('Location: ?page=home'); exit; ?>

    <?php else: ?>
        <h2>Not Found</h2>
        <p>Unknown page.</p>
    <?php endif; ?>
</body>
</html>
