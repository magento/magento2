<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Test\Fixture\PlaceOrderWithCustomerOrGuest as OrderFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[DbIsolation(true)]
#[DataFixture(ProductFixture::class, ['sku' => 'complete'], 'complete_product')]
#[DataFixture(ProductFixture::class, ['sku' => 'processing'], 'processing_product')]
#[DataFixture(ProductFixture::class, ['sku' => 'pending'], 'pending_product')]
#[DataFixture(
    OrderFixture::class,
    ['increment_id' => 'T37905-JOIN-01', 'items' => [['sku' => '$complete_product.sku$']]],
    'first_order'
)]
#[DataFixture(
    OrderFixture::class,
    ['increment_id' => 'T37905-JOIN-03', 'items' => [['sku' => '$processing_product.sku$']]],
    'second_order'
)]
#[DataFixture(
    OrderFixture::class,
    ['increment_id' => 'T37905-JOIN-02', 'items' => [['sku' => '$pending_product.sku$']]],
    'third_order'
)]
class ItemRepositoryExtensionAttributesTest extends TestCase
{
    /**
     * Verify hydrated values, joined filtering and sorting, and counts independent of pagination.
     *
     * @param string|null $minimumStatus
     * @param int $pageSize
     * @param int $currentPage
     * @param array $expectedStatuses
     * @param int $expectedTotal
     * @return void
     */
    #[DataProvider('searchCriteriaProvider')]
    public function testGetList(
        ?string $minimumStatus,
        int $pageSize,
        int $currentPage,
        array $expectedStatuses,
        int $expectedTotal
    ): void {
        $objectManager = Bootstrap::getObjectManager();
        $fixtures = DataFixtureStorageManager::getStorage();
        $orderIds = [
            $fixtures->get('first_order')->getId(),
            $fixtures->get('second_order')->getId(),
            $fixtures->get('third_order')->getId(),
        ];
        $sortOrder = $objectManager->create(SortOrderBuilder::class)
            ->setField('test_status')
            ->setDirection(SortOrder::SORT_DESC)
            ->create();
        $builder = $objectManager->create(SearchCriteriaBuilder::class);
        $builder->addFilter('order_id', $orderIds, 'in')
            ->setSortOrders([$sortOrder])
            ->setPageSize($pageSize)
            ->setCurrentPage($currentPage);
        if ($minimumStatus !== null) {
            $builder->addFilter('test_status', $minimumStatus, 'gteq');
        }
        $criteria = $builder->create();
        $repository = $objectManager->create(OrderItemRepositoryInterface::class);
        $result = $repository->getList($criteria);

        $this->assertSame($criteria, $result->getSearchCriteria());
        $this->assertSame($expectedTotal, $result->getTotalCount());
        $actualStatuses = [];
        foreach ($result->getItems() as $item) {
            $actualStatuses[] = $item->getExtensionAttributes()->getTestStatus();
        }
        $this->assertSame($expectedStatuses, $actualStatuses);
    }

    /**
     * Verify order item loading also hydrates joined attributes.
     *
     * @return void
     */
    public function testOrderItems(): void
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = clone DataFixtureStorageManager::getStorage()->get('first_order');
        $order->unsetData('items');

        $items = array_values($order->getItems());

        $this->assertCount(1, $items);
        $this->assertSame('complete', $items[0]->getExtensionAttributes()->getTestStatus());
    }

    /**
     * @return array
     */
    public static function searchCriteriaProvider(): array
    {
        return [
            'all joined values in descending order' => [null, 10, 1, ['processing', 'pending', 'complete'], 3],
            'joined filter and first page' => ['pending', 1, 1, ['processing'], 2],
            'joined filter and second page' => ['pending', 1, 2, ['pending'], 2],
            'no matching joined value' => ['zzz', 1, 1, [], 0],
        ];
    }
}
