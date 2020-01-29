<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var ProductAttributeRepositoryInterface $productAttributeRepository */
$productAttributeRepository = $objectManager->get(ProductAttributeRepositoryInterface::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$productSkus = ['1234-1234-1234-1234', 'Simple', 'product_with_description', 'product_with_attribute'];

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

foreach ($productSkus as $productSku) {
    try {
        $productRepository->deleteById($productSku);
    } catch (NoSuchEntityException $e) {
        //Product already deleted.
    }
}

try {
    $productAttributeRepository->deleteById('test_searchable_attribute');
} catch (NoSuchEntityException $e) {
    //attribute already deleted.
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
