<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductRepository;

require __DIR__ . '/../../../Magento/ConfigurableProduct/_files/product_configurable.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$addressData = include __DIR__ . '/../../../Magento/Sales/_files/address_data.php';

$billingAddress = $objectManager->create(\Magento\Sales\Model\Order\Address::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

$payment = $objectManager->create(\Magento\Sales\Model\Order\Payment::class);
$payment->setMethod('checkmo');

/** @var ProductRepository $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$product = $productRepository->getById(1);
/** @var \Magento\Catalog\Model\Product $productSimple */
$simpleProduct = $productRepository->getById(20);


/** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
$eavConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class);
$attribute = $eavConfig->getAttribute('catalog_product', 'test_configurable');

/** @var $options \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection */
$options = $objectManager->create(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection::class);
$option = $options->setAttributeFilter($attribute->getId())
    ->getFirstItem();

$requestInfo = [
    'qty' => 1,
    'super_attribute' => [
        $attribute->getId() => $option->getId(),
    ],
];
/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->create(\Magento\Sales\Model\Order::class);
$order->setIncrementId('100000001');
$order->loadByIncrementId('100000001');
if ($order->getId()) {
    $order->delete();
}
/** @var \Magento\Sales\Model\Order\Item $orderItem */
$orderItem = $objectManager->create(\Magento\Sales\Model\Order\Item::class);
$orderItemSimple = clone $orderItem;
$orderItem->setProductId($product->getId());
$orderItem->setQtyOrdered(1);
$orderItem->setBasePrice($product->getPrice());
$orderItem->setPrice($product->getPrice());
$orderItem->setRowTotal($product->getPrice());
$orderItem->setProductType($product->getTypeId());
$orderItem->setProductOptions(['info_buyRequest' => $requestInfo]);
$orderItem->setName($product->getName());
$orderItem->setSku($simpleProduct->getSku());
$orderItemSimple->setProductId($simpleProduct->getId());
$orderItemSimple->setParentItem($orderItem);
$orderItemSimple->setStoreId(0);
$orderItemSimple->setProductType($simpleProduct->getTypeId());
$orderItemSimple->setProductOptions(['info_buyRequest' => $requestInfo]);
$orderItemSimple->setSku($simpleProduct->getSku());

/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->create(\Magento\Sales\Model\Order::class);
$order->setIncrementId('100001001');
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
$order->addItem($orderItemSimple);
$order->setStoreId($objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore()->getId());
$order->setSubtotal(100);
$order->setBaseSubtotal(100);
$order->setBaseGrandTotal(100);
$order->save();
// Change attribute value for simple of configurable
$simpleProduct->setData('test_configurable', 100);
$simpleProduct->save();
$simpleProduct->isAvailable();
