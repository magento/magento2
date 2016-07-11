#!/bin/bash

# Copyright © 2016 Magento. All rights reserved.
# See COPYING.txt for license details.

websiteCode=$1;

cd ../../../../
mkdir -p websites
mkdir -p websites/${websiteCode}
cp pub/.htaccess websites/${websiteCode}/
cd websites/${websiteCode}/

cat > index.php <<EOF
<?php
require __DIR__ . '/../../app/bootstrap.php';
\$params = \$_SERVER;
\$params[\Magento\Store\Model\StoreManager::PARAM_RUN_CODE] = '${websiteCode}';
\$params[\Magento\Store\Model\StoreManager::PARAM_RUN_TYPE] = 'website';
\$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, \$params);
/** @var \Magento\Framework\App\Http \$app */
\$app = \$bootstrap->createApplication('Magento\Framework\App\Http');
\$bootstrap->run(\$app);
EOF
