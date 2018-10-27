<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
declare(strict_types=1);
=======
>>>>>>> upstream/2.2-develop

use Magento\Integration\Model\Oauth\Token\RequestThrottler;
use Magento\Framework\Registry;
use Magento\Customer\Model\Customer;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Model\Address;

//Clearing websites.
<<<<<<< HEAD
include __DIR__ . '/../../Store/_files/websites_different_countries_rollback.php';
=======
include __DIR__
    . '/../../Store/_files/websites_different_countries_rollback.php';
>>>>>>> upstream/2.2-develop

/** @var Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);

//Removing customers.
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
/** @var $customer \Magento\Customer\Model\Customer*/
$customer = Bootstrap::getObjectManager()->create(Customer::class);
$customer->load(1);
if ($customer->getId()) {
    $customer->delete();
}
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
/* Unlock account if it was locked for tokens retrieval */
/** @var RequestThrottler $throttler */
$throttler = Bootstrap::getObjectManager()->create(RequestThrottler::class);
$throttler->resetAuthenticationFailuresCount(
    'customer@example.com',
    RequestThrottler::USER_TYPE_CUSTOMER
);
//Second customer.
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
/** @var $customer \Magento\Customer\Model\Customer*/
$customer = Bootstrap::getObjectManager()->create(Customer::class);
$customer->load(2);
if ($customer->getId()) {
    $customer->delete();
}
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
/* Unlock account if it was locked for tokens retrieval */
$throttler->resetAuthenticationFailuresCount(
    'customer2@example.com',
    RequestThrottler::USER_TYPE_CUSTOMER
);
