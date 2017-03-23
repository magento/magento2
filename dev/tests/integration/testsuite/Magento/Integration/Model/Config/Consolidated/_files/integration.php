<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'TestIntegration1' => [
        'email' => 'test-integration1@example.com',
        'endpoint_url' => 'http://example.com/endpoint1',
        'identity_link_url' => 'http://www.example.com/identity1',
        'resource' => [
            'Magento_Customer::customer',
            'Magento_Customer::manage',
            'Magento_Sales::sales',
            'Magento_Sales::sales_operation',
            'Magento_Sales::sales_order',
            'Magento_Sales::actions',
            'Magento_Sales::capture',
            'Magento_Backend::marketing',
            'Magento_CatalogRule::promo',
            'Magento_SalesRule::quote'
        ]
    ],
    'TestIntegration2' => [
        'email' => 'test-integration2@example.com',
        'endpoint_url' => 'http://example.com/integration2',
        'identity_link_url' => 'http://www.example.com/identity2',
        'resource' => [
            'Magento_Sales::sales',
            'Magento_Sales::sales_operation',
            'Magento_Sales::sales_order',
            'Magento_Sales::actions',
            'Magento_Sales::capture',
            'Magento_Backend::stores',
            'Magento_Backend::stores_settings',
            'Magento_Config::config',
            'Magento_SalesRule::config_promo'
        ]
    ],
    'TestIntegration3' => [
        'email' => 'test-integration3@example.com',
        'resource' => [
            'Magento_Sales::sales',
            'Magento_Sales::sales_operation',
            'Magento_Sales::sales_order',
            'Magento_Sales::actions',
            'Magento_Sales::create',
            'Magento_Backend::marketing',
            'Magento_CatalogRule::promo',
            'Magento_SalesRule::quote'
        ]
    ]
];
