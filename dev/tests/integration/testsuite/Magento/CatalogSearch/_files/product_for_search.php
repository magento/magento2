<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require 'searchable_attribute.php';
require __DIR__ . '/../../../Magento/Catalog/_files/product_simple.php';

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Store\Model\StoreManager $storeManager */
$storeManager = $objectManager->get(\Magento\Store\Model\StoreManager::class);
$storeManager->setIsSingleStoreModeAllowed(false);
/** @var \Magento\Store\Model\Store $store */
$store = $storeManager->getStore('default');

/** @var \Magento\Catalog\Model\Product $product */
$product = $objectManager->create(\Magento\Catalog\Model\ProductRepository::class)->get('simple');
/** @var \Magento\Catalog\Model\Product\Action $productAction */
$productAction = $objectManager->create(\Magento\Catalog\Model\Product\Action::class);
$productAction->updateAttributes([$product->getId()], ['test_searchable_attribute' => 'VALUE1'], $store->getId());
