<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../Eav/_files/empty_attribute_set.php';
require __DIR__ . '/../../Catalog/_files/product_simple.php';

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
try {
    $product = $productRepository->get('simple', true, null, true);
    $product->setAttributeSetId($attributeSet->getId());
    $productRepository->save($product);
} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
}
