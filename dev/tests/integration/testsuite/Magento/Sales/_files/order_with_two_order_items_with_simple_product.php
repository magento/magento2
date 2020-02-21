<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Sales\Api\Data\OrderAddressInterfaceFactory;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\Data\OrderItemInterfaceFactory;
use Magento\Sales\Api\Data\OrderPaymentInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Store\Model\StoreManagerInterface;

require __DIR__ . '/../../../Magento/Customer/_files/customer.php';
require __DIR__ . '/../../../Magento/Catalog/_files/products.php';

$addressData = include __DIR__ . '/address_data.php';

/** @var OrderAddressInterfaceFactory $addressFactory */
$addressFactory = $objectManager->get(OrderAddressInterfaceFactory::class);
/** @var OrderPaymentInterfaceFactory $paymentFactory */
$paymentFactory = $objectManager->get(OrderPaymentInterfaceFactory::class);
/** @var OrderInterfaceFactory $orderFactory */
$orderFactory = $objectManager->get(OrderInterfaceFactory::class);
/** @var OrderItemInterfaceFactory $orderItemFactory */
$orderItemFactory = $objectManager->get(OrderItemInterfaceFactory::class);
/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);
/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->get(OrderRepositoryInterface::class);

$billingAddress = $addressFactory->create(['data' => $addressData]);
$billingAddress->setAddressType(Address::TYPE_BILLING);
$shippingAddress = $addressFactory->create(['data' => $addressData]);
$shippingAddress->setAddressType(Address::TYPE_SHIPPING);
$payment = $paymentFactory->create();
$payment->setMethod('checkmo')->setAdditionalInformation(
    [
        'last_trans_id' => '11122',
        'metadata' => [
            'type' => 'free',
            'fraudulent' => false,
        ]
    ]
);

$defaultStoreId = $storeManager->getStore('default')->getId();
$order = $orderFactory->create();
$order->setIncrementId('100000001')
    ->setState(Order::STATE_PROCESSING)
    ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING))
    ->setSubtotal(20)
    ->setGrandTotal(20)
    ->setBaseSubtotal(20)
    ->setBaseGrandTotal(20)
    ->setCustomerIsGuest(false)
    ->setCustomerId($customer->getId())
    ->setCustomerEmail($customer->getEmail())
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->setStoreId($defaultStoreId)
    ->setPayment($payment);

$orderItem = $orderItemFactory->create();
$orderItem->setProductId($product->getId())
    ->setQtyOrdered(5)
    ->setBasePrice($product->getPrice())
    ->setPrice($product->getPrice())
    ->setRowTotal($product->getPrice())
    ->setProductType($product->getTypeId())
    ->setName($product->getName())
    ->setSku($product->getSku());
$order->addItem($orderItem);

$orderItem = $orderItemFactory->create();
$orderItem->setProductId($customDesignProduct->getId())
    ->setQtyOrdered(5)
    ->setBasePrice($customDesignProduct->getPrice())
    ->setPrice($customDesignProduct->getPrice())
    ->setRowTotal($customDesignProduct->getPrice())
    ->setProductType($customDesignProduct->getTypeId())
    ->setName($customDesignProduct->getName())
    ->setSku($customDesignProduct->getSku());
$order->addItem($orderItem);
$orderRepository->save($order);
