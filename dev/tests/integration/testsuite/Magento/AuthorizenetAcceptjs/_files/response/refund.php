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
        'transId' => '5678',
        'refTransID' => '4321',
        'testRequest' => '0',
        'accountNumber' => 'XXXX1111',
        'accountType' => 'Visa',
        'messages' => [
            [
                'code' => '1',
                'description' => 'This transaction has been approved.'
            ]
        ],
        'transHashSha2' => '78BD31BA5BCDF3C3FA3C8373D8DF80EF07FC7E02C3545FCF18A408E2F76ED4F20D'
            . 'FF007221374B576FDD1BFD953B3E5CF37249CEC4C135EEF975F7B478D8452C',
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
