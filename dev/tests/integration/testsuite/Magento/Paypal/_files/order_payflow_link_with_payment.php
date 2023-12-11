<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Paypal\Model\Config;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/GraphQl/Quote/_files/guest/create_empty_cart.php');
Resolver::getInstance()->requireDataFixture('Magento/PaypalGraphQl/_files/add_simple_product_payflowLink.php');
Resolver::getInstance()->requireDataFixture('Magento/GraphQl/Quote/_files/guest/set_guest_email.php');
Resolver::getInstance()->requireDataFixture('Magento/GraphQl/Quote/_files/set_new_shipping_address.php');
Resolver::getInstance()->requireDataFixture('Magento/GraphQl/Quote/_files/set_new_billing_address.php');
Resolver::getInstance()->requireDataFixture('Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php');

$objManager = Bootstrap::getObjectManager();

/** @var \Magento\Framework\UrlInterface $url */
$url = $objManager->get(\Magento\Framework\UrlInterface::class);
$baseUrl = $url->getBaseUrl();

/** @var StoreInterface $store */
$store = $objManager->get(StoreManagerInterface::class)
    ->getStore();

/** @var \Magento\Quote\Model\QuoteFactory $quoteFactory */
$quoteFactory = Bootstrap::getObjectManager()->get(\Magento\Quote\Model\QuoteFactory::class);
/** @var CartRepositoryInterface  $cartRepository */
$cartRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
/** @var QuoteResource $quoteResource */
$quoteResource = Bootstrap::getObjectManager()->get(QuoteResource::class);

$quote = $quoteFactory->create();
$quoteResource->load($quote, 'test_quote', 'reserved_order_id');
$quote->setStoreId($store->getId());
$cartRepository->save($quote);

/** @var Payment $payment */
$payment = $objManager->create(Payment::class);
$payment->setMethod(Config::METHOD_PAYFLOWLINK)
    ->setBaseAmountAuthorized(30)
    ->setAdditionalInformation(
        [
        'cancel_url'=> $baseUrl . 'paypal/payflow/cancelPayment',
        'return_url'=> $baseUrl . 'paypal/payflow/returnUrl',
        'secure_token_id' => 'mysecuretokenId',
        'secure_token' => 'mysecuretoken'
        ]
    );

/** @var OrderInterface $order */
$order = $objManager->create(OrderInterface::class);
$order->setIncrementId('test_quote')
    ->setBaseGrandTotal(30)
    ->setQuoteId($quote->getId())
    ->setStoreId($store->getId())
    ->setState(Order::STATE_PENDING_PAYMENT)
    ->setStatus(Order::STATE_PENDING_PAYMENT)
    ->setPayment($payment);

/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objManager->get(OrderRepositoryInterface::class);
$orderRepository->save($order);
