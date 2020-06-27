<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\DataObject;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Wishlist\Model\Wishlist;

Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple.php');

$objectManager = Bootstrap::getObjectManager();
/** @var CustomerRegistry $customerRegistry */
$customerRegistry = Bootstrap::getObjectManager()->create(CustomerRegistry::class);
$customer = $customerRegistry->retrieve(1);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$product = $productRepository->get('simple');
$wishlist = Bootstrap::getObjectManager()->create(Wishlist::class);
$wishlist->loadByCustomerId($customer->getId(), true);
$item = $wishlist->addNewItem($product, new DataObject([]));
$wishlist->setSharingCode('fixture_unique_code')->save();

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
