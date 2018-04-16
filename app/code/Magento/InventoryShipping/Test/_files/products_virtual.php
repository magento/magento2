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
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var ProductInterfaceFactory $productFactory */
$productFactory = $objectManager->get(ProductInterfaceFactory::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->cleanCache();

$stockData = [
    'VIRT-1' => [
        'qty' => 33,
        'is_in_stock' => true,
        'manage_stock' => true
    ],
    'VIRT-2' => [
        'qty' => 30,
        'is_in_stock' => true,
        'manage_stock' => true
    ],
    'VIRT-3' => [
        'qty' => 2,
        'is_in_stock' => true,
        'manage_stock' => true
    ],
    'VIRT-4' => [
        'qty' => 6,
        'is_in_stock' => false,
        'manage_stock' => true
    ]
];

for ($i = 1; $i <= 4; $i++) {
    $product = $productFactory->create();
    $product->setTypeId(Type::TYPE_VIRTUAL)
        ->setAttributeSetId(4)
        ->setName('Virtual Product ' . $i)
        ->setSku('VIRT-' . $i)
        ->setPrice(10)
        ->setStockData($stockData['VIRT-' . $i])
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
        ->addFilter(SourceItemInterface::SKU, ['VIRT-1', 'VIRT-2', 'VIRT-3', 'VIRT-4'], 'in')
        ->addFilter(SourceItemInterface::SOURCE_CODE, $defaultSourceProvider->getCode())
        ->create();
    $sourceItems = $sourceItemRepository->getList($searchCriteria)->getItems();
    if (count($sourceItems)) {
        $sourceItemsDelete->execute($sourceItems);
    }
}
