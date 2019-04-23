<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);

$currentArea = $registry->registry('isSecureArea');
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

try {
    $productRepository->deleteById('simple_product');
} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
    /**
     * Tests which are wrapped with MySQL transaction clear all data by transaction rollback.
     */
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', $currentArea);
