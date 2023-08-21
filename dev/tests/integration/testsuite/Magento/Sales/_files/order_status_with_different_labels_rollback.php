<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\TestFramework\Helper\Bootstrap;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order_with_customer_on_second_website_rollback.php');

$objectManager = Bootstrap::getObjectManager();

$orderStatus = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Sales\Model\Order\Status::class
);

$status = $objectManager->get(\Magento\Sales\Model\Order\Status::class)->load('processing');

$data = [
    'status' => 'processing',
    'label' => 'Processing',
    'store_labels' => []
];

$status->setData($data)->setStatus('processing');
$status->save();
