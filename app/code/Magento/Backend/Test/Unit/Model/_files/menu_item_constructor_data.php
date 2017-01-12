<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'default data to constructor' => [
        [],
        [
            'id' => 'item',
            'title' => 'Item Title',
            'action' => '/system/config',
            'resource' => 'Magento_Config::config',
            'depends_on_module' => 'Magento_Backend',
            'depends_on_config' => 'system/config/isEnabled',
            'tooltip' => 'Item tooltip',
        ],
        [
            'parent_id' => null,
            'module_name' => 'Magento_Backend',
            'sort_index' => null,
            'depends_on_config' => 'system/config/isEnabled',
            'id' => 'item',
            'resource' => 'Magento_Config::config',
            'path' => '',
            'action' => '/system/config',
            'depends_on_module' => 'Magento_Backend',
            'tooltip' => 'Item tooltip',
            'title' => 'Item Title',
            'sub_menu' => null
        ],
    ],
    'data without submenu to constructor' => [
        [
            'id' => 'item',
            'title' => 'Item Title',
            'action' => '/system/config',
            'resource' => 'Magento_Config::config',
            'depends_on_module' => 'Magento_Backend',
            'depends_on_config' => 'system/config/isEnabled',
            'tooltip' => 'Item tooltip',
        ],
        [
            'parent_id' => '1',
            'module_name' => 'Magento_Module1',
            'sort_index' => '50',
            'depends_on_config' => null,
            'id' => '5',
            'resource' => null,
            'path' => null,
            'action' => null,
            'depends_on_module' => null,
            'tooltip' => null,
            'title' => null,
            'sub_menu' => [
                'id' => 'item',
                'title' => 'Item Title',
                'action' => '/system/config',
                'resource' => 'Magento_Config::config',
                'depends_on_module' => 'Magento_Backend',
                'depends_on_config' => 'system/config/isEnabled',
                'tooltip' => 'Item tooltip',
            ],
        ],
        [
            'parent_id' => '1',
            'module_name' => 'Magento_Module1',
            'sort_index' => '50',
            'depends_on_config' => null,
            'id' => '5',
            'resource' => null,
            'path' => '',
            'action' => null,
            'depends_on_module' => null,
            'tooltip' => '',
            'title' => null,
            'sub_menu' => ['submenuArray']
        ],
    ],
    'data with submenu to constructor' => [
        [
            'parent_id' => '1',
            'module_name' => 'Magento_Module1',
            'sort_index' => '50',
            'depends_on_config' => null,
            'id' => '5',
            'resource' => null,
            'path' => null,
            'action' => null,
            'depends_on_module' => null,
            'tooltip' => null,
            'title' => null,
            'sub_menu' => [
                'id' => 'item',
                'title' => 'Item Title',
                'action' => '/system/config',
                'resource' => 'Magento_Config::config',
                'depends_on_module' => 'Magento_Backend',
                'depends_on_config' => 'system/config/isEnabled',
                'tooltip' => 'Item tooltip',
            ],
        ],
        [
            'parent_id' => '1',
            'module_name' => 'Magento_Module1',
            'sort_index' => '50',
            'sub_menu' => [
                'id' => 'item',
                'title' => 'Item Title',
                'action' => '/system/config',
                'resource' => 'Magento_Config::config',
                'depends_on_module' => 'Magento_Backend',
                'depends_on_config' => 'system/config/isEnabled',
                'tooltip' => 'Item tooltip',
            ],
        ],
        [
            'parent_id' => '1',
            'module_name' => 'Magento_Module1',
            'sort_index' => '50',
            'depends_on_config' => null,
            'id' => null,
            'resource' => null,
            'path' => '',
            'action' => null,
            'depends_on_module' => null,
            'tooltip' => '',
            'title' => null,
            'sub_menu' => ['submenuArray']
        ],
    ]
];
