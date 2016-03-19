<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Framework\ObjectManagerInterface $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get('Magento\Framework\Registry');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$productIds = [1, 2];
foreach ($productIds as $productId) {
    /** @var $product \Magento\Catalog\Model\Product */
    $product = $objectManager->create('Magento\Catalog\Model\Product');
    $product->load($productId);
    if ($product->getId()) {
        $product->delete();
    }
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
