<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Inventory\Model\ResourceModel\StockSourceLink\SaveMultiple;
use Magento\TestFramework\Helper\Bootstrap;

/** @var SaveMultiple $saveMultiple */
$saveMultiple = Bootstrap::getObjectManager()->get(SaveMultiple::class);
$saveMultiple->execute([1], 1);
$saveMultiple->execute([1,2,3,4,5], 2);