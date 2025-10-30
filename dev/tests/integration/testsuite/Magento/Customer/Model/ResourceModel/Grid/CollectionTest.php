<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Customer\Model\ResourceModel\Grid;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Indexer\TestCase;

/**
 * Customer grid collection tests.
 */
class CollectionTest extends TestCase
{
    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var \Magento\Framework\ObjectManagerInterface */
    private $objectManager;

    /** @var IndexerRegistry */
    private $indexerRegistry;

    /** @var \Magento\Customer\Model\ResourceModel\Grid\Collection */
    private $targetObject;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->indexerRegistry = $this->objectManager->create(IndexerRegistry::class);
        $this->targetObject = $this->objectManager
            ->create(\Magento\Customer\Model\ResourceModel\Grid\Collection::class);
        $this->customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
    }

    /**
     * Test updated data for customer grid indexer
     * in 'Update on Schedule' mode.
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     */
    public function testGetItemByIdForUpdateOnSchedule()
    {
        $indexer = $this->indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID);
        $indexer->reindexAll();
        $newCustomer = $this->customerRepository->get('customer@example.com');
        $item = $this->targetObject->getItemById($newCustomer->getId());
        $this->assertNotEmpty($item);
        $this->assertSame($newCustomer->getEmail(), $item->getEmail());
        $this->assertSame('test street test city Armed Forces Middle East 01001', $item->getBillingFull());

        /** set customer grid indexer on schedule' mode */
        $indexer->setScheduled(true);

        /** Verify after update */
        $newCustomer->setEmail('customer_updated@example.com');
        $this->customerRepository->save($newCustomer);
        $this->targetObject->clear();
        $item = $this->targetObject->getItemById($newCustomer->getId());
        $this->assertNotEquals('customer_updated@example.com', $item->getEmail());
    }

    /**
     * Verifies that filter condition date is being converted to config timezone before select sql query
     *
     * @return void
     */
    public function testAddFieldToFilter(): void
    {
        $filterDate = "2021-01-26 00:00:00";
        /** @var TimezoneInterface $timeZone */
        $timeZone = Bootstrap::getObjectManager()
            ->get(TimezoneInterface::class);
        /** @var Collection $gridCollection */
        $gridCollection = Bootstrap::getObjectManager()
            ->get(Collection::class);
        $convertedDate = $timeZone->convertConfigTimeToUtc($filterDate);
        $collection = $gridCollection->addFieldToFilter('created_at', ['qteq' => $filterDate]);
        $expectedSelect = "WHERE (((`main_table`.`created_at` = '{$convertedDate}')))";

        $this->assertStringContainsString($expectedSelect, $collection->getSelectSql(true));
    }
}
