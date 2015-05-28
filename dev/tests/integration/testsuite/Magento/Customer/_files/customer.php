<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var $repository \Magento\Customer\Api\CustomerRepositoryInterface */
$repository = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
$customer = $objectManager->create('Magento\Customer\Model\Customer');

/** @var Magento\Customer\Model\Customer $customer */
$customer->setWebsiteId(1)
    ->setId(1)
    ->setEmail('customer@example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setCreatedIn('Store Name')
    ->setPrefix('Mr.')
    ->setFirstname('John')
    ->setMiddlename('A')
    ->setLastname('Smith')
    ->setSuffix('Esq.')
    ->setDob('1970-01-01')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1)
    ->setTaxvat('12')
    ->setConfirmation('a4fg7h893e39d')
    ->setGender(0);
$customer->isObjectNew(true);
$customer->save();
