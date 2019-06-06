<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
// phpcs:ignore Magento2.Security.IncludeFile
include __DIR__ . '/authenticate.php';

// phpcs:ignore Magento2.Security.Superglobal
if (!empty($_POST['token']) && !empty($_POST['name'])) {
    // phpcs:ignore Magento2.Security.Superglobal
    if (authenticate(urldecode($_POST['token']))) {
        // phpcs:ignore Magento2.Security.Superglobal
        $name = urldecode($_POST['name']);
        if (preg_match('/\.\.(\\\|\/)/', $name)) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \InvalidArgumentException('Invalid log file name');
        }

        // phpcs:ignore Magento2.Security.InsecureFunction, Magento2.Functions.DiscouragedFunction, Magento2.Security.LanguageConstruct
        echo serialize(file_get_contents('../../../../var/log' . '/' . $name));
    } else {
        // phpcs:ignore Magento2.Security.LanguageConstruct
        echo "Command not unauthorized.";
    }
} else {
    // phpcs:ignore Magento2.Security.LanguageConstruct
    echo "'token' or 'name' parameter is not set.";
}
