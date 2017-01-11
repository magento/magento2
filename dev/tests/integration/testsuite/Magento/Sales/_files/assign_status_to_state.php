<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Sales\Model\Order\Status $status */
$status = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Sales\Model\Order\Status::class
);
$status->setData(
    [
        'status' => 'fake_status_do_not_use_it',
        'label' => 'Fake status do not use it',
    ]
);
$status->save();
$status->assignState('fake_state_do_not_use_it', true, true);
