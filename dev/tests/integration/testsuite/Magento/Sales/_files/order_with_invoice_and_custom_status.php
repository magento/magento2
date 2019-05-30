<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Sales\Model\Order\Status;
use Magento\TestFramework\Helper\Bootstrap;

// phpcs:ignore Magento2.Security.IncludeFile
require 'invoice.php';

$orderStatus = Bootstrap::getObjectManager()->create(Status::class);
$data = [
    'status' => 'custom_processing',
    'label' => 'Custom Processing Status',
];
$orderStatus->setData($data)->setStatus('custom_processing');
$orderStatus->save();
$orderStatus->assignState('processing');

$order->setStatus('custom_processing');
$order->save();
