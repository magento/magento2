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
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.4 Authorize.net payment method core integration is deprecated in favor of
 * official payment integration available on the marketplace
 */
class RefundReferenceTransactionDataBuilder implements BuilderInterface
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
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $data = [];

        if ($payment instanceof Payment) {
            $transactionId = $payment->getAuthorizationTransaction()->getParentTxnId();
            $data = [
                'transactionRequest' => [
                    'refTransId' => $transactionId
                ]
            ];
        }

        return $data;
    }
}
