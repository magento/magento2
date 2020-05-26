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
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/ConfigurableProduct/_files/product_configurable.php');

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = Bootstrap::getObjectManager();

/** @var $product \Magento\Catalog\Model\Product */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$product = $productRepository->get('configurable');
/** @var \Magento\Eav\Model\Config $eavConfig */
$eavConfig = $objectManager->get(\Magento\Eav\Model\Config::class);
$attribute = $eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'test_configurable');
/** @var $options Collection */
$options = $objectManager->create(Collection::class);
$option = $options->setAttributeFilter($attribute->getId())->getFirstItem();

$requestInfo = new \Magento\Framework\DataObject(
    [
        'product' => 1,
        'selected_configurable_option' => 1,
        'qty' => 100,
        'super_attribute' => [
            $attribute->getId() => $option->getId()
        ]
    ]
);

/** @var $cart Cart */
$cart = $objectManager->create(Cart::class);
$cart->addProduct($product, $requestInfo);

/** @var $rate Rate */
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
