<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create('Magento\Catalog\Model\Product');
$product->load('configurable', 'sku');
if ($product->getId()) {
    $product->delete();
}

/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create('Magento\Catalog\Model\Product');
$product->load('simple_100', 'sku');
if ($product->getId()) {
    $product->delete();
}

$product = $objectManager->create('Magento\Catalog\Model\Product');
$product->load('simple_200', 'sku');
if ($product->getId()) {
    $product->delete();
}
