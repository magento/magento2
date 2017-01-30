<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require 'customer_two_addresses.php';

/** @var \Magento\Customer\Model\Customer $customer */
$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Customer\Model\Customer'
)->load(
    1
);
$customer->setDefaultBilling(1)->setDefaultShipping(2);
$customer->save();
