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
 * EU-source-1(code:eu-1) - EU-stock(id:10)
 * EU-source-2(code:eu-2) - EU-stock(id:10)
 * EU-source-3(code:eu-3) - EU-stock(id:10)
 * EU-source-disabled(code:eu-dis) - EU-stock(id:10)
 *
 * US-source-1(code:us-1) - US-stock(id:20)
 *
 * EU-source-1(code:eu-1) - Global-stock(id:30)
 * EU-source-2(code:eu-2) - Global-stock(id:30)
 * EU-source-2(code:eu-2) - Global-stock(id:30)
 * EU-source-disabled(code:eu-dis) - Global-stock(id:30)
 * US-source-1(code:us-1) - Global-stock(id:30)
 */
$assignSourcesToStock->execute(['eu-1', 'eu-2', 'eu-3', 'eu-dis'], 10);
$assignSourcesToStock->execute(['us-1'], 20);
$assignSourcesToStock->execute(['eu-1', 'eu-2', 'eu-3', 'eu-dis', 'us-1'], 30);
