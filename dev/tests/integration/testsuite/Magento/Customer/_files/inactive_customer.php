<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Customer\Model\Customer::class
);
$customer->setWebsiteId(
    1
)->setId(
    1
)->setConfirmation(
    $customer->getRandomConfirmationKey()
)->setEntityTypeId(
    1
)->setAttributeSetId(
    0
)->setEmail(
    'customer@needAconfirmation.com'
)->setPassword(
    'password'
)->setGroupId(
    1
)->setStoreId(
    1
)->setFirstname(
    'Firstname'
)->setLastname(
    'Lastname'
)->setDefaultBilling(
    1
)->setDefaultShipping(
    1
);
$customer->isObjectNew(true);
$customer->save();
