<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$products = ['grouped-association-1', 'grouped-association-2', 'grouped-product'];

foreach ($products as $sku) {
    try {
        /** @var \Magento\Catalog\Model\Product $simpleProduct */
        $simpleProduct = $productRepository->get($sku, false, null, true);
        $simpleProduct->delete();
    } catch (NoSuchEntityException $e) {
        //already deleted
    }
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

require 'custom_product_tax_class_rollback.php';
