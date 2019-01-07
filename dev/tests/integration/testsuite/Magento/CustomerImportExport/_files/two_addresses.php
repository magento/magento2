<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\Store\Model\Website;
use Magento\Store\Model\Store;

include __DIR__ . '/../../Store/_files/websites_different_countries.php';

//Creating two customers for different websites.
$objectManager = Bootstrap::getObjectManager();
//First for default website.
$customer = $objectManager->create(Customer::class);
/** @var Customer $customer */
$customer->setId(1)
    ->setPassword('password')
    ->setIsActive(1)
    ->setPrefix('Mr.')
    ->setFirstname('John')
    ->setMiddlename('A')
    ->setLastname('Smith')
    ->setSuffix('Esq.')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1)
    ->setTaxvat('12')
    ->setGender(0)
    ->setWebsiteId(1)
    ->setGroupId(1)
    ->setEmail('customer@example.com')
    ->setStoreId(1);

$customer->isObjectNew(true);
$customer->save();
//Second for second website
/** @var Website $secondWebsite */
$secondWebsite = $objectManager->create(Website::class);
$secondWebsite->load('test', 'code');
/** @var Store $secondStore */
$secondStore = $objectManager->create(Store::class);
$secondStore->load('fixture_second_store', 'code');
$customer = $objectManager->create(Customer::class);
/** @var Customer $customer */
$customer->setId(2)
    ->setPassword('password')
    ->setIsActive(1)
    ->setPrefix('Mr.')
    ->setFirstname('Second')
    ->setMiddlename(null)
    ->setLastname('Customer')
    ->setSuffix('Esq.')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1)
    ->setTaxvat('12')
    ->setGender(0)
    ->setWebsiteId($secondWebsite->getId())
    ->setGroupId(1)
    ->setEmail('customer2@example.com')
    ->setStoreId($secondStore->getId());

$customer->isObjectNew(true);
$customer->save();

//Creating address for the 1st customer.
/** @var Address $customerAddress */
$customerAddress = $objectManager->create(Address::class);
$customerAddress->isObjectNew(true);
$customerAddress->setData(
    [
        'entity_id' => 2,
        'attribute_set_id' => 2,
        'telephone' => '+33254060414',
        'postcode' => 36400,
        'country_id' => 'FR',
        'city' => 'Montgivray',
        'street' => ['1 Avenue du Lion d\'Argent'],
        'lastname' => 'Smith',
        'firstname' => 'John',
        'parent_id' => 1,
        'region_id' => 1,
    ]
)->setCustomerId(1)
    ->setStoreId(1)
    ->setWebsiteId(1);
$customerAddress->save();
//Address for the 2nd customer
/** @var Address $customerAddress */
$customerAddress = $objectManager->create(Address::class);
$customerAddress->isObjectNew(true);
$customerAddress->setData(
    [
        'entity_id' => 3,
        'attribute_set_id' => 2,
        'telephone' => '+34912759224',
        'postcode' => 28045,
        'country_id' => 'ES',
        'city' => 'Madrid',
        'street' => ['Calle de Méndez Álvaro, 72'],
        'lastname' => 'Last',
        'firstname' => 'First',
        'parent_id' => 1,
        'region_id' => 1,
    ]
)->setCustomerId(2)
    ->setStoreId($secondStore->getId())
    ->setWebsiteId($secondWebsite->getId());
$customerAddress->save();
