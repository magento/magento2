<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Sales\Api\Data\OrderAddressInterfaceFactory;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\Data\OrderPaymentInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderItemInterfaceFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;

require __DIR__ . '/../../../Magento/Customer/_files/customer_with_uk_address.php';
require __DIR__ . '/../../../Magento/Catalog/_files/product_simple_duplicated.php';
require __DIR__ . '/../../../Magento/Catalog/_files/products_new.php';

/** @var OrderInterfaceFactory $orderFactory */
$orderFactory = $objectManager->get(OrderInterfaceFactory::class);
/** @var OrderItemInterfaceFactory $orderItemFactory */
$orderItemFactory = $objectManager->get(OrderItemInterfaceFactory::class);
/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->get(OrderRepositoryInterface::class);
/** @var OrderAddressInterfaceFactory $orderAddressFactory */
$orderAddressFactory = $objectManager->get(OrderAddressInterfaceFactory::class);
/** @var OrderPaymentInterfaceFactory $orderPaymentFactory */
$orderPaymentFactory = $objectManager->get(OrderPaymentInterfaceFactory::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->cleanCache();

$billingAddress = $orderAddressFactory->create(['data' => $customerAddress->getData()]);
$billingAddress->setAddressType(Address::TYPE_BILLING);

$shippingAddress = $orderAddressFactory->create(['data' => $customerAddress->getData()]);
$shippingAddress->setAddressType(Address::TYPE_SHIPPING)
    ->setStreet('street for shipping')
    ->setRegion('North West')
    ->setPostcode('GU16 7HF')
    ->setShippingMethod('flatrate_flatrate');

$orderPayment = $orderPaymentFactory->create();
$orderPayment->setMethod('checkmo')
    ->setAdditionalInformation('last_trans_id', '11122')
    ->setAdditionalInformation(
        'metadata',
        ['type' => 'free', 'fraudulent' => false]
    );

$firstProduct = $productRepository->get('simple-1');
$firstOrderItem = $orderItemFactory->create();
$firstOrderItem->setProductId($firstProduct->getId())
    ->setQtyOrdered(1)
    ->setBasePrice($firstProduct->getPrice())
    ->setPrice($firstProduct->getPrice())
    ->setRowTotal($firstProduct->getPrice())
    ->setProductType(Type::TYPE_SIMPLE)
    ->setName($firstProduct->getName())
    ->setSku($firstProduct->getSku());

$secondProduct = $productRepository->get('simple');
$secondOrderItem = $orderItemFactory->create();
$secondOrderItem->setProductId($secondProduct->getId())
    ->setQtyOrdered(1)
    ->setBasePrice($secondProduct->getPrice())
    ->setPrice($secondProduct->getPrice())
    ->setRowTotal($secondProduct->getPrice())
    ->setProductType(Type::TYPE_SIMPLE)
    ->setName($secondProduct->getName())
    ->setSku($secondProduct->getSku());

$order = $orderFactory->create();
$order->setIncrementId('100000555')
    ->setState(Order::STATE_PROCESSING)
    ->setStatus(Order::STATE_PROCESSING)
    ->setSubtotal(20)
    ->setShippingAmount(10)
    ->setGrandTotal(30)
    ->setBaseSubtotal(20)
    ->setBaseShippingAmount(10)
    ->setBaseGrandTotal(30)
    ->setCustomerIsGuest(false)
    ->setCustomerEmail($customerDataModel->getEmail())
    ->setCustomerId($customerDataModel->getId())
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->setShippingDescription('Flat Rate - Fixed')
    ->setStoreId($mainWebsite->getDefaultStore()->getId())
    ->addItem($firstOrderItem)
    ->addItem($secondOrderItem)
    ->setPayment($orderPayment);
$orderRepository->save($order);
