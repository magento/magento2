<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

return [
    'global' => [
        'sales_convert_quote_address' => [
            'company' => ['to_order_address' => '*', 'to_customer_address' => '*'],
            'street_full' => ['to_order_address' => 'street'],
            'street' => ['to_customer_address' => '*'],
        ],
    ]
];
