<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$productRepository = $objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');
/** @var \Magento\Catalog\Api\Data\ProductInterface $product */
$product = $productRepository->get('downloadable-product');
if ($product->getId()) {
    $product->delete();
}
