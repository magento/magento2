<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var $repository \Magento\Customer\Api\CustomerRepositoryInterface */
$repository = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');

$objectManager->get('Magento\Framework\Locale\ResolverInterface')->setLocale('en_US');
$customer1 = $objectManager->create('Magento\Customer\Api\Data\CustomerInterface');
/** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
$customer1->setWebsiteId(1)
    ->setEmail('john.doe1@ex.com')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setFirstname('John')
    ->setLastname('Smith')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1)
    ->setTaxvat('12')
    ->setGender(0)
    ->setDob('1991-12-31')
    ->setCustomAttribute('date', '12/25/2017');
$repository->save($customer1, 'password');

$objectManager->get('Magento\Framework\Locale\ResolverInterface')->setLocale('fr_FR');
$customer2 = $objectManager->create('Magento\Customer\Api\Data\CustomerInterface');
/** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
$customer2->setWebsiteId(1)
    ->setEmail('john.doe2@ex.com')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setFirstname('John')
    ->setLastname('Smith')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1)
    ->setTaxvat('12')
    ->setGender(0)
    ->setDob('1991-12-31')
    ->setCustomAttribute('date', '25/12/2017');
$repository->save($customer2, 'password');

$objectManager->get('Magento\Framework\Locale\ResolverInterface')->setLocale('ar_KW');
$customer3 = $objectManager->create('Magento\Customer\Api\Data\CustomerInterface');
/** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
$customer3->setWebsiteId(1)
    ->setEmail('john.doe3@ex.com')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setFirstname('John')
    ->setLastname('Smith')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1)
    ->setTaxvat('12')
    ->setGender(0)
    ->setDob('1991-12-31')
    ->setCustomAttribute('date', '25/12/2017');
$repository->save($customer3, 'password');
