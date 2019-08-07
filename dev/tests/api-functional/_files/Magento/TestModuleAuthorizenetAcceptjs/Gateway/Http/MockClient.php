<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleAuthorizenetAcceptjs\Gateway\Http;

use Magento\Framework\Math\Random;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

/**
 * A client for mocking communicate with the Authorize.net API
 */
class MockClient implements ClientInterface
{
    /**
     * @var Random
     */
    private $random;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @param Random $random
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        Random $random,
        ArrayManager $arrayManager
    ) {
        $this->random = $random;
        $this->arrayManager = $arrayManager;
    }

    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject): array
    {
        $request = $transferObject->getBody();
        $nonce = $this->arrayManager->get('transactionRequest/payment/opaqueData/dataValue', $request);
        $descriptor = $this->arrayManager->get('transactionRequest/payment/opaqueData/dataDescriptor', $request);

        $approve = true;
        if ($nonce !== 'fake-nonce' || $descriptor !== 'COMMON.ACCEPT.INAPP.PAYMENT') {
            $approve = false;
        }

        return $this->createResponse($approve);
    }

    /**
     * Create mock response body
     *
     * @param bool $approve
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function createResponse(bool $approve): array
    {
        return [
            'transactionResponse' => [
                'responseCode' => $approve ? '1' : '2',
                'authCode' => strtoupper($this->random->getRandomString(6)),
                'avsResultCode' => 'Y',
                'cvvResultCode' => 'P',
                'cavvResultCode' => '2',
                'transId' => random_int(10000000000, 99999999999),
                'refTransId' => '',
                'transHash' => '',
                'testRequest' => '0',
                'accountNumber' => 'XXXX1111',
                'accountType' => 'Visa',
                'messages' => $approve ? $this->getApprovalMessage() : $this->getDeclineMessage(),
                'userFields' => [
                    [
                        'name' => 'transactionType',
                        'value' => 'authOnlyTransaction',
                    ],
                ],
                'transHashSha2' => 'fake-hash',
                'SupplementalDataQualificationIndicator' => '0',
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
    }

    /**
     * Provide approval message for response
     *
     * @return array
     */
    private function getApprovalMessage(): array
    {
        return [
            [
                'code' => '1',
                'description' => 'This transaction has been approved.',
            ],
        ];
    }

    /**
     * Provide decline message for response
     *
     * @return array
     */
    private function getDeclineMessage(): array
    {
        return [
            [
                'code' => '2',
                'description' => 'This transaction has been declined.',
            ],
        ];
    }
}
