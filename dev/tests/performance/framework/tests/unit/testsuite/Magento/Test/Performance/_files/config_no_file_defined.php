<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

$result = require __DIR__ . '/config_data.php';
unset($result['scenario']['scenarios']['Scenario']['file']);
return $result;
