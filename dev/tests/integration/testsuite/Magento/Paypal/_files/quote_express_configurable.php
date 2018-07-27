<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/../../../Magento/ConfigurableProduct/_files/product_configurable.php';

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();

/** @var \Magento\Catalog\Model\Product $product */
/** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$product = $productRepository->get('configurable');

/** @var Collection $options */
$options = $objectManager->create(Collection::class);
$option = $options->setAttributeFilter($attribute->getId())->getFirstItem();

$requestInfo = new \Magento\Framework\DataObject(
    [
        'product' => 1,
        'selected_configurable_option' => 1,
        'qty' => 100,
        'super_attribute' => [
            $attribute->getId() => $option->getId(),
        ],
    ]
);

/** @var Cart $cart */
$cart = $objectManager->create(Cart::class);
$cart->addProduct($product, $requestInfo);

/** @var Rate $rate */
$rate = $objectManager->create(Rate::class);
$rate->setCode('flatrate_flatrate');
$rate->setPrice(1);

$addressData = include __DIR__ . '/address_data.php';
$billingAddress = $objectManager->create(Address::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)
    ->setAddressType('shipping')
    ->setShippingMethod('flatrate_flatrate')
    ->addShippingRate($rate);

$cart->getQuote()
    ->setReservedOrderId('test_cart_with_configurable')
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress);

$cart->save();
