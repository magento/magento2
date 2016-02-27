#!/usr/bin/env bash

# Copyright © 2015 Magento. All rights reserved.
# See COPYING.txt for license details.

set -e
export PATH="$HOME/.cache/bin:$PATH"

# install or update composer in casher dir
if [ "$CASHER_DIR" ]; then
    if [ -x $HOME/.cache/bin/composer ]; then
        $HOME/.cache/bin/composer self-update
    else
        mkdir -p $HOME/.cache/bin
        curl --connect-timeout 30 -sS https://getcomposer.org/installer \
            | php -- --install-dir $HOME/.cache/bin/ --filename composer
    fi
fi
