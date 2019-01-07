<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Framework\DataObject;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Adds the meta transaction information to the request
 */
class CaptureDataBuilder implements BuilderInterface
{
    const REQUEST_TYPE_CAPTURE_ONLY = 'captureOnlyTransaction';

    const REQUEST_AUTH_AND_CAPTURE = 'authCaptureTransaction';

    const REQUEST_TYPE_PRIOR_AUTH_CAPTURE = 'priorAuthCaptureTransaction';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Magento\Sales\Api\TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @param SubjectReader $subjectReader
     * @param \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository
     */
    public function __construct(
        SubjectReader $subjectReader,
        \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository
    ) {
        $this->subjectReader = $subjectReader;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        /** @var DataObject|\Magento\Payment\Model\InfoInterface $payment */
        $payment = $this->subjectReader->readPayment($buildSubject)->getPayment();
        $transactionData = [
            'transactionRequest' => []
        ];

        if ($payment->getData(Payment::PARENT_TXN_ID)) {
            $transactionData['transactionRequest']['transactionType'] = self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE;
            $transactionData['transactionRequest']['refTransId'] = $this->getRealParentTransactionId($payment);
        } else {
            $transactionData['transactionRequest']['transactionType'] = self::REQUEST_AUTH_AND_CAPTURE;
        }

        return $transactionData;
    }

    /**
     * Retrieves the previously Auth'd transaction id to be captured
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return string
     */
    private function getRealParentTransactionId($payment): string
    {
        $transaction = $this->transactionRepository->getByTransactionId(
            $payment->getData(Payment::PARENT_TXN_ID),
            $payment->getId(),
            $payment->getOrder()->getId()
        );

        return $transaction->getAdditionalInformation('real_transaction_id');
    }
}
