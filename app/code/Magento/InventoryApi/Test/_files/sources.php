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
        // define only required and needed for tests fields
        SourceInterface::NAME => 'source-name-1',
        SourceInterface::ENABLED => true,
        SourceInterface::PRIORITY => 300,
        SourceInterface::POSTCODE => 'postcode',
        SourceInterface::COUNTRY_ID => 'US',
    ],
    [
        SourceInterface::NAME => 'source-name-2',
        SourceInterface::ENABLED => true,
        SourceInterface::PRIORITY => 200,
        SourceInterface::POSTCODE => 'postcode',
        SourceInterface::COUNTRY_ID => 'US',
    ],
    [
        SourceInterface::NAME => 'source-name-3',
        SourceInterface::ENABLED => false,
        SourceInterface::PRIORITY => 200,
        SourceInterface::POSTCODE => 'postcode',
        SourceInterface::COUNTRY_ID => 'US',
    ],
    [
        SourceInterface::NAME => 'source-name-4',
        SourceInterface::ENABLED => false,
        SourceInterface::PRIORITY => 100,
        SourceInterface::POSTCODE => 'postcode',
        SourceInterface::COUNTRY_ID => 'US',
    ],
];
foreach ($sourcesData as $sourceData) {
    /** @var SourceInterface $source */
    $source = $sourceFactory->create();
    $dataObjectHelper->populateWithArray($source, $sourceData, SourceInterface::class);
    $sourceRepository->save($source);
}
