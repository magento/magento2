<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\ProductAlert\Model\ResourceModel\Stock;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$resource = $objectManager->get(Stock::class);

$resource->getConnection()->delete(
    $resource->getMainTable(),
    ['product_id = ?' => 30221]
);
