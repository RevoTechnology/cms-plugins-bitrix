FROM bitnami/php-fpm
RUN apt-get update && apt-get install nginx -y
COPY . /var/www/bitrix
EXPOSE 80
CMD sleep 1
