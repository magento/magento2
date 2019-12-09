<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Item;
use Magento\TestFramework\Helper\Bootstrap;

$addressData = include __DIR__ . '/../../../Magento/Sales/_files/address_data.php';
require __DIR__ . '/../../../Magento/Customer/_files/customer.php';

$objectManager = Bootstrap::getObjectManager();

/** @var $product Product */
$product = $objectManager->create(Product::class);
$product->isObjectNew(true);
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setId(1)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product')
    ->setSku('simple')
    ->setPrice(10)
    ->setWeight(1)
    ->setShortDescription('Short description')
    ->setTaxClassId(0)
    ->setDescription('Description with <b>html tag</b>')
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(
        [
            'use_config_manage_stock'   => 1,
            'qty'                       => 100,
            'is_qty_decimal'            => 0,
            'is_in_stock'               => 1,
        ]
    )->setCanSaveCustomOptions(true)
    ->setHasOptions(false);

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$productRepository->save($product);


$billingAddress = $objectManager->create(Address::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)
    ->setAddressType('shipping')
    ->setStreet(['6161 West Centinela Avenue'])
    ->setFirstname('John')
    ->setLastname('Doe')
    ->setShippingMethod('flatrate_flatrate');

/** @var Payment $payment */
$payment = $objectManager->create(Payment::class);
$payment->setAdditionalInformation('ccLast4', '1111');
$payment->setAdditionalInformation('opaqueDataDescriptor', 'mydescriptor');
$payment->setAdditionalInformation('opaqueDataValue', 'myvalue');

/** @var Item $orderItem */
$orderItem1 = $objectManager->create(Item::class);
$orderItem1->setProductId($product->getId())
    ->setSku($product->getSku())
    ->setName($product->getName())
    ->setQtyOrdered(1)
    ->setBasePrice($product->getPrice())
    ->setPrice($product->getPrice())
    ->setRowTotal($product->getPrice())
    ->setProductType($product->getTypeId());

/** @var Item $orderItem */
$orderItem2 = $objectManager->create(Item::class);
$orderItem2->setProductId($product->getId())
    ->setSku('simple2')
    ->setName('Simple product')
    ->setPrice(100)
    ->setQtyOrdered(2)
    ->setBasePrice($product->getPrice())
    ->setPrice($product->getPrice())
    ->setRowTotal($product->getPrice())
    ->setProductType($product->getTypeId());

$orderAmount = 100;
$customerEmail = $billingAddress->getEmail();

/** @var Order $order */
$order = $objectManager->create(Order::class);
$order->setIncrementId('100000001')
    ->setState(Order::STATE_PROCESSING)
    ->setStatus(Order::STATE_PROCESSING)
    ->setCustomerId($customer->getId())
    ->setCustomerIsGuest(false)
    ->setRemoteIp('127.0.0.1')
    ->setCreatedAt(date('Y-m-d 00:00:55'))
    ->setOrderCurrencyCode('USD')
    ->setBaseCurrencyCode('USD')
    ->setSubtotal($orderAmount)
    ->setGrandTotal($orderAmount)
    ->setBaseSubtotal($orderAmount)
    ->setBaseGrandTotal($orderAmount)
    ->setCustomerEmail($customerEmail)
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->setShippingDescription('Flat Rate - Fixed')
    ->setShippingAmount(10)
    ->setBaseShippingAmount(10)
    ->setStoreId(1)
    ->addItem($orderItem1)
    ->addItem($orderItem2)
    ->setQuoteId(1)
    ->setPayment($payment);

return $order;
