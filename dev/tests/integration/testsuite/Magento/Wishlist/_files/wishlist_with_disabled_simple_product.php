<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Wishlist/_files/wishlist.php');

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);

$productSku = 'simple';
$product = $productRepository->get($productSku);
$product->setStatus(ProductStatus::STATUS_DISABLED);
$productRepository->save($product);

/**
 * We need to remember that automatic reindexation is not working properly in integration tests
 * Reindexation is sitting on top of afterCommit callbacks:
 * \Magento\Catalog\Model\Product::priceReindexCallback
 *
 * However, callbacks are applied only when transaction_level = 0 (when transaction is commited), however
 * integration tests are not committing transactions, so we need to reindex data manually in order to reuse it in tests
 */
/** @var \Magento\Indexer\Model\Indexer $indexer */
$indexer = \Magento\TestFramework\Helper\Bootstrap
    ::getObjectManager()->create(\Magento\Indexer\Model\Indexer::class);
$indexer->load('catalog_product_price');
$indexer->reindexList([$product->getId()]);
