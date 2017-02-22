<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

if (isset($_GET['type']) && $_GET['type'] == 'deployed') {
    $directory = __DIR__ . '/../../../../pub/static/adminhtml/Magento/backend';
    $locales = array_diff(scandir($directory), ['..', '.']);
} else {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';
    $localeConfig = $magentoObjectManager->create(\Magento\Framework\Locale\Config::class);
    $locales = $localeConfig->getAllowedLocales();
}

echo implode('|', $locales);
