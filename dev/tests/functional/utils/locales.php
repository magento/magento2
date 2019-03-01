<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
include __DIR__ . '/authenticate.php';

if (!empty($_POST['token'])) {
    if (authenticate(urldecode($_POST['token']))) {
        if ($_POST['type'] == 'deployed') {
            $themePath = isset($_POST['theme_path']) ? $_POST['theme_path'] : 'adminhtml/Magento/backend';
            $directory = __DIR__ . '/../../../../pub/static/' . $themePath;
            $locales = array_diff(scandir($directory), ['..', '.']);
        } else {
            require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';
            $localeConfig = $magentoObjectManager->create(\Magento\Framework\Locale\Config::class);
            $locales = $localeConfig->getAllowedLocales();
        }
        echo implode('|', $locales);
    } else {
        echo "Command not unauthorized.";
    }
} else {
    echo "'token' parameter is not set.";
}
