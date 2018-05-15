<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;
use Magento\Inventory\Model\SourceItem\Command\SourceItemsSave;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var SourceItemRepositoryInterface $sourceItemRepository */
$sourceItemRepository = Bootstrap::getObjectManager()->create(SourceItemRepositoryInterface::class);

/** @var GetSourceItemsBySkuInterface $getSourceItemsBySku */
$getSourceItemsBySku = Bootstrap::getObjectManager()->create(GetSourceItemsBySkuInterface::class);

/** @var SourceItemsSave $sourceItemSave */
$sourceItemSave = Bootstrap::getObjectManager()->create(SourceItemsSave::class);

$skuList = ['simple_11', 'simple_21', 'simple_31'];
foreach ($skuList as $sku) {
    $sourceItems = $getSourceItemsBySku->execute($sku);
    $changesSourceItems = [];
    foreach ($sourceItems as $sourceItem) {
        $sourceItem->setStatus(SourceItemInterface::STATUS_OUT_OF_STOCK);
        $changesSourceItems[] = $sourceItem;
    }
    $sourceItemSave->execute($changesSourceItems);
}
