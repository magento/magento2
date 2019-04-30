<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// phpcs:ignore Magento2.Security.IncludeFile
include __DIR__ . '/authenticate.php';

if (!empty($_POST['token']) && !empty($_POST['path'])) {
    if (authenticate(urldecode($_POST['token']))) {
        // phpcs:ignore Magento2.Security.InsecureFunction
        exec('rm -rf ../../../../generated/*');
    } else {
        echo "Command not unauthorized.";
    }
} else {
    echo "'token' parameter is not set.";
}
