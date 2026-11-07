<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Element\UiComponent\DataProvider;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterApplierInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for FilterPool non-database and database collection paths.
 *
 * @see \Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool
 */
class FilterPoolTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var FilterApplierInterface&MockObject
     */
    private FilterApplierInterface $regularApplier;

    /**
     * @var FilterPool
     */
    private FilterPool $filterPool;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->regularApplier = $this->createMock(FilterApplierInterface::class);
        $this->filterPool = new FilterPool(['regular' => $this->regularApplier]);
    }

    /**
     * Non-AbstractDb collections must not call getSelect() (GitHub #32292).
     */
    public function testApplyFiltersOnNonDbCollectionWithEmptyFilterGroupsDoesNotCallGetSelect(): void
    {
        $criteria = $this->createMock(SearchCriteriaInterface::class);
        $criteria->method('getFilterGroups')->willReturn([]);

        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addFieldToFilter'])
            ->getMock();

        $this->regularApplier->expects($this->never())->method('apply');

        $this->filterPool->applyFilters($collection, $criteria);
        $this->addToAssertionCount(1);
    }

    /**
     * Filters on non-AbstractDb collections are delegated to appliers only.
     */
    public function testApplyFiltersOnNonDbCollectionInvokesAppliers(): void
    {
        $filterA = $this->createFilterMock('eq', 'sku', 'ABC');
        $filterB = $this->createFilterMock('like', 'name', '%test%');

        $group = $this->createMock(FilterGroup::class);
        $group->method('getFilters')->willReturn([$filterA, $filterB]);

        $criteria = $this->createMock(SearchCriteriaInterface::class);
        $criteria->method('getFilterGroups')->willReturn([$group]);

        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addFieldToFilter'])
            ->getMock();

        $applied = [];
        $this->regularApplier->expects($this->exactly(2))
            ->method('apply')
            ->willReturnCallback(function ($coll, $filter) use ($collection, &$applied) {
                $this->assertSame($collection, $coll);
                $applied[] = $filter;
            });

        $this->filterPool->applyFilters($collection, $criteria);

        $this->assertSame([$filterA, $filterB], $applied);
    }

    /**
     * Condition-type-specific appliers are preferred over the regular applier.
     */
    public function testApplyFiltersOnNonDbCollectionUsesConditionSpecificApplier(): void
    {
        $fulltextApplier = $this->createMock(FilterApplierInterface::class);
        $filterPool = new FilterPool([
            'regular' => $this->regularApplier,
            'fulltext' => $fulltextApplier,
        ]);

        $filter = $this->createFilterMock('fulltext', 'query', 'search term');
        $group = $this->createMock(FilterGroup::class);
        $group->method('getFilters')->willReturn([$filter]);

        $criteria = $this->createMock(SearchCriteriaInterface::class);
        $criteria->method('getFilterGroups')->willReturn([$group]);

        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->regularApplier->expects($this->never())->method('apply');
        $fulltextApplier->expects($this->once())
            ->method('apply')
            ->with($collection, $filter);

        $filterPool->applyFilters($collection, $criteria);
    }

    /**
     * AbstractDb path still captures and rewrites Select WHERE parts for OR groups.
     */
    public function testApplyFiltersOnDbCollectionRegroupsWherePartsWithOr(): void
    {
        $whereState = [];
        $select = $this->createMock(Select::class);
        $select->method('getPart')
            ->with(Select::WHERE)
            ->willReturnCallback(static function () use (&$whereState) {
                return $whereState;
            });
        $select->method('reset')
            ->with(Select::WHERE)
            ->willReturnCallback(static function () use (&$whereState) {
                $whereState = [];
            });
        $select->method('setPart')
            ->willReturnCallback(static function ($part, $value) use (&$whereState) {
                if ($part === Select::WHERE) {
                    $whereState = $value;
                }
            });

        /** @var AbstractDb&MockObject $collection */
        $collection = $this->createPartialMockWithReflection(
            AbstractDb::class,
            ['getSelect', 'getResource']
        );
        $collection->method('getSelect')->willReturn($select);

        $filterA = $this->createFilterMock('eq', 'email', '%1%');
        $filterB = $this->createFilterMock('eq', 'email', '%2%');

        $group = $this->createMock(FilterGroup::class);
        $group->method('getFilters')->willReturn([$filterA, $filterB]);

        $criteria = $this->createMock(SearchCriteriaInterface::class);
        $criteria->method('getFilterGroups')->willReturn([$group]);

        $call = 0;
        $this->regularApplier->expects($this->exactly(2))
            ->method('apply')
            ->willReturnCallback(function () use (&$whereState, &$call) {
                $call++;
                // Simulate RegularFilter / addFieldToFilter appending a WHERE part
                $whereState[] = ($call === 1 ? '' : 'AND ') . "email LIKE '%{$call}%'";
            });

        $this->filterPool->applyFilters($collection, $criteria);

        $this->assertNotEmpty($whereState);
        $combined = implode(' ', $whereState);
        $this->assertStringContainsString(Select::SQL_OR, $combined);
        $this->assertStringContainsString("email LIKE '%1%'", $combined);
        $this->assertStringContainsString("email LIKE '%2%'", $combined);
    }

    /**
     * Empty filter groups on AbstractDb must not error.
     */
    public function testApplyFiltersOnDbCollectionWithEmptyFilterGroups(): void
    {
        $select = $this->createMock(Select::class);
        $select->method('getPart')->with(Select::WHERE)->willReturn([]);
        $select->expects($this->never())->method('setPart');

        /** @var AbstractDb&MockObject $collection */
        $collection = $this->createPartialMockWithReflection(
            AbstractDb::class,
            ['getSelect', 'getResource']
        );
        $collection->method('getSelect')->willReturn($select);

        $criteria = $this->createMock(SearchCriteriaInterface::class);
        $criteria->method('getFilterGroups')->willReturn([]);

        $this->regularApplier->expects($this->never())->method('apply');
        $this->filterPool->applyFilters($collection, $criteria);
        $this->addToAssertionCount(1);
    }

    /**
     * @param string $conditionType
     * @param string $field
     * @param string $value
     * @return Filter&MockObject
     */
    private function createFilterMock(string $conditionType, string $field, string $value): Filter
    {
        $filter = $this->createMock(Filter::class);
        $filter->method('getConditionType')->willReturn($conditionType);
        $filter->method('getField')->willReturn($field);
        $filter->method('getValue')->willReturn($value);
        return $filter;
    }
}
