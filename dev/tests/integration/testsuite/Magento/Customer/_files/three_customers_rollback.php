<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Integration\Model\Oauth\Token\RequestThrottler;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$customersToRemove = [
    'customer@search.example.com',
    'customer2@search.example.com',
    'customer3@search.example.com',
];

/**
 * @var Magento\Customer\Api\CustomerRepositoryInterface
 */
$customerRepository = $objectManager->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
/**
 * @var RequestThrottler $throttler
 */
$throttler = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(RequestThrottler::class);
foreach ($customersToRemove as $customerEmail) {
    try {
        $customer = $customerRepository->get($customerEmail);
        $customerRepository->delete($customer);
    } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
        /**
         * Tests which are wrapped with MySQL transaction clear all data by transaction rollback.
         */
        continue;
    }

    /* Unlock account if it was locked for tokens retrieval */
    $throttler->resetAuthenticationFailuresCount($customerEmail, RequestThrottler::USER_TYPE_CUSTOMER);
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
