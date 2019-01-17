<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order\Payment;
use Psr\Log\LoggerInterface;

/**
 * Adds the meta transaction information to the request
 */
class VoidDataBuilder implements BuilderInterface
{
    private const REQUEST_TYPE_VOID = 'voidTransaction';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param SubjectReader $subjectReader
     * @param LoggerInterface $logger
     */
    public function __construct(
        SubjectReader $subjectReader,
        LoggerInterface $logger
    ) {
        $this->subjectReader = $subjectReader;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $payment = $this->subjectReader->readPayment($buildSubject)->getPayment();
        $transactionData = [];

        if ($payment instanceof Payment) {
            $authorizationTransaction = $payment->getAuthorizationTransaction();
            $refId = $authorizationTransaction->getAdditionalInformation('real_transaction_id');

            $transactionData['transactionRequest'] = [
                'transactionType' => self::REQUEST_TYPE_VOID,
                'refTransId' => $refId
            ];
        }

        return $transactionData;
    }
}
