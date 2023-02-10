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

Resolver::getInstance()->requireDataFixture(
    'Magento/Catalog/_files/multiple_products_with_disabled_virtual_product_rollback.php'
);

$objectManager = Bootstrap::getObjectManager();
/** @var Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

//delete bundle product
try {
    $product = $productRepository->get('bundle-product');
    $productRepository->delete($product);
} catch (NoSuchEntityException $exception) {
    //Product already removed
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
