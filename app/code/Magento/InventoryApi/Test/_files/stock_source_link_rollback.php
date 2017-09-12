<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\InventoryApi\Api\UnassignSourceFromStockInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var UnassignSourceFromStockInterface $unassignSourceFromStock */
$unassignSourceFromStock = Bootstrap::getObjectManager()->get(UnassignSourceFromStockInterface::class);
// EU stock
$unassignSourceFromStock->execute(1, 1);
$unassignSourceFromStock->execute(2, 1);
$unassignSourceFromStock->execute(3, 1);
$unassignSourceFromStock->execute(4, 1);
// US stock
$unassignSourceFromStock->execute(5, 2);
// Global Stock
$unassignSourceFromStock->execute(1, 3);
$unassignSourceFromStock->execute(2, 3);
$unassignSourceFromStock->execute(3, 3);
$unassignSourceFromStock->execute(4, 3);
$unassignSourceFromStock->execute(5, 3);
