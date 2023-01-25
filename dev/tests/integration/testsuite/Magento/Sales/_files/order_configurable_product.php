<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/ConfigurableProduct/_files/product_configurable.php');

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$configurableProduct = $productRepository->get('configurable');

$addressData = include __DIR__ . '/address_data.php';

/** @var \Magento\Sales\Model\Order\Address $billingAddress */
$billingAddress = $objectManager->create(\Magento\Sales\Model\Order\Address::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

/** @var Payment $payment */
$payment = $objectManager->create(Payment::class);
$payment->setMethod('checkmo')
    ->setAdditionalInformation([
        'token_metadata' => [
            'token'       => 'f34vjw',
            'customer_id' => 1,
        ],
    ]);

/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->create(\Magento\Sales\Model\Order::class);
$order->setIncrementId('100000001')
    ->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT)
    ->setStatus($order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT))
    ->setSubtotal(100)
    ->setGrandTotal(100)
    ->setBaseSubtotal(100)
    ->setBaseGrandTotal(100)
    ->setCustomerIsGuest(true)
    ->setCustomerEmail('customer@null.com')
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->setStoreId($objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore()->getId())
    ->setPayment($payment);
$order->save();

$qtyOrdered = 2;
/** @var \Magento\Sales\Model\Order\Item $orderItem */
$orderConfigurableItem = $objectManager->create(\Magento\Sales\Model\Order\Item::class);
$orderConfigurableItem->setProductId($configurableProduct->getId())->setQtyOrdered($qtyOrdered);
$orderConfigurableItem->setBasePrice($configurableProduct->getPrice());
$orderConfigurableItem->setPrice($configurableProduct->getPrice());
$orderConfigurableItem->setRowTotal($configurableProduct->getPrice());
$orderConfigurableItem->setParentItemId(null);
$orderConfigurableItem->setProductType('configurable');
$orderConfigurableItem->setOrder($order);
/** @var \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemsRepository */
$orderItemsRepository = $objectManager->create(\Magento\Sales\Api\OrderItemRepositoryInterface::class);
// Configurable item must be present in database to have its real ID to set parent id of simple product.
$orderItemsRepository->save($orderConfigurableItem);

if ($configurableProduct->getExtensionAttributes()
    && (array)$configurableProduct->getExtensionAttributes()->getConfigurableProductLinks()
) {
    $simpleProductId = current($configurableProduct->getExtensionAttributes()->getConfigurableProductLinks());
    /** @var \Magento\Catalog\Api\Data\ProductInterface $simpleProduct */
    $simpleProduct = $productRepository->getById($simpleProductId);
    $orderItem = $objectManager->create(\Magento\Sales\Api\Data\OrderItemInterface::class);
    $orderItem->setBasePrice($simpleProduct->getPrice());
    $orderItem->setPrice($simpleProduct->getPrice());
    $orderItem->setRowTotal($simpleProduct->getPrice());
    $orderItem->setProductType('simple');
    // duplicate behavior with simple product associated with configurable one that happens during order process.
    $orderItem->setProductId($simpleProduct->getId())->setQtyOrdered($qtyOrdered);
    $orderItem->setParentItemId($orderConfigurableItem->getId());
    $orderItem->setOrder($order);
    $orderItemsRepository->save($orderItem);
}
