<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    // Totals declared in Magento_Sales
    'subtotal' => ['after' => [], 'before' => ['grand_total']],
    'shipping' => [
        'after' => ['subtotal', 'freeshipping', 'tax_subtotal'],
        'before' => ['grand_total'],
    ],
    'grand_total' => ['after' => ['subtotal'], 'before' => []],
    'msrp' => ['after' => [], 'before' => []],
    // Totals declared in Magento_SalesRule
    'freeshipping' => ['after' => ['subtotal'], 'before' => ['tax_subtotal', 'shipping']],
    'discount' => ['after' => ['subtotal', 'shipping'], 'before' => ['grand_total']],
    // Totals declared in Magento_Tax
    'tax_subtotal' => ['after' => ['freeshipping'], 'before' => ['tax', 'discount']],
    'tax_shipping' => ['after' => ['shipping'], 'before' => ['tax', 'discount']],
    'tax' => ['after' => ['subtotal', 'shipping'], 'before' => ['grand_total']],
    // Totals declared in Magento_Weee
    'weee' => ['after' => ['subtotal', 'tax', 'discount', 'grand_total', 'shipping'], 'before' => []]
];
