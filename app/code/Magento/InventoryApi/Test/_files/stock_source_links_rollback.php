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
$unassignSourceFromStock->execute('eu-1', 10);
$unassignSourceFromStock->execute('eu-2', 10);
$unassignSourceFromStock->execute('eu-3', 10);
$unassignSourceFromStock->execute('eu-disabled', 10);
// US stock
$unassignSourceFromStock->execute('us-1', 20);
// Global Stock
$unassignSourceFromStock->execute('eu-1', 30);
$unassignSourceFromStock->execute('eu-2', 30);
$unassignSourceFromStock->execute('eu-3', 30);
$unassignSourceFromStock->execute('eu-disabled', 30);
$unassignSourceFromStock->execute('us-1', 30);
