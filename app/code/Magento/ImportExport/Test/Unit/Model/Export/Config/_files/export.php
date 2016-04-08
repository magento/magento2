<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'entities' => [
        'product' => [
            'name' => 'product',
            'label' => 'Label_One',
            'model' => 'Model\One',
            'types' => [
                'product_type_one' => ['name' => 'product_type_one', 'model' => 'Product\Model\Type\One'],
                'type_two' => ['name' => 'type_two', 'model' => 'Model\Type\Two'],
            ],
            'entityAttributeFilterType' => 'product',
        ],
        'customer' => [
            'name' => 'customer',
            'label' => 'Label_One',
            'model' => 'Model\One',
            'types' => [
                'type_one' => ['name' => 'type_one', 'model' => 'Model\Type\One'],
                'type_two' => ['name' => 'type_two', 'model' => 'Model\Type\Two'],
            ],
            'entityAttributeFilterType' => 'customer',
        ],
    ],
    'fileFormats' => [
        'name_three' => ['name' => 'name_three', 'model' => 'Model\Three', 'label' => 'Label_Three'],
    ]
];
