<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

if (!isset($_GET['name'])) {
    throw new \InvalidArgumentException(
        'The name of log file is required for getting logs.'
    );
}
$name = urldecode($_GET['name']);
if (preg_match('/\.\.(\\\|\/)/', $name)) {
    throw new \InvalidArgumentException('Invalid log file name');
}

echo serialize(file_get_contents('../../../../var/log' .'/' .$name));
