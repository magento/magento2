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

/** @var SourceInterface $source */
$source = $sourceFactory->create();
$dataObjectHelper->populateWithArray(
    $source,
    [
        SourceInterface::SOURCE_CODE => 'source-code-1',
        SourceInterface::NAME => 'source-name-1',
        SourceInterface::CONTACT_NAME => 'source-contact-name',
        SourceInterface::EMAIL => 'source-email',
        SourceInterface::ENABLED => true,
        SourceInterface::DESCRIPTION => 'source-description',
        SourceInterface::LATITUDE => 11.123456,
        SourceInterface::LONGITUDE => 12.123456,
        SourceInterface::COUNTRY_ID => 'US',
        SourceInterface::REGION_ID => 10,
        SourceInterface::CITY => 'source-city',
        SourceInterface::STREET => 'source-street',
        SourceInterface::POSTCODE => 'source-postcode',
        SourceInterface::PHONE => 'source-phone',
        SourceInterface::FAX => 'source-fax',
        SourceInterface::USE_DEFAULT_CARRIER_CONFIG => 0,
        SourceInterface::USE_DEFAULT_CARRIER_CONFIG => false,
        SourceInterface::CARRIER_LINKS => [],
    ],
    SourceInterface::class
);
$sourceRepository->save($source);
