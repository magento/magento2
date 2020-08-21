<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;

require __DIR__ . '/../../Checkout/_files/quote_with_address_saved.php';

$objectManager = Bootstrap::getObjectManager();
/** @var CartRepositoryInterface $quoteRepository */
$quoteRepository = $objectManager->get(CartRepositoryInterface::class);
/** @var CartManagementInterface $quoteManagement */
$quoteManagement = $objectManager->get(CartManagementInterface::class);
/** @var PaymentInterface $payment */
$payment = $objectManager->get(PaymentInterface::class);

$quote = $objectManager->get(GetQuoteByReservedOrderId::class)->execute('test_order_1');
$quote->getShippingAddress()->setShippingMethod('flatrate_flatrate');
$quote->getShippingAddress()->setCollectShippingRates(true);
$quote->getShippingAddress()->collectShippingRates();
$quoteRepository->save($quote);
$payment->setMethod('checkmo');
$quoteManagement->placeOrder($quote->getId(), $payment);
