<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

return [
    'transactionResponse' =>  [
        'responseCode' => '1',
        'authCode' => '',
        'avsResultCode' => 'P',
        'cvvResultCode' => '',
        'cavvResultCode' => '',
        'transId' => '1234',
        'refTransID' => '1234',
        'testRequest' => '0',
        'accountNumber' => 'XXXX1111',
        'accountType' => 'Visa',
        'messages' => [
            [
                'code' => '1',
                'description' => 'This transaction has been approved.'
            ]
        ],
        'transHashSha2' => '1B22AB4E4DF750CF2E0D1944BB6903537C145545C7313C87B6FD4A6384'
            . '709EA2609CE9A9788C128F2F2EAEEE474F6010418904648C6D000BE3AF7BCD98A5AD8F',
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
