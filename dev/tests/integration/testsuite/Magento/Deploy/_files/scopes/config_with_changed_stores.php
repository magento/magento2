<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'scopes' => [
        'websites' => [
            'admin' => [
                'website_id' => '0',
                'code' => 'admin',
                'name' => 'Admin',
                'sort_order' => '0',
                'default_group_id' => '0',
                'is_default' => '0',
            ],
            'base_code_changed' => [
                'website_id' => '1',
                'code' => 'base_code_changed',
                'name' => 'Main Website',
                'sort_order' => '0',
                'default_group_id' => '1',
                'is_default' => '1',
            ],
            'test_website' => [
                'website_id' => '2',
                'code' => 'test_website',
                'name' => 'Changed Test Website',
                'sort_order' => '10',
                'default_group_id' => '1',
                'is_default' => '0',
            ],
        ],
        'groups' => [
            0 => [
                'group_id' => '0',
                'website_id' => '0',
                'name' => 'Default',
                'root_category_id' => '0',
                'default_store_id' => '0',
                'code' => 'default',
            ],
            1 => [
                'group_id' => '1',
                'website_id' => '1',
                'name' => 'Main Website Store',
                'root_category_id' => '2',
                'default_store_id' => '1',
                'code' => 'main_website_store',
            ],
            2 => [
                'group_id' => '2',
                'website_id' => '2',
                'name' => 'Changed Test Website Store',
                'root_category_id' => '2',
                'default_store_id' => '1',
                'code' => 'test_website_store',
            ],
        ],
        'stores' => [
            'admin' => [
                'store_id' => '0',
                'code' => 'admin',
                'website_id' => '0',
                'group_id' => '0',
                'name' => 'Admin24',
                'sort_order' => '10',
                'is_active' => '1',
            ],
            'default' => [
                'store_id' => '1',
                'code' => 'default',
                'website_id' => '1',
                'group_id' => '1',
                'name' => 'Default Store View',
                'sort_order' => '0',
                'is_active' => '1',
            ],
            'test' => [
                'store_id' => '2',
                'code' => 'test',
                'website_id' => '2',
                'group_id' => '2',
                'name' => 'Changed Test Store view',
                'sort_order' => '23',
                'is_active' => '1',
            ],
        ],
    ]
];
