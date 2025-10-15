# Dockerfile

# SQL 파일에서 PHP 8.2 버전을 확인하여, 8.2-apache 이미지를 기반으로 시작
FROM php:8.2-apache

# 데이터베이스 연결에 필요한 pdo_mysql 확장 모듈과
# ZIP 파일 처리에 필요한 zip 확장 모듈을 설치합니다.
# (download.php에서 ZipArchive를 사용하기 때문에 zip 모듈이 필수입니다.)
RUN apt-get update && apt-get install -y libzip-dev zip \
    && docker-php-ext-install pdo_mysql zip

# Apache의 DocumentRoot를 /var/www/html/public 으로 설정
# 모든 웹 요청이 public 폴더를 기준으로 작동하게 됩니다.
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# .htaccess 사용을 위한 mod_rewrite 활성화 (선택사항이지만 권장)
RUN a2enmod rewrite