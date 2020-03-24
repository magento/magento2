<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Data\CustomerFactory;
use Magento\Customer\Model\GroupManagement;
use Magento\Eav\Model\AttributeRepository;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/../../../Magento/Store/_files/second_website_with_two_stores.php';

$objectManager = Bootstrap::getObjectManager();
/** @var AccountManagementInterface $accountManagment */
$accountManagment = $objectManager->get(AccountManagementInterface::class);
/** @var CustomerFactory $customerFactory */
$customerFactory = $objectManager->get(CustomerFactory::class);
/** @var AttributeRepository $attributeRepository */
$attributeRepository = $objectManager->get(AttributeRepository::class);
$gender = $attributeRepository->get(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, CustomerInterface::GENDER)
    ->getSource()->getOptionId('Male');
$defaultGroupId = $objectManager->get(GroupManagement::class)->getDefaultGroup($store->getStoreId())->getId();

$customer = $customerFactory->create();
$customer->setWebsiteId($websiteId)
    ->setEmail('customer@example.com')
    ->setGroupId($defaultGroupId)
    ->setStoreId($store->getStoreId())
    ->setFirstname('John')
    ->setLastname('Smith')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1)
    ->setGender($gender);

$accountManagment->createAccount($customer, 'Apassword1');
