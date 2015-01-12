<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require __DIR__ . '/../../Core/_files/store.php';
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var Magento\Store\Model\Store $store */
$store = $objectManager->create('Magento\Store\Model\Store');
$store->load('fixturestore', 'code');

/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create('Magento\Catalog\Model\Product');
//$product->isObjectNew(true);
$product->setTypeId(
    \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
)->setId(
    1
)->setAttributeSetId(
    4
)->setStoreId(
    1
)->setWebsiteIds(
    [1]
)->setName(
    'Simple Product One'
)->setSku(
    'simple'
)->setPrice(
    10
)->setWeight(
    18
)->setStockData(
    ['use_config_manage_stock' => 0]
)->setCategoryIds(
    [9]
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
)->save();

$product = $objectManager->create('Magento\Catalog\Model\Product');
$product->setStoreId(1)
    ->load(1)
    ->setStoreId($store->getId())
    ->setName('StoreTitle')
    ->save();
