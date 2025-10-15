<?php
require_once __DIR__ . '/functions.php';

$code = $_GET['code'] ?? '';
$code = trim($code);

$row = find_by_code($code);
if (!$row) {
    http_response_code(404);
    echo "Not Found";
    exit;
}

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
$shortLink = BASE_URL . '/' . $row['code'];
?>
<!doctype html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <title>통계 - <?=h($row['code'])?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="stats-body">
  <h1>단축 링크 통계</h1>
  <p><strong>Short:</strong> <a href="<?=h($shortLink)?>" target="_blank"><?=h($shortLink)?></a></p>
  <p><strong>Long:</strong> <?=h($row['long_url'])?></p>
  <table class="stats-table">
    <tr><td>코드</td><td><?=h($row['code'])?></td></tr>
    <tr><td>생성(UTC)</td><td><?=h($row['created_at'])?></td></tr>
    <tr><td>만료(UTC)</td><td><?=h($row['expires_at'] ?: '없음')?></td></tr>
    <tr><td>클릭 수</td><td><?= (int)$row['click_count'] ?></td></tr>
    <tr><td>마지막 클릭(UTC)</td><td><?=h($row['last_clicked'] ?: '없음')?></td></tr>
    <tr><td>생성자 IP</td><td><?=h($row['creator_ip'])?></td></tr>
  </table>
  <p class="stats-hint">개인정보에 유의하세요. 필요 시 IP/User-Agent 수집을 비활성화할 수 있습니다.</p>
</body>
</html>