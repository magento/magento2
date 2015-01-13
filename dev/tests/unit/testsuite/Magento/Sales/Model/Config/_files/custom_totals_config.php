<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$result = require __DIR__ . '/core_totals_config.php';
$result += [
    'handling' => ['after' => ['shipping'], 'before' => ['tax']],
    'handling_tax' => ['after' => ['tax_shipping'], 'before' => ['tax']],
    'own_subtotal' => ['after' => ['nominal'], 'before' => ['subtotal']],
    'own_total1' => ['after' => ['nominal'], 'before' => ['subtotal']],
    'own_total2' => ['after' => ['nominal'], 'before' => ['subtotal']]
];
return $result;
