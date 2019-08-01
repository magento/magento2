<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/** @var ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()
    ->get(ProductRepositoryInterface::class);

/**
 * Delete 10 products
 */
$productsAmount = 10;

try {
    for ($i = 1; $i <= $productsAmount; $i++) {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $productRepository->get("Product{$i}", false, null, true);
        $productRepository->delete($product);
    }
} catch (NoSuchEntityException $e) {
}
