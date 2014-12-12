<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

$result = require __DIR__ . '/config_data.php';
$result['scenario']['scenarios']['Scenario']['fixtures'] = 'fixtures/*.php';
return $result;
