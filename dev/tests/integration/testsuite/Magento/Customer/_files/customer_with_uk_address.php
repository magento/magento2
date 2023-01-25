<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\AddressRegistry;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\Website;
use Magento\Store\Model\WebsiteRepository;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->create(CustomerRepositoryInterface::class);
/** @var CustomerFactory $customerFactory */
$customerFactory = $objectManager->get(CustomerFactory::class);
$customer = $customerFactory->create();
/** @var CustomerRegistry $customerRegistry */
$customerRegistry = $objectManager->get(CustomerRegistry::class);
/** @var WebsiteRepository $websiteRepository */
$websiteRepository = $objectManager->create(WebsiteRepositoryInterface::class);
/** @var Website $mainWebsite */
$mainWebsite = $websiteRepository->get('base');
/** @var EncryptorInterface $encryptor */
$encryptor = $objectManager->get(EncryptorInterface::class);

$customer->setWebsiteId($mainWebsite->getId())
    ->setEmail('customer_uk_address@test.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId($mainWebsite->getDefaultStore()->getId())
    ->setIsActive(1)
    ->setPrefix('Mr.')
    ->setFirstname('John')
    ->setMiddlename('A')
    ->setLastname('Smith')
    ->setSuffix('Esq.')
    ->setTaxvat('12')
    ->setGender(0);
/** @var AddressFactory $customerAddressFactory */
$customerAddressFactory = $objectManager->get(AddressFactory::class);
/** @var AddressRepositoryInterface $customerAddressRepository */
$customerAddressRepository = $objectManager->create(AddressRepositoryInterface::class);
/** @var Address $customerAddress */
$customerAddress = $customerAddressFactory->create();
$customerAddress->isObjectNew(true);
$customerAddress->setData(
    [
        'attribute_set_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
        AddressInterface::TELEPHONE => 3468676,
        AddressInterface::POSTCODE => 'EC1A 1AA',
        AddressInterface::COUNTRY_ID => 'GB',
        AddressInterface::CITY => 'London',
        AddressInterface::COMPANY => 'CompanyName',
        AddressInterface::STREET => 'test street address',
        AddressInterface::LASTNAME => 'Smith',
        AddressInterface::FIRSTNAME => 'John',
        AddressInterface::REGION_ID => 1,
    ]
);
$customer->addAddress($customerAddress);
$customer->isObjectNew(true);
$customerDataModel = $customerRepository->save($customer->getDataModel(), $encryptor->hash('password'));
$addressId = $customerDataModel->getAddresses()[0]->getId();
$customerDataModel->setDefaultShipping($addressId);
$customerDataModel->setDefaultBilling($addressId);
$customerRepository->save($customerDataModel);
$customerRegistry->remove($customerDataModel->getId());
/** @var AddressRegistry $addressRegistry */
$addressRegistry = $objectManager->get(AddressRegistry::class);
$addressRegistry->remove($customerAddress->getId());
