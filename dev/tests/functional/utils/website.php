<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
include __DIR__ . '/authenticate.php';

if (!empty($_POST['token']) && !empty($_POST['website_code'])) {
    if (authenticate(urldecode($_POST['token']))) {
        $websiteCode = urldecode($_POST['website_code']);
        $rootDir = '../../../../';
        $websiteDir = $rootDir . 'websites/' . $websiteCode . '/';
        $contents = file_get_contents($rootDir . 'index.php');

        $websiteParam = <<<EOD
\$params = \$_SERVER;
\$params[\Magento\Store\Model\StoreManager::PARAM_RUN_CODE] = '$websiteCode';
\$params[\Magento\Store\Model\StoreManager::PARAM_RUN_TYPE] = 'website';
EOD;

        $pattern = '`(try {.*?)(\/app\/bootstrap.*?}\n)(.*?)\$_SERVER`mis';
        $replacement = "$1/../..$2\n$websiteParam$3\$params";

        $contents = preg_replace($pattern, $replacement, $contents);

        $old = umask(0);
        mkdir($websiteDir, 0760, true);
        umask($old);

        copy($rootDir . '.htaccess', $websiteDir . '.htaccess');
        file_put_contents($websiteDir . 'index.php', $contents);
    } else {
        echo "Command not unauthorized.";
    }
} else {
    echo "'token' or 'website_code' parameter is not set.";
}
