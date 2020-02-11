<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Api\AccountManagementInterface;
use \Magento\Customer\Model\Data\CustomerFactory;
use Magento\Framework\Math\Random;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var AccountManagementInterface $accountManagment */
$accountManagment = $objectManager->get(AccountManagementInterface::class);
/** @var CustomerFactory $customerFactory */
$customerFactory = $objectManager->get(CustomerFactory::class);
/** @var Random $random */
$random = $objectManager->get(Random::class);
/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);
$customer = $customerFactory->create();
$defaultStore = $storeManager->getDefaultStoreView();
$websiteId = $defaultStore->getWebsiteId();

$customer->setWebsiteId($websiteId)
    ->setEmail('unconfirmedcustomer@example.com')
    ->setGroupId(1)
    ->setStoreId($defaultStore->getId())
    ->setPrefix('Mr.')
    ->setFirstname('John')
    ->setMiddlename('A')
    ->setLastname('Smith')
    ->setSuffix('Esq.')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1)
    ->setTaxvat('12')
    ->setConfirmation($random->getUniqueHash())
    ->setGender(1);

$accountManagment->createAccount($customer, 'Qwert12345');
