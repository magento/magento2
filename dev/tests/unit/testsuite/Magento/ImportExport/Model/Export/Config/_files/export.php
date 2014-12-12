<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
return [
    'entities' => [
        'product' => [
            'name' => 'product',
            'label' => 'Label_One',
            'model' => 'Model_One',
            'types' => [
                'product_type_one' => ['name' => 'product_type_one', 'model' => 'Product_Model_Type_One'],
                'type_two' => ['name' => 'type_two', 'model' => 'Model_Type_Two'],
            ],
            'entityAttributeFilterType' => 'product',
        ],
        'customer' => [
            'name' => 'customer',
            'label' => 'Label_One',
            'model' => 'Model_One',
            'types' => [
                'type_one' => ['name' => 'type_one', 'model' => 'Model_Type_One'],
                'type_two' => ['name' => 'type_two', 'model' => 'Model_Type_Two'],
            ],
            'entityAttributeFilterType' => 'customer',
        ],
    ],
    'fileFormats' => [
        'name_three' => ['name' => 'name_three', 'model' => 'Model_Three', 'label' => 'Label_Three'],
    ]
];
