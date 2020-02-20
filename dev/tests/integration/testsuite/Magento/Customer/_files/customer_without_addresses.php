<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\TestFramework\Helper\Bootstrap;

/** @var CustomerRegistry $customerRegistry */
$customerRegistry = Bootstrap::getObjectManager()->get(CustomerRegistry::class);
/** @var Customer $customer */
$customer = Bootstrap::getObjectManager()->get(Customer::class);

$customer->setWebsiteId(1)
    ->setId(1)
    ->setEmail('customer@example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setPrefix('Mr.')
    ->setFirstname('John')
    ->setMiddlename('A')
    ->setLastname('Smith')
    ->setSuffix('Esq.')
    ->setGender(0)
    ->save();
$customerRegistry->remove($customer->getId());
