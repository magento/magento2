<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    'data' => [
        'default' => [
            'payment' => [
                'payment_method' => [
                    'active' => 0,
                    'debug' => 0,
                    'email_customer' => 0,
                    'login' => null,
                    'merchant_email' => null,
                    'order_status' => 'processing',
                    'trans_key' => null,
                ],
            ],
        ],
    ],
    'metadata' => [
        'payment/payment_method/login' => ['backendModel' => 'Custom_Backend_Model_Config_Backend_Encrypted'],
        'payment/payment_method/trans_key' => ['backendModel' => 'Custom_Backend_Model_Config_Backend_Encrypted'],
    ]
];
