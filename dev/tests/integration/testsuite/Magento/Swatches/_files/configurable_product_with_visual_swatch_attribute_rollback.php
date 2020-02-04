<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
/** @var ProductAttributeRepositoryInterface $attributeRepository */
$attributeRepository = $objectManager->create(ProductAttributeRepositoryInterface::class);
$attribute = $attributeRepository->get('test_configurable');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
$options = $attribute->getSource()->getAllOptions();
array_shift($options);
foreach ($options as $option) {
    try {
        $productRepository->deleteById('simple_' . str_replace(' ', '_', $option['label']));
    } catch (NoSuchEntityException $e) {
        //Product already removed
    }
}

try {
    $productRepository->deleteById('configurable');
} catch (NoSuchEntityException $e) {
    //Product already removed
}

require __DIR__ . '/visual_swatch_attribute_with_different_options_type_rollback.php';

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
