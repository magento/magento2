<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Customer\Model\Customer::class
);
/** @var Magento\Customer\Model\Customer $customer */
$customer->setWebsiteId(1)
    ->setId(1)
    ->setEntityTypeId(1)
    ->setAttributeSetId(1)
    ->setEmail('customer@search.example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('Firstname')
    ->setLastname('Lastname')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1)
    ->setCreatedAt('2014-02-28 15:52:26');
$customer->isObjectNew(true);

$customer->save();
$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Customer\Model\Customer::class
);
$customer->setWebsiteId(1)
    ->setEntityId(2)
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('customer2@search.example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('Firstname2')
    ->setLastname('Lastname2')
    ->setDefaultBilling(2)
    ->setDefaultShipping(2)
    ->setCreatedAt('2010-02-28 15:52:26');
$customer->isObjectNew(true);
$customer->save();

$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Customer\Model\Customer::class
);
$customer->setWebsiteId(1)
    ->setEntityId(3)
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('customer3@search.example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('Firstname3')
    ->setLastname('Lastname3')
    ->setDefaultBilling(3)
    ->setDefaultShipping(3)
    ->setCreatedAt('2012-02-28 15:52:26');
$customer->isObjectNew(true);
$customer->save();

$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Customer\Model\Customer::class
);
/** @var Magento\Customer\Model\Customer $customer */
$customer->setWebsiteId(1)
    ->setId(4)
    ->setEntityTypeId(1)
    ->setAttributeSetId(1)
    ->setEmail('customer4@search.example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('Firstname4')
    ->setLastname('Lastname4')
    ->setDefaultBilling(4)
    ->setDefaultShipping(4)
    ->setCreatedAt('2014-02-28 15:52:26');
$customer->isObjectNew(true);

$customer->save();
$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Customer\Model\Customer::class
);
$customer->setWebsiteId(1)
    ->setEntityId(5)
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('customer5@search.example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('Firstname5')
    ->setLastname('Lastname5')
    ->setDefaultBilling(5)
    ->setDefaultShipping(5)
    ->setCreatedAt('2010-02-28 15:52:26');
$customer->isObjectNew(true);
$customer->save();

$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Customer\Model\Customer::class
);
$customer->setWebsiteId(1)
    ->setEntityId(6)
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('customer6@search.example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('Firstname6')
    ->setLastname('Lastname6')
    ->setDefaultBilling(6)
    ->setDefaultShipping(6)
    ->setCreatedAt('2012-02-28 15:52:26');
$customer->isObjectNew(true);
$customer->save();

$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Customer\Model\Customer::class
);
$customer->setWebsiteId(1)
    ->setEntityId(7)
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('customer7@search.example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('Firstname7')
    ->setLastname('Lastname7')
    ->setDefaultBilling(7)
    ->setDefaultShipping(7)
    ->setCreatedAt('2012-02-28 15:52:26');
$customer->isObjectNew(true);
$customer->save();

$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Customer\Model\Customer::class
);
$customer->setWebsiteId(1)
    ->setEntityId(8)
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('customer8@search.example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('Firstname8')
    ->setLastname('Lastname8')
    ->setDefaultBilling(8)
    ->setDefaultShipping(8)
    ->setCreatedAt('2012-02-28 15:52:26');
$customer->isObjectNew(true);
$customer->save();

$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Customer\Model\Customer::class
);
$customer->setWebsiteId(1)
    ->setEntityId(9)
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('customer9@search.example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('Firstname9')
    ->setLastname('Lastname9')
    ->setDefaultBilling(9)
    ->setDefaultShipping(9)
    ->setCreatedAt('2012-02-28 15:52:26');
$customer->isObjectNew(true);
$customer->save();

$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Customer\Model\Customer::class
);
$customer->setWebsiteId(1)
    ->setEntityId(10)
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('customer10@search.example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('Firstname10')
    ->setLastname('Lastname10')
    ->setDefaultBilling(10)
    ->setDefaultShipping(10)
    ->setCreatedAt('2012-02-28 15:52:26');
$customer->isObjectNew(true);
$customer->save();

$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Customer\Model\Customer::class
);
$customer->setWebsiteId(1)
    ->setEntityId(11)
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('customer11@search.example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('Firstname11')
    ->setLastname('Lastname11')
    ->setDefaultBilling(11)
    ->setDefaultShipping(11)
    ->setCreatedAt('2012-02-28 15:52:26');
$customer->isObjectNew(true);
$customer->save();

$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Customer\Model\Customer::class
);
$customer->setWebsiteId(1)
    ->setEntityId(12)
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('customer12@search.example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('Firstname12')
    ->setLastname('Lastname12')
    ->setDefaultBilling(12)
    ->setDefaultShipping(12)
    ->setCreatedAt('2012-02-28 15:52:26');
$customer->isObjectNew(true);
$customer->save();

$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Customer\Model\Customer::class
);
$customer->setWebsiteId(1)
    ->setEntityId(13)
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('customer13@search.example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('Firstname13')
    ->setLastname('Lastname13')
    ->setDefaultBilling(13)
    ->setDefaultShipping(13)
    ->setCreatedAt('2012-02-28 15:52:26');
$customer->isObjectNew(true);
$customer->save();

$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Customer\Model\Customer::class
);
$customer->setWebsiteId(1)
    ->setEntityId(14)
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('customer14@search.example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('Firstname14')
    ->setLastname('Lastname14')
    ->setDefaultBilling(14)
    ->setDefaultShipping(14)
    ->setCreatedAt('2012-02-28 15:52:26');
$customer->isObjectNew(true);
$customer->save();

$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Customer\Model\Customer::class
);
$customer->setWebsiteId(1)
    ->setEntityId(15)
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('customer15@search.example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('Firstname15')
    ->setLastname('Lastname15')
    ->setDefaultBilling(15)
    ->setDefaultShipping(15)
    ->setCreatedAt('2012-02-28 15:52:26');
$customer->isObjectNew(true);
$customer->save();

$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Customer\Model\Customer::class
);
$customer->setWebsiteId(1)
    ->setEntityId(16)
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('customer16@search.example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('Firstname16')
    ->setLastname('Lastname16')
    ->setDefaultBilling(16)
    ->setDefaultShipping(16)
    ->setCreatedAt('2012-02-28 15:52:26');
$customer->isObjectNew(true);
$customer->save();

$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Customer\Model\Customer::class
);
$customer->setWebsiteId(1)
    ->setEntityId(17)
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('customer17@search.example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('Firstname17')
    ->setLastname('Lastname17')
    ->setDefaultBilling(17)
    ->setDefaultShipping(17)
    ->setCreatedAt('2012-02-28 15:52:26');
$customer->isObjectNew(true);
$customer->save();

$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Customer\Model\Customer::class
);
$customer->setWebsiteId(1)
    ->setEntityId(18)
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('customer18@search.example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('Firstname18')
    ->setLastname('Lastname18')
    ->setDefaultBilling(18)
    ->setDefaultShipping(18)
    ->setCreatedAt('2012-02-28 15:52:26');
$customer->isObjectNew(true);
$customer->save();

$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Customer\Model\Customer::class
);
$customer->setWebsiteId(1)
    ->setEntityId(19)
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('customer19@search.example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('Firstname19')
    ->setLastname('Lastname19')
    ->setDefaultBilling(19)
    ->setDefaultShipping(19)
    ->setCreatedAt('2012-02-28 15:52:26');
$customer->isObjectNew(true);
$customer->save();

$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Customer\Model\Customer::class
);
$customer->setWebsiteId(1)
    ->setEntityId(20)
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('customer20@search.example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('Firstname20')
    ->setLastname('Lastname20')
    ->setDefaultBilling(20)
    ->setDefaultShipping(20)
    ->setCreatedAt('2012-02-28 15:52:26');
$customer->isObjectNew(true);
$customer->save();

$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Customer\Model\Customer::class
);
$customer->setWebsiteId(1)
    ->setEntityId(21)
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('customer21@search.example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('Firstname21')
    ->setLastname('Lastname21')
    ->setDefaultBilling(21)
    ->setDefaultShipping(21)
    ->setCreatedAt('2012-02-28 15:52:26');
$customer->isObjectNew(true);
$customer->save();
