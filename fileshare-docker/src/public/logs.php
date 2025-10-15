<?php
// public/logs.php
require_once __DIR__ . '/../app/bootstrap.php';

// 관리자가 아니면 접근 거부
if (!is_admin()) {
    header('Location: 403.php');
    exit();
}

// 로그 삭제 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_logs') {
    if (clear_logs($pdo)) {
        $message = "모든 로그가 성공적으로 삭제되었습니다.";
    } else {
        $message = "로그 삭제 중 오류가 발생했습니다.";
    }
    // GET 요청으로 리디렉션하여 새로고침 시 폼 재전송 방지
    header('Location: logs.php?message=' . urlencode($message));
    exit();
}

// 업로드 및 다운로드 로그 가져오기
$upload_logs = get_logs_by_action($pdo, 'upload');
$download_logs = get_logs_by_action($pdo, 'download');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>활동 기록 - File Share</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="app-container">
        <!-- 사이드바 -->
        <nav class="sidebar">
            <div class="sidebar-header"><h2>Menu</h2></div>
            <ul>
                <li><a href="dashboard.php">파일 공유</a></li>
                <?php if (is_admin()): ?>
                    <li class="active"><a href="logs.php">기록보기</a></li>
                <?php endif; ?>
                <li><a href="logout.php">로그아웃</a></li>
            </ul>
            <div class="sidebar-footer"><p>Logged in as: <strong><?php echo e($_SESSION['username']); ?></strong></p></div>
        </nav>

        <!-- 메인 컨텐츠 -->
        <main class="main-content">
            <header class="main-header">
                <h1>활동 기록 (관리자)</h1>
            </header>

            <?php if (isset($_GET['message'])): ?>
                <p class="message success"><?php echo e($_GET['message']); ?></p>
            <?php endif; ?>

            <section class="delete-logs-section">
                <form action="logs.php" method="post" onsubmit="return confirm('정말로 모든 로그를 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.');">
                    <input type="hidden" name="action" value="delete_logs">
                    <button type="submit" class="delete-logs-btn">전체 로그 삭제</button>
                </form>
            </section>

            <div class="logs-container">
                <!-- 업로드 기록 -->
                <div class="log-table-wrapper">
                    <h2>업로드 기록</h2>
                    <table class="log-table">
                        <thead>
                            <tr>
                                <th>파일명</th>
                                <th>시간</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($upload_logs)): ?>
                                <tr><td colspan="2">업로드 기록이 없습니다.</td></tr>
                            <?php else: ?>
                                <?php foreach ($upload_logs as $log): ?>
                                    <tr>
                                        <td><?php echo e($log['filename']); ?></td>
                                        <td><?php echo $log['timestamp']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- 다운로드 기록 -->
                <div class="log-table-wrapper">
                    <h2>다운로드 기록</h2>
                    <table class="log-table">
                        <thead>
                            <tr>
                                <th>파일명</th>
                                <th>시간</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($download_logs)): ?>
                                <tr><td colspan="2">다운로드 기록이 없습니다.</td></tr>
                            <?php else: ?>
                                <?php foreach ($download_logs as $log): ?>
                                    <tr>
                                        <td><?php echo e($log['filename']); ?></td>
                                        <td><?php echo $log['timestamp']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
