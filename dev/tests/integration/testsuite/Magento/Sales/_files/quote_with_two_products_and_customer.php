<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Checkout/_files/quote_with_address.php');

$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$quote = Bootstrap::getObjectManager()->create(Quote::class);
$quote->load('test_order_1', 'reserved_order_id');
$customDesignProduct = $productRepository->get('custom-design-simple-product');
/** @var Quote $quote */
$quote->addProduct(
    $customDesignProduct,
    1
);

$quote->getPayment()->setMethod('payflowpro');
$quote->setIsMultiShipping('0');
$quote->getShippingAddress()->setShippingMethod('freeshipping_freeshipping');
$quote->setReservedOrderId('test01');
$quote->collectTotals()
    ->save();

/** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
$quoteIdMask = Bootstrap::getObjectManager()
    ->create(\Magento\Quote\Model\QuoteIdMaskFactory::class)
    ->create();
$quoteIdMask->setQuoteId($quote->getId());
$quoteIdMask->setDataChanges(true);
$quoteIdMask->save();
