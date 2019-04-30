<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// phpcs:ignore Magento2.Security.IncludeFile
include __DIR__ . '/authenticate.php';

if (!empty($_POST['token']) && !empty($_POST['path'])) {
    if (authenticate(urldecode($_POST['token']))) {
        $path = urldecode($_POST['path']);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if (file_exists('../../../../' . $path)) {
            // phpcs:ignore Magento2.Security.LanguageConstruct
            echo 'path exists: true';
        } else {
            // phpcs:ignore Magento2.Security.LanguageConstruct
            echo 'path exists: false';
        }
    } else {
        echo "Command not unauthorized.";
    }
} else {
    echo "'token' or 'path' parameter is not set.";
}
