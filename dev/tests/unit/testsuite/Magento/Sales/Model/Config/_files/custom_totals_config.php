<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

$result = require __DIR__ . '/core_totals_config.php';
$result += [
    'handling' => ['after' => ['shipping'], 'before' => ['tax']],
    'handling_tax' => ['after' => ['tax_shipping'], 'before' => ['tax']],
    'own_subtotal' => ['after' => [], 'before' => ['subtotal']],
    'own_total1' => ['after' => [], 'before' => ['subtotal']],
    'own_total2' => ['after' => [], 'before' => ['subtotal']]
];
return $result;
