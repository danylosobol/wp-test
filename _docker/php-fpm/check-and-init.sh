#!/bin/bash

if [ "${SITE_URL}" != "localhost" ]; then
		ENV_HOST="https://${SITE_URL}"
else
		ENV_HOST="https://${SITE_URL}:${DOCKER_SITE_PORT}"
fi

cd /var/www/html;
if [ ! -f wp-config.php ]; then
    # Wait for database to be ready using mysqladmin instead of mysqlshow
    while ! mysqladmin ping -h"${WORDPRESS_DB_HOST}" -u"${WORDPRESS_DB_USER}" -p"${WORDPRESS_DB_PASSWORD}" --silent 2>/dev/null; do
        sleep 5
    done

    wp --allow-root core config --skip-plugins --skip-themes --dbname="${WORDPRESS_DB_NAME}" --dbuser="${WORDPRESS_DB_USER}" --dbpass="${WORDPRESS_DB_PASSWORD}" --dbhost="${WORDPRESS_DB_HOST}" --dbprefix="${WORDPRESS_TABLE_PREFIX}"
		wp --allow-root config set DB_CHARSET '"utf8"' --raw
    wp --allow-root config set DB_COLLATE '"utf8_unicode_ci"' --raw
    wp --allow-root core install --skip-plugins --skip-themes --skip-packages --url="${ENV_HOST}" --title="${SITE_TITLE}" --admin_user="${SITE_USER}" --admin_password="${SITE_USER_PASSWPRD}" --admin_email="${SITE_USER_EMAIL}"
    wp --allow-root config shuffle-salts --skip-plugins --skip-themes
    wp --allow-root --skip-plugins theme activate $(wp theme list --allow-root --format=json | jq -r .[0].name)
		wp --allow-root rewrite structure '/%postname%/'
    chown -R www-data:www-data *
fi

if [ "${SITE_ENV}" != "production" ]; then
    wp --allow-root option set blog_public 0
fi

WP_CUR_VERSION=$(wp core version --allow-root)
echo "CUR_VERSION: ${WP_CUR_VERSION}"
echo "NEEDED: ${WP_VERSION}"
if [ "$WP_CUR_VERSION" != "${WP_VERSION}" ]; then
  wp core update --force --version=${WP_VERSION} --allow-root
fi

SITEURL=$(wp --allow-root eval 'echo get_option("siteurl");')
if [ "${SITEURL}" != "" ] && [ "${SITEURL}" != "${ENV_HOST}" ]; then
  wp --allow-root --no-color --skip-themes search-replace ${SITEURL} ${ENV_HOST} --all-tables
fi

wp --allow-root --no-color --skip-themes search-replace http:// https:// --all-tables
wp --allow-root config set WP_DEBUG true --raw && wp --allow-root config set WP_DEBUG_DISPLAY false --raw && wp --allow-root config set WP_DEBUG_LOG true --raw

if [ -n "${ACF_PRO_KEY}" ]; then
  wp --allow-root config set ACF_PRO_LICENSE "${ACF_PRO_KEY}"
fi

php-fpm
