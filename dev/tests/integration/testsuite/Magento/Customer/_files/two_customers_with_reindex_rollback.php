<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Customer\Model\Customer;

/** @var \Magento\Framework\Registry $registry */
$objectManager = Bootstrap::getObjectManager();
$registry = $objectManager->get(\Magento\Framework\Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
try {
    $customer = $customerRepository->get('customer_1@example.com');
    $customerRepository->delete($customer);
} catch (NoSuchEntityException $e) {
    /** Tests which are wrapped with MySQL transaction clear all data by transaction rollback. */
}

try {
    $customer = $customerRepository->get('customer_2@example.com');
    $customerRepository->delete($customer);
} catch (NoSuchEntityException $e) {
    /** Tests which are wrapped with MySQL transaction clear all data by transaction rollback. */
}

/** @var IndexerRegistry $indexerRegistry */
$indexerRegistry = $objectManager->create(IndexerRegistry::class);
/** @var IndexerInterface $indexer */
$indexer = $indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID);
try {
    $indexer->reindexAll();
} catch (\Exception $e) {
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
