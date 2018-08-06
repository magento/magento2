<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Signifyd\Api\Data\CaseInterface;

require __DIR__ . '/case.php';

for ($i = 1; $i < 4; $i ++) {
    $newOrder = clone $order;
    $newOrder->setEntityId(null)
        ->setIncrementId($order->getIncrementId() + $i);

    $orderRepository->save($newOrder);

    $newCase = clone $case;
    $newCase->setEntityId(null)
        ->setCaseId($i)
        ->setOrderId($newOrder->getEntityId())
        ->setStatus(CaseInterface::STATUS_OPEN)
        ->setCreatedAt('2016-12-0' . $i . 'T15:' . $i . ':17+0000')
        ->setUpdatedAt('2016-12-12T0' . $i . ':23:16+0000')
        ->setId(null);

    $caseRepository->save($newCase);
}
