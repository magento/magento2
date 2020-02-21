<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\Data\CustomerFactory;
use Magento\Customer\Model\GroupManagement;
use Magento\Eav\Model\AttributeRepository;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var AccountManagementInterface $accountManagement */
$accountManagement = $objectManager->get(AccountManagementInterface::class);
$customerFactory = $objectManager->get(CustomerFactory::class);
$customerFactory->create();
/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);
$website = $storeManager->getWebsite('base');
/** @var GroupManagement $groupManagement */
$groupManagement = $objectManager->get(GroupManagement::class);
$defaultStoreId = $website->getDefaultStore()->getId();
/** @var AttributeRepository $attributeRepository */
$attributeRepository = $objectManager->get(AttributeRepository::class);
$gender = $attributeRepository->get(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, 'gender')
    ->getSource()->getOptionId('Male');
$customer = $customerFactory->create();
$customer->setWebsiteId($website->getId())
    ->setEmail('new_customer@example.com')
    ->setGroupId($groupManagement->getDefaultGroup($defaultStoreId)->getId())
    ->setStoreId($defaultStoreId)
    ->setPrefix('Mr.')
    ->setFirstname('John')
    ->setMiddlename('A')
    ->setLastname('Smith')
    ->setSuffix('Esq.')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1)
    ->setGender($gender);
$accountManagement->createAccount($customer, 'Qwert12345');
