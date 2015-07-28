<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Payment\Transaction;


use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\AbstractModel;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;

class Manager implements ManagerInterface
{
    /**
     * @var \Magento\Sales\Api\TransactionRepositoryInterface
     */
    protected $transactionRepository;


    public function __construct(\Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Create transaction,
     * prepare its insertion into hierarchy and add its information to payment and comments
     *
     * To add transactions and related information,
     * the following information should be set to payment before processing:
     * - transaction_id
     * - is_transaction_closed (optional) - whether transaction should be closed or open (closed by default)
     * - parent_transaction_id (optional)
     * - should_close_parent_transaction (optional) - whether to close parent transaction (closed by default)
     *
     * If the sales document is specified, it will be linked to the transaction as related for future usage.
     * Currently transaction ID is set into the sales object
     * This method writes the added transaction ID into last_trans_id field of the payment object
     *
     * To make sure transaction object won't cause trouble before saving, use $failsafe = true
     * {@inheritdoc}
     */
    public function addTransaction(
        OrderPaymentInterface $payment,
        $type,
        $salesDocument = null,
        $failSafe = false,
        $message = false
    )
    {
        if ($payment->getSkipTransactionCreation()) {
            $payment->unsTransactionId();
            return null;
        }

        // look for set transaction ids
        $transactionId = $payment->getTransactionId();
        if (null !== $transactionId) {
            // set transaction parameters
            $transaction = false;
            if ($payment->getOrder()->getId()) {
                $transaction = $this->transactionRepository->getByTxnId(
                    $transactionId,
                    $payment->getId(),
                    $payment->getOrder()->getId()
                );
            }
            if (!$transaction) {
                $transaction = $this->transactionRepository->create()->setTxnId($transactionId);
            }
            $transaction->setOrderPaymentObject($payment)->setTxnType($type)->isFailsafe($failSafe);

            if ($payment->hasIsTransactionClosed()) {
                $transaction->setIsClosed((int)$payment->getIsTransactionClosed());
            }

            $this->setAdditionalInformationForTransaction($payment, $transaction);
            $this->linkSalesEntitiesWithTransaction($payment, $salesDocument, $transaction);
            // link with parent transaction
            $parentTransactionId = $payment->getParentTransactionId();

            if ($parentTransactionId) {
                $transaction->setParentTxnId($parentTransactionId);
                if ($payment->getShouldCloseParentTransaction()) {
                    $parentTransaction = $this->transactionRepository->getByTxnId(
                        $parentTransactionId,
                        $payment->getId(),
                        $payment->getOrder()->getId()
                    );
                    if ($parentTransaction) {
                        if (!$parentTransaction->getIsClosed()) {
                            $parentTransaction->isFailsafe($failSafe)->close(false);
                        }
                        $payment->getOrder()->addRelatedObject($parentTransaction);
                    }
                }
            }
            return $transaction;
        }

        return null;
    }

    /**
     * Sets transaction id to sales entities
     *
     * @param OrderPaymentInterface $payment
     * @param AbstractModel $salesDocument
     * @param TransactionInterface $transaction
     */
    protected function linkSalesEntitiesWithTransaction(
        OrderPaymentInterface $payment,
        $salesDocument,
        TransactionInterface $transaction
    ) {
        $payment->setLastTransId($transaction->getTxnId());
        $payment->setCreatedTransaction($transaction);
        $payment->getOrder()->addRelatedObject($transaction);
        if ($salesDocument && $salesDocument instanceof AbstractModel) {
            $salesDocument->setTransactionId($transaction->getTxnId());
        }
    }

    /**
     * Sets AdditionalInformation
     *
     * @param OrderPaymentInterface $payment
     * @param $transaction
     */
    protected function setAdditionalInformationForTransaction(OrderPaymentInterface $payment, $transaction)
    {
        if ($payment->getTransactionAdditionalInfo()) {
            foreach ($payment->getTransactionAdditionalInfo() as $key => $value) {
                $transaction->setAdditionalInformation($key, $value);
            }
            $payment->resetTransactionAdditionalInfo();
        }
    }
}