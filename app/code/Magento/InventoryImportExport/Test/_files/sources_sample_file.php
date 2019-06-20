<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Api\DataObjectHelper;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceInterfaceFactory;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

$sourceFactory = Bootstrap::getObjectManager()->get(SourceInterfaceFactory::class);
$dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);
$sourceRepository = Bootstrap::getObjectManager()->get(SourceRepositoryInterface::class);

$sourcesData = [
    [
        // define only required and needed for tests fields
        SourceInterface::SOURCE_CODE => 'source-1',
        SourceInterface::NAME => 'Source 1',
        SourceInterface::ENABLED => true,
        SourceInterface::POSTCODE => 'postcode',
        SourceInterface::COUNTRY_ID => 'US',
    ],
    [
        SourceInterface::SOURCE_CODE => 'source-2',
        SourceInterface::NAME => 'Source 2',
        SourceInterface::ENABLED => true,
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
