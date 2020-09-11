<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

/** @var ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
/** @var ProductAttributeRepositoryInterface $productAttributeRepository */
$productAttributeRepository = $objectManager->create(ProductAttributeRepositoryInterface::class);
$options = $productAttributeRepository->get('text_swatch_attribute')->getOptions();
$secondAttributeOptions = $productAttributeRepository->get('visual_swatch_attribute')->getOptions();
array_shift($options);
array_shift($secondAttributeOptions);
$productsArray = [];

foreach ($options as $option) {
    foreach ($secondAttributeOptions as $secondAttrOption) {
        $productsArray[] = strtolower(
            str_replace(' ', '_', 'simple ' . $option->getLabel() . '_' . $secondAttrOption->getLabel())
        );
    }
}

$productsArray[] = 'configurable';
foreach ($productsArray as $sku) {
    try {
        $productRepository->deleteById($sku);
    } catch (NoSuchEntityException $e) {
        //Product already removed
    }
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

Resolver::getInstance()->requireDataFixture('Magento/Swatches/_files/product_text_swatch_attribute_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Swatches/_files/product_visual_swatch_attribute_rollback.php');
