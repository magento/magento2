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
/** @var \Magento\JwtUserToken\Api\RevokedRepositoryInterface $revokedRepo */
$revokedRepo = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(\Magento\JwtUserToken\Api\RevokedRepositoryInterface::class);
$revokedRepo->saveRevoked(
    new \Magento\JwtUserToken\Api\Data\Revoked(
        \Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER,
        (int) $customer->getId(),
        time() - 3600 * 24
    )
);
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
