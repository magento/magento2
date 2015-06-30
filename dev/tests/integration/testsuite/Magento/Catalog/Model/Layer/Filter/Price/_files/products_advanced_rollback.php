<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$prices = [5, 10, 15, 20, 50, 100, 150];

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Registry');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var $product \Magento\Catalog\Model\Product */
$lastProductId = 0;
foreach ($prices as $price) {
    /** @var \Magento\Catalog\Model\Product $product */
    $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
    $productId = $lastProductId + 1;
    $product->load($productId);

    if ($product->getId()) {
        $product->delete();
    }

    $lastProductId++;
}

$categoryIds = [3, 4];
foreach ($categoryIds as $categoryId) {
    /** @var \Magento\Catalog\Model\Category $category */
    $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Category');
    $category->load($categoryId);

    if ($category->getId()) {
        $category->delete();
    }
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
