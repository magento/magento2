<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    'TestIntegration1' => [
        'email' => 'test-integration1@magento.com',
        'endpoint_url' => 'http://endpoint.com',
        'identity_link_url' => 'http://www.example.com/identity',
        'resource' => [
            'Magento_Backend::admin',
            'Magento_Customer::manageParent',
            'Magento_Customer::manage',
            'Magento_SalesRule::quoteParent',
            'Magento_SalesRule::quote'
        ]
    ],
    'TestIntegration2' => [
        'email' => 'test-integration2@magento.com',
        'resource' => [
            'Magento_Backend::admin',
            'Magento_Sales::sales'
        ]
    ]
];
