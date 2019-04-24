<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

require_once __DIR__ . '/source_items.php';

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var DataObjectHelper $dataObjectHelper */
$dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);
/** @var SourceItemRepositoryInterface $sourceItemRepository */
$sourceItemRepository = Bootstrap::getObjectManager()->get(SourceItemRepositoryInterface::class);
/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
/** @var  SourceItemsSaveInterface $sourceItemsSave */
$sourceItemsSave = Bootstrap::getObjectManager()->get(SourceItemsSaveInterface::class);

$sourceItems = $sourceItemRepository->getList($searchCriteriaBuilder->create())->getItems();

foreach ($sourceItems as $sourceItem) {
    $sourceItem->setStatus(SourceItemInterface::STATUS_OUT_OF_STOCK);
}

$sourceItemsSave->execute($sourceItems);
