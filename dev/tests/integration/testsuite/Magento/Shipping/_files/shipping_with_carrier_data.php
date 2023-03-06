<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Framework\DB\Transaction;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order_with_customer.php');

$objectManager = Bootstrap::getObjectManager();
/** @var Transaction $transaction */
$transaction = $objectManager->get(Transaction::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$product = $productRepository->get('simple');
/** @var Order $order */
$order = $objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000001');
$order->setShippingDescription('UPS Next Day Air')
    ->setShippingMethod('ups_11')
    ->setShippingAmount(0)
    ->setCouponCode('1234567890')
    ->setDiscountDescription('1234567890');

/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->create(OrderRepositoryInterface::class);
$orderRepository->save($order);

$shipmentItems = [];
foreach ($order->getItems() as $orderItem) {
    $shipmentItems[$orderItem->getId()] = $orderItem->getQtyOrdered();
}
$tracking = [
    'carrier_code' => 'ups',
    'title' => 'United Parcel Service',
    'number' => '987654321'
];

$shipment = $objectManager->get(ShipmentFactory::class)->create($order, $shipmentItems, [$tracking]);
$shipment->register();
$transaction->addObject($shipment)->addObject($order)->save();
