<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Store\Model\Store;

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/websites_different_countries.php');

$objectManager = Bootstrap::getObjectManager();
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$website = $websiteRepository->get('test');
$store = $objectManager->create(Store::class);
$store->load('fixture_second_store', 'code');
/** @var CustomerInterface $customer */
$customer = $objectManager->create(CustomerInterface::class);
$customer->setWebsiteId($website->getId())
    ->setEmail('customer.web@example.com')
    ->setGroupId(1)
    ->setStoreId($store->getId())
    ->setFirstname('John')
    ->setLastname('Doe')
    ->setGender(1);

/** @var $repository CustomerRepositoryInterface */
$repository = $objectManager->get(CustomerRepositoryInterface::class);
$customer = $repository->save($customer);
