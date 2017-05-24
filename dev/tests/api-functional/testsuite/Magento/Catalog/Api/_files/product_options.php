<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    [
        'title' => 'test_option_code_1',
        'type' => 'field',
        'sort_order' => 1,
        'is_require' => 1,
        'price' => 10,
        'price_type' => 'fixed',
        'sku' => 'sku1',
        'max_characters' => 10,
    ],
    [
        'title' => 'area option',
        'type' => 'area',
        'sort_order' => 2,
        'is_require' => 0,
        'price' => 20,
        'price_type' => 'percent',
        'sku' => 'sku2',
        'max_characters' => 20,
    ],
    [
        'title' => 'file option',
        'type' => 'file',
        'sort_order' => 3,
        'is_require' => 1,
        'price' => 30,
        'price_type' => 'percent',
        'sku' => 'sku3',
        'file_extension' => 'jpg, png, gif',
        'image_size_x' => 10,
        'image_size_y' => 20,
    ],
    [
        'title' => 'drop_down option',
        'type' => 'drop_down',
        'sort_order' => 4,
        'is_require' => 1,
        'values' => [
            [
                'title' => 'drop_down option 1',
                'sort_order' => 1,
                'price' => 10,
                'price_type' => 'fixed',
                'sku' => 'drop_down option 1 sku',

            ],
            [
                'title' => 'drop_down option 2',
                'sort_order' => 2,
                'price' => 20,
                'price_type' => 'fixed',
                'sku' => 'drop_down option 2 sku'
            ],
        ],
    ],
    [
        'title' => 'radio option',
        'type' => 'radio',
        'sort_order' => 5,
        'is_require' => 1,
        'values' => [
            [
                'title' => 'radio option 1',
                'sort_order' => 1,
                'price' => 10,
                'price_type' => 'fixed',
                'sku' => 'radio option 1 sku',
            ],
            [
                'title' => 'radio option 2',
                'sort_order' => 2,
                'price' => 20,
                'price_type' => 'fixed',
                'sku' => 'radio option 2 sku',
            ],
        ],
    ],
    [
        'title' => 'checkbox option',
        'type' => 'checkbox',
        'sort_order' => 6,
        'is_require' => 1,
        'values' => [
            [
                'title' => 'checkbox option 1',
                'sort_order' => 1,
                'price' => 10,
                'price_type' => 'fixed',
                'sku' => 'checkbox option 1 sku',
            ],
            [
                'title' => 'checkbox option 2',
                'sort_order' => 2,
                'price' => 20,
                'price_type' => 'fixed',
                'sku' => 'checkbox option 2 sku'
            ],
        ],
    ],
    [
        'title' => 'multiple option',
        'type' => 'multiple',
        'sort_order' => 7,
        'is_require' => 1,
        'values' => [
            [
                'title' => 'multiple option 1',
                'sort_order' => 1,
                'price' => 10,
                'price_type' => 'fixed',
                'sku' => 'multiple option 1 sku',
            ],
            [
                'title' => 'multiple option 2',
                'sort_order' => 2,
                'price' => 20,
                'price_type' => 'fixed',
                'sku' => 'multiple option 2 sku'
            ],
        ],
    ],
    [
        'title' => 'date option',
        'type' => 'date',
        'is_require' => 1,
        'sort_order' => 8,
        'price' => 80.0,
        'price_type' => 'fixed',
        'sku' => 'date option sku',
    ],
    [
        'title' => 'date_time option',
        'type' => 'date_time',
        'is_require' => 1,
        'sort_order' => 9,
        'price' => 90.0,
        'price_type' => 'fixed',
        'sku' => 'date_time option sku',
    ],
    [
        'title' => 'time option',
        'type' => 'time',
        'is_require' => 1,
        'sort_order' => 10,
        'price' => 100.0,
        'price_type' => 'fixed',
        'sku' => 'time option sku',
    ],
];
