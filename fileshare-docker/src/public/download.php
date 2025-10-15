<?php
// public/download.php (폴더 개별 다운로드 로직 추가)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../app/bootstrap.php';

if (!is_logged_in()) {
    header("HTTP/1.1 403 Forbidden");
    exit('Access Denied');
}

// --- 경로 설정 및 보안 ---
$current_path_raw = $_REQUEST['path'] ?? '';
$current_path = sanitize_path($current_path_raw);
$full_base_path = SHARE_FOLDER . $current_path;

if (strpos(realpath($full_base_path), realpath(SHARE_FOLDER)) !== 0 || !is_dir($full_base_path)) {
    header("HTTP/1.1 400 Bad Request");
    exit('Invalid path.');
}

/**
 * 파일을 청크 단위로 안전하게 스트리밍하는 함수
 * @param string $filepath
 */
function stream_file(string $filepath): void
{
    if (ob_get_level()) ob_end_clean();
    $handle = fopen($filepath, 'rb');
    if ($handle === false) {
        header("HTTP/1.1 500 Internal Server Error");
        exit('Cannot open file.');
    }
    while (!feof($handle)) {
        echo fread($handle, 8192);
        flush();
        if (connection_status() != 0) {
            fclose($handle);
            exit;
        }
    }
    fclose($handle);
}

// --- 단일 폴더 다운로드 처리 (GET 방식) ---
if (isset($_GET['folder'])) {
    $foldername = $_GET['folder'];
    if (empty($foldername) || basename($foldername) !== $foldername) {
        header("HTTP/1.1 400 Bad Request");
        exit('Invalid folder name.');
    }

    $folderpath = $full_base_path . '/' . $foldername;

    if (file_exists($folderpath) && is_dir($folderpath)) {
        
        $zip_filename = tempnam(sys_get_temp_dir(), 'fileshare_') . '.zip';
        $zip = new ZipArchive();

        if ($zip->open($zip_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            header("HTTP/1.1 500 Internal Server Error");
            exit('Cannot create ZIP archive.');
        }

        add_folder_to_zip($zip, $folderpath, basename($folderpath) . '/');
        $zip->close();
        
        log_action($pdo, $_SESSION['username'], 'download', ltrim($current_path . '/' . $foldername, '/') . ' (Folder)');

        if (file_exists($zip_filename) && filesize($zip_filename) > 0) {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . basename($foldername) . '.zip"');
            header('Content-Length: ' . filesize($zip_filename));
            
            stream_file($zip_filename);
            
            unlink($zip_filename);
            exit();
        } else {
            if (file_exists($zip_filename)) unlink($zip_filename);
            // 비어있는 폴더의 경우, 빈 zip 파일 다운로드 대신 메시지 표시
            echo "<script>alert('빈 폴더는 다운로드할 수 없습니다.'); window.history.back();</script>";
            exit();
        }
    }
}

// --- 다중 파일/폴더 다운로드 처리 (POST 방식) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['files']) || isset($_POST['folders']))) {
    $items_to_zip = array_merge($_POST['files'] ?? [], $_POST['folders'] ?? []);

    if (empty($items_to_zip)) {
        header("HTTP/1.1 400 Bad Request");
        exit('No files or folders selected.');
    }

    $zip_filename = tempnam(sys_get_temp_dir(), 'fileshare_') . '.zip';
    $zip = new ZipArchive();

    if ($zip->open($zip_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        header("HTTP/1.1 500 Internal Server Error");
        exit('Cannot create ZIP archive.');
    }

    foreach ($items_to_zip as $item_name) {
        if (basename($item_name) !== $item_name) continue; // 보안
        $item_path = $full_base_path . '/' . $item_name;

        if (file_exists($item_path) && is_readable($item_path)) {
            if (is_dir($item_path)) {
                add_folder_to_zip($zip, $item_path, basename($item_path) . '/');
                log_action($pdo, $_SESSION['username'], 'download', ltrim($current_path . '/' . $item_name, '/') . ' (Folder)');
            } else {
                $zip->addFile($item_path, basename($item_path));
                log_action($pdo, $_SESSION['username'], 'download', ltrim($current_path . '/' . $item_name, '/'));
            }
        }
    }
    $zip->close();

    if (file_exists($zip_filename) && filesize($zip_filename) > 0) {
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="download_' . date('Ymd_His') . '.zip"');
        header('Content-Length: ' . filesize($zip_filename));
        
        stream_file($zip_filename);
        
        unlink($zip_filename);
        exit();
    } else {
        if (file_exists($zip_filename)) unlink($zip_filename);
        header("HTTP/1.1 500 Internal Server Error");
        exit('Failed to create the zip file or no items were added.');
    }
}

// --- 단일 파일 다운로드 처리 (GET 방식) ---
if (isset($_GET['file'])) {
    $filename = $_GET['file'];
    if (empty($filename) || basename($filename) !== $filename) { // 보안
        header("HTTP/1.1 400 Bad Request");
        exit('Invalid filename.');
    }

    $filepath = $full_base_path . '/' . $filename;

    if (file_exists($filepath) && is_readable($filepath) && !is_dir($filepath)) {
        log_action($pdo, $_SESSION['username'], 'download', ltrim($current_path . '/' . $filename, '/'));

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        
        stream_file($filepath);
        exit();
    }
}

// 처리할 수 없는 요청
header("HTTP/1.1 404 Not Found");
exit('File not found or invalid request.');

