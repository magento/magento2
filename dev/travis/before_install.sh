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
if [ $TEST_SUITE = "static" ] || [ test $TEST_SUITE == "js" ]; then
    curl -o- https://raw.githubusercontent.com/creationix/nvm/v0.33.1/install.sh | bash
    export NVM_DIR="$HOME/.nvm"
    [ -s "$NVM_DIR/nvm.sh" ] && . "$NVM_DIR/nvm.sh" # This loads nvm

    nvm install $NODE_JS_VERSION
    nvm use $NODE_JS_VERSION
    node --version

    npm install -g yarn
    yarn global add grunt-cli
fi
