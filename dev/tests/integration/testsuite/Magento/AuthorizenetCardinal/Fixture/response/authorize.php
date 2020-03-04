<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

return [
    'transactionResponse' => [
        'responseCode' => '1',
        'authCode' => 'abc123',
        'avsResultCode' => 'P',
        'cvvResultCode' => '',
        'cavvResultCode' => '2',
        'transId' => '123456',
        'refTransID' => '',
        'transHash' => 'foobar',
        'testRequest' => '0',
        'accountNumber' => 'XXXX1111',
        'accountType' => 'Visa',
        'messages' => [
            [
                'code' => '1',
                'description' => 'This transaction has been approved.'
            ]
        ],
        'userFields' => [
            [
                'name' => 'transactionType',
                'value' => 'authOnlyTransaction'
            ]
        ],
        'transHashSha2' => 'CD1E57FB1B5C876FDBD536CB16F8BBBA687580EDD78DD881C7F14DC4467C32BF6C'
            . '808620FBD59E5977DF19460B98CCFC0DA0D90755992C0D611CABB8E2BA52B0',
        'SupplementalDataQualificationIndicator' => 0
    ],
    'messages' => [
        'resultCode' => 'Ok',
        'message' => [
            [
                'code' => 'I00001',
                'text' => 'Successful.'
            ]
        ]
    ]
];
