<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\ObjectManagerInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\Registry;

/** @var $objectManager ObjectManagerInterface */
$objectManager = Bootstrap::getObjectManager();

$customers = [];
$customer = $objectManager->create(Customer::class);

$customer->setWebsiteId(
    1
)->setEntityTypeId(
    1
)->setAttributeSetId(
    0
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
);
$customer->isObjectNew(true);
$customer->save();
$customers[] = $customer;

$customer = $objectManager->create(Customer::class);
$customer->setWebsiteId(
    1
)->setEntityTypeId(
    1
)->setAttributeSetId(
    0
)->setEmail(
    'julie.worrell@example.com'
)->setPassword(
    'password'
)->setGroupId(
    1
)->setStoreId(
    1
)->setIsActive(
    1
)->setFirstname(
    'Julie'
)->setLastname(
    'Worrell'
)->setDefaultBilling(
    1
)->setDefaultShipping(
    1
);
$customer->isObjectNew(true);
$customer->save();
$customers[] = $customer;

$customer = $objectManager->create(Customer::class);
$customer->setWebsiteId(
    1
)->setEntityTypeId(
    1
)->setAttributeSetId(
    0
)->setEmail(
    'david.lamar@example.com'
)->setPassword(
    'password'
)->setGroupId(
    1
)->setStoreId(
    1
)->setIsActive(
    1
)->setFirstname(
    'David'
)->setLastname(
    'Lamar'
)->setDefaultBilling(
    1
)->setDefaultShipping(
    1
);
$customer->isObjectNew(true);
$customer->save();
$customers[] = $customer;

$objectManager->get(Registry::class)
    ->unregister('_fixture/Magento_ImportExport_Customer_Collection');
$objectManager->get(Registry::class)
    ->register('_fixture/Magento_ImportExport_Customer_Collection', $customers);
