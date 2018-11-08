<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Exception\NoSuchEntityException;

/** @var \Magento\Framework\ObjectManagerInterface $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
$categoryCollection = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Category\Collection::class);
$categoryCollection
    ->addAttributeToFilter('level', 2)
    ->load()
    ->delete();

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
try {
    $product = $productRepository->get('simple3', false, null, true);
    $productRepository->delete($product);
} catch (NoSuchEntityException $e) {
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
