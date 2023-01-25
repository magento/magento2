<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Registry;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = Bootstrap::getObjectManager();
/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->get(OrderRepositoryInterface::class);
/** @var InvoiceRepositoryInterface $invoiceRepository */
$invoiceRepository = $objectManager->get(InvoiceRepositoryInterface::class);
/** @var ShipmentRepositoryInterface $shipmentRepository */
$shipmentRepository = $objectManager->get(ShipmentRepositoryInterface::class);
/** @var CreditmemoRepositoryInterface $creditmemoRepository */
$creditmemoRepository = $objectManager->get(CreditmemoRepositoryInterface::class);
/** @var OrderInterface $order */
$order = $objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('200000001');
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

foreach ($order->getInvoiceCollection() as $invoice) {
    $invoiceRepository->delete($invoice);
}
foreach ($order->getShipmentsCollection() as $shipment) {
    $shipmentRepository->delete($shipment);
}
foreach ($order->getCreditmemosCollection() as $creditMemo) {
    $creditmemoRepository->delete($creditMemo);
}
$orderRepository->delete($order);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple_rollback.php');
Resolver::getInstance()->requireDataFixture(
    'Magento/Store/_files/second_website_with_store_group_and_store_rollback.php'
);
