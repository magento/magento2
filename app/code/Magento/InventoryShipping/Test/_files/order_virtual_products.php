<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\ProductRepositoryInterface;

/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
/** @var CartRepositoryInterface $cartRepository */
$cartRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
/** @var CartItemInterfaceFactory $cartItemFactory */
$cartItemFactory = Bootstrap::getObjectManager()->get(CartItemInterfaceFactory::class);
/** @var CartManagementInterface $cartManagement */
$cartManagement = Bootstrap::getObjectManager()->get(CartManagementInterface::class);
/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = Bootstrap::getObjectManager()->get(OrderRepositoryInterface::class);
/** @var AddressInterfaceFactory $addressFactory */
$addressFactory = Bootstrap::getObjectManager()->get(AddressInterfaceFactory::class);

$itemsToBuy = [
    'VIRT-1' => 5,
    'VIRT-2' => 6
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
$cart->setReservedOrderId('test_order_virt_1');
$cart->setBillingAddress($address);
$cart->setShippingAddress($address);
$cart->getPayment()->setMethod('checkmo');
$cart->getShippingAddress()->setShippingMethod('flatrate_flatrate');
$cart->getShippingAddress()->setCollectShippingRates(true);
$cart->getShippingAddress()->collectShippingRates();

foreach ($itemsToBuy as $sku => $qty) {
    $product = $productRepository->get($sku);
    $cartItem = $cartItemFactory->create(
        [
            'data' => [
                CartItemInterface::KEY_SKU => $product->getSku(),
                CartItemInterface::KEY_QTY => $qty,
                CartItemInterface::KEY_QUOTE_ID => (int)$cart->getId(),
                'product_id' => (int)$product->getId(),
                'product' => $product,
            ]
        ]
    );
    $cart->addItem($cartItem);
}

$cartRepository->save($cart);
$cartManagement->placeOrder($cart->getId());
