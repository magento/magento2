<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    'scopes' => [
        'websites' => [
            'admin' => [
                'website_id' => '0'
            ],
        ],
    ],
    /**
     * Shared configuration was written to config.php and system-specific configuration to env.php.
     * Shared configuration file (config.php) doesn't contain sensitive data for security reasons.
     * Sensitive data can be stored in the following environment variables:
     * CONFIG__DEFAULT__SOME__PAYMENT__PASSWORD for some/payment/password
     */
    'system' => []
    /**
     * CONFIG__DEFAULT__SOME__PAYMENT__TOKEN for some/payment/token
     * test phrase CONFIG__DEFAULT__SOME__PAYMENT__TOKEN for some/payment/token
     */
];
