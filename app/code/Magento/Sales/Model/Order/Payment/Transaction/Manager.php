<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Payment\Transaction;

use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;

class Manager implements ManagerInterface
{
    /**
     * @var \Magento\Sales\Api\TransactionRepositoryInterface
     */
    protected $transactionRepository;

    /**
     * @param \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository
     */
    public function __construct(\Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationTransaction($parentTransactionId, $paymentId, $orderId)
    {
        $transaction = false;
        if ($parentTransactionId) {
            $transaction = $this->transactionRepository->getByTxnId(
                $parentTransactionId,
                $paymentId,
                $orderId
            );
        }

        return $transaction ?: $this->transactionRepository->getByTxnType(
            Transaction::TYPE_AUTH,
            $paymentId,
            $orderId
        );
    }
}