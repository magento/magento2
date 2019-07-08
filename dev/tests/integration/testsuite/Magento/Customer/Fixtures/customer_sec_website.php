<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/../../Store/_files/websites_different_countries.php';

$objectManager = Bootstrap::getObjectManager();

/** @var CustomerInterface $customer */
$customer = $objectManager->create(CustomerInterface::class);
$customer->setWebsiteId($websiteId)
    ->setEmail('customer.web@example.com')
    ->setGroupId(1)
    ->setStoreId($store->getId())
    ->setFirstname('John')
    ->setLastname('Doe')
    ->setGender(1);

/** @var $repository CustomerRepositoryInterface */
$repository = $objectManager->get(CustomerRepositoryInterface::class);
$customer = $repository->save($customer);
