<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type as Simple;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Downloadable\Model\Product\Type as Downloadable;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);

$productTypes = [
    Bundle::TYPE_CODE,
    Configurable::TYPE_CODE,
    Downloadable::TYPE_DOWNLOADABLE,
    Grouped::TYPE_CODE,
    Simple::TYPE_SIMPLE,
    Simple::TYPE_VIRTUAL,
];

foreach ($productTypes as $productType) {
    try {
        $product = $productRepository->get($productType . '_sku', false, null, true);
        $productRepository->delete($product);
    } catch (NoSuchEntityException $exception) {
        //Product already removed
    }
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
