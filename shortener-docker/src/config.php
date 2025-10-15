<?php
// config.php (설정 전용 — 함수 없음)

// === Database & App Config ===
// [수정] 도커 환경변수에서 DB 접속 정보를 가져옵니다.
define('DB_HOST', getenv('DB_HOST') ?: 'common_database_server');
define('DB_PORT', 3306);
define('DB_NAME', getenv('MYSQL_DATABASE') ?: 'shortener');
define('DB_USER', getenv('MYSQL_USER') ?: 'root');
define('DB_PASS', getenv('MYSQL_PASSWORD') ?: 'dldntjd@D79');

// 서비스의 베이스 URL (마지막 슬래시 없음) - 이 부분은 자신의 도메인에 맞게 유지
define('BASE_URL', 'https://s.wooskor.site');

// 이미 존재하는 동일 URL에 대해 기존 코드를 재사용할지 여부
define('REUSE_EXISTING', true);

// 기본 만료일(일) - 0이면 만료 없음
define('DEFAULT_EXPIRE_DAYS', 0);

// 허용 최대 URL 길이
define('MAX_URL_LENGTH', 2000);

// 예약어(코드로 쓰지 않도록)
$RESERVED_CODES = ['api', 'stats', 'admin', 'login', 'logout', 'assets', 'static'];

// === 보안/운영 설정 ===
// [수정] 도커 환경변수에서 관리자 비밀번호를 가져옵니다.
define('ADMIN_SECRET', getenv('ADMIN_SECRET') ?: 'your_new_admin_password');

// 리다이렉트 상태 코드: 302(권장, 캐싱 방지) 또는 301(영구)
define('REDIRECT_STATUS', 302);

// [수정됨] 코드 최소 길이 느낌을 주고 싶다면, 신규 생성 시 id에 오프셋을 더해 base62 인코딩합니다.
// 예) 100000으로 설정하면 초기 코드도 3~4글자로 생성됩니다.
define('ID_OFFSET', 100000); // 0 -> 100000


