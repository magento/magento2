<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

// phpcs:ignore Magento2.Security.IncludeFile
require __DIR__ . '/../../ConfigurableProduct/_files/configurable_products.php';

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Eav\Api\AttributeRepositoryInterface;

$eavConfig = Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class);

/** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
$attribute = $eavConfig->getAttribute('catalog_product', 'test_configurable');

$eavConfig->clear();

$attribute->setIsSearchable(1)
          ->setIsVisibleInAdvancedSearch(1)
         ->setIsFilterable(true)
         ->setIsFilterableInSearch(true)
    ->setIsVisibleOnFront(1);

/** @var AttributeRepositoryInterface $attributeRepository */
$attributeRepository = Bootstrap::getObjectManager()->create(AttributeRepositoryInterface::class);
$attributeRepository->save($attribute);

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$outOfStockChildProduct = $productRepository->get('simple_10');
$outOfStockChildProduct->setStockData(
    ['use_config_manage_stock' => 1,
        'qty' => 0,
        'is_qty_decimal' => 0,
        'is_in_stock' => 0]
);
$productRepository->save($outOfStockChildProduct);

/** @var \Magento\Indexer\Model\Indexer\Collection $indexerCollection */
$indexerCollection = Bootstrap::getObjectManager()->get(\Magento\Indexer\Model\Indexer\Collection::class);
$indexerCollection->load();
/** @var \Magento\Indexer\Model\Indexer $indexer */
foreach ($indexerCollection->getItems() as $indexer) {
    $indexer->reindexAll();
}
