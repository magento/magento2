<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'missing_product_sku' => [
        [
            'title'          => 'title',
            'type'           => 'field',
            'sort_order'     => 1,
            'is_require'     => 1,
            'price'          => 10.0,
            'price_type'     => 'fixed',
            'max_characters' => 10,
        ],
        'ProductSku should be specified',
    ],
    'invalid_product_sku' => [
        [
            'title'          => 'title',
            'type'           => 'field',
            'sort_order'     => 1,
            'is_require'     => 1,
            'price'          => 10.0,
            'price_type'     => 'fixed',
            'sku'            => 'sku1',
            'max_characters' => 10,
        ],
        'Requested product doesn\'t exist',
    ],
];
