<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
include __DIR__ . '/authenticate.php';

if (!empty($_POST['token']) && !empty($_POST['name'])) {
    if (authenticate(urldecode($_POST['token']))) {
        $name = urldecode($_POST['name']);
        if (preg_match('/\.\.(\\\|\/)/', $name)) {
            throw new \InvalidArgumentException('Invalid log file name');
        }

        echo serialize(file_get_contents('../../../../var/log' . '/' . $name));
    } else {
        echo "Command not unauthorized.";
    }
} else {
    echo "'token' or 'name' parameter is not set.";
}
