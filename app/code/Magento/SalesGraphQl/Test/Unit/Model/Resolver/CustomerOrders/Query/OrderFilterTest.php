<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Test\Unit\Model\Resolver\CustomerOrders\Query;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\SalesGraphQl\Model\Resolver\CustomerOrders\Query\OrderFilter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrderFilterTest extends TestCase
{
    /**
     * @var FilterBuilder|MockObject
     */
    private $filterBuilderMock;

    /**
     * @var FilterGroupBuilder|MockObject
     */
    private $filterGroupBuilderMock;

    /**
     * @var OrderFilter
     */
    private $model;

    protected function setUp(): void
    {
        $this->filterBuilderMock = $this->createMock(FilterBuilder::class);
        $this->filterGroupBuilderMock = $this->createMock(FilterGroupBuilder::class);
        $this->model = new OrderFilter($this->filterBuilderMock, $this->filterGroupBuilderMock);
    }

    public function testCreateFilterGroups(): void
    {
        $userId = 123;
        $storeId = 5;
        $storeIds = [3, 4];
        $args = [];

        $customerFilterBuilderMock = $this->createMock(FilterBuilder::class);
        $customerFilterBuilderMock->expects(self::once())->method('setValue')->with($userId)->willReturnSelf();
        $customerFilterBuilderMock->expects(self::once())->method('setConditionType')->with('eq')->willReturnSelf();
        $customerFilterMock = $this->createMock(Filter::class);
        $customerFilterBuilderMock->expects(self::once())->method('create')->willReturn($customerFilterMock);
        $storeFilterBuilderMock = $this->createMock(FilterBuilder::class);
        $storeFilterBuilderMock->expects(self::once())->method('setValue')->with([3, 4, 5])->willReturnSelf();
        $storeFilterBuilderMock->expects(self::once())->method('setConditionType')->with('in')->willReturnSelf();
        $storeFilterMock = $this->createMock(Filter::class);
        $storeFilterBuilderMock->expects(self::once())->method('create')->willReturn($storeFilterMock);
        $this->filterBuilderMock->method('setField')
            ->willReturnMap([
                ['customer_id', $customerFilterBuilderMock],
                ['store_id', $storeFilterBuilderMock],
            ]);

        $this->filterGroupBuilderMock->expects(self::exactly(2))->method('setFilters')->willReturnSelf();
        $customerFilterGroup = $this->createMock(FilterGroup::class);
        $storeFilterGroup = $this->createMock(FilterGroup::class);
        $this->filterGroupBuilderMock->expects(self::exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls($customerFilterGroup, $storeFilterGroup);

        $result = $this->model->createFilterGroups($args, $userId, $storeId, $storeIds);
        self::assertEquals([$customerFilterGroup, $storeFilterGroup], $result);
    }

    public function testCreateFilterGroupsWithDateRangeFilter(): void
    {
        $userId = 123;
        $storeId = 5;
        $storeIds = [3, 4];
        $args = [
            'filter' => [
                'order_date' => [
                    'from' => '2025-02-01',
                    'to' => '2025-03-30',
                ],
            ],
        ];

        $customerFilterBuilderMock = $this->createMock(FilterBuilder::class);
        $customerFilterBuilderMock->expects(self::once())->method('setValue')->with($userId)->willReturnSelf();
        $customerFilterBuilderMock->expects(self::once())->method('setConditionType')->with('eq')->willReturnSelf();
        $customerFilterMock = $this->createMock(Filter::class);
        $customerFilterBuilderMock->expects(self::once())->method('create')->willReturn($customerFilterMock);
        $storeFilterBuilderMock = $this->createMock(FilterBuilder::class);
        $storeFilterBuilderMock->expects(self::once())->method('setValue')->with([3, 4, 5])->willReturnSelf();
        $storeFilterBuilderMock->expects(self::once())->method('setConditionType')->with('in')->willReturnSelf();
        $storeFilterMock = $this->createMock(Filter::class);
        $storeFilterBuilderMock->expects(self::once())->method('create')->willReturn($storeFilterMock);
        $createdAtFilterBuilderMock = $this->createMock(FilterBuilder::class);
        $createdAtFilterBuilderMock->expects(self::exactly(2))->method('setValue')->willReturnSelf();
        $createdAtFilterBuilderMock->expects(self::exactly(2))->method('setConditionType')->willReturnSelf();
        $createdAtFromFilterMock = $this->createMock(Filter::class);
        $createdAtToFilterMock = $this->createMock(Filter::class);
        $createdAtFilterBuilderMock->expects(self::exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls($createdAtFromFilterMock, $createdAtToFilterMock);
        $this->filterBuilderMock->method('setField')
            ->willReturnMap([
                ['customer_id', $customerFilterBuilderMock],
                ['store_id', $storeFilterBuilderMock],
                ['created_at', $createdAtFilterBuilderMock],
            ]);

        $this->filterGroupBuilderMock->expects(self::exactly(3))->method('setFilters')->willReturnSelf();
        $customerFilterGroup = $this->createMock(FilterGroup::class);
        $storeFilterGroup = $this->createMock(FilterGroup::class);
        $createdAtFilterGroup = $this->createMock(FilterGroup::class);
        $this->filterGroupBuilderMock->expects(self::exactly(3))
            ->method('create')
            ->willReturnOnConsecutiveCalls($customerFilterGroup, $storeFilterGroup, $createdAtFilterGroup);

        $result = $this->model->createFilterGroups($args, $userId, $storeId, $storeIds);
        self::assertEquals([$customerFilterGroup, $storeFilterGroup, $createdAtFilterGroup], $result);
    }
}
