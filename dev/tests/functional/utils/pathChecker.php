<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
include __DIR__ . '/authenticate.php';

if (!empty($_POST['token']) && !empty($_POST['path'])) {
    if (authenticate(urldecode($_POST['token']))) {
        $path = urldecode($_POST['path']);

        if (file_exists('../../../../' . $path)) {
            echo 'path exists: true';
        } else {
            echo 'path exists: false';
        }
    } else {
        echo "Command not unauthorized.";
    }
} else {
    echo "'token' or 'path' parameter is not set.";
}
