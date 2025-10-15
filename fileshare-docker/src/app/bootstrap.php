<?php
// app/bootstrap.php

// **디버깅 모드**: 숨겨진 PHP 오류를 화면에 표시합니다.
// 이 코드는 문제 해결 후 보안을 위해 제거하는 것이 좋습니다.
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 웹사이트의 모든 페이지에서 공통적으로 사용되는 핵심 파일들을 불러옵니다.

// 1. 세션 시작 (가장 먼저 실행되어야 함)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. 설정 파일 불러오기 (DB 정보, 경로 등)
require_once __DIR__ . '/config.php';

// 3. 데이터베이스 연결 파일 불러오기 ($pdo 객체 생성)
require_once __DIR__ . '/db.php';

// 4. 모든 핵심 함수가 들어있는 파일 불러오기
require_once __DIR__ . '/functions.php';

