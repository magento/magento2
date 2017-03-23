<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

include __DIR__ . '/order.php';
include __DIR__ . '/../../../Magento/Customer/_files/customer.php';

$customerIdFromFixture = 1;
/** @var $order \Magento\Sales\Model\Order */
$order->setCustomerId($customerIdFromFixture)->setCustomerIsGuest(false)->save();
