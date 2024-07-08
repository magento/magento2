<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/transactions_list_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/transactions_detailed.php');
$objectManager = Bootstrap::getObjectManager();
/** @var Order $order */
$order = $objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000006');
$payment = $order->getPayment();

$transactions = [
    [
        'transaction_id' => 'trx_auth1',
        'is_transaction_closed' => 1,
        'order_id' => $order->getId(),
        'payment_id' => $payment->getId(),
        'parent_transaction_id' => 'trx_auth1',
        'txn_id' => 'aaabbbccc',
    ],
    [
        'transaction_id' => 'trx_auth2',
        'is_transaction_closed' => 1,
        'parent_transaction_id' => 'trx_auth1',
        'order_id' => $order->getId(),
        'payment_id' => $payment->getId(),
        'txn_id' => '123456',
    ],
    [
        'transaction_id' => 'trx_auth3',
        'is_transaction_closed' => 1,
        'parent_transaction_id' => 'trx_auth1',
        'order_id' => $order->getId(),
        'payment_id' => $payment->getId(),
        'txn_id' => 'wooooh',
    ],
    [
        'transaction_id' => 'trx_auth4',
        'is_transaction_closed' => 1,
        'parent_transaction_id' => 'trx_auth2',
        'order_id' => $order->getId(),
        'payment_id' => $payment->getId(),
        'txn_id' => '--09--',
    ]
];

/** @var array $transactionData */
foreach ($transactions as $transactionData) {
    $payment->addData($transactionData);
    $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH);
}

$order->save();
