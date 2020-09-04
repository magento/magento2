<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

$result = require __DIR__ . '/core_totals_config.php';
$result += [
    'handling' => ['after' => ['shipping'], 'before' => ['tax']],
    'handling_tax' => ['after' => ['tax_shipping'], 'before' => ['tax']],
    'own_subtotal' => ['after' => [], 'before' => ['subtotal']],
    'own_total1' => ['after' => [], 'before' => ['subtotal']],
    'own_total2' => ['after' => [], 'before' => ['subtotal']]
];
return $result;
