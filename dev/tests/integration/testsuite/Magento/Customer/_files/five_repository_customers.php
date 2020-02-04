<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Config as EavModelConfig;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->create(CustomerRepositoryInterface::class);
/** @var CustomerInterfaceFactory $customerFactory */
$customerFactory = $objectManager->get(CustomerInterfaceFactory::class);

for ($i = 1; $i <= 5; $i++) {
    /** @var CustomerInterface $customer */
    $customer = $customerFactory->create();
    $customer->setFirstname('John')
        ->setGroupId(1)
        ->setLastname('Smith')
        ->setWebsiteId(1)
        ->setEmail('customer'.$i.'@example.com');
    try {
        $customerRepository->save($customer, 'password');
    } catch (\Exception $e) {
    }
}

/** @var EavModelConfig $eavConfig */
$eavConfig = $objectManager->get(EavModelConfig::class);
$eavConfig->clear();

/** @var IndexerRegistry $indexerRegistry */
$indexerRegistry = $objectManager->create(IndexerRegistry::class);
/** @var IndexerInterface $indexer */
$indexer = $indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID);
try {
    $indexer->reindexAll();
} catch (\Exception $e) {
}
