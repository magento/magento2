<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
$products = [
    [
        'name' => 'index enabled',
        'sku' => 'index_enabled',
        'status' => Status::STATUS_ENABLED,
        'visibility' => Visibility::VISIBILITY_BOTH,
    ],
    [
        'name' => 'index disabled',
        'sku' => 'index_disabled',
        'status' => Status::STATUS_DISABLED,
        'visibility' => Visibility::VISIBILITY_BOTH,
    ],
    [
        'name' => 'index visible search',
        'sku' => 'index_visible_search',
        'status' => Status::STATUS_ENABLED,
        'visibility' => Visibility::VISIBILITY_IN_SEARCH,
    ],
    [
        'name' => 'index visible category',
        'sku' => 'index_visible_category',
        'status' => Status::STATUS_ENABLED,
        'visibility' => Visibility::VISIBILITY_IN_CATALOG,
    ],
    [
        'name' => 'index visible both',
        'sku' => 'index_visible_both',
        'status' => Status::STATUS_ENABLED,
        'visibility' => Visibility::VISIBILITY_BOTH,
    ],
    [

        'name' => 'index not visible',
        'sku' => 'index_not_visible',
        'status' => Status::STATUS_ENABLED,
        'visibility' => Visibility::VISIBILITY_NOT_VISIBLE,

    ]
];

/** @var $productFactory ProductInterfaceFactory */
$productFactory = Bootstrap::getObjectManager()->create(ProductInterfaceFactory::class);
foreach ($products as $data) {
    /** @var ProductInterface $product */
    $product = $productFactory->create();
    $product
        ->setTypeId(Type::TYPE_SIMPLE)
        ->setAttributeSetId(4)
        ->setName($data['name'])
        ->setSku($data['sku'])
        ->setPrice(10)
        ->setVisibility($data['visibility'])
        ->setStatus($data['status']);
    $product = $productRepository->save($product);

    /** @var StockItemInterface $stockItem */
    $stockItem = $product->getExtensionAttributes()->getStockItem();
    $stockItem->setUseConfigManageStock(0);
    $productRepository->save($product);
}
