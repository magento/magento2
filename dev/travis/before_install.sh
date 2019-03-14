#!/usr/bin/env bash

# Copyright © Magento, Inc. All rights reserved.
# See COPYING.txt for license details.

set -e
trap '>&2 echo Error: Command \`$BASH_COMMAND\` on line $LINENO failed with exit code $?' ERR

# mock mail
sudo service postfix stop
echo # print a newline
smtp-sink -d "%d.%H.%M.%S" localhost:2500 1000 &
echo 'sendmail_path = "/usr/sbin/sendmail -t -i "' > ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/sendmail.ini

# disable xdebug and adjust memory limit
echo > ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/xdebug.ini
echo 'memory_limit = -1' >> ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/travis.ini
phpenv rehash;

# If env var is present, configure support for 3rd party builds which include private dependencies
test -n "$GITHUB_TOKEN" && composer config github-oauth.github.com "$GITHUB_TOKEN" || true

# Node.js setup via NVM
if [ $TEST_SUITE == "js" ]; then
    curl -o- https://raw.githubusercontent.com/creationix/nvm/v0.33.1/install.sh | bash
    export NVM_DIR="$HOME/.nvm"
    [ -s "$NVM_DIR/nvm.sh" ] && . "$NVM_DIR/nvm.sh" # This loads nvm

    nvm install $NODE_JS_VERSION
    nvm use $NODE_JS_VERSION
    node --version

    npm install -g yarn
    yarn global add grunt-cli
fi

if [ $TEST_SUITE = "functional" ] || [ $TEST_SUITE = "msi-api-functional" ] || [ $TEST_SUITE = "graphql-api-functional" ]; then
    # Install apache
    sudo apt-get update
    sudo apt-get install apache2 libapache2-mod-fastcgi
    if [ ${TRAVIS_PHP_VERSION:0:1} == "7" ]; then
        sudo cp ${TRAVIS_BUILD_DIR}/dev/travis/config/www.conf ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.d/
    fi

    # Enable php-fpm
    sudo cp ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf.default ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf
    sudo a2enmod rewrite actions fastcgi alias
    echo "cgi.fix_pathinfo = 1" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
    ~/.phpenv/versions/$(phpenv version-name)/sbin/php-fpm

    # Configure apache virtual hosts
    sudo cp -f ${TRAVIS_BUILD_DIR}/dev/travis/config/apache_virtual_host /etc/apache2/sites-available/000-default.conf
    sudo sed -e "s?%TRAVIS_BUILD_DIR%?$(pwd)?g" --in-place /etc/apache2/sites-available/000-default.conf
    sudo sed -e "s?%MAGENTO_HOST_NAME%?${MAGENTO_HOST_NAME}?g" --in-place /etc/apache2/sites-available/000-default.conf

    sudo usermod -a -G www-data travis
    sudo usermod -a -G travis www-data

    phpenv config-rm xdebug.ini
    sudo service apache2 restart

    /sbin/start-stop-daemon --start --quiet --pidfile /tmp/custom_xvfb_99.pid --make-pidfile --background --exec /usr/bin/Xvfb -- :1 -screen 0 1280x1024x24
fi
