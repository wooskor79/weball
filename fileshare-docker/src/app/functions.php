<?php
// app/functions.php
// 웹사이트의 핵심 함수들을 모아놓은 파일

// --- 보안 관련 함수 ---
function e(?string $string): string
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

function sanitize_path(string $path): string
{
    $path = trim($path, " /");
    $path = str_replace('..', '', $path);
    $path = preg_replace('/\/+/', '/', $path);
    if (!empty($path)) {
        return '/' . $path;
    }
    return '';
}


// --- 인증 관련 함수 ---
function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function is_admin(): bool
{
    return is_logged_in() && $_SESSION['role'] === 'admin';
}

function login(PDO $pdo, string $username, string $password): bool
{
    if (strtolower($username) === 'guest' && empty($password)) {
        session_regenerate_id();
        $_SESSION['user_id'] = 0;
        $_SESSION['username'] = 'guest';
        $_SESSION['role'] = 'user';
        return true;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    return false;
}

function logout(): void
{
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

// --- 파일 관련 함수 ---

/**
 * 파일 크기를 사람이 읽기 쉬운 형태로 변환 (GB, MB, KB)
 * @param int $bytes
 * @return string
 */
function format_file_size(int $bytes): string
{
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 1) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 1) . ' MB';
    } elseif ($bytes >= 1024) {
        // KB는 소수점 없이 표시하여 간결하게
        return number_format($bytes / 1024) . ' KB';
    } elseif ($bytes > 0) {
        return $bytes . ' B';
    } else {
        return '0 B';
    }
}


function get_file_icon(string $filename, string $base_path): string
{
    $full_path = $base_path . '/' . $filename;
    
    if (is_dir($full_path)) {
        return '📁';
    }
    
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    switch ($extension) {
        case 'jpg': case 'jpeg': case 'png': case 'gif': case 'bmp': case 'svg': case 'webp':
            return '🖼️';
        case 'pdf':
            return '📄';
        case 'doc': case 'docx':
            return ' W ';
        case 'xls': case 'xlsx':
            return ' E ';
        case 'ppt': case 'pptx':
            return ' P ';
        case 'zip': case 'rar': case '7z': case 'gz': case 'tar':
            return '📦';
        case 'mp4': case 'mov': case 'avi': case 'mkv': case 'wmv':
            return '🎬';
        case 'mp3': case 'wav': case 'flac': case 'ogg':
            return '🎵';
        case 'exe': case 'msi':
            return '⚙️';
        case 'txt': case 'md': case 'log':
            return '📝';
        case 'html': case 'css': case 'js': case 'php': case 'py': case 'java':
            return '💻';
        case 'reg':
            return '🔩';
        case 'dll':
            return '🔗';
        default:
            return '📎';
    }
}

function add_folder_to_zip(ZipArchive $zip, string $folder, string $zip_path = ''): void
{
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($folder, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = $zip_path . substr($filePath, strlen($folder) + 1);
            $zip->addFile($filePath, $relativePath);
        }
    }
}

// --- 로그 관련 함수 ---
function log_action(PDO $pdo, string $username, string $action, string $filename): bool
{
    if ($username === 'guest') {
        return true;
    }
    $stmt = $pdo->prepare("INSERT INTO logs (username, action, filename) VALUES (?, ?, ?)");
    return $stmt->execute([$username, $action, $filename]);
}

function get_logs_by_action(PDO $pdo, string $action): array
{
    $stmt = $pdo->prepare("SELECT filename, timestamp FROM logs WHERE action = ? ORDER BY timestamp DESC");
    $stmt->execute([$action]);
    return $stmt->fetchAll();
}

function clear_logs(PDO $pdo): bool
{
    try {
        $pdo->exec("TRUNCATE TABLE logs");
        return true;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}

