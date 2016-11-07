<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Sales\Model\Order;

require 'order.php';
/** @var Order $order */

$payments = [
    [
        'parent_id' => $order->getId(),
        'cc_exp_month' => '06',
        'cc_ss_start_year' => '2014',
        'method' => 'checkmo',
        'cc_last_4' => '123'
    ],
    [
        'parent_id' => $order->getId(),
        'cc_exp_month' => '07',
        'cc_ss_start_year' => '2014',
        'method' => 'checkmo',
        'cc_last_4' => '456'
    ],
    [
        'parent_id' => $order->getId(),
        'cc_exp_month' => '08',
        'cc_ss_start_year' => '2015',
        'method' => 'checkmo'
    ],
    [
        'parent_id' => $order->getId(),
        'cc_exp_month' => '09',
        'cc_ss_start_year' => '2016',
        'method' => 'paypal_express'
    ],
];

/** @var array $payments */
foreach ($payments as $paymentData) {
    /** @var $address \Magento\Sales\Model\Order\Payment */
    $payment = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
        \Magento\Sales\Model\Order\Payment::class
    );
    $payment
        ->setData($paymentData)
        ->save();
}
