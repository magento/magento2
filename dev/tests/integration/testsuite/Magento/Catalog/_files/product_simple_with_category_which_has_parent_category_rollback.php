<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

require __DIR__ . '/categories_no_products_rollback.php';

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
try {
    $productRepository->deleteById('simple_with_child_category');
} catch (NoSuchEntityException $exception) {
    //Product already deleted.
}
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
