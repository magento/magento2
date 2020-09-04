<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

foreach (range(3, 7) as $categoryId) {
    $category = $objectManager->create(\Magento\Catalog\Model\Category::class);
    $category->isObjectNew(true);
    $category->setId($categoryId)
        ->setName('Category ' . $categoryId)
        ->setParentId(2)
        ->setPath('1/2/' . $categoryId)
        ->setLevel(2)
        ->setIsActive(true)
        ->save();
}

foreach (range(1, 3) as $productId) {
    $product = $objectManager->create(\Magento\Catalog\Model\Product::class);
    $product->isObjectNew(true);
    $product->setId($productId)
        ->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
        ->setAttributeSetId(4)
        ->setStoreId(1)
        ->setWebsiteIds([1])
        ->setName('Simple Product ' . $productId)
        ->setSku('simple' . $productId)
        ->setPrice(10)
        ->setWeight(1)
        ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
        ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
        ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
        ->save();
}

$categoryRepository = $objectManager->create(\Magento\Catalog\Api\CategoryRepositoryInterface::class);
$categoryLinkRepository = $objectManager->create(
    \Magento\Catalog\Api\CategoryLinkRepositoryInterface::class,
    [
        'categoryRepository' => $categoryRepository,
    ]
);
$categoryLinkManagement = $objectManager->create(
    \Magento\Catalog\Api\CategoryLinkManagementInterface::class,
    [
        'categoryRepository' => $categoryRepository,
    ]
);
$reflectionClass = new \ReflectionClass(get_class($categoryLinkManagement));
$reflectionProperty = $reflectionClass->getProperty('categoryLinkRepository');
$reflectionProperty->setAccessible(true);
$reflectionProperty->setValue($categoryLinkManagement, $categoryLinkRepository);
$categoryLinkManagement->assignProductToCategories('simple1', [3]);
$categoryLinkManagement->assignProductToCategories('simple2', [3, 5, 6]);
