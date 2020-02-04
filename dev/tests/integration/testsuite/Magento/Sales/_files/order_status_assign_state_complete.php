<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Sales\Model\Order\Status;
use Magento\TestFramework\Helper\Bootstrap;

/** @var Status $orderStatus */
$orderStatus = Bootstrap::getObjectManager()->create(Status::class);
$data = [
    'status' => 'custom_complete',
    'label' => 'Custom Complete Status',
];
$orderStatus->setData($data)->save();
$orderStatus->assignState(\Magento\Sales\Model\Order::STATE_COMPLETE, false, true);
