<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Sales\Model\Order;

require 'transactions_list_rollback.php';
require 'transactions_detailed.php';

/** @var Order $order */
/** @var  Order\Payment $payment */

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
    $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);
}

$order->save();
