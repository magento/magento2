<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

return [
    'updateHeldTransactionRequest' => [
        'merchantAuthentication' => [
            'name' => 'someusername',
            'transactionKey' => 'somepassword'
        ],
        'heldTransactionRequest' => [
            'action' => 'approve',
            'refTransId' => '1234',
        ]
    ]
];
