<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Api\DataObjectHelper;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\BillingAddressManagementInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;

use Magento\TestFramework\Helper\Bootstrap;

/** @var QuoteFactory $quoteFactory */
$quoteFactory = Bootstrap::getObjectManager()->get(QuoteFactory::class);
/** @var QuoteResource $quoteResource */
$quoteResource = Bootstrap::getObjectManager()->get(QuoteResource::class);
/** @var AddressInterfaceFactory $quoteAddressFactory */
$quoteAddressFactory = Bootstrap::getObjectManager()->get(AddressInterfaceFactory::class);
/** @var DataObjectHelper $dataObjectHelper */
$dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);
/** @var BillingAddressManagementInterface $billingAddressManagement */
$billingAddressManagement = Bootstrap::getObjectManager()->get(BillingAddressManagementInterface::class);

$quoteAddressData = [
    AddressInterface::KEY_TELEPHONE => 3468676,
    AddressInterface::KEY_POSTCODE => 75477,
    AddressInterface::KEY_COUNTRY_ID => 'US',
    AddressInterface::KEY_CITY => 'CityM',
    AddressInterface::KEY_COMPANY => 'CompanyName',
    AddressInterface::KEY_STREET => 'Green str, 67',
    AddressInterface::KEY_LASTNAME => 'Smith',
    AddressInterface::KEY_FIRSTNAME => 'John',
    AddressInterface::KEY_REGION_ID => 1,
];
$quoteAddress = $quoteAddressFactory->create();
$dataObjectHelper->populateWithArray($quoteAddress, $quoteAddressData, AddressInterfaceFactory::class);

$quote = $quoteFactory->create();
$quoteResource->load($quote, 'test_quote', 'reserved_order_id');
$billingAddressManagement->assign($quote->getId(), $quoteAddress);
