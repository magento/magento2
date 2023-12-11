<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

/** @var $objectManager ObjectManagerInterface */
$objectManager = Bootstrap::getObjectManager();

$customers = [];

/**
 * @var $customer Customer
 * @var $customerResource CustomerResource
 */
$customer = $objectManager->create(Customer::class);
$customerResource = $objectManager->create(CustomerResource::class);

$customer->setWebsiteId(1)
    ->setCreatedAt('1999-01-02')
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('customer@example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('Firstname')
    ->setLastname('Lastname')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1);
$customer->isObjectNew(true);
$customerResource->save($customer);
$customers[] = $customer;

$customer = $objectManager->create(Customer::class);
$customer->setWebsiteId(1)
    ->setCreatedAt('1999-03-04')
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('julie.worrell@example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('Julie')
    ->setLastname('Worrell')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1);
$customer->isObjectNew(true);
$customerResource->save($customer);
$customers[] = $customer;

$customer = $objectManager->create(Customer::class);
$customer->setWebsiteId(1)
    ->setCreatedAt('1999-05-06')
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('david.lamar@example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('David')
    ->setLastname('Lamar')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1);
$customer->isObjectNew(true);
$customerResource->save($customer);
$customers[] = $customer;

$objectManager->get(Registry::class)->unregister('_fixture/Magento_ImportExport_Customer_Collection');
$objectManager->get(Registry::class)->register('_fixture/Magento_ImportExport_Customer_Collection', $customers);
