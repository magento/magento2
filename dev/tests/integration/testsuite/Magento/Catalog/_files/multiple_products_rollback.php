<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Registry');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$productIds = range(10, 12, 1);
foreach ($productIds as $productId) {
    /** @var \Magento\Catalog\Model\Product $product */
    $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
    $product->load($productId);

    if ($product->getId()) {
        $product->delete();
    }
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
