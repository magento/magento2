<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Framework\Api\DataObjectHelper;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceInterfaceFactory;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var SourceInterfaceFactory $sourceFactory */
$sourceFactory = Bootstrap::getObjectManager()->get(SourceInterfaceFactory::class);
/** @var DataObjectHelper $dataObjectHelper */
$dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);
/** @var SourceRepositoryInterface $sourceRepository */
$sourceRepository = Bootstrap::getObjectManager()->get(SourceRepositoryInterface::class);

$sourcesData = [
    [
        SourceInterface::NAME => 'source-name-1',
        SourceInterface::ENABLED => true,
        SourceInterface::PRIORITY => 300,
    ],
    [
        SourceInterface::NAME => 'source-name-2',
        SourceInterface::ENABLED => true,
        SourceInterface::PRIORITY => 200,
    ],
    [
        SourceInterface::NAME => 'source-name-3',
        SourceInterface::ENABLED => false,
        SourceInterface::PRIORITY => 200,
    ],
    [
        SourceInterface::NAME => 'source-name-4',
        SourceInterface::ENABLED => false,
        SourceInterface::PRIORITY => 100,
    ],
];
foreach ($sourcesData as $sourceData) {
    /** @var SourceInterface $source */
    $source = $sourceFactory->create();
    $dataObjectHelper->populateWithArray($source, $sourceData, SourceInterface::class);
    $sourceRepository->save($source);
}
