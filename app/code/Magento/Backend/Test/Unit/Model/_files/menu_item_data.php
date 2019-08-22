<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'no submenu' => [
        [
            'id' => 'item',
            'title' => 'Item Title',
            'action' => '/system/config',
            'resource' => 'Magento_Config::config',
            'dependsOnModule' => 'Magento_Backend',
            'dependsOnConfig' => 'system/config/isEnabled',
            'toolTip' => 'Item tooltip',
            'sub_menu' => null,
        ],
        [
            'parent_id' => null,
            'module' => 'Magento_Backend',
            'sort_index' => null,
            'dependsOnConfig' => 'system/config/isEnabled',
            'id' => 'item',
            'resource' => 'Magento_Config::config',
            'path' => '',
            'action' => '/system/config',
            'dependsOnModule' => 'Magento_Backend',
            'toolTip' => 'Item tooltip',
            'title' => 'Item Title',
            'sub_menu' => null,
            'target' => null
        ]
    ],
    'with submenu' => [
        [
            'parent_id' => '1',
            'module' => 'Magento_Module1',
            'sort_index' => '50',
            'dependsOnConfig' => null,
            'id' => '5',
            'resource' => null,
            'path' => null,
            'action' => null,
            'dependsOnModule' => null,
            'toolTip' => null,
            'title' => null,
            'sub_menu' => [
                'id' => 'item',
                'title' => 'Item Title',
                'action' => '/system/config',
                'resource' => 'Magento_Config::config',
                'dependsOnModule' => 'Magento_Backend',
                'dependsOnConfig' => 'system/config/isEnabled',
                'toolTip' => 'Item tooltip',
            ],
        ],
        [
            'parent_id' => '1',
            'module' => 'Magento_Module1',
            'sort_index' => '50',
            'dependsOnConfig' => null,
            'id' => '5',
            'resource' => null,
            'path' => null,
            'action' => null,
            'dependsOnModule' => null,
            'toolTip' => '',
            'title' => null,
            'sub_menu' => [
                'id' => 'item',
                'title' => 'Item Title',
                'action' => '/system/config',
                'resource' => 'Magento_Config::config',
                'dependsOnModule' => 'Magento_Backend',
                'dependsOnConfig' => 'system/config/isEnabled',
                'toolTip' => 'Item tooltip',
            ],
            'target' => null
        ]
    ],
    'small set of data' => [
        [
            'parent_id' => '1',
            'module' => 'Magento_Module1',
            'sort_index' => '50',
            'sub_menu' => [
                'id' => 'item',
                'title' => 'Item Title',
                'action' => '/system/config',
                'resource' => 'Magento_Config::config',
                'dependsOnModule' => 'Magento_Backend',
                'dependsOnConfig' => 'system/config/isEnabled',
                'toolTip' => 'Item tooltip',
            ],
        ],
        [
            'parent_id' => '1',
            'module' => 'Magento_Module1',
            'sort_index' => '50',
            'dependsOnConfig' => null,
            'id' => null,
            'resource' => null,
            'path' => '',
            'action' => null,
            'dependsOnModule' => null,
            'toolTip' => '',
            'title' => null,
            'sub_menu' => [
                'id' => 'item',
                'title' => 'Item Title',
                'action' => '/system/config',
                'resource' => 'Magento_Config::config',
                'dependsOnModule' => 'Magento_Backend',
                'dependsOnConfig' => 'system/config/isEnabled',
                'toolTip' => 'Item tooltip',
            ],
            'target' => null
        ]
    ]
];
