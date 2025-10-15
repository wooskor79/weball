<?php
// public/login.php (수정됨)
require_once __DIR__ . '/../app/bootstrap.php';

if (is_logged_in()) {
    // 이미 로그인 상태라면 대시보드로 보냅니다.
    header('Location: dashboard.php');
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (login($pdo, $username, $password)) {
        // 로그인 성공 시 대시보드로 보냅니다.
        header('Location: dashboard.php');
        exit();
    } else {
        $error_message = '사용자 이름 또는 비밀번호가 올바르지 않습니다.';
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - File Share</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>File Share Service</h1>
            <p class="login-subtitle">로그인하여 서비스를 이용하세요.</p>

            <?php if ($error_message): ?>
                <p class="error-message"><?php echo e($error_message); ?></p>
            <?php endif; ?>

            <form action="login.php" method="post">
                <div class="input-group">
                    <label for="username">사용자 이름</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="input-group">
                    <label for="password">비밀번호</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="login-button">로그인</button>
            </form>
        </div>
    </div>
    <script src="js/main.js"></script>
</body>
</html>