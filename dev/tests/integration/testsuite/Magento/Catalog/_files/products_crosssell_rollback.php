<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get('Magento\Framework\Registry');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');

try {
    $firstProduct = $productRepository->get('simple');
    $firstProduct->delete();
} catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
    //Product already removed
}

try {
    $secondProduct = $productRepository->get('simple_with_cross');
    $secondProduct->delete();
} catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
    //Product already removed
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
