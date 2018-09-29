<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Module\Manager;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceItemsDeleteInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var ProductInterfaceFactory $productFactory */
$productFactory = $objectManager->get(ProductInterfaceFactory::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->cleanCache();

$stockData = [
    'SKU-1' => [
        'qty' => 8.5,
        'is_in_stock' => true,
        'manage_stock' => true,
        'is_qty_decimal' => true
    ],
    'SKU-2' => [
        'qty' => 5,
        'is_in_stock' => true,
        'manage_stock' => true,
        'is_qty_decimal' => false
    ],
    'SKU-3' => [
        'qty' => 0,
        'is_in_stock' => false,
        'manage_stock' => true
    ],
    'SKU-4' => [
        'use_config_manage_stock' => false
    ],
    'SKU-5' => [
        'qty' => 0,
        'is_in_stock' => false,
        'manage_stock' => true
    ],
];
$productsData = [
    1 => 'Orange',
    2 => 'Blue',
    3 => 'White',
    4 => 'Purple',
    5 => 'Black'
];

for ($i = 1; $i <= 5; $i++) {
    $product = $productFactory->create();
    $product->setTypeId(Type::TYPE_SIMPLE)
        ->setAttributeSetId(4)
        ->setName('Simple Product ' . $i . ' ' . $productsData[$i])
        ->setSku('SKU-' . $i)
        ->setPrice(10)
        ->setStockData($stockData['SKU-' . $i])
        ->setStatus(Status::STATUS_ENABLED);
    $productRepository->save($product);
}

/** @var Manager $moduleManager */
$moduleManager = Bootstrap::getObjectManager()->get(Manager::class);
// soft dependency in tests because we don't have possibility replace fixture from different modules
if ($moduleManager->isEnabled('Magento_InventoryCatalog')) {
    /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
    $searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
    /** @var DefaultSourceProviderInterface $defaultSourceProvider */
    $defaultSourceProvider = $objectManager->get(DefaultSourceProviderInterface::class);
    /** @var SourceItemRepositoryInterface $sourceItemRepository */
    $sourceItemRepository = $objectManager->get(SourceItemRepositoryInterface::class);
    /** @var SourceItemsDeleteInterface $sourceItemsDelete */
    $sourceItemsDelete = $objectManager->get(SourceItemsDeleteInterface::class);

    // Unassign created product from default Source
    $searchCriteria = $searchCriteriaBuilder
        ->addFilter(SourceItemInterface::SKU, ['SKU-1', 'SKU-2', 'SKU-3', 'SKU-4', 'SKU-5'], 'in')
        ->addFilter(SourceItemInterface::SOURCE_CODE, $defaultSourceProvider->getCode())
        ->create();
    $sourceItems = $sourceItemRepository->getList($searchCriteria)->getItems();
    if (count($sourceItems)) {
        $sourceItemsDelete->execute($sourceItems);
    }
}
