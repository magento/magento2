<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Bundle\Model\Option;
use Magento\Catalog\Model\Product;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Bundle/_files/product_with_multiple_options_with_price.php');

$objectManager = Bootstrap::getObjectManager();

$addressData = include __DIR__ . '/../../../Magento/Sales/_files/address_data.php';


$billingAddress = $objectManager->create(Address::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

$payment = $objectManager->create(Payment::class);
$payment->setMethod('checkmo');

/** @var $product Product */
$product = $objectManager->create(Product::class);
$product->load(3);

/** @var $typeInstance \Magento\Bundle\Model\Product\Type */
$typeInstance = $product->getTypeInstance();
$typeInstance->setStoreFilter($product->getStoreId(), $product);

$optionCollection = $typeInstance->getOptionsCollection($product);

/** @var $storeManager StoreManagerInterface */
$storeManager = $objectManager->get(StoreManagerInterface::class);
$storeCurrency = $storeManager->getWebsite()->getDefaultStore()->getDefaultCurrency()->getCode();

$bundleOptions = $bundleOptionsQty = [];
foreach ($optionCollection as $option) {
    /** @var $option Option */
    $selectionsCollection = $typeInstance->getSelectionsCollection([$option->getId()], $product);
    $bundleOptions[$option->getId()] = $option->isMultiSelection() ?
        array_column($selectionsCollection->toArray(), 'selection_id') :
        $selectionsCollection->getFirstItem()->getSelectionId();
    $bundleOptionsQty[$option->getId()] = 1;
}

$requestInfo = [
    'product' => $product->getId(),
    'bundle_option' => $bundleOptions,
    'bundle_option_qty' => $bundleOptionsQty,
    'qty' => 1,
    'custom_price' => 300,
];

/** @var Item $orderItem */
$orderItem = $objectManager->create(Item::class);
$orderItem->setProductId($product->getId());
$orderItem->setQtyOrdered(1);
$orderItem->setBasePrice($product->getPrice());
$orderItem->setPrice($product->getPrice());
$orderItem->setRowTotal($product->getPrice());
$orderItem->setProductType($product->getTypeId());
$orderItem->setProductOptions([
    'info_buyRequest' => $requestInfo,
    'bundle_options' => [
        [
            'value' => [
                ['title' => $product->getName()]
            ],
        ],
    ],
    'bundle_selection_attributes' => '{"qty":5,"price":99}'
]);

/** @var Order $order */
$order = $objectManager->create(Order::class);
$order->setOrderCurrencyCode($storeCurrency);
$order->setIncrementId('100000001');
$order->setState(Order::STATE_NEW);
$order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_NEW));
$order->setCustomerIsGuest(true);
$order->setCustomerEmail('customer@null.com');
$order->setCustomerFirstname('firstname');
$order->setCustomerLastname('lastname');
$order->setBillingAddress($billingAddress);
$order->setShippingAddress($shippingAddress);
$order->setAddresses([$billingAddress, $shippingAddress]);
$order->setPayment($payment);
$order->addItem($orderItem);

$order->setStoreId($objectManager->get(StoreManagerInterface::class)->getStore()->getId());
$order->setSubtotal(100);
$order->setBaseSubtotal(100);
$order->setBaseGrandTotal(100);
$order->save();
