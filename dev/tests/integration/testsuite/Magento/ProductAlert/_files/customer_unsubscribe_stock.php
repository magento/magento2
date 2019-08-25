<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\ProductAlert\Model\ResourceModel\Stock;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$resource = $objectManager->create(Stock::class);

/** @var \Magento\Framework\Stdlib\DateTime\DateTime $dateTime */
$dateTime = $objectManager->get(DateTimeFactory::class)->create();
$date = $dateTime->gmtDate(null, ($dateTime->gmtTimestamp() - 3600));

$resource->getConnection()->insert(
    $resource->getMainTable(),
    [
        'customer_id' => 1,
        'product_id' => 1,
        'website_id' => 1,
        'store_id' => 1,
        'add_date' => $date,
        'send_date' => null,
        'send_count' => 0,
        'status' => 0
    ]
);
