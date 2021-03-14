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
$registry = $objectManager->get(Registry::class);
$productRepository = $objectManager->get(ProductRepositoryInterface::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

try {
    $productRepository->deleteById('simple_with_custom_design');
} catch (NoSuchEntityException $e) {
    //product already deleted.
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
