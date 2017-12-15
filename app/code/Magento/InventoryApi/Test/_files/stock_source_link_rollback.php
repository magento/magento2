<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\InventoryApi\Api\UnassignSourceFromStockInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var UnassignSourceFromStockInterface $unassignSourceFromStock */
$unassignSourceFromStock = Bootstrap::getObjectManager()->get(UnassignSourceFromStockInterface::class);
// EU stock
$unassignSourceFromStock->execute(10, 10);
$unassignSourceFromStock->execute(20, 10);
$unassignSourceFromStock->execute(30, 10);
$unassignSourceFromStock->execute(40, 10);
// US stock
$unassignSourceFromStock->execute(50, 20);
// Global Stock
$unassignSourceFromStock->execute(10, 30);
$unassignSourceFromStock->execute(20, 30);
$unassignSourceFromStock->execute(30, 30);
$unassignSourceFromStock->execute(40, 30);
$unassignSourceFromStock->execute(50, 30);
