<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Products generation to test base data
 */

\Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea('adminhtml');

$testCases = include __DIR__ . '/_algorithm_base_data.php';

/** @var $installer \Magento\Catalog\Setup\CategorySetup */
$installer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Catalog\Setup\CategorySetup::class
);
/**
 * After installation system has two categories: root one with ID:1 and Default category with ID:2
 */
/** @var $category \Magento\Catalog\Model\Category */
$category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Category::class);
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
    $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
        \Magento\Catalog\Model\Category::class
    );
    $position = $index + 1;
    $categoryId = $index + 4;
    $category->load($categoryId);
    if ($category->getId()) {
        $category->delete();
    }

    foreach ($testCase[0] as $price) {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $productId = $lastProductId + 1;
        $product->load($productId);
        if ($product->getId()) {
            $product->delete();
        }
        ++$lastProductId;
    }
}
