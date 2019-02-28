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

/**
 * Adds the reference transaction to the request
 */
class TransactionDetailsDataBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $data = [];

        if (!empty($buildSubject['transactionId'])) {
            $data = [
                'transId' => $buildSubject['transactionId']
            ];
        } else {
            $paymentDO = $this->subjectReader->readPayment($buildSubject);
            $payment = $paymentDO->getPayment();

            if ($payment instanceof Payment) {
                $authorizationTransaction = $payment->getAuthorizationTransaction();

                if (empty($authorizationTransaction)) {
                    $transactionId = $payment->getLastTransId();
                } else {
                    $transactionId = $authorizationTransaction->getParentTxnId();

                    if (empty($transactionId)) {
                        $transactionId = $authorizationTransaction->getTxnId();
                    }
                }

                $data = [
                    'transId' => $transactionId
                ];
            }
        }

        return $data;
    }
}
