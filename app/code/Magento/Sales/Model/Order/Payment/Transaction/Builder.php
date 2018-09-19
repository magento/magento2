<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Payment\Transaction;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\AbstractModel;
use Magento\Sales\Model\Order\Payment;

/**
 * Class Builder build transaction
 */
class Builder implements BuilderInterface
{
    /**
     * @var OrderPaymentInterface
     */
    protected $payment;

    /**
     * @var OrderInterface
     */
    protected $order;

    /**
     * @var AbstractModel
     */
    protected $document;

    /**
     * @var bool
     */
    protected $failSafe = false;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var string
     */
    protected $transactionId;

    /**
     * @var array
     */
    protected $transactionAdditionalInfo = [];

    /**
     * @var TransactionRepositoryInterface
     */
    protected $transactionRepository;

    /**
     * @param TransactionRepositoryInterface $transactionRepository
     */
    public function __construct(TransactionRepositoryInterface $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function setPayment(OrderPaymentInterface $payment)
    {
        $this->payment = $payment;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setOrder(OrderInterface $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSalesDocument(\Magento\Sales\Model\AbstractModel $document)
    {
        $this->document = $document;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setFailSafe($failSafe)
    {
        $this->failSafe = $failSafe;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAdditionalInformation(array $value)
    {
        $this->transactionAdditionalInfo = $value;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addAdditionalInformation($key, $value)
    {
        $this->transactionAdditionalInfo[$key] = $value;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        unset($this->payment);
        unset($this->document);
        unset($this->order);
        unset($this->message);
        unset($this->transactionId);
        $this->failSafe = false;
        $this->transactionAdditionalInfo = [];
        return $this;
    }

    /**
     * Checks if payment was set
     *
     * @return bool
     */
    protected function isPaymentExists()
    {
        if ($this->payment) {
            if ($this->payment->getSkipTransactionCreation()) {
                $this->payment->unsTransactionId();
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function build($type)
    {
        if ($this->isPaymentExists() && $this->transactionId !== null) {
            $transaction = $this->transactionRepository->getByTransactionId(
                $this->transactionId,
                $this->payment->getId(),
                $this->order->getId()
            );
            if (!$transaction) {
                $transaction = $this->transactionRepository->create()->setTxnId($this->transactionId);
            }
            $transaction->setPaymentId($this->payment->getId())
                ->setPayment($this->payment)
                ->setOrderId($this->order->getId())
                ->setOrder($this->order)
                ->setTxnType($type)
                ->isFailsafe($this->failSafe);

            if ($this->payment->hasIsTransactionClosed()) {
                $transaction->setIsClosed((int)$this->payment->getIsTransactionClosed());
            }
            if ($this->transactionAdditionalInfo) {
                foreach ($this->transactionAdditionalInfo as $key => $value) {
                    $transaction->setAdditionalInformation($key, $value);
                }
            }
            $this->transactionAdditionalInfo = [];

            $this->payment->setLastTransId($transaction->getTxnId());
            $this->payment->setCreatedTransaction($transaction);
            $this->order->addRelatedObject($transaction);
            if ($this->document && $this->document instanceof AbstractModel) {
                $this->document->setTransactionId($transaction->getTxnId());
            }

            return $this->linkWithParentTransaction($transaction);
        }
        return null;
    }

    /**
     * Links transaction with parent transaction
     *
     * @param TransactionInterface $transaction
     * @return TransactionInterface
     */
    protected function linkWithParentTransaction(TransactionInterface $transaction)
    {
        $parentTransactionId = $this->payment->getParentTransactionId();

        if ($parentTransactionId) {
            $transaction->setParentTxnId($parentTransactionId);
            if ($this->payment->getShouldCloseParentTransaction()) {
                $parentTransaction = $this->transactionRepository->getByTransactionId(
                    $parentTransactionId,
                    $this->payment->getid(),
                    $this->order->getId()
                );
                if ($parentTransaction) {
                    if (!$parentTransaction->getIsClosed()) {
                        $parentTransaction->isFailsafe($this->failSafe)->close(false);
                    }
                    $this->order->addRelatedObject($parentTransaction);
                }
            }
        }
        return $transaction;
    }
}
