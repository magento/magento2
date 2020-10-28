<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/multiple_products.php');

$objectManager = Bootstrap::getObjectManager();
/** @var \Magento\Quote\Api\CartRepositoryInterface $quoteRepository */
$quoteRepository = $objectManager->create(\Magento\Quote\Api\CartRepositoryInterface::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$product1 = $productRepository->get('simple1');
$product2 = $productRepository->get('simple2');
/** @var \Magento\Quote\Model\Quote $quote */
$quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
$quote->setStoreId(1)
    ->setIsActive(true)
    ->setIsMultiShipping(false)
    ->setReservedOrderId('test_order_with_multiple_products_without_address')
    ->setEmail('store@example.com')
    ->addProduct($product1->load($product1->getId()), 1);

$quote->collectTotals();
$quoteRepository->save($quote);

$quote->addProduct($product2->load($product2->getId()), 1);
$quote->collectTotals();
$quoteRepository->save($quote);

/** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
$quoteIdMask = $objectManager
    ->create(\Magento\Quote\Model\QuoteIdMaskFactory::class)
    ->create();
$quoteIdMask->setQuoteId($quote->getId());
$quoteIdMask->setDataChanges(true);

/** @var ProductRepositoryInterface $productRepository */
$quoteIdMaskResource = $objectManager->create(\Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask::class);
$quoteIdMaskResource->save($quoteIdMask);
