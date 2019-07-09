<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

try {
    $productRepository->deleteById('bundle-product-separate-shipping-1');
    $productRepository->deleteById('bundle-product-separate-shipping-2');
} catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
    // When DbIsolation is used products can be already removed by rollback main transaction
}

$registry->register('isSecureArea', false);

require __DIR__ . '/multiple_products_rollback.php';
