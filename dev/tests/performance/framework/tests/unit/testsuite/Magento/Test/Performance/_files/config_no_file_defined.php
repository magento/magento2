<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$result = require __DIR__ . '/config_data.php';
unset($result['scenario']['scenarios']['Scenario']['file']);
return $result;
