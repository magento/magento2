<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Framework\Exception\NoSuchEntityException;

\Magento\TestFramework\Helper\Bootstrap::getInstance()->getInstance()->reinitialize();

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

try {
    $product = $productRepository->get('simple_with_date', false, null, true);
    $productRepository->delete($product);
} catch (NoSuchEntityException $e) {
}

try {
    $product = $productRepository->get('simple_with_date2', false, null, true);
    $productRepository->delete($product);
} catch (NoSuchEntityException $e) {
}

try {
    $product = $productRepository->get('simple_with_date3', false, null, true);
    $productRepository->delete($product);
} catch (NoSuchEntityException $e) {
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

include __DIR__ . '/product_date_attribute_rollback.php';
