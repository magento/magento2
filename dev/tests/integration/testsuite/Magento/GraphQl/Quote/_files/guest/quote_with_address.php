<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\ShippingAddressManagementInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Api\DataObjectHelper;

/** @var GuestCartManagementInterface $guestCartManagement */
$guestCartManagement = Bootstrap::getObjectManager()->get(GuestCartManagementInterface::class);
/** @var CartRepositoryInterface $cartRepository */
$cartRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
/** @var MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId */
$maskedQuoteIdToQuoteId = Bootstrap::getObjectManager()->get(MaskedQuoteIdToQuoteIdInterface::class);
/** @var AddressInterfaceFactory $quoteAddressFactory */
$quoteAddressFactory = Bootstrap::getObjectManager()->get(AddressInterfaceFactory::class);
/** @var DataObjectHelper $dataObjectHelper */
$dataObjectHelper = Bootstrap::getObjectManager()->get(DataObjectHelper::class);
/** @var ShippingAddressManagementInterface $shippingAddressManagement */
$shippingAddressManagement = Bootstrap::getObjectManager()->get(ShippingAddressManagementInterface::class);

$cartHash = $guestCartManagement->createEmptyCart();
$cartId = $maskedQuoteIdToQuoteId->execute($cartHash);
$cart = $cartRepository->get($cartId);
$cart->setReservedOrderId('guest_quote_with_address');
$cartRepository->save($cart);

$quoteAddressData = [
    AddressInterface::KEY_TELEPHONE => 4435555,
    AddressInterface::KEY_POSTCODE => 78717,
    AddressInterface::KEY_COUNTRY_ID => 'US',
    AddressInterface::KEY_CITY => 'CityA',
    AddressInterface::KEY_COMPANY => 'CompanyName',
    AddressInterface::KEY_STREET => 'Andora str, 121',
    AddressInterface::KEY_LASTNAME => 'Smith',
    AddressInterface::KEY_FIRSTNAME => 'John',
    AddressInterface::KEY_REGION_ID => 1,
];
$quoteAddress = $quoteAddressFactory->create();
$dataObjectHelper->populateWithArray($quoteAddress, $quoteAddressData, AddressInterfaceFactory::class);
$shippingAddressManagement->assign($cartId, $quoteAddress);
