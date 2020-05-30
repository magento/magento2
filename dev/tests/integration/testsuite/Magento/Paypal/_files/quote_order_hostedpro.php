<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Sales\Model\Order\Payment;
use Magento\Paypal\Model\Config;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/GraphQl/Quote/_files/guest/create_empty_cart.php');
Resolver::getInstance()->requireDataFixture('Magento/PaypalGraphQl/_files/add_simple_product_payflowLink.php');
Resolver::getInstance()->requireDataFixture('Magento/GraphQl/Quote/_files/guest/set_guest_email.php');
Resolver::getInstance()->requireDataFixture('Magento/GraphQl/Quote/_files/set_new_shipping_address.php');
Resolver::getInstance()->requireDataFixture('Magento/GraphQl/Quote/_files/set_new_billing_address.php');
Resolver::getInstance()->requireDataFixture('Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php');

/** @var ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var QuoteFactory $quoteFactory */
$quoteFactory = Bootstrap::getObjectManager()->get(QuoteFactory::class);
$store = $objectManager->get(StoreManagerInterface::class)->getStore();

$quoteResource = Bootstrap::getObjectManager()->get(QuoteResource::class);
$quote = $quoteFactory->create();
$quoteResource->load($quote, 'test_quote', 'reserved_order_id');

/** @var \Magento\Sales\Model\Order\Payment $payment */
$payment = $objectManager->create(Payment::class);
$payment->setMethod(Config::METHOD_HOSTEDPRO)
    ->setBaseAmountAuthorized(30)
    ->setAdditionalInformation('secure_form_url', 'https://hostedpro.paypal.com');

/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->create(Order::class);
$order->setCustomerEmail('wpphs.co@co.com')
    ->setIncrementId('100000017')
    ->setQuoteId($quote->getId())
    ->setStoreId($store->getId())
    ->setState(Order::STATE_PENDING_PAYMENT)
    ->setStatus(Order::STATE_PENDING_PAYMENT)
    ->setCustomerIsGuest(true)
    ->setPayment($payment);

/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->get(OrderRepositoryInterface::class);
$orderRepository->save($order);
