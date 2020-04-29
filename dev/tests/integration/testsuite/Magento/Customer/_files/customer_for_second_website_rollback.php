<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/../../../Magento/Store/_files/second_website_with_two_stores_rollback.php';

$objectManager = Bootstrap::getObjectManager();
/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

try {
    $customer = $customerRepository->get('customer@example.com', $websiteId);
    $customerRepository->delete($customer);
} catch (NoSuchEntityException $e) {
    //customer already deleted
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
