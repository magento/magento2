<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'TestIntegration1' => [
        'email' => 'test-integration1@magento.com',
        'endpoint_url' => 'http://endpoint.com',
        'identity_link_url' => 'http://www.example.com/identity',
        'resource' => [
            'Magento_Customer::manageParent',
            'Magento_Customer::manage',
            'Magento_SalesRule::quoteParent',
            'Magento_SalesRule::quote'
        ]
    ],
    'TestIntegration2' => [
        'email' => 'test-integration2@magento.com',
        'resource' => ['Magento_Sales::sales']
    ]
];
