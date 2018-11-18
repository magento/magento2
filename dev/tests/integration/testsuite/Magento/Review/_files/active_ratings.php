<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../../Magento/Store/_files/store.php';

$firstStoreId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    \Magento\Store\Model\StoreManagerInterface::class
)->getStore()->getId();

$secondStoreId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Store\Model\Store::class)->load('test')->getId();

/** @var \Magento\Review\Model\ResourceModel\Review\Collection $ratingCollection */
$ratingCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Review\Model\Rating::class
)->getCollection();

foreach ($ratingCollection as $rating) {
    $rating->setStores([$firstStoreId, $secondStoreId])->setIsActive(1)->save();
}
