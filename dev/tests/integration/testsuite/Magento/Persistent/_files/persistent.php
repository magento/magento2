<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../../Magento/Customer/_files/customer.php';

/** @var \Magento\Persistent\Model\Session $persistentSession */
$persistentSession = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    'Magento\Persistent\Model\Session'
);
$persistentSession->setCustomerId($customer->getId())->save();
