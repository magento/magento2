<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Framework\ObjectManagerInterface $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get('Magento\Framework\Registry');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var \Magento\Catalog\Model\Product $product */
$product = $objectManager->create('Magento\Catalog\Model\Product');
/** @var \Magento\Catalog\Model\Product[] $products */
$products = $product->getCollection()->getItems();
foreach ($products as $product) {
    if ($product->getId()) {
        $product->delete();
    }
}

/** @var \Magento\Catalog\Model\Resource\Product\Collection $collection */
$collection = $objectManager->create('Magento\Catalog\Model\Resource\Category\Collection');
$collection
    ->addAttributeToFilter('level', 2)
    ->load()
    ->delete();

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
