<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$customer = $objectManager->create('Magento\Customer\Api\Data\CustomerInterface');
/** @var Magento\Customer\Api\AccountManagementInterface $accountManagement */
$accountManagement = $objectManager->create('Magento\Customer\Api\AccountManagementInterface');

/** @var Magento\Customer\Api\Data\CustomerInterface $customer */
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
    ->setGender(0);
$accountManagement->createAccount($customer, 'password');
