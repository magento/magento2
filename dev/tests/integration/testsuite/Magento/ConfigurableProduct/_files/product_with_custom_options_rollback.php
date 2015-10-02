<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require __DIR__ . '/product_configurable_rollback.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$productRepository = $objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');
/** @var \Magento\Catalog\Api\Data\ProductInterface $product */
$product = $productRepository->get('simple_100');
if ($product && $product->getId()) {
    $product->delete();
}
$product = $productRepository->get('configurable');
if ($product && $product->getId()) {
    $product->delete();
}