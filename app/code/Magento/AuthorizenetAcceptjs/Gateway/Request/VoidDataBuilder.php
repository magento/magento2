<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\Response\PaymentResponseHandler;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Framework\Exception\InputException;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction\Repository as TransactionRepository;

/**
 * Adds the meta transaction information to the request
 */
class VoidDataBuilder implements BuilderInterface
{
    const REQUEST_TYPE_VOID = 'voidTransaction';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var TransactionRepository
     */
    private $transactionRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param SubjectReader $subjectReader
     * @param TransactionRepository $transactionRepository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        SubjectReader $subjectReader,
        TransactionRepository $transactionRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->subjectReader = $subjectReader;
        $this->transactionRepository = $transactionRepository;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $payment = $this->subjectReader->readPayment($buildSubject)->getPayment();
        $transactionData = [];

        if ($payment instanceof Payment) {
            $transactionData['transactionRequest'] = [
                'transactionType' => self::REQUEST_TYPE_VOID,
                'refTransId' => $this->getRealParentTransactionId($payment)
            ];
        }

        return $transactionData;
    }

    /**
     * Lookup the original authorize.net transaction id
     * @param $payment
     * @return mixed
     */
    private function getRealParentTransactionId(Payment $payment)
    {

        try {
            /** @var Payment\Transaction $transaction */
            $transaction = $this->transactionRepository->getByTransactionId(
                $payment->getParentTransactionId(),
                $payment->getId(),
                $payment->getOrder()->getId()
            );
        } catch (InputException $e) {
            $this->logger->critical($e);
        }

        return $transaction->getAdditionalInformation(PaymentResponseHandler::REAL_TRANSACTION_ID);
    }
}
