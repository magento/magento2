<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\ResourceModel\Grid;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Customer grid collection tests.
 */
class CollectionTest extends \Magento\TestFramework\Indexer\TestCase
{
    /** @var \Magento\Framework\ObjectManagerInterface */
    private $objectManager;

    /** @var IndexerRegistry */
    private $indexerRegistry;

    /** @var \Magento\Customer\Model\ResourceModel\Grid\Collection */
    private $targetObject;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->indexerRegistry = $this->objectManager->create(IndexerRegistry::class);
        $this->targetObject = $this->objectManager
            ->create(\Magento\Customer\Model\ResourceModel\Grid\Collection::class);
        $this->customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
    }

    /**
     * Test updated data for customer grid indexer during save/update customer data(including address data)
     * in 'Update on Schedule' mode.
     *
     * Customer Grid Indexer can't work in 'Update on Schedule' mode. All data for indexer must be updated in realtime
     * during save/update customer data(including address data).
     *
     * @magentoDataFixture Magento/Customer/_files/customer_grid_indexer_enabled_update_on_schedule.php
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     */
    public function testGetItemByIdForUpdateOnSchedule()
    {
        /** Verify after first save */
        /** @var CustomerInterface $newCustomer */
        $newCustomer = $this->customerRepository->get('customer@example.com');
        /** @var CustomerInterface $item */
        $item = $this->targetObject->getItemById($newCustomer->getId());
        $this->assertNotEmpty($item);
        $this->assertSame($newCustomer->getEmail(), $item->getEmail());
        $this->assertSame('test street test city Armed Forces Middle East 01001', $item->getBillingFull());

        /** Verify after update */
        $newCustomer->setEmail('customer_updated@example.com');
        $this->customerRepository->save($newCustomer);
        $this->targetObject->clear();
        $item = $this->targetObject->getItemById($newCustomer->getId());
        $this->assertSame($newCustomer->getEmail(), $item->getEmail());
    }
}
