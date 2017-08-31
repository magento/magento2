<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Inventory\Model\ResourceModel\StockSourceLink\SaveMultiple;
use Magento\TestFramework\Helper\Bootstrap;

/** @var SaveMultiple $saveMultiple */
$saveMultiple = Bootstrap::getObjectManager()->get(SaveMultiple::class);
$saveMultiple->execute([1, 2], 1);
$saveMultiple->execute([3], 2);