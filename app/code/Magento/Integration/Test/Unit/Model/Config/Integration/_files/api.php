<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
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
