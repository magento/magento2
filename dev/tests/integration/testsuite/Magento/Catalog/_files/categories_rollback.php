<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get('Magento\Framework\Registry');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

foreach (range(1, 4, 1) as $productId) {
    /** @var $product \Magento\Catalog\Model\Product */
    $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
    $product->load($productId);
    if ($product->getId()) {
        $product->delete();
    }
}
foreach (range(12, 3, -1) as $categoryId) {
    /** @var $category \Magento\Catalog\Model\Category */
    $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Category');
    $category->load($categoryId);
    if ($category->getId()) {
        $category->delete();
    }
}
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
