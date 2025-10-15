<?php
// public/dashboard.php (수정됨)
require_once __DIR__ . '/../app/bootstrap.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

// --- 경로 설정 및 보안 ---
$current_path_raw = $_GET['path'] ?? '';
$current_path = sanitize_path($current_path_raw);
$full_current_path = SHARE_FOLDER . $current_path;

// ⬇️ 수정된 부분: realpath가 실패하거나 디렉토리가 아닐 경우, 무한 리디렉션 대신 오류 메시지를 출력하고 실행을 중지합니다.
if (realpath($full_current_path) === false || !is_dir($full_current_path) || strpos(realpath($full_current_path), realpath(SHARE_FOLDER)) !== 0) {
    http_response_code(500);
    die("오류: 설정된 공유 폴더(SHARE_FOLDER)를 찾을 수 없습니다. <br>1. app/config.php 파일의 경로가 올바른지 확인하세요. <br>2. docker-compose.yml 파일에 공유 폴더 볼륨 마운트가 올바르게 설정되었는지 확인하세요.");
}


$upload_message = '';
$upload_error = false;

// 파일 업로드 처리
if (is_admin() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['shared_file'])) {
    $file = $_FILES['shared_file'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $target_path = $full_current_path . '/' . basename($file['name']);
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            log_action($pdo, $_SESSION['username'], 'upload', ltrim($current_path . '/' . $file['name'], '/'));
            $upload_message = "파일 '" . e($file['name']) . "' 업로드 성공!";
        } else {
            $upload_message = "파일 업로드 중 오류가 발생했습니다. 폴더 권한을 확인하세요.";
            $upload_error = true;
        }
    } else {
        $upload_message = "파일 업로드 오류 코드: " . $file['error'];
        $upload_error = true;
    }
}

// --- 파일 및 폴더 목록 읽기 및 정렬 ---
$folders = [];
$files = [];
if (is_dir($full_current_path)) {
    $items = array_diff(scandir($full_current_path), ['.', '..', '@eaDir']);
    foreach ($items as $item) {
        if (is_dir($full_current_path . '/' . $item)) {
            $folders[] = $item;
        } else {
            $files[] = $item;
        }
    }
} else {
    $files_error = "폴더를 찾을 수 없습니다.";
}
sort($folders, SORT_NATURAL | SORT_FLAG_CASE);
sort($files, SORT_NATURAL | SORT_FLAG_CASE);
$sorted_items = array_merge($folders, $files);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Share</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="app-container">
        <nav class="sidebar">
            <div class="sidebar-header"><h2>Menu</h2></div>
            <ul>
                <li class="active"><a href="dashboard.php">파일 공유</a></li>
                <?php if (is_admin()): ?>
                    <li><a href="logs.php">기록보기</a></li>
                <?php endif; ?>
                <li><a href="logout.php">로그아웃</a></li>
            </ul>
            <div class="sidebar-footer"><p>Logged in as: <strong><?php echo e($_SESSION['username']); ?></strong></p></div>
        </nav>

        <main class="main-content">
            <header class="main-header">
                <h1>File Share</h1>
                <nav class="breadcrumbs">
                    <a href="dashboard.php">Root</a>
                    <?php
                    $path_parts = explode('/', trim($current_path, '/'));
                    $built_path = '';
                    foreach ($path_parts as $part) {
                        if (empty($part)) continue;
                        $built_path .= '/' . $part;
                        echo '<span>/</span><a href="dashboard.php?path=' . urlencode($built_path) . '">' . e($part) . '</a>';
                    }
                    ?>
                </nav>
            </header>

            <?php if (is_admin()): ?>
            <section class="upload-section">
                <h2>파일 업로드 (관리자)</h2>
                <form action="dashboard.php?path=<?php echo urlencode($current_path); ?>" method="post" enctype="multipart/form-data">
                    <div class="upload-box">
                        <button type="submit">업로드</button>
                        <label for="shared_file" class="file-select-btn">파일 선택</label>
                        <input type="file" name="shared_file" id="shared_file" required>
                        <span class="selected-filename">선택된 파일 없음</span>
                    </div>
                </form>
                <?php if ($upload_message): ?>
                    <p class="message <?php echo $upload_error ? 'error' : 'success'; ?>"><?php echo $upload_message; ?></p>
                <?php endif; ?>
            </section>
            <?php endif; ?>
            
            <form action="download.php" method="post" id="downloadForm">
                <input type="hidden" name="path" value="<?php echo e($current_path); ?>">
                <div class="download-controls">
                    <div class="download-actions">
                        <button type="submit" class="batch-download-btn">선택 다운로드</button>
                        <label class="select-all-label"><input type="checkbox" id="selectAllCheckbox"><span>전체 선택</span></label>
                    </div>
                    <div class="view-switcher">
                        <button type="button" id="gridViewBtn" class="view-btn" title="그리드 보기">▦</button>
                        <button type="button" id="listViewBtn" class="view-btn active" title="목록 보기">≡</button>
                    </div>
                </div>
                
                <section class="file-grid" id="fileContainer">
                    <?php if (isset($files_error)): ?>
                        <p class="error-message"><?php echo e($files_error); ?></p>
                    <?php elseif (empty($sorted_items)): ?>
                        <p>폴더가 비어있습니다.</p>
                    <?php else: ?>
                        <?php foreach ($sorted_items as $item):
                            $is_folder = is_dir($full_current_path . '/' . $item);
                            if ($is_folder): ?>
                                <div class="file-card is-folder">
                                    <input type="checkbox" name="folders[]" value="<?php echo e($item); ?>" class="file-checkbox">
                                    <a href="download.php?path=<?php echo urlencode($current_path); ?>&folder=<?php echo urlencode($item); ?>" class="download-btn">다운로드</a>
                                    <span class="file-size"></span> <div class="file-name">
                                        <a href="dashboard.php?path=<?php echo urlencode($current_path . '/' . $item); ?>" title="<?php echo e($item); ?>">
                                            <span class="folder-list-label">폴더</span><?php echo e($item); ?>
                                        </a>
                                    </div>
                                    <div class="file-icon"><?php echo get_file_icon($item, $full_current_path); ?></div>
                                </div>
                            <?php else: 
                                $file_path = $full_current_path . '/' . $item;
                                $file_size = format_file_size(filesize($file_path));
                            ?>
                                <div class="file-card is-file">
                                    <input type="checkbox" name="files[]" value="<?php echo e($item); ?>" class="file-checkbox">
                                    <a href="download.php?path=<?php echo urlencode($current_path); ?>&file=<?php echo urlencode($item); ?>" class="download-btn">다운로드</a>
                                    <span class="file-size"><?php echo e($file_size); ?></span>
                                    <div class="file-icon"><?php echo get_file_icon($item, $full_current_path); ?></div>
                                    <div class="file-name" title="<?php echo e($item); ?>"><?php echo e($item); ?></div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>
            </form>
        </main>
    </div>
    <script src="js/main.js"></script>
</body>
</html>