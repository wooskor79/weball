<?php
// File: app/actions/edit_link.php
require_once '../core/init.php';

header('Content-Type: application/json');

function send_json_error($message, $code = 500) {
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit;
}

if (!$is_loggedin || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_error('Unauthorized', 403);
}

$id = $_POST['id'] ?? null;
$title = trim($_POST['title'] ?? '');
$url = trim($_POST['url'] ?? '');

if (empty($id) || empty($title) || empty($url)) {
    send_json_error('All fields are required.', 400);
}

if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
    $url = "https://".$url;
}

$stmt = $conn->prepare("UPDATE quick_links SET title = ?, url = ? WHERE id = ?");
$stmt->bind_param("ssi", $title, $url, $id);

if ($stmt->execute()) {
    $stmt->close();
    
    $link_query = $conn->prepare("SELECT id, title, url FROM quick_links WHERE id = ?");
    $link_query->bind_param("i", $id);
    $link_query->execute();
    $result = $link_query->get_result();
    $updated_link = $result->fetch_assoc();
    $link_query->close();
    $conn->close();

    echo json_encode($updated_link);
} else {
    send_json_error('Failed to update link: ' . $stmt->error);
}
?>

