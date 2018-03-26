<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

require __DIR__ . '/../../../../../../dev/tests/integration/testsuite/Magento/Store/_files/core_fixturestore.php';
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var Magento\Store\Model\Store $store */
$store = $objectManager->create(\Magento\Store\Model\Store::class);
$store->load('fixturestore', 'code');

$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->load($product->getIdBySku('SKU-1'))
    ->setStoreId($store->getId())
    ->setName('StoreTitle')
    ->save();
