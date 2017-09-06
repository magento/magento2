<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\InventoryApi\Api\UnassignSourceFromStockInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var UnassignSourceFromStockInterface $unassignSourceFromStock */
$unassignSourceFromStock = Bootstrap::getObjectManager()->get(UnassignSourceFromStockInterface::class);
$unassignSourceFromStock->execute(1, 1);
$unassignSourceFromStock->execute(2, 1);
$unassignSourceFromStock->execute(3, 2);
