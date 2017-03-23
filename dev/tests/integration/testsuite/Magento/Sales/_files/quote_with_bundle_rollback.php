<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var $quote \Magento\Quote\Model\Quote */
$quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Quote\Model\Quote::class);
$quote->load('test01', 'reserved_order_id');
if ($quote->getId()) {
    $quote->delete();
}

/** @var $product \Magento\Catalog\Model\Product */
$productIds = [1, 2, 3];
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
foreach ($productIds as $productId) {
    $product->load($productId);
    if ($product->getId()) {
        $product->delete();
    }
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
