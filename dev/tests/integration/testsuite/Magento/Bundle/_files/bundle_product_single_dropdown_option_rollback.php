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
use Psr\Log\LoggerInterface;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/multiple_products_rollback.php');

$objectManager = Bootstrap::getObjectManager();
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);

try {
    $product = $productRepository->get('bundle-product-single-dropdown-option', false, null, true);
    $productRepository->delete($product);
} catch (NoSuchEntityException $exception) {
    $logger = $objectManager->get(LoggerInterface::class);
    $logger->log($exception->getCode(), $exception->getMessage());
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
