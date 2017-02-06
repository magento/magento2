<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/dynamic_bundle_product.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);

/** @var $product \Magento\Catalog\Model\Product */
$productRepository
    ->get('bundle_product')
    ->setSpecialPrice(50)
    ->save();

$productRepository
    ->get('simple2')
    ->setSpecialPrice(2.5)
    ->save();

$productRepository
    ->get('simple5')
    ->setSpecialPrice(9.9)
    ->save();
