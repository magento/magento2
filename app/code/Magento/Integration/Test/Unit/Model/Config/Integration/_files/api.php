<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    'TestIntegration1' => [
        'resource' => [
            'Magento_Customer::manage',
            'Magento_Customer::online',
            'Magento_Sales::capture',
            'Magento_SalesRule::quote',
        ],
    ],
    'TestIntegration2' => ['resource' => ['Magento_Catalog::product_read']]
];
