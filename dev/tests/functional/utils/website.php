<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// phpcs:ignore Magento2.Security.IncludeFile
include __DIR__ . '/authenticate.php';

// phpcs:ignore Magento2.Security.Superglobal
if (!empty($_POST['token']) && !empty($_POST['website_code'])) {
    // phpcs:ignore Magento2.Security.Superglobal
    if (authenticate(urldecode($_POST['token']))) {
        // phpcs:ignore Magento2.Security.Superglobal
        $websiteCode = urldecode($_POST['website_code']);
        $rootDir = '../../../../';
        $websiteDir = $rootDir . 'websites/' . $websiteCode . '/';
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
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
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        mkdir($websiteDir, 0760, true);
        umask($old);

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        copy($rootDir . '.htaccess', $websiteDir . '.htaccess');
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        file_put_contents($websiteDir . 'index.php', $contents);
    } else {
        // phpcs:ignore Magento2.Security.LanguageConstruct
        echo "Command not unauthorized.";
    }
} else {
    // phpcs:ignore Magento2.Security.LanguageConstruct
    echo "'token' or 'website_code' parameter is not set.";
}
