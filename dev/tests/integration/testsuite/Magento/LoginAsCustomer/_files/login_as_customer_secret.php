<?php
/**
 * Copyright 2025 Adobe
 * All rights reserved.
 */
declare(strict_types=1);

use Magento\LoginAsCustomer\Model\GenerateAuthenticationSecret;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\LoginAsCustomerApi\Api\Data\AuthenticationDataInterfaceFactory;
use Magento\User\Model\User;

$objectManager = Bootstrap::getObjectManager();

$user = Bootstrap::getObjectManager()->create(User::class);
$user->load('TestAdmin1', 'username');
$authData = $objectManager->get(AuthenticationDataInterfaceFactory::class)->create([
    'customerId' => 1,
    'adminId' => $user->getId(),
]);

$secret = $objectManager->get(GenerateAuthenticationSecret::class)->execute($authData);

/**
 * Make secret accessible to the test
 */
$GLOBALS['login_as_customer_secret'] = $secret;
