<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/** @var \Magento\Framework\ObjectManagerInterface $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

try {
    $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
        ->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
    $product = $productRepository->get('product-with-xss', true);
    if ($product->getId()) {
        $productRepository->delete($product);
    }
} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
    //Product already removed
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
