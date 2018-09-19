<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Customer\Model\Customer::class
);
$customer->setWebsiteId(
    1
)->setId(
    5
)->setEntityTypeId(
    1
)->setAttributeSetId(
    1
)->setEmail(
    'customer5@example.com'
)->setPassword(
    'password'
)->setGroupId(
    1
)->setStoreId(
    1
)->setIsActive(
    1
)->setFirstname(
    'Firstname'
)->setLastname(
    'Lastname'
);
$customer->isObjectNew(true);
$customer->save();
