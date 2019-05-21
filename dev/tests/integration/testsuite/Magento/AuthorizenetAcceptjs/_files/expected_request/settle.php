<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

return [
    'createTransactionRequest' => [
        'merchantAuthentication' =>[
            'name' => 'someusername',
            'transactionKey' => 'somepassword',
        ],
        'transactionRequest' => [
            'transactionType' => 'priorAuthCaptureTransaction',
            'refTransId' => '1234',
            'userFields' => [
                'userField' => [
                    [
                        'name' => 'transactionType',
                        'value' => 'priorAuthCaptureTransaction',
                    ],
                ],
            ],
        ],
    ]
];
