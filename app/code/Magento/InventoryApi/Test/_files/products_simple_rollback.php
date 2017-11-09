<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\Exception\NoSuchEntityException;

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
try {
    $product = $productRepository->get('SKU-1', false, null, true);
    $productRepository->delete($product);
} catch (NoSuchEntityException $e) {
}
