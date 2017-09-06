<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\InventoryApi\Api\AssignSourcesToStockInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var AssignSourcesToStockInterface $assignSourcesToStock */
$assignSourcesToStock = Bootstrap::getObjectManager()->get(AssignSourcesToStockInterface::class);
$assignSourcesToStock->execute([1, 2], 1);
$assignSourcesToStock->execute([3, 4], 2);
