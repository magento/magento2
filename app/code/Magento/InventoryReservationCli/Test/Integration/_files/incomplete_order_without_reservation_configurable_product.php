<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ .
    '/../../../../../../../dev/tests/integration/testsuite/Magento/Sales/_files/order_configurable_product.php';

$objectManager = Bootstrap::getObjectManager();

$order = $objectManager->create(Order::class)->loadByIncrementId('100000001');
foreach ($order->getItems() as $orderItem) {
    if ($orderItem->getTypeId() !== $configurableProduct->getTypeId()) {
        $orderItem->setSku($simpleProduct->getSku());
        $orderItemsRepository->save($orderItem);
    }
}
