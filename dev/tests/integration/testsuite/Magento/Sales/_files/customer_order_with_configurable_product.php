<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/ConfigurableProduct/_files/product_configurable.php');

$objectManager = Bootstrap::getObjectManager();
/** @var CustomerRegistry $customerRegistry */
$customerRegistry = $objectManager->create(CustomerRegistry::class);
$customer = $customerRegistry->retrieve(1);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$configurableProduct = $productRepository->get('configurable');

$addressData = include __DIR__ . '/address_data.php';

/** @var Address $billingAddress */
$billingAddress = $objectManager->create(Address::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

/** @var Payment $payment */
$payment = $objectManager->create(Payment::class);
$payment->setMethod('checkmo')
    ->setAdditionalInformation([
        'token_metadata' => [
            'token'       => 'f34vjw',
            'customer_id' => $customer->getId(),
        ],
    ]);

/** @var Order $order */
$order = $objectManager->create(Order::class);
/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->create(OrderRepositoryInterface::class);

$order->setIncrementId('100000001')
    ->setState(Order::STATE_PENDING_PAYMENT)
    ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PENDING_PAYMENT))
    ->setSubtotal(100)
    ->setGrandTotal(100)
    ->setBaseSubtotal(100)
    ->setBaseGrandTotal(100)
    ->setCustomerIsGuest(false)
    ->setCustomerId($customer->getId())
    ->setCustomerEmail($customer->getEmail())
    ->setCustomerFirstname($customer->getName())
    ->setCustomerLastname($customer->getLastname())
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->setStoreId($objectManager->get(StoreManagerInterface::class)->getStore()->getId())
    ->setPayment($payment);
$orderRepository->save($order);

$qtyOrdered = 2;
/** @var Item $orderItem */
$orderConfigurableItem = $objectManager->create(Item::class);
$orderConfigurableItem->setProductId($configurableProduct->getId())->setQtyOrdered($qtyOrdered);
$orderConfigurableItem->setBasePrice($configurableProduct->getPrice());
$orderConfigurableItem->setPrice($configurableProduct->getPrice());
$orderConfigurableItem->setRowTotal($configurableProduct->getPrice());
$orderConfigurableItem->setParentItemId(null);
$orderConfigurableItem->setProductType('configurable');
$orderConfigurableItem->setSku($configurableProduct->getSku());
$orderConfigurableItem->setName($configurableProduct->getName());
$orderConfigurableItem->setOrder($order);

/** @var OrderItemRepositoryInterface $orderItemsRepository */
$orderItemsRepository = $objectManager->create(OrderItemRepositoryInterface::class);
// Configurable item must be present in database to have its real ID to set parent id of simple product.
$orderItemsRepository->save($orderConfigurableItem);

if ($configurableProduct->getExtensionAttributes()
    && (array)$configurableProduct->getExtensionAttributes()->getConfigurableProductLinks()
) {
    $simpleProductId = current($configurableProduct->getExtensionAttributes()->getConfigurableProductLinks());
    /** @var ProductInterface $simpleProduct */
    $simpleProduct = $productRepository->getById($simpleProductId);
    $orderItem = $objectManager->create(\Magento\Sales\Api\Data\OrderItemInterface::class);
    $orderItem->setBasePrice($simpleProduct->getPrice());
    $orderItem->setPrice($simpleProduct->getPrice());
    $orderItem->setRowTotal($simpleProduct->getPrice());
    $orderItem->setProductType('simple');
    $orderItem->setSku($simpleProduct->getSku());
    $orderItem->setName($simpleProduct->getName());
    // duplicate behavior with simple product associated with configurable one that happens during order process.
    $orderItem->setProductId($simpleProduct->getId())->setQtyOrdered($qtyOrdered);
    $orderItem->setParentItemId($orderConfigurableItem->getId());
    $orderItem->setOrder($order);
    $orderConfigurableItem->setSku($simpleProduct->getSku());
    $orderItemsRepository->save($orderItem);
    $orderItemsRepository->save($orderConfigurableItem);
}
