<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

/** @var CustomerRegistry $customerRegistry */
$customerRegistry = Bootstrap::getObjectManager()->get(CustomerRegistry::class);
/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);

/** @var Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

try {
    $customerRepository->deleteById(1);
} catch (NoSuchEntityException $e) {
    /**
     * Tests which are wrapped with MySQL transaction clear all data by transaction rollback.
     */
}
$customerRegistry->remove(1);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
