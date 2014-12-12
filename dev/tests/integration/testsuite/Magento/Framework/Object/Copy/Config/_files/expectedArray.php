<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
