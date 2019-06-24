<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\CardinalCommerce\Model\Config;
use Magento\CardinalCommerce\Model\JwtManagement;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var JwtManagement $jwtManagment */
$jwtManagment = $objectManager->get(JwtManagement::class);
/** @var Config $config */
$config = $objectManager->get(Config::class);
$currentDate = new \DateTime('now', new \DateTimeZone('UTC'));
$response = [
    'iss' => 'some_api_identifier',
    'iat' => 1559855656,
    'exp' => $currentDate->getTimestamp() + 3600,
    'jti' => '0d695df5-ca06-4f7d-b150-ff169510f6d2',
    'ConsumerSessionId' => '0_9e6a4084-2191-4fd7-9631-19f576375e0a',
    'ReferenceId' => '0_9e6a4084-2191-4fd7-9631-19f576375e0a',
    'aud' => '52efb9cc-843c-4ee9-a38c-107943be6b03',
    'Payload' => [
        'Validated' => true,
        'Payment' => [
            'Type' => 'CCA',
            'ProcessorTransactionId' => '4l7xg1WA7CS0YwgPgNZ0',
            'ExtendedData' => [
                'CAVV' => 'AAABAWFlmQAAAABjRWWZEEFgFz8=',
                'ECIFlag' => '05',
                'XID' => 'NGw3eGcxV0E3Q1MwWXdnUGdOWjA=',
                'Enrolled' => 'Y',
                'PAResStatus' => 'Y',
                'SignatureVerification' => 'Y',
            ],
        ],
        'ActionCode' => 'SUCCESS',
        'ErrorNumber' => 0,
        'ErrorDescription' => 'Success',
    ],
];
$cardinalJWT = $jwtManagment->encode($response, $config->getApiKey());

return $cardinalJWT;
