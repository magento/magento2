<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$result = require __DIR__ . '/config_data.php';
$result['scenario']['scenarios']['Scenario']['arguments'] = [
    \Magento\TestFramework\Performance\Scenario::ARG_LOOPS => 'A',
];
return $result;
