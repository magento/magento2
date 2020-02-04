<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);

try {
    $productRepository->deleteById('bundle-product-separate-shipping-1');
    $productRepository->deleteById('bundle-product-separate-shipping-2');
} catch (NoSuchEntityException $exception) {
    // When DbIsolation is used products can be already removed by rollback main transaction
}

$registry->register('isSecureArea', false);

require __DIR__ . '/multiple_products_rollback.php';
