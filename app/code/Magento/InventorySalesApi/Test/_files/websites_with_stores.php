<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Api\Data\GroupInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\InventorySalesApi\Test\OriginalSequenceBuilder;
use Magento\SalesSequence\Model\EntityPool;
use Magento\SalesSequence\Model\Config;

$websiteCodes = ['eu_website', 'us_website', 'global_website'];

$objectManager = Bootstrap::getObjectManager();
/** @var ManagerInterface $eventManager */
$eventManager = $objectManager->create(ManagerInterface::class);

/** @var OriginalSequenceBuilder $sequence */
$sequenceBuilder = $objectManager->create(OriginalSequenceBuilder::class);
/** @var EntityPool $entityPool */
$entityPool = $objectManager->create(EntityPool::class);
/** @var Config $sequenceConfig */
$sequenceConfig = $objectManager->create(Config::class);


/** @var StoreInterface $store */
$store = $objectManager->create(Store::class);
$store->load('default');
$rootCategoryId = $store->getRootCategoryId();

foreach ($websiteCodes as $key => $websiteCode) {
    /** @var Website $website */
    $website = $objectManager->create(Website::class);
    $website->setData([
        'code' => $websiteCode,
        'name' => 'Test Website ' . $websiteCode,
        'is_default' => '0',
    ]);
    $website->save();

    $store = $objectManager->create(Store::class);
    $store->setCode(
        'store_for_' . $websiteCode
    )->setWebsiteId(
        $website->getId()
    )->setName(
        'store_for_' . $websiteCode
    )->setSortOrder(
        10 + $key
    )->setIsActive(
        1
    );

    /** @var GroupInterface $group */
    $group = $objectManager->create(GroupInterface::class);
    $group->setName('store_view_' . $websiteCode);
    $group->setCode('store_view_' . $websiteCode);
    $group->setWebsiteId($website->getId());
    $group->setDefaultStoreId($store->getId());
    $group->setRootCategoryId($rootCategoryId);
    $group->save();

    $website->setDefaultGroupId($group->getId());
    $website->save();
    $store->setGroupId($group->getId());
    $store->save();

    //Generate sequence tables
    // It use for testing sales operations (order, invoice, shipment etc.)
    foreach ($entityPool->getEntities() as $entityType) {
        $sequenceBuilder->setPrefix($store->getId())
            ->setSuffix($sequenceConfig->get('suffix'))
            ->setStartValue($sequenceConfig->get('startValue'))
            ->setStoreId($store->getId())
            ->setStep($sequenceConfig->get('step'))
            ->setWarningValue($sequenceConfig->get('warningValue'))
            ->setMaxValue($sequenceConfig->get('maxValue'))
            ->setEntityType($entityType)
            ->create();
    }
}
