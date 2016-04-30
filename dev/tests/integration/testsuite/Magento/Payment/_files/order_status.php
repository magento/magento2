<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Sales\Model\Order\Status $status */
$status = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order\Status');
//status for state new
$status->setData('status', 'custom_new_status')->setData('label', 'Test Status')->save();
$status->assignState(\Magento\Sales\Model\Order::STATE_NEW, true);
//status for state canceled
$status->setData('status', 'custom_canceled_status')->setData('label', 'Test Status')->unsetData('id')->save();
$status->assignState(\Magento\Sales\Model\Order::STATE_CANCELED, true);
