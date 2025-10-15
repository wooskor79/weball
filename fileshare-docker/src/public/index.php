<?php
// public/index.php (새로운 메인 셸 페이지)
// 이 페이지는 주소창에 고정되며, 내부에 다른 페이지를 불러옵니다.

require_once __DIR__ . '/../app/bootstrap.php';

// 로그인 상태에 따라 내부 프레임에 표시할 페이지를 결정합니다.
// .php 확장자를 명시적으로 추가합니다.
$page_to_load = is_logged_in() ? 'dashboard.php' : 'login.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Share Service</title>
    <style>
        /* 화면을 가득 채우는 iframe 스타일 */
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
        }
        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
</head>
<body>
    <!-- 로그인 상태에 따라 'dashboard.php' 또는 'login.php' 페이지를 내부 프레임에 로드합니다. -->
    <iframe src="<?php echo $page_to_load; ?>"></iframe>
</body>
</html>

