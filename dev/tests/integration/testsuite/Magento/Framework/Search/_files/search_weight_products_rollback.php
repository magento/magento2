<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

/** @var \Magento\Framework\Registry $registry */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$registry = $objectManager->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
$collection = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
$collection->addAttributeToSelect('id')->load();
if ($collection->count() > 0) {
    $collection->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

/** @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository */
$productAttributeRepository = $objectManager->get(
    \Magento\Catalog\Api\ProductAttributeRepositoryInterface::class
);

$nameAttribute = $productAttributeRepository->get('name');
$nameAttribute->setSearchWeight(5);
$productAttributeRepository->save($nameAttribute);

$descriptionAttribute = $productAttributeRepository->get('description');
$descriptionAttribute->setSearchWeight(1);
$productAttributeRepository->save($descriptionAttribute);
