<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

return [
    'createTransactionRequest' => [
        'merchantAuthentication' => [
            'name' => 'someusername',
            'transactionKey' => 'somepassword',
        ],
        'transactionRequest' =>[
            'transactionType' => 'voidTransaction',
            'refTransId' => '1234',
        ],
    ]
];
