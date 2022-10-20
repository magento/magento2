<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Sales\Model\Order\Status;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Store\Model\StoreManagerInterface;

$objectManager = Bootstrap::getObjectManager();
Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order_with_customer_on_second_website.php');

$status = $objectManager->get(Status::class)->load('processing');
$storeManager = $objectManager->get(StoreManagerInterface::class);
$storeId = (int)$storeManager->getStore('fixture_third_store')->getId();

$data = [
    'status' => 'processing',
    'label' => 'Processing',
    'store_labels' => [
        1 => 'First store label',
        $storeId => 'Custom status label'
    ]
];

$status->addData($data)->setStatus('processing');
$status->save();
