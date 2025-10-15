# File: Dockerfile
# 베이스 이미지로 PHP 8.0과 Apache 서버가 포함된 이미지를 사용합니다.
FROM php:8.0-apache

# 데이터베이스 연동을 위한 mysqli 확장 모듈을 설치합니다.
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Apache의 mod_rewrite 모듈을 활성화합니다. (옵션)
RUN a2enmod rewrite

# 작업 디렉토리를 /var/www/html로 설정합니다.
WORKDIR /var/www/html

