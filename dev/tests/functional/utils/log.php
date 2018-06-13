<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

if (!isset($_GET['name'])) {
    throw new \InvalidArgumentException('The name of log file is required for getting logs.');
}

$name = urldecode($_GET['name']);
$logDir = realpath('../../../../var/log');
$logFile = realpath($logDir .'/' .$name);
if (!$logFile || !$logDir || mb_strpos($logFile, $logDir .'/') !== 0) {
    throw new \InvalidArgumentException('Invalid log file name');
}
$file = file_get_contents($logFile);

echo serialize($file);
