<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Customer\Model\Customer;
use Magento\TestFramework\Helper\Bootstrap;

include __DIR__ . '/customer_confirmation_config_enable.php';

$objectManager = Bootstrap::getObjectManager();
/** @var Customer $customer */
$customer = $objectManager->create(Customer::class);
$customer->setWebsiteId(1)
    ->setId(1)
    ->setEmail('customer+confirmation@example.com')
    ->setPassword('password')
    ->setConfirmation($customer->getRandomConfirmationKey())
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('John')
    ->setLastname('Smith')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1)
    ->setTaxvat('12')
    ->setGender(0);

$customer->isObjectNew(true);
$customer->save();
