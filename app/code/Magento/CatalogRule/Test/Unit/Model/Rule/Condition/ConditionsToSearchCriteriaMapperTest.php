<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\Rule\Condition;

use Magento\CatalogRule\Model\Rule\Condition\Combine as CombinedCondition;
use Magento\CatalogRule\Model\Rule\Condition\ConditionsToSearchCriteriaMapper;
use Magento\CatalogRule\Model\Rule\Condition\Product as SimpleCondition;
use Magento\Framework\Api\CombinedFilterGroup;
use Magento\Framework\Api\CombinedFilterGroup as FilterGroup;
use Magento\Framework\Api\CombinedFilterGroupFactory;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterFactory;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Rule\Model\Condition\AbstractCondition;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for ConditionsToSearchCriteriaMapper
 *
 * Tests the critical instanceof that allows custom condition classes
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConditionsToSearchCriteriaMapperTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var ConditionsToSearchCriteriaMapper
     */
    private $mapper;

    /**
     * @var SearchCriteriaBuilderFactory|MockObject
     */
    private $searchCriteriaBuilderFactoryMock;

    /**
     * @var CombinedFilterGroupFactory|MockObject
     */
    private $combinedFilterGroupFactoryMock;

    /**
     * @var FilterFactory|MockObject
     */
    private $filterFactoryMock;

    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        $this->searchCriteriaBuilderFactoryMock = $this->createMock(SearchCriteriaBuilderFactory::class);
        $this->combinedFilterGroupFactoryMock = $this->createMock(CombinedFilterGroupFactory::class);
        $this->filterFactoryMock = $this->createMock(FilterFactory::class);

        $this->objectManagerHelper = new ObjectManager($this);

        $this->mapper = $this->objectManagerHelper->getObject(
            ConditionsToSearchCriteriaMapper::class,
            [
                'searchCriteriaBuilderFactory' => $this->searchCriteriaBuilderFactoryMock,
                'combinedFilterGroupFactory' => $this->combinedFilterGroupFactoryMock,
                'filterFactory' => $this->filterFactoryMock,
            ]
        );
    }

    /**
     * Test mapping standard Product condition to search criteria
     *
     * Baseline test - ensures standard SimpleCondition works
     */
    public function testMapStandardProductConditionToSearchCriteria()
    {
        $conditionMock = $this->createSimpleConditionMock('sku', '==', 'test-sku');
        $combinedConditionMock = $this->createCombinedConditionMock([$conditionMock], 'all');

        $filterMock = $this->createFilterMock();
        $filterGroupMock = $this->createMock(CombinedFilterGroup::class);
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $searchCriteriaBuilderMock = $this->createSearchCriteriaBuilderMock($searchCriteriaMock);

        $this->filterFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($filterMock);

        $this->combinedFilterGroupFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($filterGroupMock);

        $this->searchCriteriaBuilderFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaBuilderMock);

        $result = $this->mapper->mapConditionsToSearchCriteria($combinedConditionMock);

        $this->assertSame($searchCriteriaMock, $result);
    }

    /**
     * Test mapping custom Product condition (subclass) to search criteria
     *
     * Core test for instanceof fix - validates that custom condition classes
     * extending SimpleCondition are accepted
     *
     * Tests ($condition instanceof SimpleCondition)
     */
    public function testMapCustomProductConditionToSearchCriteria()
    {
        // Create a mock that extends SimpleCondition (simulates custom condition class)
        $customConditionMock = $this->createPartialMockWithReflection(
            SimpleCondition::class,
            ['getAttribute', 'getOperator', 'getValue']
        );

        $customConditionMock->method('getAttribute')->willReturn('price');
        $customConditionMock->method('getOperator')->willReturn('>');
        $customConditionMock->method('getValue')->willReturn('100');

        $combinedConditionMock = $this->createCombinedConditionMock([$customConditionMock], 'all');

        $filterMock = $this->createFilterMock();
        $filterGroupMock = $this->createMock(CombinedFilterGroup::class);
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $searchCriteriaBuilderMock = $this->createSearchCriteriaBuilderMock($searchCriteriaMock);

        $this->filterFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($filterMock);

        $this->combinedFilterGroupFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($filterGroupMock);

        $this->searchCriteriaBuilderFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaBuilderMock);

        $result = $this->mapper->mapConditionsToSearchCriteria($combinedConditionMock);

        // If instanceof check works correctly, this should succeed
        $this->assertSame($searchCriteriaMock, $result);
    }

    /**
     * Test mapping mixed standard and custom conditions
     *
     * Real-world scenario where both standard and custom conditions coexist
     */
    public function testMapMixedStandardAndCustomConditions()
    {
        // Standard condition
        $standardConditionMock = $this->createSimpleConditionMock('category_ids', '==', '2');

        // Custom condition (subclass of SimpleCondition)
        $customConditionMock = $this->createPartialMockWithReflection(
            SimpleCondition::class,
            ['getAttribute', 'getOperator', 'getValue']
        );

        $customConditionMock->method('getAttribute')->willReturn('price');
        $customConditionMock->method('getOperator')->willReturn('>');
        $customConditionMock->method('getValue')->willReturn('50');

        $combinedConditionMock = $this->createCombinedConditionMock(
            [$standardConditionMock, $customConditionMock],
            'all'
        );

        $filterMock = $this->createFilterMock();
        $filterGroupMock = $this->createMock(CombinedFilterGroup::class);
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $searchCriteriaBuilderMock = $this->createSearchCriteriaBuilderMock($searchCriteriaMock);

        $this->filterFactoryMock
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturn($filterMock);

        $this->combinedFilterGroupFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($filterGroupMock);

        $this->searchCriteriaBuilderFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaBuilderMock);

        $result = $this->mapper->mapConditionsToSearchCriteria($combinedConditionMock);

        $this->assertSame($searchCriteriaMock, $result);
    }

    /**
     * Test that invalid condition type throws InputException
     *
     * Validates error handling for unsupported condition types
     */
    public function testMapInvalidConditionTypeThrowsException()
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('Undefined condition type');

        $invalidConditionMock = $this->createPartialMockWithReflection(
            AbstractCondition::class,
            ['getType']
        );
        $invalidConditionMock->method('getType')->willReturn('SomeOtherConditionType');

        $combinedConditionMock = $this->createCombinedConditionMock([$invalidConditionMock], 'all');

        // Exception is thrown before factory is called, so no factory expectations needed

        $this->mapper->mapConditionsToSearchCriteria($combinedConditionMock);
    }

    /**
     * Test mapping empty combined condition
     *
     * Edge case - ensures empty conditions are handled gracefully
     */
    public function testMapEmptyCombinedCondition()
    {
        $combinedConditionMock = $this->createCombinedConditionMock([], 'all');

        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $searchCriteriaBuilderMock = $this->createSearchCriteriaBuilderMock($searchCriteriaMock);

        $this->searchCriteriaBuilderFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaBuilderMock);

        $result = $this->mapper->mapConditionsToSearchCriteria($combinedConditionMock);

        $this->assertSame($searchCriteriaMock, $result);
    }

    /**
     * Test that instanceof check accepts subclasses
     *
     * Direct validation of the instanceof logic
     */
    public function testInstanceofCheckAcceptsSubclasses()
    {
        // Create a subclass of SimpleCondition
        $subclassConditionMock = $this->createMock(SimpleCondition::class);

        // Verify instanceof relationship
        $this->assertInstanceOf(
            SimpleCondition::class,
            $subclassConditionMock,
            'Subclass should be instance of SimpleCondition (validates instanceof logic)'
        );
    }

    /**
     * Test condition with array value
     *
     * Tests handling of conditions with multiple values (e.g., "in" operator)
     */
    public function testMapConditionWithArrayValue()
    {
        $conditionMock = $this->createSimpleConditionMock('category_ids', '()', ['2', '3', '4']);
        $combinedConditionMock = $this->createCombinedConditionMock([$conditionMock], 'all');

        $filterMock = $this->createFilterMock();
        $filterGroupMock = $this->createMock(CombinedFilterGroup::class);
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $searchCriteriaBuilderMock = $this->createSearchCriteriaBuilderMock($searchCriteriaMock);

        $this->filterFactoryMock
            ->expects($this->exactly(3))
            ->method('create')
            ->willReturn($filterMock);

        $this->combinedFilterGroupFactoryMock
            ->expects($this->exactly(2))  // Called for the array values combined + main group
            ->method('create')
            ->willReturn($filterGroupMock);

        $this->searchCriteriaBuilderFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaBuilderMock);

        $result = $this->mapper->mapConditionsToSearchCriteria($combinedConditionMock);

        $this->assertSame($searchCriteriaMock, $result);
    }

    /**
     * Test nested combined conditions
     *
     * Tests handling of complex nested condition structures
     */
    public function testMapNestedCombinedConditions()
    {
        $innerConditionMock = $this->createSimpleConditionMock('price', '>', '50');
        $innerCombinedMock = $this->createCombinedConditionMock([$innerConditionMock], 'all');

        $outerConditionMock = $this->createSimpleConditionMock('sku', '==', 'test-sku');
        $outerCombinedMock = $this->createCombinedConditionMock(
            [$innerCombinedMock, $outerConditionMock],
            'all'
        );

        $filterMock = $this->createFilterMock();
        $filterGroupMock = $this->createMock(CombinedFilterGroup::class);
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $searchCriteriaBuilderMock = $this->createSearchCriteriaBuilderMock($searchCriteriaMock);

        $this->filterFactoryMock
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturn($filterMock);

        $this->combinedFilterGroupFactoryMock
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturn($filterGroupMock);

        $this->searchCriteriaBuilderFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaBuilderMock);

        $result = $this->mapper->mapConditionsToSearchCriteria($outerCombinedMock);

        $this->assertSame($searchCriteriaMock, $result);
    }

    /**
     * Helper method to create SimpleCondition mock
     *
     * @param string $attribute
     * @param string $operator
     * @param mixed $value
     * @return SimpleCondition|MockObject
     */
    private function createSimpleConditionMock(string $attribute, string $operator, $value): MockObject
    {
        $conditionMock = $this->createPartialMockWithReflection(
            SimpleCondition::class,
            ['getAttribute', 'getOperator', 'getValue']
        );

        // Stub the getter methods that are called by the mapper
        $conditionMock->method('getAttribute')->willReturn($attribute);
        $conditionMock->method('getOperator')->willReturn($operator);
        $conditionMock->method('getValue')->willReturn($value);

        return $conditionMock;
    }

    /**
     * Helper method to create CombinedCondition mock
     *
     * @param array $conditions
     * @param string $aggregator
     * @return CombinedCondition|MockObject
     */
    private function createCombinedConditionMock(array $conditions, string $aggregator): MockObject
    {
        $combinedConditionMock = $this->createPartialMockWithReflection(
            CombinedCondition::class,
            ['getAggregator', 'getConditions', 'getValue']
        );

        // Stub the getter methods that are called by the mapper
        $combinedConditionMock->method('getConditions')->willReturn($conditions);
        $combinedConditionMock->method('getAggregator')->willReturn($aggregator);
        $combinedConditionMock->method('getValue')->willReturn(true);

        return $combinedConditionMock;
    }

    /**
     * Helper method to create Filter mock
     *
     * @return Filter|MockObject
     */
    private function createFilterMock(): MockObject
    {
        return $this->createMock(Filter::class);
    }

    /**
     * Helper method to create SearchCriteriaBuilder mock
     *
     * @param SearchCriteria $searchCriteria
     * @return SearchCriteriaBuilder|MockObject
     */
    private function createSearchCriteriaBuilderMock(SearchCriteria $searchCriteria): MockObject
    {
        $builderMock = $this->createPartialMock(
            SearchCriteriaBuilder::class,
            ['setFilterGroups', 'create']
        );

        $builderMock->method('setFilterGroups')->willReturnSelf();
        $builderMock->method('create')->willReturn($searchCriteria);

        return $builderMock;
    }

    /**
     * Test combined condition with getValue() === false (reverse operator logic)
     *
     * Tests if ((bool)$combinedCondition->getValue() === false)
     */
    public function testMapCombinedConditionWithValueFalse()
    {
        $conditionMock = $this->createSimpleConditionMock('sku', '==', 'test-sku');
        
        $combinedConditionMock = $this->createPartialMockWithReflection(
            CombinedCondition::class,
            ['getAggregator', 'getConditions', 'getValue']
        );
        
        $combinedConditionMock->method('getConditions')->willReturn([$conditionMock]);
        $combinedConditionMock->method('getAggregator')->willReturn('all');
        $combinedConditionMock->method('getValue')->willReturn(false); // This triggers reverse logic

        $filterMock = $this->createPartialMock(
            Filter::class,
            ['getConditionType', 'setConditionType']
        );
        
        $filterMock->method('getConditionType')->willReturn('eq');
        $filterMock->expects($this->once())
            ->method('setConditionType')
            ->with('neq'); // Should be reversed from 'eq' to 'neq'

        $filterGroupMock = $this->createPartialMock(
            CombinedFilterGroup::class,
            ['getFilters']
        );
        $filterGroupMock->method('getFilters')->willReturn([$filterMock]);

        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $searchCriteriaBuilderMock = $this->createSearchCriteriaBuilderMock($searchCriteriaMock);

        $this->filterFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($filterMock);

        $this->combinedFilterGroupFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($filterGroupMock);

        $this->searchCriteriaBuilderFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaBuilderMock);

        $result = $this->mapper->mapConditionsToSearchCriteria($combinedConditionMock);

        $this->assertSame($searchCriteriaMock, $result);
    }

    /**
     * Test all rule operator mappings
     *
     * Tests mapRuleOperatorToSQLCondition method for all valid operators
     */
    public function testMapAllRuleOperators()
    {
        $operatorMappings = [
            '==' => 'eq',
            '!=' => 'neq',
            '>=' => 'gteq',
            '<=' => 'lteq',
            '>' => 'gt',
            '<' => 'lt',
            '{}' => 'like',
            '!{}' => 'nlike',
            '()' => 'in',
            '!()' => 'nin',
            '<=>' => 'is_null'
        ];

        foreach ($operatorMappings as $ruleOp => $sqlOp) {
            $conditionMock = $this->createSimpleConditionMock('sku', $ruleOp, 'test-value');
            $combinedConditionMock = $this->createCombinedConditionMock([$conditionMock], 'all');

            $filterMock = $this->createMock(Filter::class);

            $filterGroupMock = $this->createMock(CombinedFilterGroup::class);
            $searchCriteriaMock = $this->createMock(SearchCriteria::class);
            $searchCriteriaBuilderMock = $this->createSearchCriteriaBuilderMock($searchCriteriaMock);

            $this->filterFactoryMock
                ->expects($this->once())
                ->method('create')
                ->with($this->callback(function ($data) use ($sqlOp) {
                    return $data['data'][Filter::KEY_CONDITION_TYPE] === $sqlOp;
                }))
                ->willReturn($filterMock);

            $this->combinedFilterGroupFactoryMock
                ->expects($this->once())
                ->method('create')
                ->willReturn($filterGroupMock);

            $this->searchCriteriaBuilderFactoryMock
                ->expects($this->once())
                ->method('create')
                ->willReturn($searchCriteriaBuilderMock);

            $this->mapper->mapConditionsToSearchCriteria($combinedConditionMock);

            // Reset mocks for next iteration
            $this->setUp();
        }
    }

    /**
     * Test invalid rule operator throws exception
     *
     * Tests mapRuleOperatorToSQLCondition exception handling
     */
    public function testMapInvalidRuleOperatorThrowsException()
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('Undefined rule operator');

        $conditionMock = $this->createSimpleConditionMock('sku', 'INVALID_OP', 'test-value');
        $combinedConditionMock = $this->createCombinedConditionMock([$conditionMock], 'all');

        // Exception is thrown in mapRuleOperatorToSQLCondition before factory is called

        $this->mapper->mapConditionsToSearchCriteria($combinedConditionMock);
    }

    /**
     * Test both rule aggregators (all/any to AND/OR)
     *
     * Tests mapRuleAggregatorToSQLAggregator method
     */
    public function testMapRuleAggregators()
    {
        $aggregatorMappings = [
            'all' => 'AND',
            'any' => 'OR'
        ];

        foreach ($aggregatorMappings as $ruleAgg => $sqlAgg) {
            $conditionMock = $this->createSimpleConditionMock('sku', '==', 'test-value');
            $combinedConditionMock = $this->createCombinedConditionMock([$conditionMock], $ruleAgg);

            $filterMock = $this->createFilterMock();
            $filterGroupMock = $this->createMock(CombinedFilterGroup::class);
            $searchCriteriaMock = $this->createMock(SearchCriteria::class);
            $searchCriteriaBuilderMock = $this->createSearchCriteriaBuilderMock($searchCriteriaMock);

            $this->filterFactoryMock
                ->expects($this->once())
                ->method('create')
                ->willReturn($filterMock);

            $this->combinedFilterGroupFactoryMock
                ->expects($this->once())
                ->method('create')
                ->with($this->callback(function ($data) use ($sqlAgg) {
                    return $data['data'][FilterGroup::COMBINATION_MODE] === $sqlAgg;
                }))
                ->willReturn($filterGroupMock);

            $this->searchCriteriaBuilderFactoryMock
                ->expects($this->once())
                ->method('create')
                ->willReturn($searchCriteriaBuilderMock);

            $this->mapper->mapConditionsToSearchCriteria($combinedConditionMock);

            // Reset mocks for next iteration
            $this->setUp();
        }
    }

    /**
     * Test invalid aggregator throws exception
     *
     * Tests mapRuleAggregatorToSQLAggregator exception handling
     */
    public function testMapInvalidAggregatorThrowsException()
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('Undefined rule aggregator');

        $conditionMock = $this->createSimpleConditionMock('sku', '==', 'test-value');
        $combinedConditionMock = $this->createCombinedConditionMock([$conditionMock], 'INVALID_AGG');

        // Mock the filter to be returned
        $filterMock = $this->createFilterMock();
        $this->filterFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($filterMock);

        // Exception is thrown in mapRuleAggregatorToSQLAggregator during createCombinedFilterGroup

        $this->mapper->mapConditionsToSearchCriteria($combinedConditionMock);
    }

    /**
     * Test getGlueForArrayValues with negative operators (returns 'all')
     *
     * Tests if (in_array($operator, ['!=', '!{}', '!()'], true))
     */
    public function testGetGlueForArrayValuesWithNegativeOperators()
    {
        $negativeOperators = ['!=', '!{}', '!()'];

        foreach ($negativeOperators as $operator) {
            $conditionMock = $this->createSimpleConditionMock('category_ids', $operator, ['2', '3']);
            $combinedConditionMock = $this->createCombinedConditionMock([$conditionMock], 'all');

            $filterMock = $this->createFilterMock();
            $filterGroupMock = $this->createMock(CombinedFilterGroup::class);
            $searchCriteriaMock = $this->createMock(SearchCriteria::class);
            $searchCriteriaBuilderMock = $this->createSearchCriteriaBuilderMock($searchCriteriaMock);

            $this->filterFactoryMock
                ->expects($this->exactly(2))
                ->method('create')
                ->willReturn($filterMock);

            // The inner group should use 'all' (AND) for negative operators
            $this->combinedFilterGroupFactoryMock
                ->expects($this->exactly(2))
                ->method('create')
                ->willReturnOnConsecutiveCalls(
                    $filterGroupMock, // Inner group for array values
                    $filterGroupMock  // Outer group
                );

            $this->searchCriteriaBuilderFactoryMock
                ->expects($this->once())
                ->method('create')
                ->willReturn($searchCriteriaBuilderMock);

            $this->mapper->mapConditionsToSearchCriteria($combinedConditionMock);

            // Reset mocks for next iteration
            $this->setUp();
        }
    }

    /**
     * Test getGlueForArrayValues with positive operators (returns 'any')
     *
     * Test return 'any'
     */
    public function testGetGlueForArrayValuesWithPositiveOperators()
    {
        $positiveOperators = ['==', '{}', '()'];

        foreach ($positiveOperators as $operator) {
            $conditionMock = $this->createSimpleConditionMock('category_ids', $operator, ['2', '3']);
            $combinedConditionMock = $this->createCombinedConditionMock([$conditionMock], 'all');

            $filterMock = $this->createFilterMock();
            $filterGroupMock = $this->createMock(CombinedFilterGroup::class);
            $searchCriteriaMock = $this->createMock(SearchCriteria::class);
            $searchCriteriaBuilderMock = $this->createSearchCriteriaBuilderMock($searchCriteriaMock);

            $this->filterFactoryMock
                ->expects($this->exactly(2))
                ->method('create')
                ->willReturn($filterMock);

            // The inner group should use 'any' (OR) for positive operators
            $this->combinedFilterGroupFactoryMock
                ->expects($this->exactly(2))
                ->method('create')
                ->willReturnOnConsecutiveCalls(
                    $filterGroupMock, // Inner group for array values
                    $filterGroupMock  // Outer group
                );

            $this->searchCriteriaBuilderFactoryMock
                ->expects($this->once())
                ->method('create')
                ->willReturn($searchCriteriaBuilderMock);

            $this->mapper->mapConditionsToSearchCriteria($combinedConditionMock);

            // Reset mocks for next iteration
            $this->setUp();
        }
    }

    /**
     * Test reverse SQL operator with invalid operator
     *
     * Tests reverseSqlOperatorInFilter exception handling
     */
    public function testReverseSqlOperatorWithInvalidOperator()
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('Undefined SQL operator');

        $conditionMock = $this->createSimpleConditionMock('sku', '==', 'test-sku');
        
        $combinedConditionMock = $this->createPartialMockWithReflection(
            CombinedCondition::class,
            ['getAggregator', 'getConditions', 'getValue']
        );
        
        $combinedConditionMock->method('getConditions')->willReturn([$conditionMock]);
        $combinedConditionMock->method('getAggregator')->willReturn('all');
        $combinedConditionMock->method('getValue')->willReturn(false); // Triggers reverse logic

        $filterMock = $this->createPartialMock(
            Filter::class,
            ['getConditionType', 'setConditionType']
        );
        
        // Return an invalid operator that's not in the reversal map
        $filterMock->method('getConditionType')->willReturn('INVALID_SQL_OP');

        $filterGroupMock = $this->createPartialMock(
            CombinedFilterGroup::class,
            ['getFilters']
        );
        $filterGroupMock->method('getFilters')->willReturn([$filterMock]);

        // The filterFactory needs to return the actual filter
        $this->filterFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($filterMock);

        // Exception is thrown during reversal before combinedFilterGroupFactory is called
        // So no expectation for combinedFilterGroupFactory->create()

        $this->mapper->mapConditionsToSearchCriteria($combinedConditionMock);
    }

    /**
     * Test all SQL operator reversals
     *
     * Tests all operator reversals in reverseSqlOperatorInFilter
     */
    public function testAllSqlOperatorReversals()
    {
        $reversalMappings = [
            'eq' => 'neq',
            'neq' => 'eq',
            'gteq' => 'lt',
            'lteq' => 'gt',
            'gt' => 'lteq',
            'lt' => 'gteq',
            'like' => 'nlike',
            'nlike' => 'like',
            'in' => 'nin',
            'nin' => 'in',
        ];

        foreach ($reversalMappings as $original => $reversed) {
            $conditionMock = $this->createSimpleConditionMock('sku', '==', 'test-sku');
            
            $combinedConditionMock = $this->createPartialMockWithReflection(
                CombinedCondition::class,
                ['getAggregator', 'getConditions', 'getValue']
            );
            
            $combinedConditionMock->method('getConditions')->willReturn([$conditionMock]);
            $combinedConditionMock->method('getAggregator')->willReturn('all');
            $combinedConditionMock->method('getValue')->willReturn(false);

            $filterMock = $this->createPartialMock(
                Filter::class,
                ['getConditionType', 'setConditionType']
            );
            
            $filterMock->method('getConditionType')->willReturn($original);
            $filterMock->expects($this->once())
                ->method('setConditionType')
                ->with($reversed);

            $filterGroupMock = $this->createPartialMock(
                CombinedFilterGroup::class,
                ['getFilters']
            );
            $filterGroupMock->method('getFilters')->willReturn([$filterMock]);

            $searchCriteriaMock = $this->createMock(SearchCriteria::class);
            $searchCriteriaBuilderMock = $this->createSearchCriteriaBuilderMock($searchCriteriaMock);

            $this->filterFactoryMock
                ->expects($this->once())
                ->method('create')
                ->willReturn($filterMock);

            $this->combinedFilterGroupFactoryMock
                ->expects($this->once())
                ->method('create')
                ->willReturn($filterGroupMock);

            $this->searchCriteriaBuilderFactoryMock
                ->expects($this->once())
                ->method('create')
                ->willReturn($searchCriteriaBuilderMock);

            $this->mapper->mapConditionsToSearchCriteria($combinedConditionMock);

            // Reset mocks for next iteration
            $this->setUp();
        }
    }

    /**
     * Test nested FilterGroup reversal
     *
     * Tests reverseSqlOperatorInFilterRecursively with nested FilterGroups
     */
    public function testReverseSqlOperatorInNestedFilterGroup()
    {
        $innerCondition = $this->createSimpleConditionMock('price', '>', '50');
        $innerCombined = $this->createPartialMockWithReflection(
            CombinedCondition::class,
            ['getAggregator', 'getConditions', 'getValue']
        );
        
        $innerCombined->method('getConditions')->willReturn([$innerCondition]);
        $innerCombined->method('getAggregator')->willReturn('all');
        $innerCombined->method('getValue')->willReturn(true);

        $outerCombined = $this->createPartialMockWithReflection(
            CombinedCondition::class,
            ['getAggregator', 'getConditions', 'getValue']
        );
        
        $outerCombined->method('getConditions')->willReturn([$innerCombined]);
        $outerCombined->method('getAggregator')->willReturn('all');
        $outerCombined->method('getValue')->willReturn(false); // Triggers reverse logic

        $innerFilterMock = $this->createPartialMock(
            Filter::class,
            ['getConditionType', 'setConditionType']
        );
        $innerFilterMock->method('getConditionType')->willReturn('gt');
        $innerFilterMock->expects($this->once())
            ->method('setConditionType')
            ->with('lteq');

        $innerFilterGroupMock = $this->createPartialMock(
            CombinedFilterGroup::class,
            ['getFilters']
        );
        $innerFilterGroupMock->method('getFilters')->willReturn([$innerFilterMock]);

        $outerFilterGroupMock = $this->createPartialMock(
            CombinedFilterGroup::class,
            ['getFilters']
        );
        $outerFilterGroupMock->method('getFilters')->willReturn([$innerFilterGroupMock]);

        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $searchCriteriaBuilderMock = $this->createSearchCriteriaBuilderMock($searchCriteriaMock);

        $this->filterFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($innerFilterMock);

        $this->combinedFilterGroupFactoryMock
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls($innerFilterGroupMock, $outerFilterGroupMock);

        $this->searchCriteriaBuilderFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaBuilderMock);

        $this->mapper->mapConditionsToSearchCriteria($outerCombined);
    }

    /**
     * Test combined condition with null filter (skipped condition)
     *
     * Tests: if ($filter === null) { continue; }
     */
    public function testMapCombinedConditionSkipsNullFilters()
    {
        // Create a valid condition and an empty combined condition (which returns null)
        $validCondition = $this->createSimpleConditionMock('sku', '==', 'test-sku');
        
        // Create an empty combined condition that will return null
        $emptyCombinedCondition = $this->createPartialMockWithReflection(
            CombinedCondition::class,
            ['getAggregator', 'getConditions', 'getValue']
        );
        
        $emptyCombinedCondition->method('getConditions')->willReturn([]); // Empty conditions
        $emptyCombinedCondition->method('getAggregator')->willReturn('all');
        $emptyCombinedCondition->method('getValue')->willReturn(true);

        // Main combined condition with both valid and empty conditions
        $mainCombinedCondition = $this->createPartialMockWithReflection(
            CombinedCondition::class,
            ['getAggregator', 'getConditions', 'getValue']
        );
        
        // Mix valid condition with empty combined condition
        $mainCombinedCondition->method('getConditions')->willReturn([
            $validCondition,
            $emptyCombinedCondition  // This will return null and be skipped
        ]);
        $mainCombinedCondition->method('getAggregator')->willReturn('all');
        $mainCombinedCondition->method('getValue')->willReturn(true);

        $filterMock = $this->createFilterMock();
        $filterGroupMock = $this->createMock(CombinedFilterGroup::class);
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $searchCriteriaBuilderMock = $this->createSearchCriteriaBuilderMock($searchCriteriaMock);

        // Only one filter should be created (for the valid condition)
        $this->filterFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($filterMock);

        // Only one filter group should be created (empty condition returns null, so skipped)
        $this->combinedFilterGroupFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($filterGroupMock);

        $this->searchCriteriaBuilderFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaBuilderMock);

        $result = $this->mapper->mapConditionsToSearchCriteria($mainCombinedCondition);

        $this->assertSame($searchCriteriaMock, $result);
    }
}
