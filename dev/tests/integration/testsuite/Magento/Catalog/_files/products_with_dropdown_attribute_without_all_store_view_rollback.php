<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Indexer\Product\Eav as ProductEav;
use Magento\Framework\Registry;

$objectManager = Bootstrap::getObjectManager();
/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$eavConfig = $objectManager->get(EavConfig::class);
$attributesToDelete = ['dropdown_without_default'];
/** @var AttributeRepositoryInterface $attributeRepository */
$attributeRepository = $objectManager->get(AttributeRepositoryInterface::class);
/** @var AttributeInterface $attribute */
$attribute = $attributeRepository->get(ProductAttributeInterface::ENTITY_TYPE_CODE, 'dropdown_without_default');
$attributeRepository->delete($attribute);
$eavConfig->clear();

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
/** @var ProductInterface $product */
$product = $productRepository->get('test_attribute_dropdown_without_default');
if ($product->getId()) {
    $productRepository->delete($product);
}
$objectManager->get(ProductEav::class)->executeRow($product->getId());

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
