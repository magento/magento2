<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);

$attributeSetMuffins = $objectManager->create(\Magento\Eav\Model\Entity\Attribute\Set::class)
    ->load('Super Powerful Muffins', 'attribute_set_name');
$attributeSetRangers = $objectManager->create(\Magento\Eav\Model\Entity\Attribute\Set::class)
    ->load('Banana Rangers', 'attribute_set_name');
$attributeSetGuardians = $objectManager->create(\Magento\Eav\Model\Entity\Attribute\Set::class)
    ->load('Guardians of the Refrigerator', 'attribute_set_name');

$productsData = [
    [
        'type-id' => 'simple',
        'attribute-set-id' => $attributeSetMuffins->getId(),
        'website-ids' => [1],
        'name' => 'Simple Product 1 (Sale)',
        'sku' => 'simple-product-1',
        'price' => 10,
        'visibility' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
        'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
        'stock-data' => ['use_config_manage_stock' => 1, 'qty' => 77, 'is_in_stock' => 1],
        'qty' => 42,
        'categories' => ['Category 1', 'Category 2'],
    ],
    [
        'type-id' => 'simple',
        'attribute-set-id' => $attributeSetRangers->getId(),
        'website-ids' => [1],
        'name' => 'Simple Product 2',
        'sku' => 'simple-product-2',
        'price' => 10,
        'visibility' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
        'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
        'stock-data' => ['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1],
        'qty' => 42,
        'categories' => ['Category 1', 'Category 2'],
    ],
    [
        'type-id' => 'simple',
        'attribute-set-id' => $attributeSetGuardians->getId(),
        'website-ids' => [1],
        'name' => 'Simple Product 3',
        'sku' => 'simple-product-3',
        'price' => 10,
        'visibility' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
        'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
        'stock-data' => ['use_config_manage_stock' => 1, 'qty' => 100, 'is_in_stock' => 1],
        'qty' => 42,
        'categories' => ['Category 1.1', 'Category 3'],
    ],
    [
        'type-id' => 'simple',
        'attribute-set-id' => $attributeSetMuffins->getId(),
        'website-ids' => [1],
        'name' => 'Simple Product 4',
        'sku' => 'simple-product-4',
        'price' => 10,
        'visibility' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
        'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
        'stock-data' => ['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1],
        'qty' => 42,
        'categories' => ['Category 1.1', 'Category 3'],
    ],
    [
        'type-id' => 'simple',
        'attribute-set-id' => $attributeSetRangers->getId(),
        'website-ids' => [1],
        'name' => 'Simple Product 5',
        'sku' => 'simple-product-5',
        'price' => 10,
        'visibility' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
        'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
        'stock-data' => ['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1],
        'qty' => 42,
        'categories' => ['Category 1.2', 'Category 1.1.1'],
    ],
    [
        'type-id' => 'simple',
        'attribute-set-id' => $attributeSetGuardians->getId(),
        'website-ids' => [1],
        'name' => 'Simple Product 6',
        'sku' => 'simple-product-6',
        'price' => 10,
        'visibility' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
        'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
        'stock-data' => ['use_config_manage_stock' => 1, 'qty' => 97, 'is_in_stock' => 1],
        'qty' => 42,
        'categories' => ['Category 1.2', 'Category 1.1.1'],
    ],
    [
        'type-id' => 'simple',
        'attribute-set-id' => $attributeSetMuffins->getId(),
        'website-ids' => [1],
        'name' => 'Simple Product 7',
        'sku' => 'simple-product-7',
        'price' => 10,
        'visibility' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
        'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
        'stock-data' => ['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1],
        'qty' => 42,
        'categories' => ['Category 3', 'Category 2'],
    ],
    [
        'type-id' => 'simple',
        'attribute-set-id' => $attributeSetRangers->getId(),
        'website-ids' => [1],
        'name' => 'Simple Product 8',
        'sku' => 'simple-product-8',
        'price' => 10,
        'visibility' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
        'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
        'stock-data' => ['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1],
        'qty' => 42,
        'categories' => ['Category 3', 'Category 2'],
    ],
    [
        'type-id' => 'simple',
        'attribute-set-id' => $attributeSetGuardians->getId(),
        'website-ids' => [1],
        'name' => 'Simple Product 9 (Sale)',
        'sku' => 'simple-product-9',
        'price' => 10,
        'visibility' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
        'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
        'stock-data' => ['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1],
        'qty' => 42,
        'categories' => ['Category 1.1', 'Category 1.2'],
    ],
    [
        'type-id' => 'simple',
        'attribute-set-id' => $attributeSetMuffins->getId(),
        'website-ids' => [1],
        'name' => 'Simple Product 10',
        'sku' => 'simple-product-10',
        'price' => 10,
        'visibility' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
        'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
        'stock-data' => ['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1],
        'qty' => 42,
        'categories' => ['Category 1.1', 'Category 1.2'],
    ],
    [
        'type-id' => 'simple',
        'attribute-set-id' => $attributeSetRangers->getId(),
        'website-ids' => [1],
        'name' => 'Simple Product 11',
        'sku' => 'simple-product-11',
        'price' => 10,
        'visibility' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
        'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
        'stock-data' => ['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1],
        'qty' => 42,
        'categories' => ['Category 1.1.1'],
    ],
    [
        'type-id' => 'simple',
        'attribute-set-id' => $attributeSetGuardians->getId(),
        'website-ids' => [1],
        'name' => 'Simple Product 12 (Sale)',
        'sku' => 'simple-product-12',
        'price' => 10,
        'visibility' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
        'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
        'stock-data' => ['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1],
        'qty' => 42,
        'categories' => ['Category 1.1.1'],
    ],
];

foreach ($productsData as $productData) {
    $categoriesIds = [];

    foreach ($productData['categories'] as $category) {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection */
        $categoryCollection = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Category\Collection::class);
        $categoryCollection->addAttributeToFilter('name', $category);

        array_push($categoriesIds, ...$categoryCollection->getAllIds());
    }

    /** @var $product \Magento\Catalog\Model\Product */
    $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
        ->create(\Magento\Catalog\Model\Product::class);

    $product
        ->setTypeId($productData['type-id'])
        ->setAttributeSetId($productData['attribute-set-id'])
        ->setWebsiteIds($productData['website-ids'])
        ->setName($productData['name'])
        ->setSku($productData['sku'])
        ->setPrice($productData['price'])
        ->setVisibility($productData['visibility'])
        ->setStatus($productData['status'])
        ->setStockData($productData['stock-data'])
        ->setQty($productData['qty'])
        ->setCategoryIds($categoriesIds);

    $productRepository->save($product);
}
