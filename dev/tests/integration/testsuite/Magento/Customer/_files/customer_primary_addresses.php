<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Customer\Model\CustomerRegistry;

require 'customer_two_addresses.php';

/** @var \Magento\Customer\Model\Customer $customer */
$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Customer\Model\Customer'
)->load(
    1
);
/** @var CustomerRegistry $customerRegistry */
$customerRegistry = $objectManager->get(CustomerRegistry::class);
$customer->setDefaultBilling(1)->setDefaultShipping(2);
$customer->save();
$customerRegistry->remove($customer->getId());
