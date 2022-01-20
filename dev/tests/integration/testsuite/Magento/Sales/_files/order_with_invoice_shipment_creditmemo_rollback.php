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
$order = $objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000111');
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

foreach ($order->getInvoiceCollection() as $invoice) {
    $invoiceRepository->delete($invoice);
}

$orderRepository->delete($order);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

$creditMemoGridAggregator = $objectManager->get(\CreditmemoGridAggregator::class);
$creditMemoGridAggregator->purge('100000111', 'order_increment_id');

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple_rollback.php');
