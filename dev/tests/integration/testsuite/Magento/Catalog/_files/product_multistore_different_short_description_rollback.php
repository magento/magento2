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
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = Bootstrap::getObjectManager();
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
$productRepository = $objectManager->create(ProductRepositoryInterface::class);

try {
    $product = $productRepository->get('simple-different-short-description');
    $productRepository->delete($product);
} catch (NoSuchEntityException $e) {
    //already removed
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/core_fixturestore_rollback.php');
