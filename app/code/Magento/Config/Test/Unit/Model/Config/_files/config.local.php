<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'scopes' => [
        'websites' => [
            'admin' => [
                'website_id' => '0'
            ],
        ],
    ],
    /**
     * The configuration file doesn't contain sensitive data for security reasons.
     * Sensitive data can be stored in the following environment variables:
     * CONFIG__DEFAULT__SOME__PAYMENT__PASSWORD for some/payment/password
     */
    'system' => []
    /**
     * CONFIG__DEFAULT__SOME__PAYMENT__TOKEN for some/payment/token
     * test phrase CONFIG__DEFAULT__SOME__PAYMENT__TOKEN for some/payment/token
     */
];
