<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

use Magento\Customer\Model\CustomerRegistry;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer_two_addresses.php');

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Customer\Model\Customer $customer */
$customer = $objectManager->create(
    \Magento\Customer\Model\Customer::class
)->load(
    1
);
/** @var CustomerRegistry $customerRegistry */
$customerRegistry = $objectManager->get(CustomerRegistry::class);
$customer->setDefaultBilling(1)->setDefaultShipping(2);
$customer->save();
$customerRegistry->remove($customer->getId());
