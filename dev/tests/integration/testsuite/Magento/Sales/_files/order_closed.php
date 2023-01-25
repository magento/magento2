<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\DB\Transaction;
use Magento\OfflinePayments\Model\Checkmo;
use Magento\Sales\Api\CreditmemoItemRepositoryInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterfaceFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderPaymentInterfaceFactory;
use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Api\Data\OrderItemInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\AddressFactory;
use Magento\Sales\Api\Data\OrderAddressInterfaceFactory;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple.php');

$objectManager = Bootstrap::getObjectManager();
/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->cleanCache();
/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->get(OrderRepositoryInterface::class);
/** @var InvoiceManagementInterface $invoiceService */
$invoiceService = $objectManager->get(InvoiceManagementInterface::class);
/** @var ShipmentFactory $shipmentFactory */
$shipmentFactory = $objectManager->get(ShipmentFactory::class);
/** @var CreditmemoFactory $creditmemoFactory */
$creditmemoFactory = $objectManager->get(CreditmemoFactory::class);
/** @var CreditmemoItemInterfaceFactory $creditmemoItemFactory */
$creditmemoItemFactory = $objectManager->get(CreditmemoItemInterfaceFactory::class);
/** @var CreditmemoRepositoryInterface $creditmemoRepository */
$creditmemoRepository = $objectManager->get(CreditmemoRepositoryInterface::class);
/** @var CreditmemoItemRepositoryInterface $creditmemoItemRepository */
$creditmemoItemRepository = $objectManager->get(CreditmemoItemRepositoryInterface::class);
$addressData = [
    AddressInterface::REGION => 'CA',
    AddressInterface::REGION_ID => '12',
    AddressInterface::POSTCODE => '11111',
    AddressInterface::LASTNAME => 'lastname',
    AddressInterface::FIRSTNAME => 'firstname',
    AddressInterface::STREET => 'street',
    AddressInterface::CITY => 'Los Angeles',
    CustomerInterface::EMAIL => 'admin@example.com',
    AddressInterface::TELEPHONE => '11111111',
    AddressInterface::COUNTRY_ID => 'US',
];
$product = $productRepository->get('simple');
/** @var AddressFactory $addressFactory */
$addressFactory = $objectManager->get(AddressFactory::class);
$billingAddress = $addressFactory->create(['data' => $addressData]);
$billingAddress->setAddressType(Address::TYPE_BILLING);
$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType(Address::TYPE_SHIPPING);
/** @var OrderPaymentInterfaceFactory $paymentFactory */
$paymentFactory = $objectManager->get(OrderPaymentInterfaceFactory::class);
$payment = $paymentFactory->create();
$payment->setMethod(Checkmo::PAYMENT_METHOD_CHECKMO_CODE)
    ->setAdditionalInformation('last_trans_id', '11122')
    ->setAdditionalInformation('metadata', ['type' => 'free', 'fraudulent' => false]);
/** @var OrderItemInterface $orderItem */
$orderItem = $objectManager->get(OrderItemInterfaceFactory::class)->create();
$orderItem->setProductId($product->getId())
    ->setQtyOrdered(2)
    ->setBasePrice($product->getPrice())
    ->setPrice($product->getPrice())
    ->setRowTotal($product->getPrice())
    ->setProductType('simple')
    ->setName($product->getName())
    ->setSku($product->getSku())
    ->setName('Test item');
/** @var  OrderInterface $order */
$order = $objectManager->get(OrderInterfaceFactory::class)->create();
$order->setIncrementId('100001111')
    ->setState(Order::STATE_PROCESSING)
    ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING))
    ->setSubtotal(100)
    ->setGrandTotal(100)
    ->setBaseSubtotal(100)
    ->setBaseGrandTotal(100)
    ->setOrderCurrencyCode('USD')
    ->setBaseCurrencyCode('USD')
    ->setCustomerIsGuest(true)
    ->setCustomerEmail('customer@null.com')
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->addItem($orderItem)
    ->setPayment($payment)
    ->setStoreId($storeManager->getStore('default')->getId());
$orderRepository->save($order);

$invoice = $invoiceService->prepareInvoice($order);
$invoice->register();
$invoice->setIncrementId($order->getIncrementId());
$order = $invoice->getOrder();
$order->setIsInProcess(true);
$transactionSave = $objectManager->create(Transaction::class);
$transactionSave->addObject($invoice)->addObject($order)->save();

$items = [];
foreach ($order->getItems() as $item) {
    $items[$item->getId()] = $item->getQtyOrdered();
}

$shipment = $objectManager->get(ShipmentFactory::class)->create($order, $items);
$shipment->register();
$shipment->setIncrementId($order->getIncrementId());
$transactionSave = $objectManager->create(Transaction::class);
$transactionSave->addObject($shipment)->addObject($order)->save();
/** @var CreditmemoFactory $creditmemoFactory */
$creditmemoFactory = $objectManager->get(CreditmemoFactory::class);
$creditmemo = $creditmemoFactory->createByOrder($order, $order->getData());
$creditmemo->setOrder($order);
$creditmemo->setState(Creditmemo::STATE_OPEN);
$creditmemo->setIncrementId($order->getIncrementId());
$creditmemoRepository->save($creditmemo);

$orderItem->setName('Test item')
    ->setQtyRefunded(2)
    ->setQtyInvoiced(2)
    ->setOriginalPrice($product->getPrice());
$creditItem = $creditmemoItemFactory->create();
$creditItem->setCreditmemo($creditmemo)
    ->setName('Creditmemo item')
    ->setOrderItemId($orderItem->getId())
    ->setQty(2)
    ->setPrice($product->getPrice());
$creditmemoItemRepository->save($creditItem);
