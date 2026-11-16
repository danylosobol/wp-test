ARG PHP_VERSION=${PHP_VERSION}
FROM php:${PHP_VERSION}-fpm
ARG WP_VERSION=${WP_VERSION}

RUN set -ex; \
	apt-get update && apt-get install -y ghostscript libfreetype6-dev libicu-dev libjpeg-dev libpng-dev libwebp-dev libzip-dev curl libcurl4-openssl-dev vim mariadb-client nodejs npm git libonig-dev zlib1g-dev jq zip; \
	docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp; \
	docker-php-ext-install -j "$(nproc)" bcmath exif gd intl mysqli curl zip mbstring pdo pdo_mysql;

RUN set -eux; \
	curl --silent --show-error https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer; \
	curl --silent --show-error -o /usr/local/bin/wp-cli.phar https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar; \
	# Wrapper: keeps PHP notices of WP-CLI itself out of stdout so `--format=json` output stays parseable on new PHP versions.
	printf '#!/bin/sh\nexec php -d display_errors=stderr -d error_reporting="E_ALL & ~E_DEPRECATED" /usr/local/bin/wp-cli.phar "$@"\n' > /usr/local/bin/wp; \
	chmod +x /usr/local/bin/wp /usr/local/bin/wp-cli.phar; \
	# No SSH keys inside the build: fetch GitHub sources over HTTPS.
	git config --global url."https://github.com/".insteadOf "git@github.com:"; \
	/usr/local/bin/wp package install trepmal/wp-revisions-cli --allow-root; \
	npm install -g yarn; \
	composer global require "squizlabs/php_codesniffer=*"

COPY php.ini /usr/local/etc/php/php.ini

RUN set -eux; \
	mkdir -p /var/www/html && cd /var/www/html; \
	wp --allow-root --skip-plugins --skip-themes core download --version="${WP_VERSION}"; \
	chown -R www-data:www-data *;

CMD sed -i 's/\r$//' /check-and-init.sh

ENTRYPOINT sh /check-and-init.sh
