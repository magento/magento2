<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Products generation to test base data
 */

$testCases = include __DIR__ . '/_algorithm_base_data.php';

/** @var $installer \Magento\Catalog\Setup\CategorySetup */
$installer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Setup\CategorySetup');
/**
 * After installation system has two categories: root one with ID:1 and Default category with ID:2
 */
/** @var $category \Magento\Catalog\Model\Category */
$category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Category');
$category->isObjectNew(true);
$category->setId(
    3
)->setName(
    'Root Category'
)->setParentId(
    2
)->setPath(
    '1/2/3'
)->setLevel(
    2
)->setAvailableSortBy(
    'name'
)->setDefaultSortBy(
    'name'
)->setIsActive(
    true
)->setPosition(
    1
)->save();

$lastProductId = 0;
foreach ($testCases as $index => $testCase) {
    $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Category');
    $position = $index + 1;
    $categoryId = $index + 4;
    $category->isObjectNew(true);
    $category->setId(
        $categoryId
    )->setName(
        'Category ' . $position
    )->setParentId(
        3
    )->setPath(
        '1/2/3/' . $categoryId
    )->setLevel(
        3
    )->setAvailableSortBy(
        'name'
    )->setDefaultSortBy(
        'name'
    )->setIsActive(
        true
    )->setIsAnchor(
        true
    )->setPosition(
        $position
    )->setUrlKey(
        'category_' . $categoryId
    )->save();

    foreach ($testCase[0] as $price) {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $productId = $lastProductId + 1;
        $product->setTypeId(
            \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
        )->setId(
            $productId
        )->setAttributeSetId(
            $installer->getAttributeSetId('catalog_product', 'Default')
        )->setStoreId(
            1
        )->setWebsiteIds(
            [1]
        )->setName(
            'Simple Product ' . $productId
        )->setSku(
            'simple-' . $productId
        )->setPrice(
            $price
        )->setStockData(
            [
                'qty' => 100,
                'is_in_stock' => 1,
            ]
        )->setWeight(
            18
        )->setCategoryIds(
            [$categoryId]
        )->setVisibility(
            \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
        )->setStatus(
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
        )->save();
        ++$lastProductId;
    }
}
