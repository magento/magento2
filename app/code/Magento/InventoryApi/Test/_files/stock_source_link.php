<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\InventoryApi\Api\AssignSourcesToStockInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var AssignSourcesToStockInterface $assignSourcesToStock */
$assignSourcesToStock = Bootstrap::getObjectManager()->get(AssignSourcesToStockInterface::class);
/**
 * EU-source-1(id:10) - EU-stock(id:10)
 * EU-source-2(id:20) - EU-stock(id:10)
 * EU-source-3(id:30) - EU-stock(id:10)
 * EU-source-disabled(id:40) - EU-stock(id:10)
 *
 * US-source-1(id:50) - US-stock(id:20)
 *
 * EU-source-1(id:10) - Global-stock(id:30)
 * EU-source-2(id:20) - Global-stock(id:30)
 * EU-source-2(id:30) - Global-stock(id:30)
 * EU-source-disabled(id:40) - Global-stock(id:30)
 * US-source-1(id:50) - Global-stock(id:30)
 */
$assignSourcesToStock->execute([10, 20, 30, 40], 10);
$assignSourcesToStock->execute([50], 20);
$assignSourcesToStock->execute([10, 20, 30, 40, 50], 30);
