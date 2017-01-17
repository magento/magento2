<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

if (!isset($_GET['name'])) {
    throw new \InvalidArgumentException('The name of log file is required for getting logs.');
}

$name = urldecode($_GET['name']);
$file = file_get_contents('../../../../var/log/' . $name);

echo serialize($file);
