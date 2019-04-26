<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// phpcs:ignore Magento2.Security.Superglobal
if (isset($_GET['type']) && $_GET['type'] == 'deployed') {
    // phpcs:ignore Magento2.Security.Superglobal
    $themePath = isset($_GET['theme_path']) ? $_GET['theme_path'] : 'adminhtml/Magento/backend';
    $directory = __DIR__ . '/../../../../pub/static/' . $themePath;
    // phpcs:ignore Magento2.Functions.DiscouragedFunction
    $locales = array_diff(scandir($directory), ['..', '.']);
} else {
    // phpcs:ignore Magento2.Security.IncludeFile
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';
    $localeConfig = $magentoObjectManager->create(\Magento\Framework\Locale\Config::class);
    $locales = $localeConfig->getAllowedLocales();
}

// phpcs:ignore Magento2.Security.LanguageConstruct
echo implode('|', $locales);
