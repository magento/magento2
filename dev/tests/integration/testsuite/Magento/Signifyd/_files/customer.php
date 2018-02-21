<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Customer\Model\Customer;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerRegistry;

$objectManager = Bootstrap::getObjectManager();
/** @var CustomerRegistry $customerRegistry */
$customerRegistry = $objectManager->get(CustomerRegistry::class);
$customer = $objectManager->create(Customer::class);

/** @var CustomerInterface $customer */
$customer->setWebsiteId(1)
    ->setId(1)
    ->setEmail('customer@example.com')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setPrefix('Mr.')
    ->setFirstname('John')
    ->setMiddlename('A')
    ->setLastname('Smith')
    ->setSuffix('Esq.')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1)
    ->setTaxvat('12')
    ->setGender(0)
    ->setCreatedAt('2016-12-12T11:00:00+0000')
    ->setUpdatedAt('2016-12-12T11:05:00+0000');

$customer->isObjectNew(true);
$customer->save();

$customerRegistry->remove($customer->getId());
