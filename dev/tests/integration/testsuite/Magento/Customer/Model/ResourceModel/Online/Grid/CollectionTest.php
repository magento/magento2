<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\ResourceModel\Online\Grid;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Fixture\CustomerVisitors;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Customer\Model\ResourceModel\Online\Grid\Collection
 */
class CollectionTest extends TestCase
{
    /**
     * @return void
     */
    #[
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(
            CustomerVisitors::class,
            [
                'customer_id' => '$customer.id$', 'count' => 3, 'include_guest' => true
            ],
            as: 'visitors'
        )
    ]
    public function testReturnsOnlyLatestSessionPerCustomerAndKeepsGuestRows(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var DataFixtureStorage $fixtures */
        $fixtures = DataFixtureStorageManager::getStorage();
        $customer = $fixtures->get('customer');
        $visitors = $fixtures->get('visitors');

        /** @var Collection $collection */
        $collection = $objectManager->create(Collection::class);
        $items = $collection->getItems();
        $this->assertNotEmpty($items, 'Online collection should not be empty');

        $customerRows = [];
        $guestRows = [];
        foreach ($items as $item) {
            $customerId = (int)($item->getData('customer_id') ?? 0);
            if ($customerId === (int)$customer->getId()) {
                $customerRows[] = $item;
            } elseif ($customerId === 0) {
                $guestRows[] = $item;
            }
        }

        $this->assertCount(1, $customerRows, 'Expected exactly one row for the logged-in customer');

        $insertedVisitorIds = (array)$visitors->getData('visitor_ids');
        $this->assertNotEmpty($insertedVisitorIds, 'Fixture did not provide inserted visitor IDs');
        $expectedLatestVisitorId = (int)end($insertedVisitorIds);
        $this->assertSame(
            $expectedLatestVisitorId,
            (int)$customerRows[0]->getData('visitor_id'),
            'Customer row should reference the latest visitor session'
        );

        $this->assertCount(1, $guestRows, 'Expected exactly one guest row to be present');
    }
}
