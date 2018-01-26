<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Framework\ObjectManagerInterface $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get('Magento\Framework\Registry');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$productSkuList = ['simple_product_1', 'simple_product_2'];
foreach ($productSkuList as $sku) {
    /** @var $product \Magento\Catalog\Model\Product */
    $product = $objectManager->create('Magento\Catalog\Model\Product');
    $product->loadByAttribute('sku', $sku);
    if ($product->getId()) {
        $product->delete();
    }
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
