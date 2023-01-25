<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    'backend' => [
        'id' => 'backend',
        'routes' => [
            'adminhtml' => [
                'id' => 'adminhtml',
                'frontName' => 'admin',
                'modules' => [
                    'Magento_ModuleA',
                    'Magento_ModuleB',
                    'Magento_ModuleC',
                    'Magento_ModuleD',
                    'Magento_ModuleE',
                    'Magento_ModuleF',
                ],
            ],
            'customer' => ['id' => 'customer', 'frontName' => 'customer', 'modules' => ['Magento_ModuleE']],
            'wishlist' => ['id' => 'wishlist', 'frontName' => 'wishlist', 'modules' => ['Magento_ModuleC']],
        ],
    ],
    'front' => ['id' => 'front']
];
