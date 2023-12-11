<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/**
 * @var Customer $customer
 * @var CustomerResource $customerResource
 */
$customer = Bootstrap::getObjectManager()->create(Customer::class);
$customerResource = $objectManager->create(CustomerResource::class);

$customer->setWebsiteId(0)
    ->setEntityId(1)
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('BetsyParker@example.com')
    ->setPassword('password')
    ->setGroupId(0)
    ->setStoreId(0)
    ->setIsActive(1)
    ->setFirstname('Betsy')
    ->setLastname('Parker')
    ->setGender(2);
$customer->isObjectNew(true);
$customerResource->save($customer);

$addressFirst = $objectManager->create(Address::class);
$addressFirst->addData(
    [
        'entity_id' => 1,
        'firstname' => 'Betsy',
        'lastname' => 'Parker',
        'street' => '1079 Rocky Road',
        'city' => 'Philadelphia',
        'country_id' => 'US',
        'region_id' => '51',
        'postcode' => '19107',
        'telephone' => '215-629-9720',
    ]
);
$addressFirst->isObjectNew(true);
$customer->addAddress($addressFirst);
$customer->setDefaultBilling($addressFirst->getId());

$addressSecond = $objectManager->create(Address::class);
$addressSecond->addData(
    [
        'entity_id' => 2,
        'firstname' => 'Anthony',
        'lastname' => 'Nealy',
        'street' => '3176 Cambridge Court',
        'city' => 'Fayetteville',
        'country_id' => 'US',
        'region_id' => '5',
        'postcode' => '72701',
        'telephone' => '479-899-9849',
    ]
);
$addressSecond->isObjectNew(true);
$customer->addAddress($addressSecond);
$customer->setDefaultShipping($addressSecond->getId());
$customerResource->save($customer);
