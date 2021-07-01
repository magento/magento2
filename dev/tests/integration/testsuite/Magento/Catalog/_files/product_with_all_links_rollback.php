<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);

try {
    $firstProduct = $productRepository->get('simple_related', false, null, true);
    $productRepository->delete($firstProduct);
} catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
    //Product already removed
}

try {
    $secondProduct = $productRepository->get('simple_up', false, null, true);
    $productRepository->delete($secondProduct);
} catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
    //Product already removed
}

try {
    $thirdProduct = $productRepository->get('simple_cross', false, null, true);
    $productRepository->delete($thirdProduct);
} catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
    //Product already removed
}

try {
    $fourthProduct = $productRepository->get('simple_with_links', false, null, true);
    $productRepository->delete($fourthProduct);
} catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
    //Product already removed
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
