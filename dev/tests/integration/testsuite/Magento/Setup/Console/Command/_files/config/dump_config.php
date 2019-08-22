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
            'base' => [
                'website_id' => '1',
                'code' => 'base',
                'name' => 'Main Website',
                'sort_order' => '0',
                'default_group_id' => '1',
                'is_default' => '1',
            ],
        ],
        'groups' => [
            0 => [
                'group_id' => '0',
                'website_id' => '0',
                'code' => 'default',
                'name' => 'Default',
                'root_category_id' => '0',
                'default_store_id' => '0',
            ],
            1 => [
                'group_id' => '1',
                'website_id' => '1',
                'code' => 'main_website_store',
                'name' => 'Main Website Store',
                'root_category_id' => '2',
                'default_store_id' => '1',
            ],
        ],
        'stores' => [
            'admin' => [
                'store_id' => '0',
                'code' => 'admin',
                'website_id' => '0',
                'group_id' => '0',
                'name' => 'Admin',
                'sort_order' => '0',
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
        ],
    ],
    'themes' => [
            'adminhtml/Magento/backend' => [
                    'parent_id' => null,
                    'theme_path' => 'Magento/backend',
                    'theme_title' => 'Magento 2 backend',
                    'is_featured' => '0',
                    'area' => 'adminhtml',
                    'type' => '0',
                    'code' => 'Magento/backend',
            ],
            'frontend/Magento/blank' => [
                    'parent_id' => null,
                    'theme_path' => 'Magento/blank',
                    'theme_title' => 'Magento Blank',
                    'is_featured' => '0',
                    'area' => 'frontend',
                    'type' => '0',
                    'code' => 'Magento/blank',
            ],
            'frontend/Magento/luma' => [
                    'parent_id' => 'Magento/blank',
                    'theme_path' => 'Magento/luma',
                    'theme_title' => 'Magento Luma',
                    'is_featured' => '0',
                    'area' => 'frontend',
                    'type' => '0',
                    'code' => 'Magento/luma',
            ],
    ],
];
