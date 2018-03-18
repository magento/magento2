<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'missing_product_sku' => [
        [
<<<<<<< HEAD
            [
                'title' => 'title',
                'type' => 'field',
                'sort_order' => 1,
                'is_require' => 1,
                'price' => 10.0,
                'price_type' => 'fixed',
                'sku' => 'sku1',
                'max_characters' => 10,
            ],
            'The ProductSku is empty. Set the ProductSku and try again.',
        ]
=======
            'title'          => 'title',
            'type'           => 'field',
            'sort_order'     => 1,
            'is_require'     => 1,
            'price'          => 10.0,
            'price_type'     => 'fixed',
            'max_characters' => 10,
        ],
        'ProductSku should be specified',
        400
    ],
    'invalid_product_sku' => [
        [
            'title'          => 'title',
            'type'           => 'field',
            'sort_order'     => 1,
            'is_require'     => 1,
            'price'          => 10.0,
            'price_type'     => 'fixed',
            'product_sku'            => 'sku1',
            'max_characters' => 10,
        ],
        'Requested product doesn\'t exist',
        404
    ],
>>>>>>> upstream/2.2-develop
];
