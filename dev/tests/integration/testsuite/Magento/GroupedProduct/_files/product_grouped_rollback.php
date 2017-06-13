<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Framework\Exception\NoSuchEntityException;

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

/**
 * @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
 */
$productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    \Magento\Catalog\Api\ProductRepositoryInterface::class
);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
try {
    /** @var $simpleProduct \Magento\Catalog\Model\Product */
    $simpleProduct = $productRepository->get('simple', false, null, true);
    $simpleProduct->delete();
} catch (NoSuchEntityException $e) {
    //already deleted
}

/** @var $virtualProduct \Magento\Catalog\Model\Product */
$virtualProduct = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Catalog\Model\Product::class
);
$virtualProduct->load(21);
if ($virtualProduct->getId()) {
    $virtualProduct->delete();
}

try {
    /** @var $groupedProduct \Magento\Catalog\Model\Product */
    $groupedProduct = $productRepository->get('grouped-product', false, null, true);
    $groupedProduct->delete();
} catch (NoSuchEntityException $e) {
    //already deleted
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
