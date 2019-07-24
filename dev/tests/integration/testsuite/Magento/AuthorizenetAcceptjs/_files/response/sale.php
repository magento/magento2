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
        'transId' => '123456',
        'refTransID' => '',
        'transHash' => 'foobar',
        'testRequest' => '0',
        'accountNumber' => 'XXXX1111',
        'accountType' => 'Visa',
        'messages' => [
            [
                'code' => '1',
                'description' => 'This transaction has been approved.',
            ],
        ],
        'userFields' => [
            [
                'name' => 'transactionType',
                'value' => 'authCaptureTransaction',
            ],
        ],
        'transHashSha2' => '3FA1733C95FD40BE865D1E09A0F8E9947EF8B00FDD64098B7C524DA23EE8ADA1043E'
            . 'CE5BB8DF8E706E46503DF5AB6A6A2AB10DC2FF0DFD3EA8DD6EA9DD84CEBF',
        'SupplementalDataQualificationIndicator' => 0,
    ],
    'messages' => [
        'resultCode' => 'Ok',
        'message' => [
            [
                'code' => 'I00001',
                'text' => 'Successful.',
            ],
        ],
    ],
];
