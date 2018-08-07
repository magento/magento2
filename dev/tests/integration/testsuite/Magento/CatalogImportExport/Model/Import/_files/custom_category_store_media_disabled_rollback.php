<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$registry = $objectManager->get(\Magento\Framework\Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var $category \Magento\Catalog\Model\Category */
$category = $objectManager->create(\Magento\Catalog\Model\Category::class);
$category->loadByAttribute('name', 'MV');
if ($category->getId()) {
    $category->delete();
}

/** @var $store \Magento\Store\Model\Store */
$store = $objectManager->create(\Magento\Store\Model\Store::class);
$store->load('mascota', 'code');
if ($store->getId()) {
    $store->delete();
}

/** @var $website \Magento\Store\Model\Website */
$website = $objectManager->create(\Magento\Store\Model\Website::class);
$website->load('mascota', 'code');
if ($website->getId()) {
    $website->delete();
}

/** @var $attributeSet \Magento\Eav\Model\Entity\Attribute\Set */
$attributeSet = $objectManager->create(\Magento\Eav\Model\Entity\Attribute\Set::class);
$attributeSet->load('vinos', 'attribute_set_name');
if ($attributeSet->getId()) {
    $attributeSet->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
