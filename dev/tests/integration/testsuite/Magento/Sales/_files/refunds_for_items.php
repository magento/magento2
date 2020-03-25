<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Sales\Api\Data\CreditmemoItemCreationInterfaceFactory;
use Magento\Sales\Api\RefundOrderInterface;

require __DIR__ . '/../../../Magento/Sales/_files/invoices_for_items.php';

/** @var CreditmemoItemCreationInterfaceFactory $creditmemoItemFactory */
$creditmemoItemFactory = $objectManager->get(CreditmemoItemCreationInterfaceFactory::class);
/** @var RefundOrderInterface $refundOrder */
$refundOrder = $objectManager->get(RefundOrderInterface::class);

foreach ($order->getItems() as $item) {
    $creditmemoItem = $creditmemoItemFactory->create();
    $creditmemoItem->setOrderItemId($item->getId());
    $creditmemoItem->setQty($item->getQtyOrdered());
    $refundOrder->execute($order->getId(), [$creditmemoItem]);
}
