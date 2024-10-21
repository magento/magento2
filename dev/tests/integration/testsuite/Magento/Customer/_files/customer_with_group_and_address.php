<?php
/**
 * Fixture for Customer List method.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\AddressRegistry;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

$groupRepository = $objectManager->create(
    \Magento\Customer\Api\GroupRepositoryInterface::class
);

$groupFactory = $objectManager->create(
    \Magento\Customer\Api\Data\GroupInterfaceFactory::class
);
$groupDataObject = $groupFactory->create();
$groupDataObject->setCode('custom_group')->setTaxClassId(3);
$groupRepository->save($groupDataObject);
/** @var \Magento\Customer\Model\Group $customerGroup */
$customerGroup = $objectManager->create(\Magento\Customer\Model\Group::class)
    ->load('custom_group', 'customer_group_code');

//Creating customer
/** @var $repository CustomerRepositoryInterface */
$repository = $objectManager->create(CustomerRepositoryInterface::class);
/** @var Customer $customer */
$customer = $objectManager->create(Customer::class);
/** @var CustomerRegistry $customerRegistry */
$customerRegistry = $objectManager->get(CustomerRegistry::class);
$customer->setWebsiteId(1)
    ->setEmail('customer@example.com')
    ->setPassword('password')
    ->setGroupId($customerGroup->getId())
    ->setStoreId(1)
    ->setIsActive(1)
    ->setPrefix('Mr.')
    ->setFirstname('John')
    ->setMiddlename('A')
    ->setLastname('Smith')
    ->setSuffix('Esq.')
    ->setTaxvat('12')
    ->setGender(0)
    ->setId(1);

$customer->isObjectNew(true);
$customer->save();
$customerRegistry->remove($customer->getId());

//Creating address
/** @var Address $customerAddress */
$customerAddress = $objectManager->create(Address::class);
$customerAddress->isObjectNew(true);
$customerAddress->setData(
    [
        'attribute_set_id' => 2,
        'telephone' => 3468676,
        'postcode' => 75477,
        'country_id' => 'US',
        'city' => 'CityM',
        'company' => 'CompanyName',
        'street' => 'CustomerAddress1',
        'lastname' => 'Smith',
        'firstname' => 'John',
        'parent_id' => $customer->getId(),
        'region_id' => 1,
    ]
);
$customerAddress->save();
/** @var AddressRepositoryInterface $addressRepository */
$addressRepository = $objectManager->get(AddressRepositoryInterface::class);
$customerAddress = $addressRepository->getById($customerAddress->getId());
$customerAddress->setCustomerId($customer->getId());
$customerAddress->isDefaultBilling(true);
$customerAddress->setIsDefaultShipping(true);
$customerAddress = $addressRepository->save($customerAddress);

$customer->setDefaultBilling($customerAddress->getId());
$customer->setDefaultShipping($customerAddress->getId());
$customer->save();

$customerRegistry->remove($customerAddress->getCustomerId());
/** @var AddressRegistry $addressRegistry */
$addressRegistry = $objectManager->get(AddressRegistry::class);
$addressRegistry->remove($customerAddress->getId());

