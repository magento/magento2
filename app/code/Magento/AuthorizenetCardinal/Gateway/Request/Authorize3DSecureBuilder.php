<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetCardinal\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\AuthorizenetCardinal\Model\Config;
use Magento\CardinalCommerce\Model\Response\JwtParser;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Adds the cardholder authentication information to the request
 */
class Authorize3DSecureBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var JwtParser
     */
    private $jwtParser;

    /**
     * @param SubjectReader $subjectReader
     * @param Config $config
     * @param JwtParser $jwtParser
     */
    public function __construct(
        SubjectReader $subjectReader,
        Config $config,
        JwtParser $jwtParser
    ) {
        $this->subjectReader = $subjectReader;
        $this->config = $config;
        $this->jwtParser = $jwtParser;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        if ($this->config->isActive() === false) {
            return [];
        }

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $data = [];

        if ($payment instanceof Payment) {
            $cardinalJwt = (string)$payment->getAdditionalInformation('cardinalJWT');
            $jwtPayload = $this->jwtParser->execute($cardinalJwt);
            $eciFlag = $jwtPayload['Payload']['Payment']['ExtendedData']['ECIFlag'] ?? '';
            $cavv = $jwtPayload['Payload']['Payment']['ExtendedData']['CAVV'] ?? '';
            $data = [
                'transactionRequest' => [
                    'cardholderAuthentication' => [
                        'authenticationIndicator' => $eciFlag,
                        'cardholderAuthenticationValue' => $cavv
                    ],
                ]
            ];
        }

        return $data;
    }
}
