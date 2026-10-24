<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\Sales\Model\Order\Status;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/default_rollback.php');

/** @var Status $orderStatus */
$orderStatus = Bootstrap::getObjectManager()->create(Status::class);
$orderStatus->load('custom_processing', 'status');
$orderStatus->delete();
