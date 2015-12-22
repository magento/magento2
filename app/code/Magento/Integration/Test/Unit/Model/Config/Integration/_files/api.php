<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'TestIntegration1' => [
        'resource' => [
            'Magento_Customer::manage',
            'Magento_Customer::manageParent',
            'Magento_SalesRule::quote',
            'Magento_SalesRule::quoteParent'
        ],
    ],
    'TestIntegration2' => ['resource' => ['Magento_Sales::sales']]
];
