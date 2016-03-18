<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
$quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Quote\Model\Quote');
$quote->setStoreId(1)
    ->setIsActive(true)
    ->setIsMultiShipping(false)
    ->setReservedOrderId('test_order_1')
    ->save();
