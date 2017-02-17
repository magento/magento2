<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Payment;

/**
 * Tests transaction model:
 *
 * @see \Magento\Sales\Model\Order\Payment\Transaction
 * @magentoDataFixture Magento/Sales/_files/transactions.php
 */
class TransactionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadByTxnId()
    {
        $order = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId('100000001');

        /**
         * @var $repository \Magento\Sales\Model\Order\Payment\Transaction\Repository
         */
        $repository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Sales\Model\Order\Payment\Transaction\Repository::class
        );
        /**
         * @var $model \Magento\Sales\Model\Order\Payment\Transaction
         */
        $model = $repository->getByTransactionId(
            'invalid_transaction_id',
            $order->getPayment()->getId(),
            $order->getId()
        );

        $this->assertFalse($model);

        $model = $repository->getByTransactionId('trx1', $order->getPayment()->getId(), $order->getId());
        $this->assertNotFalse($model->getId());
    }
}
