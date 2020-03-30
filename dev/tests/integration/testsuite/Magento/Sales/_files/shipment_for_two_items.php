<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Sales\Api\ShipOrderInterface;

require __DIR__ . '/../../../Magento/Sales/_files/customer_order_with_two_items.php';

/** @var ShipOrderInterface $invoiceOrder */
$shipOrder = $objectManager->get(ShipOrderInterface::class);

$shipOrder->execute($order->getId());
