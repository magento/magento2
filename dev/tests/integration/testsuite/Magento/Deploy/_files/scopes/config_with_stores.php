<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'scopes' => [
        'websites' => [
            'test_website' => [
                'website_id' => '2',
                'code' => 'test_website',
                'name' => 'Test Website',
                'sort_order' => '10',
                'default_group_id' => '1',
                'is_default' => '0',
            ],
        ],
        'groups' => [
            2 => [
                'group_id' => '2',
                'website_id' => '2',
                'name' => 'Test Website Store',
                'root_category_id' => '2',
                'default_store_id' => '1',
                'code' => 'test_website_store',
            ],
        ],
        'stores' => [
            'test' => [
                'store_id' => '2',
                'code' => 'test',
                'website_id' => '2',
                'group_id' => '2',
                'name' => 'Test Store view',
                'sort_order' => '23',
                'is_active' => '1',
            ],
        ],
    ]
];
