<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = Bootstrap::getObjectManager();

$productRepository = $objectManager->get(ProductRepositoryInterface::class);
try {
    $product = $productRepository->get('not_visible_simple', false, null, true);
    $productRepository->delete($product);
} catch (NoSuchEntityException $e) {
    //Product already removed
}

Resolver::getInstance()->requireDataFixture('Magento/CatalogSearch/_files/searchable_attribute_rollback.php');
