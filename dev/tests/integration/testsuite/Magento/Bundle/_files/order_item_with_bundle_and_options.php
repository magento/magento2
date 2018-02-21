<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/product_with_multiple_options.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$addressData = include __DIR__ . '/../../../Magento/Sales/_files/address_data.php';

$billingAddress = $objectManager->create('Magento\Sales\Model\Order\Address', ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

$payment = $objectManager->create('Magento\Sales\Model\Order\Payment');
$payment->setMethod('checkmo');

/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create('Magento\Catalog\Model\Product');
$product->load(3);

/** @var $typeInstance \Magento\Bundle\Model\Product\Type */
$typeInstance = $product->getTypeInstance();
$typeInstance->setStoreFilter($product->getStoreId(), $product);
$optionCollection = $typeInstance->getOptionsCollection($product);

$bundleOptions = [];
$bundleOptionsQty = [];
foreach ($optionCollection as $option) {
    /** @var $option \Magento\Bundle\Model\Option */
    $selectionsCollection = $typeInstance->getSelectionsCollection([$option->getId()], $product);
    if ($option->isMultiSelection()) {
        $bundleOptions[$option->getId()] = array_column($selectionsCollection->toArray(), 'selection_id');
    } else {
        $bundleOptions[$option->getId()] = $selectionsCollection->getFirstItem()->getSelectionId();
    }
    $bundleOptionsQty[$option->getId()] = 1;
}

$requestInfo = [
    'product' => $product->getId(),
    'bundle_option' => $bundleOptions,
    'bundle_option_qty' => $bundleOptionsQty,
    'qty' => 1,
];

/** @var \Magento\Sales\Model\Order\Item $orderItem */
$orderItem = $objectManager->create('Magento\Sales\Model\Order\Item');
$orderItem->setProductId($product->getId());
$orderItem->setQtyOrdered(1);
$orderItem->setBasePrice($product->getPrice());
$orderItem->setPrice($product->getPrice());
$orderItem->setRowTotal($product->getPrice());
$orderItem->setProductType($product->getTypeId());
$orderItem->setProductOptions(['info_buyRequest' => $requestInfo]);

/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->create('Magento\Sales\Model\Order');
$order->setIncrementId('100000001');
$order->setState(\Magento\Sales\Model\Order::STATE_NEW);
$order->setStatus($order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_NEW));
$order->setCustomerIsGuest(true);
$order->setCustomerEmail('customer@null.com');
$order->setCustomerFirstname('firstname');
$order->setCustomerLastname('lastname');
$order->setBillingAddress($billingAddress);
$order->setShippingAddress($shippingAddress);
$order->setAddresses([$billingAddress, $shippingAddress]);
$order->setPayment($payment);
$order->addItem($orderItem);
$order->setStoreId($objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId());
$order->setSubtotal(100);
$order->setBaseSubtotal(100);
$order->setBaseGrandTotal(100);
$order->save();
