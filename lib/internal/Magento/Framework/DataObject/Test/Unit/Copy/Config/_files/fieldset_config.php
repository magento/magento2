<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'global' => [
        'sales_convert_quote_address' => [
            'company' => ['to_order_address' => '*', 'to_customer_address' => '*'],
            'street_full' => ['to_order_address' => 'street'],
            'street' => ['to_customer_address' => '*'],
        ],
    ]
];
