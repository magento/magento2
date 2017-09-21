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
    1
)->setEntityTypeId(
    1
)->setAttributeSetId(
    1
)->setEmail(
    'customer@example.com'
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
)->setDefaultBilling(
    1
)->setDefaultShipping(
    1
)->setRpToken(
    '8ed8677e6c79e68b94e61658bd756ea5'
)->setRpTokenCreatedAt(
    date('Y-m-d H:i:s')
);
$customer->isObjectNew(true);
$customer->save();
