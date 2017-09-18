<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Paypal\Model\Config;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objManager = Bootstrap::getObjectManager();

/** @var StoreInterface $store */
$store = $objManager->get(StoreManagerInterface::class)
    ->getStore();

/** @var CartInterface $quote */
$quote = $objManager->create(CartInterface::class);
$quote->setReservedOrderId('000000045')
    ->setStoreId($store->getId());

/** @var CartRepositoryInterface $quoteRepository */
$quoteRepository = $objManager->get(CartRepositoryInterface::class);
$quoteRepository->save($quote);

/** @var Payment $payment */
$payment = $objManager->create(Payment::class);
$payment->setMethod(Config::METHOD_PAYFLOWLINK)
    ->setBaseAmountAuthorized(100)
    ->setAdditionalInformation([
        'secure_silent_post_hash' => 'cf7i85d01ed7c92223031afb4rdl2f1f'
    ]);


/** @var OrderInterface $order */
$order = $objManager->create(OrderInterface::class);
$order->setIncrementId('000000045')
    ->setBaseGrandTotal(100)
    ->setQuoteId($quote->getId())
    ->setStoreId($store->getId())
    ->setState(Order::STATE_PENDING_PAYMENT)
    ->setPayment($payment);

/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objManager->get(OrderRepositoryInterface::class);
$orderRepository->save($order);
