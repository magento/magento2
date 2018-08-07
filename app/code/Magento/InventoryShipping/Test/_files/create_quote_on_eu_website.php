<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var CartRepositoryInterface $cartRepository */
$cartRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
/** @var CartManagementInterface $cartManagement */
$cartManagement = Bootstrap::getObjectManager()->get(CartManagementInterface::class);
/** @var AddressInterfaceFactory $addressFactory */
$addressFactory = Bootstrap::getObjectManager()->get(AddressInterfaceFactory::class);
/** @var StoreRepositoryInterface $storeRepository */
$storeRepository = Bootstrap::getObjectManager()->get(StoreRepositoryInterface::class);
/** @var StoreManagerInterface\ $storeManager */
$storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);

$cartId = $cartManagement->createEmptyCart();
$cart = $cartRepository->get($cartId);
$cart->setCustomerEmail('admin@example.com');
$cart->setCustomerIsGuest(true);
$store = $storeRepository->get('store_for_eu_website');
$cart->setStoreId($store->getId());
$storeManager->setCurrentStore($store->getCode());

/** @var AddressInterface $address */
$address = $addressFactory->create(
    [
        'data' => [
            AddressInterface::KEY_COUNTRY_ID => 'US',
            AddressInterface::KEY_REGION_ID => 15,
            AddressInterface::KEY_LASTNAME => 'Doe',
            AddressInterface::KEY_FIRSTNAME => 'John',
            AddressInterface::KEY_STREET => 'example street',
            AddressInterface::KEY_EMAIL => 'customer@example.com',
            AddressInterface::KEY_CITY => 'example city',
            AddressInterface::KEY_TELEPHONE => '000 0000',
            AddressInterface::KEY_POSTCODE => 12345
        ]
    ]
);
$cart->setReservedOrderId('created_order_for_test');
$cart->setBillingAddress($address);
$cart->setShippingAddress($address);
$cart->getPayment()->setMethod('checkmo');
$cart->getShippingAddress()->setShippingMethod('flatrate_flatrate');
$cart->getShippingAddress()->setCollectShippingRates(true);
$cart->getShippingAddress()->collectShippingRates();
$cartRepository->save($cart);
