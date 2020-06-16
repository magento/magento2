<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/two_customers.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple.php');

$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$product = $productRepository->get('simple');
$firstCustomerIdFromFixture = 1;
$wishlistForFirstCustomer = $objectManager->create(
    \Magento\Wishlist\Model\Wishlist::class
);
$wishlistForFirstCustomer->loadByCustomerId($firstCustomerIdFromFixture, true);
$item = $wishlistForFirstCustomer->addNewItem($product, new \Magento\Framework\DataObject([]));
$wishlistForFirstCustomer->save();

$secondCustomerIdFromFixture = 2;
$wishlistForSecondCustomer = $objectManager->create(
    \Magento\Wishlist\Model\Wishlist::class
);
$wishlistForSecondCustomer->loadByCustomerId($secondCustomerIdFromFixture, true);
$item = $wishlistForSecondCustomer->addNewItem($product, new \Magento\Framework\DataObject([]));
$wishlistForSecondCustomer->save();

/**
 * We need to remember that automatic reindexation is not working properly in integration tests
 * Reindexation is sitting on top of afterCommit callbacks:
 * \Magento\Catalog\Model\Product::priceReindexCallback
 *
 * However, callbacks are applied only when transaction_level = 0 (when transaction is commited), however
 * integration tests are not committing transactions, so we need to reindex data manually in order to reuse it in tests
 */
/** @var \Magento\Indexer\Model\Indexer $indexer */
$indexer = Bootstrap::getObjectManager()->create(\Magento\Indexer\Model\Indexer::class);
$indexer->load('catalog_product_price');
$indexer->reindexList([$product->getId()]);
