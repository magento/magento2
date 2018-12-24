<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

include __DIR__ . '/customer_confirmation_config_enable_rollback.php';

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

/** @var Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = Bootstrap::getObjectManager()->create(CustomerRepositoryInterface::class);
$customer = $customerRepository->get('customer+confirmation@example.com');

if ($customer->getId()) {
    $customerRepository->delete($customer);
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
