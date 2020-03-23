<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Sales\Api\CreditmemoItemRepositoryInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\CreditmemoFactory;

require __DIR__ . '/../../../Magento/Sales/_files/two_invoices_for_items.php';

/** @var CreditmemoFactory $creditmemoFactory */
$creditmemoFactory = $objectManager->get(CreditmemoFactory::class);
/** @var CreditmemoRepositoryInterface $creditmemoRepository */
$creditmemoRepository = $objectManager->get(CreditmemoRepositoryInterface::class);
/** @var CreditmemoItemRepositoryInterface $creditmemoItemRepository */
$creditmemoItemRepository = $objectManager->get(CreditmemoItemRepositoryInterface::class);

foreach ($order->getInvoiceCollection() as $invoice) {
    $creditmemo = $creditmemoFactory->createByInvoice($invoice);
    $creditmemo->setState(Creditmemo::STATE_REFUNDED);
    $creditmemo->setInvoice($invoice);
    $creditmemo->setSubtotal($invoice->getSubtotal());
    $creditmemoRepository->save($creditmemo);

    foreach ($creditmemo->getItems() as $creditmemoItem) {
        $orderItem = $orderItemRepository->get($creditmemoItem->getOrderItemId());
        $creditmemoItem->setQty($orderItem->getQtyInvoiced())
            ->setRowTotal($orderItem->getRowInvoiced());
        $creditmemoItemRepository->save($creditmemoItem);

        $orderItem->setQtyRefunded($creditmemoItem->getQty())
            ->setAmountRefunded($creditmemoItem->getRowTotal());
        $orderItemRepository->save($orderItem);
    }
}
$order->setState(Order::STATE_CLOSED)
    ->setStatus(Order::STATE_CLOSED)
    ->setSubtotalRefunded(20)
    ->setShippingRefunded(10)
    ->setTotalRefunded(30);
$orderRepository->save($order);
