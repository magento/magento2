<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Framework\Api\DataObjectHelper;
use Magento\InventoryApi\Api\Data\SourceCarrierLinkInterface;
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
        SourceInterface::NAME => 'source-name-1',
        SourceInterface::CONTACT_NAME => 'source-contact-name-1',
        SourceInterface::EMAIL => 'source-email-1',
        SourceInterface::ENABLED => true,
        SourceInterface::DESCRIPTION => 'source-description-1',
        SourceInterface::LATITUDE => 11.123456,
        SourceInterface::LONGITUDE => 12.123456,
        SourceInterface::COUNTRY_ID => 'US',
        SourceInterface::REGION_ID => 10,
        SourceInterface::CITY => 'source-city-1',
        SourceInterface::STREET => 'source-street-1',
        SourceInterface::POSTCODE => 'source-postcode-1',
        SourceInterface::PHONE => 'source-phone-1',
        SourceInterface::FAX => 'source-fax-1',
        SourceInterface::PRIORITY => 1,
        SourceInterface::USE_DEFAULT_CARRIER_CONFIG => 0,
        SourceInterface::USE_DEFAULT_CARRIER_CONFIG => false,
        SourceInterface::CARRIER_LINKS => [
            [
                SourceCarrierLinkInterface::CARRIER_CODE => 'ups',
                SourceCarrierLinkInterface::POSITION => 100,
            ],
            [
                SourceCarrierLinkInterface::CARRIER_CODE => 'usps',
                SourceCarrierLinkInterface::POSITION => 200,
            ],
        ],
    ],
    SourceInterface::class
);
$sourceRepository->save($source);
