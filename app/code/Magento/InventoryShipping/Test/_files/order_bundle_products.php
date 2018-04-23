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
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\ProductRepositoryInterface;

/** @var CartRepositoryInterface $cartRepository */
$cartRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
/** @var CartManagementInterface $cartManagement */
$cartManagement = Bootstrap::getObjectManager()->get(CartManagementInterface::class);
/** @var AddressInterfaceFactory $addressFactory */
$addressFactory = Bootstrap::getObjectManager()->get(AddressInterfaceFactory::class);

$itemsToBuy = [
    'SKU-BUNDLE-1' => ['qty' => 2, 'options_qty' => [3, 4]],
    'SKU-BUNDLE-2' => ['qty' => 3, 'options_qty' => [5, 6]]
];

$cartId = $cartManagement->createEmptyCart();
$cart = $cartRepository->get($cartId);
$cart->setCustomerEmail('admin@example.com');
$cart->setCustomerIsGuest(true);
/** @var AddressInterface $address */
$address = $addressFactory->create(
    [
        'data' => [
            AddressInterface::KEY_COUNTRY_ID => 'US',
            AddressInterface::KEY_REGION_ID  => 15,
            AddressInterface::KEY_LASTNAME   => 'Doe',
            AddressInterface::KEY_FIRSTNAME  => 'John',
            AddressInterface::KEY_STREET     => 'example street',
            AddressInterface::KEY_EMAIL      => 'customer@example.com',
            AddressInterface::KEY_CITY       => 'example city',
            AddressInterface::KEY_TELEPHONE  => '000 0000',
            AddressInterface::KEY_POSTCODE   => 12345
        ]
    ]
);
$cart->setReservedOrderId('test_order_bundle_1');
$cart->setBillingAddress($address);
$cart->setShippingAddress($address);
$cart->getPayment()->setMethod('checkmo');
$cart->getShippingAddress()->setShippingMethod('flatrate_flatrate');
$cart->getShippingAddress()->setCollectShippingRates(true);
$cart->getShippingAddress()->collectShippingRates();

foreach ($itemsToBuy as $sku => $qtyData) {
    $product = $productRepository->get($sku);
    $options = $product->getTypeInstance()->getOptions($product);
    $optionsData = [];
    $optionsQtyData = [];
    $i = 0;
    foreach ($options as $option) {
        $optionsData[$option->getId()] = $option->getId();
        $optionsQtyData[$option->getId()] = $qtyData['options_qty'][$i];
        $i++;
    }
    $requestData = [
        'product'           => $product->getProductId(),
        'qty'               => $qtyData['qty'],
        'bundle_option'     => $optionsData,
        'bundle_option_qty' => $optionsQtyData,
    ];
    $request = new \Magento\Framework\DataObject($requestData);
    $cart->addProduct($product, $request);
}

$cartRepository->save($cart);
$cartManagement->placeOrder($cart->getId());
