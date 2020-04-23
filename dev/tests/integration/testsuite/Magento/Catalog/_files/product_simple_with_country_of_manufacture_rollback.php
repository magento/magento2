<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);

$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

try {
    /** @var Product $product */
    $product = $productRepository->get('simple_with_com');
    $productRepository->delete($product);
} catch (\Exception $e) {
    // In case of test run with DB isolation there is already no object in database
    // since rollback fixtures called after transaction rollback.
}
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
