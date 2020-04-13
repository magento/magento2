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

require __DIR__ . '/category_rollback.php';

$objectManager = Bootstrap::getObjectManager();
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);

try {
    $productRepository->deleteById('in-stock-product');
} catch (NoSuchEntityException $e) {
    //already removed
}
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
