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
<<<<<<< HEAD
        'ProductSku should be specified',
        400
=======
        'The ProductSku is empty. Set the ProductSku and try again.',
        400,
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    ],
    'invalid_product_sku' => [
        [
            'title'          => 'title',
            'type'           => 'field',
            'sort_order'     => 1,
            'is_require'     => 1,
            'price'          => 10.0,
            'price_type'     => 'fixed',
<<<<<<< HEAD
            'product_sku'            => 'sku1',
            'max_characters' => 10,
        ],
        'Requested product doesn\'t exist',
        404
=======
            'product_sku'    => 'sku1',
            'max_characters' => 10,
        ],
        'The product that was requested doesn\'t exist. Verify the product and try again.',
        404,
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    ],
];
