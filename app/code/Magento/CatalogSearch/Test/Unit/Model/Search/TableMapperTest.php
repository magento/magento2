<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Search;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;
use Magento\CatalogSearch\Model\Search\FilterMapper\FilterStrategyInterface;
use Magento\CatalogSearch\Model\Search\FiltersExtractor;
use Magento\CatalogSearch\Model\Search\TableMapper;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\Filter\Term;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\CatalogSearch\Model\Search\TableMapper
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @deprecated Implementation class was replaced
 * @see \Magento\ElasticSearch
 */
class TableMapperTest extends TestCase
{
    /**
     * @var AliasResolver|MockObject
     */
    private $aliasResolver;

    /**
     * @var TableMapper
     */
    private $tableMapper;

    /**
     * @var FiltersExtractor|MockObject
     */
    private $filterExtractorMock;

    /**
     * @var FilterStrategyInterface|MockObject
     */
    private $filterStrategy;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $resource = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $attributeCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $eavConfig = $this->getMockBuilder(Config::class)
            ->setMethods(['getAttribute'])
            ->disableOriginalConstructor()
            ->getMock();

        $scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->aliasResolver = $this->getMockBuilder(AliasResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterExtractorMock = $this->getMockBuilder(FiltersExtractor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterStrategy = $this->getMockBuilder(FilterStrategyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->tableMapper = $objectManager->getObject(
            TableMapper::class,
            [
                'resource' => $resource,
                'storeManager' => $storeManager,
                'attributeCollectionFactory' => $attributeCollectionFactory,
                'eavConfig' => $eavConfig,
                'scopeConfig' => $scopeConfig,
                'filterStrategy' => $this->filterStrategy,
                'aliasResolver' => $this->aliasResolver,
                'filtersExtractor' => $this->filterExtractorMock
            ]
        );
    }

    public function testRequestHasNoFilters()
    {
        $select = $this->getSelectMock();
        $request = $this->getRequestMock();
        $query = $this->getQueryMock();

        $request
            ->method('getQuery')
            ->willReturn($query);

        $this->filterExtractorMock
            ->method('extractFiltersFromQuery')
            ->with($query)
            ->willReturn([]);

        $this->aliasResolver
            ->expects($this->never())
            ->method('getAlias');

        $this->filterStrategy
            ->expects($this->never())
            ->method('apply');

        $this->tableMapper->addTables($select, $request);
    }

    public function testRequestHasDifferentFilters()
    {
        $select = $this->getSelectMock();
        $request = $this->getRequestMock();
        $query = $this->getQueryMock();
        $filters = $this->getDifferentFiltersMock();

        $request
            ->method('getQuery')
            ->willReturn($query);

        $this->filterExtractorMock
            ->method('extractFiltersFromQuery')
            ->with($query)
            ->willReturn($filters);

        $consecutiveFilters = array_map(
            function ($filter) {
                return [$filter];
            },
            $filters
        );

        $this->aliasResolver
            ->expects($this->exactly(count($filters)))
            ->method('getAlias')
            ->withConsecutive(...$consecutiveFilters)
            ->willReturnCallback(
                function (FilterInterface $filter) {
                    return $filter->getField() . '_alias';
                }
            );

        $consecutiveFilters = array_map(
            function ($filter) use ($select) {
                return [$filter, $select];
            },
            $filters
        );

        $this->filterStrategy
            ->expects($this->exactly(count($filters)))
            ->method('apply')
            ->withConsecutive(...$consecutiveFilters)
            ->willReturn(true);

        $this->tableMapper->addTables($select, $request);
    }

    public function testRequestHasSameFilters()
    {
        $select = $this->getSelectMock();
        $request = $this->getRequestMock();
        $query = $this->getQueryMock();
        $filters = $this->getSameFiltersMock();
        $uniqueFilters = [$filters[0], $filters[2]];

        $request
            ->method('getQuery')
            ->willReturn($query);

        $this->filterExtractorMock
            ->method('extractFiltersFromQuery')
            ->with($query)
            ->willReturn($filters);

        $consecutiveFilters = array_map(
            function ($filter) {
                return [$filter];
            },
            $filters
        );

        $this->aliasResolver
            ->expects($this->exactly(count($filters)))
            ->method('getAlias')
            ->withConsecutive(...$consecutiveFilters)
            ->willReturnCallback(
                function (FilterInterface $filter) {
                    return $filter->getField() . '_alias';
                }
            );

        $consecutiveUniqueFilters = array_map(
            function ($filter) use ($select) {
                return [$filter, $select];
            },
            $uniqueFilters
        );

        $this->filterStrategy
            ->expects($this->exactly(count($uniqueFilters)))
            ->method('apply')
            ->withConsecutive(...$consecutiveUniqueFilters)
            ->willReturn(true);

        $this->tableMapper->addTables($select, $request);
    }

    public function testRequestHasUnAppliedFilters()
    {
        $select = $this->getSelectMock();
        $request = $this->getRequestMock();
        $query = $this->getQueryMock();
        $filters = $this->getSameFiltersMock();

        $request
            ->method('getQuery')
            ->willReturn($query);

        $this->filterExtractorMock
            ->method('extractFiltersFromQuery')
            ->with($query)
            ->willReturn($filters);

        $consecutiveFilters = array_map(
            function ($filter) {
                return [$filter];
            },
            $filters
        );

        $this->aliasResolver
            ->expects($this->exactly(count($filters)))
            ->method('getAlias')
            ->withConsecutive(...$consecutiveFilters)
            ->willReturnCallback(
                function (FilterInterface $filter) {
                    return $filter->getField() . '_alias';
                }
            );

        $consecutiveFilters = array_map(
            function ($filter) use ($select) {
                return [$filter, $select];
            },
            $filters
        );

        $this->filterStrategy
            ->expects($this->exactly(count($filters)))
            ->method('apply')
            ->withConsecutive(...$consecutiveFilters)
            ->willReturnCallback(
                function (FilterInterface $filter) {
                    return !($filter->getName() === 'name1' || $filter->getName() === 'name3')
                        ? true
                        : false;
                }
            );

        $this->tableMapper->addTables($select, $request);
    }

    /**
     * @return MockObject
     */
    private function getSelectMock()
    {
        return $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return MockObject
     */
    private function getRequestMock()
    {
        return $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    /**
     * @return MockObject
     */
    private function getQueryMock()
    {
        return $this->getMockBuilder(QueryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    /**
     * @return array
     */
    private function getDifferentFiltersMock()
    {
        $visibilityFilter = $this->getMockBuilder(Term::class)
            ->setConstructorArgs(['name1', 'value1', 'visibility'])
            ->setMethods(null)
            ->getMock();

        $customFilter = $this->getMockBuilder(Term::class)
            ->setConstructorArgs(['name2', 'value2', 'field1'])
            ->setMethods(null)
            ->getMock();

        $nonCustomFilter = $this->getMockBuilder(Term::class)
            ->setConstructorArgs(['name3', 'value3', 'field2'])
            ->setMethods(null)
            ->getMock();

        return [$visibilityFilter, $customFilter, $nonCustomFilter];
    }

    /**
     * @return array
     */
    private function getSameFiltersMock()
    {
        $visibilityFilter1 = $this->getMockBuilder(Term::class)
            ->setConstructorArgs(['name1', 'value1', 'visibility'])
            ->setMethods(null)
            ->getMock();

        $visibilityFilter2 = $this->getMockBuilder(Term::class)
            ->setConstructorArgs(['name2', 'value2', 'visibility'])
            ->setMethods(null)
            ->getMock();

        $customFilter1 = $this->getMockBuilder(Term::class)
            ->setConstructorArgs(['name3', 'value3', 'field1'])
            ->setMethods(null)
            ->getMock();

        $customFilter2 = $this->getMockBuilder(Term::class)
            ->setConstructorArgs(['name4', 'value4', 'field1'])
            ->setMethods(null)
            ->getMock();

        return [$visibilityFilter1, $visibilityFilter2, $customFilter1, $customFilter2];
    }
}
