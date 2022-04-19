<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Data\CustomerFactory;
use Magento\Customer\Model\GroupManagement;
use Magento\JwtUserToken\Api\Data\Revoked;
use Magento\JwtUserToken\Api\RevokedRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$websiteId = $websiteRepository->get('second')->getId();
/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);
$store = $storeManager->getStore('second_store_view');
$defaultGroupId = $objectManager->get(GroupManagement::class)->getDefaultGroup($store->getStoreId())->getId();
/** @var $repository CustomerRepositoryInterface */
$repository = $objectManager->create(CustomerRepositoryInterface::class);
$customer = $objectManager->create(Customer::class);
/** @var CustomerRegistry $customerRegistry */
$customerRegistry = $objectManager->get(CustomerRegistry::class);
/** @var Customer $customer */
$customer->setWebsiteId($websiteId)
    ->setId(2)
    ->setEmail('axl@rose.com')
    ->setPassword('axl@123')
    ->setGroupId($defaultGroupId)
    ->setStoreId($store->getStoreId())
    ->setIsActive(1)
    ->setPrefix('Mr.')
    ->setFirstname('Axl')
    ->setLastname('Rose')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1)
    ->setTaxvat('12')
    ->setGender(0);

$customer->isObjectNew(true);
$customer->save();
$customerRegistry->remove($customer->getId());
/** @var RevokedRepositoryInterface $revokedRepo */
$revokedRepo = $objectManager->get(RevokedRepositoryInterface::class);
$revokedRepo->saveRevoked(
    new Revoked(
        UserContextInterface::USER_TYPE_CUSTOMER,
        (int) $customer->getId(),
        time() - 3600 * 24
    )
);
