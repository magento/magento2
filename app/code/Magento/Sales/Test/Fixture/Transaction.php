<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Sales\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\TestFramework\Fixture\DataFixtureInterface;

/**
 * Data fixture for creating a transaction.
 *
 * Usage examples:
 *
 * 1. Create a transaction with default values:
 * <pre>
 *  #[
 *      DataFixture(TransactionFixture::class, ['order_id' => '$order.id$'], 'transaction')
 *  ]
 * </pre>
 * 2. Create a transaction with parent transaction:
 * <pre>
 *  #[
 *      DataFixture(TransactionFixture::class, ['order_id' => '$order.id$'], 'transaction1')
 *      DataFixture(
 *          TransactionFixture::class,
 *          [
 *              'order_id' => '$order.id$',
 *              'parent_txn_id' => '$transaction1.txn_id$',
 *              'txn_type' => TransactionInterface::TYPE_CAPTURE,
 *              'additional_info' => ['info_key' => 'info_value']
 *          ],
 *          'transaction2'
 *     )
 *  ]
 * </pre>
 */
class Transaction implements DataFixtureInterface
{
    private const DEFAULT_DATA = [
        'order_id' => null,
        'payment_id' => null,
        'txn_id' => 'txn%uniqid%',
        'parent_txn_id' => null,
        'is_closed' => false,
        'additional_info' => [],
        'txn_type' => TransactionInterface::TYPE_AUTH
    ];
    
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ProcessorInterface $dataProcessor
    ) {
    }

    /**
     * @inheritDoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $data = $this->dataProcessor->process($this, array_merge(self::DEFAULT_DATA, $data));
        $order = $this->orderRepository->get($data['order_id']);
        $payment = $data['payment_id'] ? $order->getPaymentById($data['payment_id']) : $order->getPayment();
        $payment->setTransactionId($data['txn_id']);
        $payment->setParentTransactionId($data['parent_txn_id']);
        $payment->setIsTransactionClosed($data['is_closed']);
        foreach ($data['additional_info'] as $key => $value) {
            $payment->setTransactionAdditionalInfo($key, $value);
        }
        $transaction = $payment->addTransaction($data['txn_type']);
        $this->orderRepository->save($order);
        return $transaction;
    }
}
