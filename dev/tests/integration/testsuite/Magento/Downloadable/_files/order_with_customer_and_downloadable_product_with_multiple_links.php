<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Downloadable\Api\Data\LinkInterface;
use Magento\Downloadable\Model\Product\Type;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\AddressFactory;
use Magento\Sales\Model\Order\ItemFactory;
use Magento\Sales\Model\Order\PaymentFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()
    ->requireDataFixture('Magento/Downloadable/_files/product_downloadable_with_purchased_separately_links.php');
Resolver::getInstance()
    ->requireDataFixture('Magento/Customer/_files/customer.php');

$addressData = include __DIR__ . '/../../../Magento/Sales/_files/address_data.php';
$objectManager = Bootstrap::getObjectManager();
/** @var CustomerRegistry $customerRegistry */
$customerRegistry = $objectManager->create(CustomerRegistry::class);
$customer = $customerRegistry->retrieve(1);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
/** @var AddressFactory $addressFactory */
$addressFactory = $objectManager->get(AddressFactory::class);
$billingAddress = $addressFactory->create(['data' => $addressData]);
$billingAddress->setAddressType(Address::TYPE_BILLING);
/** @var ItemFactory $orderItemFactory */
$orderItemFactory = $objectManager->get(ItemFactory::class);
/** @var PaymentFactory $orderPaymentFactory */
$orderPaymentFactory = $objectManager->get(PaymentFactory::class);
/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);
/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->create(OrderRepositoryInterface::class);
/** @var OrderFactory $orderFactory */
$orderFactory = $objectManager->get(OrderFactory::class);

$payment = $orderPaymentFactory->create();
$payment->setMethod('checkmo')
    ->setAdditionalInformation('last_trans_id', '11122')
    ->setAdditionalInformation(
        'metadata',
        ['type' => 'free', 'fraudulent' => false]
    );
/** @var ProductInterface $product */
$product = $productRepository->get('downloadable-product-with-purchased-separately-links');
/** @var LinkInterface $links */
$links = $product->getExtensionAttributes()->getDownloadableProductLinks();
$link = reset($links);

$orderItem = $orderItemFactory->create();
$orderItem->setProductId($product->getId())
    ->setQtyOrdered(1)
    ->setBasePrice($product->getPrice())
    ->setProductOptions(['links' => [$link->getId()]])
    ->setPrice($product->getPrice())
    ->setRowTotal($product->getPrice())
    ->setProductType(Type::TYPE_DOWNLOADABLE)
    ->setName($product->getName())
    ->setSku($product->getSku());

$order = $orderFactory->create();
$order->setIncrementId('100000002')
    ->setState(Order::STATE_PROCESSING)
    ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING))
    ->setSubtotal(100)
    ->setGrandTotal(100)
    ->setBaseSubtotal(100)
    ->setBaseGrandTotal(100)
    ->setCustomerId($customer->getId())
    ->setCustomerEmail($customer->getEmail())
    ->setBillingAddress($billingAddress)
    ->setStoreId($storeManager->getStore()->getId())
    ->addItem($orderItem)
    ->setPayment($payment);

$orderRepository->save($order);
