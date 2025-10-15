<?php
session_start();
require_once __DIR__ . '/functions.php';

$error = '';
$success = '';
$result = null;

// CSRF
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];

// Flash messages from session after redirect
if (isset($_SESSION['flash_error'])) {
    $error = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}
if (isset($_SESSION['flash_success'])) {
    $success = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}

$isAdmin = !empty($_SESSION['admin_ok']);

// GET 파라미터
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$historyOpen = ($search !== '') || isset($_GET['open']);


// POST 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'shorten';
    $token  = $_POST['csrf'] ?? '';

    if (!hash_equals($csrf, $token)) {
        $error = '보안 토큰이 유효하지 않습니다. 새로고침 후 다시 시도하세요.';
    } else {
        try {
            if ($action === 'shorten') {
                $longUrl = sanitize_url($_POST['long_url'] ?? '');
                $custom  = trim($_POST['custom'] ?? '');
                $expireDays = isset($_POST['expire_days']) ? (int)$_POST['expire_days'] : DEFAULT_EXPIRE_DAYS;

                if (!is_valid_url($longUrl)) {
                    throw new Exception('유효한 URL을 입력해 주세요.');
                }

                $result = create_new_link($longUrl, $expireDays, $custom);
                $success = '단축 링크가 생성되었습니다.';

            } elseif ($action === 'admin_login') {
                $pass = $_POST['password'] ?? '';
                if (hash_equals(ADMIN_SECRET, $pass)) {
                    $_SESSION['admin_ok'] = 1;
                    $_SESSION['flash_success'] = '관리자 로그인 성공';
                } else {
                    $_SESSION['flash_error'] = '관리자 비밀번호가 올바르지 않습니다.';
                }
                header('Location: index.php?open=1');
                exit;

            } elseif ($action === 'admin_logout') {
                unset($_SESSION['admin_ok']);
                $_SESSION['flash_success'] = '관리자에서 로그아웃했습니다.';
                header('Location: index.php?open=1');
                exit;

            } elseif ($action === 'delete') {
                if (!$isAdmin) throw new Exception('관리자 권한이 필요합니다.');
                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) throw new Exception('잘못된 요청입니다.');
                if (delete_link($id)) {
                    $success = '삭제되었습니다.';
                } else {
                    throw new Exception('삭제에 실패했습니다.');
                }
                $historyOpen = true;

            } elseif ($action === 'update_expiry') {
                if (!$isAdmin) throw new Exception('관리자 권한이 필요합니다.');
                $id = (int)($_POST['id'] ?? 0);
                $days = (int)($_POST['days'] ?? 0);
                if ($id <= 0) throw new Exception('잘못된 요청입니다.');
                if (update_expiry_days($id, $days)) {
                    $success = ($days > 0) ? "유지기간이 {$days}일로 설정되었습니다." : "만료 없음으로 변경되었습니다.";
                } else {
                    throw new Exception('유지기간 변경에 실패했습니다.');
                }
                $historyOpen = true;

            } elseif ($action === 'update_alias') { // [새로 추가됨] 별칭 수정 처리
                if (!$isAdmin) throw new Exception('관리자 권한이 필요합니다.');
                $id = (int)($_POST['id'] ?? 0);
                $title = trim($_POST['title'] ?? '');
                if ($id <= 0) throw new Exception('잘못된 요청입니다.');
                if (update_alias($id, $title)) {
                    $success = '별칭이 수정되었습니다.';
                } else {
                    throw new Exception('별칭 수정에 실패했습니다.');
                }
                $historyOpen = true;

            } else {
                throw new Exception('알 수 없는 동작입니다.');
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// 기록 목록/페이지네이션
$total = count_links($search);
$maxPage = max(1, (int)ceil($total / $perPage));
$page = min($page, $maxPage);
$offset = ($page - 1) * $perPage;
$history = list_links($search, $perPage, $offset);

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function qr_src($url){ return 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' . rawurlencode($url); }

$shortLink = $result ? short_url($result['code']) : '';
?>
<!doctype html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <title>URL 단축기</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <h1>간단 URL 단축기</h1>
  <p class="hint">긴 링크를 짧게 만들어 공유하세요. (예: <?=h(BASE_URL)?>/aZ3f)</p>

  <?php if ($error): ?>
    <div class="msg error"><?=h($error)?></div>
  <?php elseif ($success): ?>
    <div class="msg success"><?=h($success)?></div>
  <?php endif; ?>

  <?php if ($shortLink): ?>
    <div class="card">
       <?php if (!empty($result['title'])): ?>
         <div class="alias-title">
           입력 별칭: <span class="alias-value"><?=h($result['title'])?></span>
         </div>
       <?php endif; ?>
      <div class="short">
        단축 링크: <a href="<?=h($shortLink)?>" target="_blank" rel="noopener"><?=h($shortLink)?></a>
        <button class="copy" data-copy="<?=h($shortLink)?>">복사</button>
        <span class="sep">|</span>
        <a class="meta" href="<?=h(BASE_URL)?>/stats/<?=h(rawurlencode($result['code']))?>" target="_blank" rel="noopener">통계</a>
        <span class="sep">|</span>
        <a class="meta" href="<?=h(qr_src($shortLink))?>" target="_blank" rel="noopener">QR 보기</a>
      </div>

      <details class="long-url-details">
          <summary>원본 주소 보기</summary>
          <div><?=h($result['long_url'])?></div>
      </details>

      <div class="meta" style="margin-top: 8px;">
        <?php if (!empty($result['expires_at'])): ?>
          만료(UTC): <?=h($result['expires_at'])?>
        <?php else: ?>
          만료 없음
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <form method="post" class="card" autocomplete="off">
    <input type="hidden" name="csrf" value="<?=h($csrf)?>">
    <input type="hidden" name="action" value="shorten">
    <label for="long_url_input">긴 URL</label>
    <input id="long_url_input" type="text" name="long_url" placeholder="https://example.com/very/long/url?param=..." required>

    <div class="row">
      <div>
        <label for="custom_input">별칭 (식별용)</label>
        <input id="custom_input" type="text" name="custom" placeholder="예: 알리익스프레스 세일 (검색용)">
      </div>
      <div>
        <label for="expire_days_input">만료일(일)</label>
        <input id="expire_days_input" type="number" name="expire_days" min="0" value="<?= (int)DEFAULT_EXPIRE_DAYS ?>">
        <div class="meta">0 = 만료 없음</div>
      </div>
    </div>

    <button type="submit" class="primary">단축 링크 만들기</button>
  </form>

  <details <?= $historyOpen ? 'open' : '' ?>>
    <summary>📜 기록보기</summary>

    <div class="topbar">
      <form method="get" class="searchbar">
        <label for="q" class="meta">별칭(코드) 검색</label>
        <input type="search" id="q" name="q" value="<?=h($search)?>" placeholder="별칭 또는 코드로 검색">
        <button type="submit">검색</button>
        <?php if ($search !== ''): ?>
          <a href="<?=h($_SERVER['PHP_SELF'])?>?open=1">초기화</a>
        <?php endif; ?>
      </form>

      <div class="meta">
        총 <?= (int)$total ?>개 · 페이지 <?= (int)$page ?> / <?= (int)$maxPage ?>
      </div>

      <div>
        <?php if (!$isAdmin): ?>
          <form method="post" class="inline-form">
            <input type="hidden" name="csrf" value="<?=h($csrf)?>">
            <input type="hidden" name="action" value="admin_login">
            <input type="password" name="password" placeholder="관리자 비밀번호" style="margin:0;">
            <button type="submit">관리자 로그인</button>
          </form>
        <?php else: ?>
          <form method="post" class="inline-form">
            <input type="hidden" name="csrf" value="<?=h($csrf)?>">
            <input type="hidden" name="action" value="admin_logout">
            <button type="submit">로그아웃</button>
          </form>
        <?php endif; ?>
      </div>
    </div>

    <div class="list">
      <?php if (empty($history)): ?>
        <div class="meta">표시할 기록이 없습니다.</div>
      <?php else: ?>
        <?php foreach ($history as $row): ?>
          <?php
            $short = short_url($row['code']);
            $expired = false;
            if (!empty($row['expires_at'])) {
                $now = new DateTime('now', new DateTimeZone('UTC'));
                $exp = new DateTime($row['expires_at'], new DateTimeZone('UTC'));
                $expired = ($now > $exp);
            }
          ?>
          <div class="card">
            <?php if (!empty($row['title'])): ?>
              <div class="alias-title">
                입력 별칭: <span class="alias-value"><?=h($row['title'])?></span>
              </div>
            <?php endif; ?>

            <div class="flex">
              <div class="short">
                <a href="<?=h($short)?>" target="_blank" rel="noopener"><?=h($short)?></a>
                <button class="copy" data-copy="<?=h($short)?>">복사</button>
                <span class="sep">|</span>
                <a class="meta" href="<?=h(BASE_URL)?>/stats/<?=h(rawurlencode($row['code']))?>" target="_blank" rel="noopener">통계</a>
                <span class="sep">|</span>
                <a class="meta" href="<?=h(qr_src($short))?>" target="_blank" rel="noopener">QR</a>
              </div>
              <span class="stat">클릭: <?= (int)$row['click_count'] ?></span>
              <?php if ($expired): ?>
                <span class="stat" style="color:#b71c1c;border-color:#ffcdd2;background:#ffebee;">만료됨</span>
              <?php endif; ?>
            </div>

            <details class="long-url-details">
                <summary>원본 주소 보기</summary>
                <div><?=h($row['long_url'])?></div>
            </details>

            <div class="meta" style="margin-top: 8px;">
              코드: <?=h($row['code'])?> · 생성(UTC): <?=h($row['created_at'])?> ·
              만료(UTC): <?=h($row['expires_at'] ?: '없음')?> · 마지막 클릭: <?=h($row['last_clicked'] ?: '없음')?>
            </div>

            <?php if ($isAdmin): ?>
              <div class="actions">
                <form method="post" class="inline-form">
                  <input type="hidden" name="csrf" value="<?=h($csrf)?>">
                  <input type="hidden" name="action" value="update_expiry">
                  <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                  <label class="meta">유지기간(일)</label>
                  <input type="number" name="days" min="0" value="<?= !empty($row['expires_at']) ? 30 : 0 ?>" style="width:100px;margin:0;">
                  <button type="submit">유지기간 수정</button>
                </form>

                <form method="post" class="inline-form">
                    <input type="hidden" name="csrf" value="<?=h($csrf)?>">
                    <input type="hidden" name="action" value="update_alias">
                    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                    <label class="meta">별칭</label>
                    <input type="text" name="title" value="<?= h($row['title']) ?>" placeholder="별칭 입력/수정" style="width:200px;margin:0;">
                    <button type="submit">별칭 수정</button>
                </form>

                <div class="delete-container inline-form">
                    <form method="post" action="index.php" class="confirm-delete-form" style="display: none;">
                        <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                        <span class="meta">삭제?</span>
                        <button type="submit" class="btn-danger-small">예</button>
                        <button type="button" class="btn-secondary-small cancel-delete">아니오</button>
                    </form>
                    <button type="button" class="trigger-delete btn-danger-small">삭제</button>
                </div>
              </div>
            <?php endif; ?>

          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="pagination">
      <?php
        $base = $_SERVER['PHP_SELF'] . '?';
        $params = [];
        if ($search !== '') $params['q'] = $search;
        $params['open'] = 1;
        $mk = function($p) use ($base, $params) {
            $params['page'] = $p;
            return $base . http_build_query($params);
        };
      ?>
      <?php if ($page > 1): ?>
        <a href="<?=h($mk(1))?>">« 처음</a>
        <a href="<?=h($mk($page-1))?>">‹ 이전</a>
      <?php endif; ?>
      <span class="meta">페이지 <?= (int)$page ?> / <?= (int)$maxPage ?></span>
      <?php if ($page < $maxPage): ?>
        <a href="<?=h($mk($page+1))?>">다음 ›</a>
        <a href="<?=h($mk($maxPage))?>">마지막 »</a>
      <?php endif; ?>
    </div>
  </details>

  <div style="margin-top:28px;" class="meta">
    Made on PHP 8 + MariaDB · <?= (int)REDIRECT_STATUS ?> Redirect · Random Code · UTC 기준
  </div>

  <script>
    document.addEventListener('click', function(e){
      // --- Clipboard copy logic ---
      const copyBtn = e.target.closest('.copy');
      if (copyBtn) {
        const text = copyBtn.getAttribute('data-copy') || '';
        if (text) {
          navigator.clipboard.writeText(text).then(() => {
            copyBtn.textContent = '복사됨';
            setTimeout(() => { copyBtn.textContent = '복사'; }, 1200);
          }).catch(() => { alert('복사에 실패했습니다.'); });
        }
        return;
      }

      // --- Inline delete confirmation logic ---
      // '삭제' button clicked
      if (e.target.classList.contains('trigger-delete')) {
        const container = e.target.closest('.delete-container');
        if (container) {
          container.querySelector('.confirm-delete-form').style.display = 'inline-flex';
          e.target.style.display = 'none';
        }
      }

      // '아니오' button clicked
      if (e.target.classList.contains('cancel-delete')) {
        const container = e.target.closest('.delete-container');
        if (container) {
          container.querySelector('.confirm-delete-form').style.display = 'none';
          container.querySelector('.trigger-delete').style.display = 'inline-block';
        }
      }
    });
  </script>
</body>
</html>