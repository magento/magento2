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
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Checkout/_files/quote_with_taxable_product_and_customer.php');

$objectManager = Bootstrap::getObjectManager();
/** @var CartRepositoryInterface $quoteRepository */
$quoteRepository = $objectManager->get(CartRepositoryInterface::class);
/** @var CartManagementInterface $quoteManagement */
$quoteManagement = $objectManager->get(CartManagementInterface::class);
/** @var PaymentInterface $payment */
$payment = $objectManager->get(PaymentInterface::class);
$payment->setMethod('checkmo');

$quote = $quoteRepository->getActiveForCustomer(1);
$quote->getShippingAddress()->setShippingMethod('flatrate_flatrate');
$quote->getShippingAddress()->setCollectShippingRates(true);
$quote->getShippingAddress()->collectShippingRates();
$quoteRepository->save($quote);
$quoteManagement->placeOrder($quote->getId(), $payment);
