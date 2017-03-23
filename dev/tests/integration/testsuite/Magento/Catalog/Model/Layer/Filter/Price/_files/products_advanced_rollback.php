<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$prices = [5, 10, 15, 20, 50, 100, 150];

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var $product \Magento\Catalog\Model\Product */
$lastProductId = 0;
foreach ($prices as $price) {
    /** @var \Magento\Catalog\Model\Product $product */
    $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
        \Magento\Catalog\Model\Product::class
    );
    $productId = $lastProductId + 1;
    $product->load($productId);

    if ($product->getId()) {
        $product->delete();
    }

    $lastProductId++;
}

/** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
$collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\ResourceModel\Category\Collection::class);
$collection
    ->addAttributeToFilter('level', 2)
    ->load()
    ->delete();

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
