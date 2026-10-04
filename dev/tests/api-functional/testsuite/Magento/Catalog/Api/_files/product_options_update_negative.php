<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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
        'The ProductSku is empty. Set the ProductSku and try again.',
        400,
    ],
    'invalid_product_sku' => [
        [
            'title'          => 'title',
            'type'           => 'field',
            'sort_order'     => 1,
            'is_require'     => 1,
            'price'          => 10.0,
            'price_type'     => 'fixed',
            'product_sku'    => 'sku1',
            'max_characters' => 10,
        ],
        'The product with SKU "%1" does not exist.',
        404,
    ],
];
