<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();

/**
 * @var ProductRepositoryInterface $productRepository
 */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$skus = ['AdvancedPricingSimple 1', 'AdvancedPricingSimple 2'];
foreach ($skus as $sku) {
    try {
        $product = $productRepository->deleteById($sku);
    } catch (NoSuchEntityException $exception) {
        // product already removed
    }
}
