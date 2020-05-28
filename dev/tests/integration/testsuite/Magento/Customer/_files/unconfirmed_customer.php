<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\Data\CustomerFactory;
use Magento\Eav\Model\AttributeRepository;
use Magento\Framework\Math\Random;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var AccountManagementInterface $accountManagment */
$accountManagment = $objectManager->get(AccountManagementInterface::class);
/** @var CustomerFactory $customerFactory */
$customerFactory = $objectManager->get(CustomerFactory::class);
/** @var Random $random */
$random = $objectManager->get(Random::class);
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$customer = $customerFactory->create();
$website = $websiteRepository->get('base');
$defaultStoreId = $website->getDefaultStore()->getId();
/** @var AttributeRepository $attributeRepository */
$attributeRepository = $objectManager->get(AttributeRepository::class);
$gender = $attributeRepository->get(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, 'gender')
    ->getSource()->getOptionId('Male');

$customer->setWebsiteId($website->getId())
    ->setEmail('unconfirmedcustomer@example.com')
    ->setGroupId(1)
    ->setStoreId($defaultStoreId)
    ->setPrefix('Mr.')
    ->setFirstname('John')
    ->setMiddlename('A')
    ->setLastname('Smith')
    ->setSuffix('Esq.')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1)
    ->setConfirmation($random->getUniqueHash())
    ->setGender($gender);

$accountManagment->createAccount($customer, 'Qwert12345');
