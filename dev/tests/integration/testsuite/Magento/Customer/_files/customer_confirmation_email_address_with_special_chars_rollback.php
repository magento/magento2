<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer_confirmation_config_enable_rollback.php');
/** @var Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = Bootstrap::getObjectManager()->create(CustomerRepositoryInterface::class);

try {
    $customer = $customerRepository->get('customer+confirmation@example.com');
    $customerRepository->delete($customer);
} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
    // Customer with the specified email does not exist
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
