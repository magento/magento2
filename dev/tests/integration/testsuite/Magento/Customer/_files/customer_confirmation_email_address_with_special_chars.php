<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\TestFramework\Helper\Bootstrap;

include __DIR__ . '/customer_confirmation_config_enable.php';

$objectManager = Bootstrap::getObjectManager();

/** @var Customer $customer */
$customer = $objectManager->create(Customer::class);
/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->create(CustomerRepositoryInterface::class);
/** @var CustomerInterface $customerInterface */
$customerInterface = $objectManager->create(CustomerInterface::class);

$customerInterface->setWebsiteId(1)
    ->setEmail('customer+confirmation@example.com')
    ->setConfirmation($customer->getRandomConfirmationKey())
    ->setGroupId(1)
    ->setStoreId(1)
    ->setFirstname('John')
    ->setLastname('Smith')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1)
    ->setTaxvat('12')
    ->setGender(0);

$customerRepository->save($customerInterface, 'password');
