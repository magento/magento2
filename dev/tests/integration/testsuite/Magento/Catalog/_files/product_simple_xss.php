<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$objectManager->removeSharedInstance(\Magento\Catalog\Model\ProductRepository::class);
$objectManager->removeSharedInstance(\Magento\Catalog\Model\CategoryLinkRepository::class);

/** @var \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement = $objectManager->create(\Magento\Catalog\Api\CategoryLinkManagementInterface::class);

$product = $objectManager->create(\Magento\Catalog\Model\Product::class);

$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('<script>alert("xss");</script>')
    ->setSku('product-with-xss')
    ->setPrice(10)
    ->setDescription('Description with <b>html tag</b>')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->save();

$categoryLinkManagement->assignProductToCategories(
    $product->getSku(),
    [2]
);


/**
 * We need to remember that automatic reindexation is not working properly in integration tests
 * Reindexation is sitting on top of afterCommit callbacks:
 * \Magento\Catalog\Model\Product::priceReindexCallback
 *
 * However, callbacks are applied only when transaction_level = 0 (when transaction is commited), however
 * integration tests are not committing transactions, so we need to reindex data manually in order to reuse it in tests
 */
/** @var \Magento\Indexer\Model\Indexer $indexer */
$indexer = $objectManager->create(\Magento\Indexer\Model\Indexer::class);
$indexer->load('catalog_product_price');
$indexer->reindexRow($product->getId());
