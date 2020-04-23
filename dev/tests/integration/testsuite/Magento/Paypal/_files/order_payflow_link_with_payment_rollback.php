<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Framework\Registry;

$objectManager = Bootstrap::getObjectManager();

/** @var \Magento\Framework\Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var $order \Magento\Sales\Model\Order */
$orderCollection = $objectManager->create(Collection::class);
foreach ($orderCollection as $order) {
    $order->delete();
}
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
