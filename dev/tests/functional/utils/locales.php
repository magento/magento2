<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

if (isset($_GET['type']) && $_GET['type'] == 'deployed') {
    $themePath = isset($_GET['theme_path']) ? $_GET['theme_path'] : 'adminhtml/Magento/backend';
    $directory = __DIR__ . '/../../../../pub/static/' . $themePath;
    $locales = array_diff(scandir($directory), ['..', '.']);
} else {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';
    $localeConfig = $magentoObjectManager->create(\Magento\Framework\Locale\Config::class);
    $locales = $localeConfig->getAllowedLocales();
}

echo implode('|', $locales);
