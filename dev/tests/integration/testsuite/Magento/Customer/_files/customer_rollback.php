<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Integration\Model\Oauth\Token\RequestThrottler;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$objectManager->removeSharedInstance(CustomerRegistry::class);
/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->create(CustomerRepositoryInterface::class);
/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

try {
    $customer = $customerRepository->get('customer@example.com');
    $customerRepository->delete($customer);
} catch (NoSuchEntityException $e) {
    // Customer with the specified email does not exist
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

/* Unlock account if it was locked for tokens retrieval */
/** @var RequestThrottler $throttler */
$throttler = $objectManager->create(RequestThrottler::class);
$throttler->resetAuthenticationFailuresCount('customer@example.com', RequestThrottler::USER_TYPE_CUSTOMER);
