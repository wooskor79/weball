# PHP 8.0과 Apache가 함께 설치된 공식 이미지를 기반으로 시작
FROM php:8.0-apache

# MariaDB 연결에 필요한 pdo_mysql 확장 모듈 설치
RUN docker-php-ext-install pdo_mysql

# .htaccess 파일의 RewriteRule을 사용하기 위해 아파치 mod_rewrite 모듈 활성화
RUN a2enmod rewrite