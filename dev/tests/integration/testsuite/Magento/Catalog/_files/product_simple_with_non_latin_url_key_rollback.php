<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

Bootstrap::getInstance()->getInstance()->reinitialize();

/** @var Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()
    ->get(ProductRepositoryInterface::class);

$productSkus = [
    'ukrainian-with-url-key',
    'ukrainian-without-url-key',
];
try {
    foreach ($productSkus as $sku) {
        $product = $productRepository->get($sku, false, null, true);
        $productRepository->delete($product);
    }
} catch (NoSuchEntityException $e) {
    // nothing to delete
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
