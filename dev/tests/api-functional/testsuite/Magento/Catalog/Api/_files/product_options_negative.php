<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'empty_required_field' => [
        'title' => '',
        'type' => 'field',
        'sort_order' => 1,
        'is_require' => 1,
        'price' => 10.0,
        'price_type' => 'fixed',
        'sku' => 'sku1',
        'max_characters' => 10,
    ],
    'negative_price' => [
        'title' => 'area option',
        'type' => 'area',
        'sort_order' => 2,
        'is_require' => 0,
        'price' => -20,
        'price_type' => 'percent',
        'sku' => 'sku2',
        'max_characters' => 20,

    ],
    'negative_value_of_image_size' => [
        'title' => 'file option',
        'type' => 'file',
        'sort_order' => 3,
        'is_require' => 1,
        'price' => 30,
        'price_type' => 'percent',
        'sku' => 'sku3',
        'file_extension' => 'jpg',
        'image_size_x' => -10,
        'image_size_y' => -20,
    ],
    'option_with_type_select_without_options' => [
        'title' => 'drop_down option',
        'type' => 'drop_down',
        'sort_order' => 4,
        'is_require' => 1,
    ],
    'title_is_empty' => [
        'title' => 'radio option',
        'type' => 'radio',
        'sort_order' => 5,
        'is_require' => 1,
        'values' => [
            [
                'price' => 10.0,
                'price_type' => 'fixed',
                'sku' => 'radio option 1 sku',
                'title' => '',
                'sort_order' => 1,
            ],
        ],
    ],
    'option_with_non_existing_price_type' => [
        'title' => 'checkbox option',
        'type' => 'checkbox',
        'sort_order' => 6,
        'is_require' => 1,
        'values' => [
            [
                'price' => 10.0,
                'price_type' => 'fixed_one',
                'sku' => 'checkbox option 1 sku',
                'title' => 'checkbox option 1',
                'sort_order' => 1,
            ],
        ],
    ],
    'option_with_non_existing_option_type' => [
        'title' => 'multiple option',
        'type' => 'multiple_some_value',
        'sort_order' => 7,
        'is_require' => 1,
        'values' => [
            [
                'price' => 10.0,
                'price_type' => 'fixed',
                'sku' => 'multiple option 1 sku',
                'title' => 'multiple option 1',
                'sort_order' => 1,
            ],
        ],
    ],
];
