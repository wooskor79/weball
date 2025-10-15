<?php
// app/config.php
// 데이터베이스 및 서버 환경 설정 파일

define('DB_HOST', getenv('DB_HOST') ?: 'common_database_server');
define('DB_NAME', getenv('MYSQL_DATABASE') ?: 'fileshare');
define('DB_USER', getenv('MYSQL_USER') ?: 'root');
define('DB_PASS', getenv('MYSQL_PASSWORD') ?: 'dldntjd@D79'); // .env에서 가져오므로 실제 값은 상관없음

// --- 공유 폴더 설정 ---
// **매우 중요**: 실제 파일이 저장되고 공유될 NAS의 절대 경로
// 예: '/volume1/ShareFolder/Share'
define('SHARE_FOLDER', '/volume1/ShareFolder/Share');






