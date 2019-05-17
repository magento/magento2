<?php
/**
 * Fixture for Customer List method.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Customer\Model\Customer;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var $repository \Magento\Customer\Api\CustomerRepositoryInterface */
$repository = $objectManager->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
$customer = $objectManager->create(\Magento\Customer\Model\Customer::class);

/** @var Magento\Customer\Model\Customer $customer */
$customer->setWebsiteId(1)
    ->setId(1)
    ->setEmail('customer_1@example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setPrefix('Mr.')
    ->setFirstname('John')
    ->setMiddlename('A')
    ->setLastname('Smith')
    ->setSuffix('Esq.')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1)
    ->setTaxvat('12')
    ->setGender(0);

$customer->isObjectNew(true);
$customer->save();

$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Customer\Model\Customer::class);
$customer->setWebsiteId(1)
    ->setEntityId(2)
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('customer_2@example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('Firstname')
    ->setLastname('Lastname')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1);

$customer->isObjectNew(true);
$customer->save();

/** @var IndexerRegistry $indexerRegistry */
$indexerRegistry = $objectManager->create(IndexerRegistry::class);
/** @var IndexerInterface $indexer */
$indexer = $indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID);
try {
    $indexer->reindexAll();
} catch (\Exception $e) {
}
