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

/** @var SourceInterfaceFactory $sourceFactory */
$sourceFactory = Bootstrap::getObjectManager()->get(SourceInterfaceFactory::class);
/** @var DataObjectHelper $dataObjectHelper */
$dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);
/** @var SourceRepositoryInterface $sourceRepository */
$sourceRepository = Bootstrap::getObjectManager()->get(SourceRepositoryInterface::class);

$sourcesData = [
    [
        // define only required and needed for tests fields
        SourceInterface::SOURCE_ID => 10,
        SourceInterface::CODE => 'source-code-1',
        SourceInterface::NAME => 'EU-source-1',
        SourceInterface::ENABLED => true,
        SourceInterface::PRIORITY => 100,
        SourceInterface::POSTCODE => 'postcode',
        SourceInterface::COUNTRY_ID => 'FR',
    ],
    [
        SourceInterface::SOURCE_ID => 20,
        SourceInterface::CODE => 'source-code-2',
        SourceInterface::NAME => 'EU-source-2',
        SourceInterface::ENABLED => true,
        SourceInterface::PRIORITY => 200,
        SourceInterface::POSTCODE => 'postcode',
        SourceInterface::COUNTRY_ID => 'FR',
    ],
    [
        SourceInterface::SOURCE_ID => 30,
        SourceInterface::CODE => 'source-code-3',
        SourceInterface::NAME => 'EU-source-3',
        SourceInterface::ENABLED => true,
        SourceInterface::PRIORITY => 300,
        SourceInterface::POSTCODE => 'postcode',
        SourceInterface::COUNTRY_ID => 'DE',
    ],
    [
        SourceInterface::SOURCE_ID => 40,
        SourceInterface::CODE => 'source-code-4',
        SourceInterface::NAME => 'EU-source-disabled',
        SourceInterface::ENABLED => false,
        SourceInterface::PRIORITY => 10,
        SourceInterface::POSTCODE => 'postcode',
        SourceInterface::COUNTRY_ID => 'DE',
    ],
    [
        SourceInterface::SOURCE_ID => 50,
        SourceInterface::CODE => 'source-code-5',
        SourceInterface::NAME => 'US-source-1',
        SourceInterface::ENABLED => true,
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
