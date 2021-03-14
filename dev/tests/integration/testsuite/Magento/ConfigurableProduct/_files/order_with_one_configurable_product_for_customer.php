<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\TestFramework\Helper\Bootstrap;

Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer.php');
Resolver::getInstance()->requireDataFixture('Magento/GraphQl/Quote/_files/customer/create_empty_cart.php');
Resolver::getInstance()->requireDataFixture('Magento/CatalogRule/_files/configurable_product.php');
Resolver::getInstance()->requireDataFixture('Magento/GraphQl/Quote/_files/add_configurable_product.php');
Resolver::getInstance()->requireDataFixture('Magento/GraphQl/Quote/_files/set_new_shipping_address.php');
Resolver::getInstance()->requireDataFixture('Magento/GraphQl/Quote/_files/set_new_billing_address.php');
Resolver::getInstance()->requireDataFixture('Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php');
Resolver::getInstance()->requireDataFixture('Magento/GraphQl/Quote/_files/set_checkmo_payment_method.php');

$objectManager = Bootstrap::getObjectManager();
$cartRepository = $objectManager->get(CartRepositoryInterface::class);
$getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
$quoteIdMaskFactory = $objectManager->get(QuoteIdMaskFactory::class);
$quoteManagement = $objectManager->get(QuoteManagement::class);
$orderRepository = $objectManager->get(OrderRepositoryInterface::class);

$quoteMask = $getMaskedQuoteIdByReservedOrderId->execute('test_quote');
$quoteId = $quoteIdMaskFactory->create()
    ->load($quoteMask, 'masked_id')
    ->getId();
$quote = $cartRepository->get($quoteId);

$order = $quoteManagement->submit($quote);
$order->setIncrementId('100000001');
$orderRepository->save($order);
