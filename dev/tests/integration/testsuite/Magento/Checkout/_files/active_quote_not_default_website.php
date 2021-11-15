<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/second_website_with_two_stores.php');

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$storeRepository = $objectManager->get(\Magento\Store\Api\StoreRepositoryInterface::class);
$store = $storeRepository->get('fixture_second_store');

$quote = $objectManager->create(\Magento\Quote\Model\Quote::class)
    ->setReservedOrderId('test_order_2')
    ->setStoreId($store->getId())
    ->setIsActive(true);
$quoteRepository = $objectManager->get(\Magento\Quote\Api\CartRepositoryInterface::class);
$quoteRepository->save($quote);

$quoteIdMask = $objectManager->create(\Magento\Quote\Model\QuoteIdMask::class)
    ->setQuoteId($quote->getId())
    ->setDataChanges(true);
$quoteIdMaskResource = $objectManager->get(\Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask::class);
$quoteIdMaskResource->save($quoteIdMask);
