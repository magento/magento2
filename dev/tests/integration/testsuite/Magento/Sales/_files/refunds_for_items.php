<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Sales\Api\Data\CreditmemoItemCreationInterfaceFactory;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\RefundOrderInterface;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/invoices_for_items.php');

$objectManager = Bootstrap::getObjectManager();
/** @var Order $order */
$order = $objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000555');
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
