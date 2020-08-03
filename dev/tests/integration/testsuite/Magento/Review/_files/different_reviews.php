<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple.php');

$review = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Review\Model\Review::class,
    ['data' => ['nickname' => 'Nickname', 'title' => 'Review Summary', 'detail' => 'Review text']]
);
$review->setEntityId(
    $review->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE)
)->setEntityPkValue(
    1
)->setStatusId(
    \Magento\Review\Model\Review::STATUS_PENDING
)->setStoreId(
    \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
        \Magento\Store\Model\StoreManagerInterface::class
    )->getStore()->getId()
)->setStores(
    [
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Store\Model\StoreManagerInterface::class
        )->getStore()->getId()
    ]
)->save();

/*
 * Added a sleep because in a few tests the sql query orders by created at. Without the sleep the reviews
 * have sometimes the same created at timestamp, that causes this tests randomly to fail.
 */
sleep(1);

$review = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Review\Model\Review::class,
    ['data' => ['nickname' => 'Nickname', 'title' => '2 filter first review', 'detail' => 'Review text']]
);
$review->setEntityId(
    $review->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE)
)->setEntityPkValue(
    1
)->setStatusId(
    \Magento\Review\Model\Review::STATUS_APPROVED
)->setStoreId(
    \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
        \Magento\Store\Model\StoreManagerInterface::class
    )->getStore()->getId()
)->setStores(
    [
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Store\Model\StoreManagerInterface::class
        )->getStore()->getId()
    ]
)->save();

/*
 * Added a sleep because in a few tests the sql query orders by created at. Without the sleep the reviews
 * have sometimes the same created at timestamp, that causes this tests randomly to fail.
 */
sleep(1);

$review = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Review\Model\Review::class,
    ['data' => ['nickname' => 'Nickname', 'title' => '1 filter second review', 'detail' => 'Review text']]
);
$review->setEntityId(
    $review->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE)
)->setEntityPkValue(
    1
)->setStatusId(
    \Magento\Review\Model\Review::STATUS_APPROVED
)->setStoreId(
    \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
        \Magento\Store\Model\StoreManagerInterface::class
    )->getStore()->getId()
)->setStores(
    [
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Store\Model\StoreManagerInterface::class
        )->getStore()->getId()
    ]
)->save();
$review->aggregate();
