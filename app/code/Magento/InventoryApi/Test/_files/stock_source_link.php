<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\InventoryApi\Api\AssignSourcesToStockInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var AssignSourcesToStockInterface $assignSourcesToStock */
$assignSourcesToStock = Bootstrap::getObjectManager()->get(AssignSourcesToStockInterface::class);
/**
 * EU-source-1(id:1) - EU-stock(id:1)
 * EU-source-2(id:2) - EU-stock(id:1)
 * EU-source-3(id:3) - EU-stock(id:1)
 * EU-source-disabled(id:4) - EU-stock(id:1)
 *
 * US-source-1(id:5) - US-stock(id:2)
 *
 * EU-source-1(id:1) - Global-stock(id:3)
 * EU-source-2(id:2) - Global-stock(id:3)
 * EU-source-2(id:3) - Global-stock(id:3)
 * EU-source-disabled(id:4) - Global-stock(id:3)
 * US-source-1(id:5) - Global-stock(id:3)
 */
$assignSourcesToStock->execute([1, 2, 3, 4], 1);
$assignSourcesToStock->execute([5], 2);
$assignSourcesToStock->execute([1, 2, 3, 4, 5], 3);
