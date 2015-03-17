<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'types' => [
        'type_one' => [
            'name' => 'type_one',
            'label' => 'Label One',
            'model' => 'Instance_Type',
            'composite' => true,
            'index_priority' => 40,
            'can_use_qty_decimals' => true,
            'is_qty' => true,
            'sort_order' => 100,
            'price_model' => 'Instance_Type_One',
            'price_indexer' => 'Instance_Type_Two',
            'stock_indexer' => 'Instance_Type_Three',
        ],
        'type_two' => [
            'name' => 'type_two',
            'label' => false,
            'model' => 'Instance_Type',
            'composite' => false,
            'index_priority' => 0,
            'can_use_qty_decimals' => true,
            'is_qty' => false,
            'sort_order' => 0,
            'allowed_selection_types' => ['type_two' => 'type_two'],
            'custom_attributes' => ['some_name' => 'some_value'],
        ],
        'type_three' => [
            'name' => 'type_three',
            'label' => 'Label Three',
            'model' => 'Instance_Type',
            'composite' => false,
            'index_priority' => 20,
            'can_use_qty_decimals' => false,
            'is_qty' => false,
            'sort_order' => 5,
            'price_model' => 'Instance_Type_Three',
            'price_indexer' => 'Instance_Type_Three',
            'stock_indexer' => 'Instance_Type_Three',
        ],
    ],
    'composableTypes' => ['type_one' => 'type_one', 'type_three' => 'type_three']
];
