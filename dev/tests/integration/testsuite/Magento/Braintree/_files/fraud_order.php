<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Braintree\Model\Ui\ConfigProvider;
use Magento\Config\Model\Config;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var Config $config */
$config = $objectManager->get(Config::class);
$config->setDataByPath('payment/' . ConfigProvider::CODE . '/active', 1);
$config->save();

/** @var Payment $payment */
$payment = $objectManager->create(Payment::class);
$payment->setMethod(ConfigProvider::CODE)
    ->setLastTransId('000001');

$amount = 100;

/** @var Order $order */
$order = $objectManager->create(Order::class);
$order->setIncrementId('100000002')
    ->setState(Order::STATE_PAYMENT_REVIEW)
    ->setStatus(Order::STATUS_FRAUD)
    ->setBaseGrandTotal($amount)
    ->setPayment($payment);

/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->get(OrderRepositoryInterface::class);
$orderRepository->save($order);
