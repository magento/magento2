<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

require 'customer_rollback.php';

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;

/** @var \Magento\Framework\Registry $registry */
$registry = Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);
try {
    $customer = $customerRepository->get('customer_two@example.com');
    $customerRepository->delete($customer);
} catch (NoSuchEntityException $e) {
    /** Tests which are wrapped with MySQL transaction clear all data by transaction rollback. */
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
