
FROM php:8.2-cli

RUN docker-php-ext-install mysqli pdo pdo_mysql

WORKDIR /var/www/html

COPY . .

EXPOSE 10000

CMD ["sh", "-c", "if [ -f /var/www/html/OnlineCleanBook/CODE/index.php ]; then DOCROOT=/var/www/html/OnlineCleanBook; elif [ -f /var/www/html/index.php ]; then DOCROOT=/var/www/html; else echo 'index.php not found'; exit 1; fi; php -S 0.0.0.0:${PORT:-10000} -t \"$DOCROOT\""]
