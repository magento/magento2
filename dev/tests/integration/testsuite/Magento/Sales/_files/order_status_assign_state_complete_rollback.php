<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\Sales\Model\Order\Status;
use Magento\TestFramework\Helper\Bootstrap;

/** @var Status $orderStatus */
$orderStatus = Bootstrap::getObjectManager()->create(Status::class);
$orderStatus->load('custom_complete', 'status');
$orderStatus->delete();
