<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Status;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/invoice.php');

$objectManager = Bootstrap::getObjectManager();
/** @var Order $order */
$order = $objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000001');
$orderStatus = Bootstrap::getObjectManager()->create(Status::class);
$data = [
    'status' => 'custom_processing',
    'label' => 'Custom Processing Status',
];
$orderStatus->setData($data)->setStatus('custom_processing');
$orderStatus->save();
$orderStatus->assignState('processing');

$order->setStatus('custom_processing');
$order->save();
