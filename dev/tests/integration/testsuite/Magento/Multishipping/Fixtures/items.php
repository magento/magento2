<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var QuoteFactory $quoteFactory */
$quoteFactory = Bootstrap::getObjectManager()->get(QuoteFactory::class);
/** @var QuoteResource $quoteResource */
$quoteResource = Bootstrap::getObjectManager()->get(QuoteResource::class);
$quote = $quoteFactory->create();
$quoteResource->load($quote, 'multishipping_quote_id', 'reserved_order_id');
$productList = [];
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
Resolver::getInstance()->requireDataFixture('Magento/Multishipping/Fixtures/simple_product_10.php');
$productList[] = $productRepository->get('simple_10');
Resolver::getInstance()->requireDataFixture('Magento/Multishipping/Fixtures/simple_product_20.php');
$productList[] = $productRepository->get('simple_20');
Resolver::getInstance()->requireDataFixture('Magento/Multishipping/Fixtures/virtual_product_5.php');
$productList[] = $productRepository->get('virtual_5');

foreach ($productList as $product) {
    /** @var Item $item */
    $item = $objectManager->create(Item::class);
    $item->setProduct($product)
        ->setPrice($product->getPrice())
        ->setQty(1);
    $quote->addItem($item);
}
$quoteResource->save($quote);
