<?php

use Magento\Sales\Api\OrderRepositoryInterface;

require __DIR__ . '/../../../Magento/Customer/_files/customer.php';
require __DIR__ . '/../../../Magento/Catalog/_files/product_simple.php';

/** \Magento\Customer\Model\Customer $customer */
$addressData = include __DIR__ . '/../../../Magento/Sales/_files/address_data.php';
$billingAddress = $objectManager->create(\Magento\Sales\Model\Order\Address::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');
$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$payment = $objectManager->create(\Magento\Sales\Model\Order\Payment::class);
$payment->setMethod('checkmo');
$customerIdFromFixture = 1;
$requestInfo = [
    'qty' => 1,
];

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
$orderItem->setSku($product->getSku());
$orderItem->setStoreId(0);
//$orderItemSimple->setProductId($simpleProduct->getId());
//$orderItemSimple->setParentItem($orderItem);
//$orderItemSimple->setStoreId(0);
//$orderItemSimple->setProductType($simpleProduct->getTypeId());
//$orderItemSimple->setProductOptions(['info_buyRequest' => $requestInfo]);
//$orderItemSimple->setSku($simpleProduct->getSku());


/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->create(\Magento\Sales\Model\Order::class);
$order->setIncrementId('100001001');
$order->setState(\Magento\Sales\Model\Order::STATE_NEW);
$order->setStatus($order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_NEW));
$order->setCustomerIsGuest(false);
$order->setCustomerId($customer->getId());
$order->setCustomerEmail($customer->getEmail());
$order->setCustomerFirstname($customer->getName());
$order->setCustomerLastname($customer->getLastname());
$order->setBillingAddress($billingAddress);
$order->setShippingAddress($shippingAddress);
$order->setAddresses([$billingAddress, $shippingAddress]);
$order->setPayment($payment);
$order->addItem($orderItem);
//$order->addItem($orderItemSimple);
$order->setStoreId($objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore()->getId());
$order->setSubtotal(100);
$order->setBaseSubtotal(100);
$order->setBaseGrandTotal(100);

$orderRepository = $objectManager->create(OrderRepositoryInterface::class);
$orderRepository->save($order);











/** @var \Magento\Catalog\Model\Product $product */
/** @var \Magento\Sales\Model\Order $order */
//$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

//require __DIR__ . '/../../../Magento/Catalog/_files/product_simple_duplicated.php';





/** @var $product \Magento\Catalog\Model\Product */
//$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
//$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
//    ->setAttributeSetId(4)
//    ->setWebsiteIds([1])
//    ->setName('Simple Product')
//    ->setSku('simple-2')
//    ->setPrice(10)
//    ->setDescription('Description with <b>html tag</b>')
//    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
//    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
//    ->setCategoryIds([2])
//    ->set
//    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
//    ->setUrlKey('simple-product-2')
//    ->save();

//
//$orderItems[] = [
//    'product_id' => $product->getId(),
//    'base_price' => 123,
//    'order_id' => $order->getId(),
//    'price' => 123,
//    'row_total' => 126,
//    'product_type' => 'simple'
//];

/** @var array $orderItemData */
//foreach ($orderItems as $orderItemData) {
//
//    $requestInfo = [
//        'product' => $orderItemData['product_id'],
//        'qty' => 1,
//    ];
//    /** @var $orderItem \Magento\Sales\Model\Order\Item */
//    $orderItem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
//        \Magento\Sales\Model\Order\Item::class
//    );
//    $orderItem->setProductOptions(['info_buyRequest' => $requestInfo]);
//    $orderItem->setData($orderItemData)->save();
//    $order->addItem($orderItem);
//}


/** @var OrderRepositoryInterface $orderRepository */
//$orderRepository = $objectManager->create(OrderRepositoryInterface::class);
//$orderRepository->save($order);

//$customerIdFromFixture = 1;
///** @var $order \Magento\Sales\Model\Order */
//$order->setCustomerId($customerIdFromFixture)->setCustomerIsGuest(false)->save();

