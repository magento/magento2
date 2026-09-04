<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$registry = Bootstrap::getObjectManager()->get(Registry::class);
$productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
try {
    $groupedProduct = $productRepository->get('grouped-product', false, null, true);
    $groupedProduct->delete();
} catch (NoSuchEntityException $e) {
    //already deleted
}
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
