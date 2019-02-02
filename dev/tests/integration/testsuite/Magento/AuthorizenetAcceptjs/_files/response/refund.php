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
        'avsResultCode' => 'Y',
        'cvvResultCode' => 'P',
        'cavvResultCode' => '2',
        'transId' => '40024660848',
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
        'transHashSha2' => '2FDBC382A938B1D84FEC2DDCC3DF2AAAA89CD9D0A8991C2E26367422ECC4A6878AA',
        'SupplementalDataQualificationIndicator' => 0
    ],
    'messages' => [
        'resultCode' => 'Ok',
        'message' => [
            ['code' => 'I00001','text' => 'Successful.']
        ]
    ]
];
