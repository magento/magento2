<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Test\Unit\Adapter\Mysql;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Filter\Builder;
use Magento\Framework\Search\Adapter\Mysql\IndexBuilderInterface;
use Magento\Framework\Search\Adapter\Mysql\Mapper;
use Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match;
use Magento\Framework\Search\Adapter\Mysql\Query\MatchContainer;
use Magento\Framework\Search\Adapter\Mysql\Query\QueryContainer;
use Magento\Framework\Search\Adapter\Mysql\Query\QueryContainerFactory;
use Magento\Framework\Search\Adapter\Mysql\ScoreBuilder;
use Magento\Framework\Search\Adapter\Mysql\ScoreBuilderFactory;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory;
use Magento\Framework\Search\EntityMetadata;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\Search\Request\Query\Filter;
use Magento\Framework\Search\Request\Query\Match as SearchQueryMatch;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MapperTest extends TestCase
{
    const INDEX_NAME = 'test_index_fulltext';
    const REQUEST_LIMIT = 120321;
    const METADATA_ENTITY_ID = 'some_entity_id';

    /**
     * @var AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $connectionAdapter;

    /**
     * @var IndexBuilderInterface|MockObject
     */
    private $indexBuilder;

    /**
     * @var TemporaryStorage|MockObject
     */
    private $temporaryStorage;

    /**
     * @var Match|MockObject
     */
    private $matchBuilder;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var ScoreBuilder|MockObject
     */
    private $scoreBuilder;

    /**
     * @var ScoreBuilderFactory|MockObject
     */
    private $scoreBuilderFactory;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resource;

    /**
     * @var Match|MockObject
     */
    private $queryContainer;

    /**
     * @var Builder|MockObject
     */
    private $filterBuilder;

    /**
     * @var Mapper
     */
    private $mapper;

    protected function setUp(): void
    {
        $this->connectionAdapter = $this->getMockBuilder(AdapterInterface::class)
            ->setMethods(['select', 'dropTable'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->resource = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource->expects($this->any())->method('getConnection')
            ->willReturn($this->connectionAdapter);

        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['getQuery', 'getIndex', 'getSize'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->request->expects($this->any())
            ->method('getIndex')
            ->willReturn(self::INDEX_NAME);

        $this->queryContainer = $this->getMockBuilder(
            QueryContainer::class
        )
            ->setMethods(['addMatchQuery', 'getMatchQueries'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryContainer->expects($this->any())
            ->method('addMatchQuery')
            ->willReturnArgument(0);

        $this->temporaryStorage = $this->getMockBuilder(TemporaryStorage::class)
            ->setMethods(['storeDocumentsFromSelect'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testBuildMatchQuery()
    {
        $query = $this->createMatchQuery();

        $select = $this->createSelectMock(null, false, false);
        $this->mockBuilders($select);
        $parentSelect = $this->createSelectMock($select, true);
        $this->addSelects([$parentSelect]);

        $this->request->expects($this->once())
            ->method('getSize')
            ->willReturn(self::REQUEST_LIMIT);

        $this->queryContainer->expects($this->once())
            ->method('getMatchQueries')
            ->willReturn([]);

        $this->queryContainer->expects($this->any())->method('addMatchQuery')
            ->with(
                $select,
                $query,
                BoolExpression::QUERY_CONDITION_MUST
            )
            ->willReturn($select);

        $this->request->expects($this->once())->method('getQuery')->willReturn($query);

        $select->expects($this->any())->method('columns')->willReturnSelf();

        $response = $this->mapper->buildQuery($this->request);

        $this->assertEquals($select, $response);
    }

    public function testBuildFilterQuery()
    {
        $query = $this->createFilterQuery(Filter::REFERENCE_FILTER, $this->createFilter());

        $select = $this->createSelectMock(null, false, false);
        $this->mockBuilders($select);
        $parentSelect = $this->createSelectMock($select, true);
        $this->addSelects([$parentSelect]);

        $this->request->expects($this->once())
            ->method('getSize')
            ->willReturn(self::REQUEST_LIMIT);

        $select->expects($this->any())->method('columns')->willReturnSelf();

        $this->request->expects($this->once())->method('getQuery')->willReturn($query);

        $this->filterBuilder->expects($this->once())->method('build')->willReturn('(1)');

        $response = $this->mapper->buildQuery($this->request);

        $this->assertEquals($select, $response);
    }

    /**
     * @param $query
     * @param array $derivedQueries
     * @param int $queriesCount
     * @throws \Exception
     * @dataProvider buildQueryDataProvider
     */
    public function testBuildQuery($query, array $derivedQueries = [], $queriesCount = 0)
    {
        $select = $this->createSelectMock(null, false, false);

        $this->mockBuilders($select);

        $previousSelect = $select;
        $selects = [];
        for ($i = $queriesCount; $i >= 0; $i--) {
            $isLast = $i === 0;
            $select = $this->createSelectMock($previousSelect, $isLast, true);
            $previousSelect = $select;
            $selects[] = $select;
        }

        $this->addSelects($selects);

        $this->request->expects($this->once())
            ->method('getSize')
            ->willReturn(self::REQUEST_LIMIT);

        $this->filterBuilder->expects($this->any())->method('build')->willReturn('(1)');

        $table = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->temporaryStorage->expects($this->any())
            ->method('storeDocumentsFromSelect')
            ->willReturn($table);
        $table->expects($this->any())
            ->method('getName')
            ->willReturn('table_name');

        $this->queryContainer->expects($this->any())
            ->method('getMatchQueries')
            ->willReturn($derivedQueries);

        $this->request->expects($this->once())->method('getQuery')->willReturn($query);

        $response = $this->mapper->buildQuery($this->request);
        $this->assertEquals(end($selects), $response);
    }

    /**
     * @return array
     */
    public function buildQueryDataProvider()
    {
        return [
            'one' => [
                $this->createBoolQuery(
                    [
                        $this->createMatchQuery(),
                        $this->createFilterQuery(Filter::REFERENCE_QUERY, $this->createMatchQuery()),
                    ],
                    [
                        $this->createMatchQuery(),
                        $this->createFilterQuery(Filter::REFERENCE_FILTER, $this->createFilter()),
                    ],
                    [
                        $this->createMatchQuery(),
                        $this->createFilterQuery(Filter::REFERENCE_FILTER, $this->createFilter()),
                    ]
                ),
            ],
            'two' => [
                $this->createBoolQuery(
                    [
                        $this->createMatchQuery(),
                        $this->createMatchQuery(),
                    ],
                    [],
                    []
                ),
                [
                    $this->createMatchContainer(
                        $this->createMatchQuery(),
                        'mustNot'
                    ),
                ],
            ],
            'three' => [
                $this->createBoolQuery(
                    [
                        $this->createMatchQuery(),
                        $this->createMatchQuery(),
                    ],
                    [],
                    []
                ),
                [
                    $this->createMatchContainer(
                        $this->createMatchQuery(),
                        'mustNot'
                    ),
                    $this->createMatchContainer(
                        $this->createMatchQuery(),
                        'must'
                    ),
                ],
                1
            ],
        ];
    }

    public function testGetUnknownQueryType()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Unknown query type \'unknownQuery\'');
        $select = $this->createSelectMock(null, false, false);
        $this->mockBuilders($select);
        $query = $this->getMockBuilder(QueryInterface::class)
            ->setMethods(['getType'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $query->expects($this->exactly(2))
            ->method('getType')
            ->willReturn('unknownQuery');
        $this->connectionAdapter->expects($this->never())->method('select');
        $this->connectionAdapter->expects($this->never())->method('dropTable');

        $this->request->expects($this->once())->method('getQuery')->willReturn($query);

        $this->mapper->buildQuery($this->request);
    }

    /**
     * @param array $selects
     */
    protected function addSelects(array $selects)
    {
        $this->connectionAdapter->method('select')
            ->will(call_user_func_array([$this, 'onConsecutiveCalls'], $selects));
    }

    /**
     * @param array $tables
     */
    protected function addDroppedTables(array $tables)
    {
        $this->connectionAdapter->method('select')
            ->will(call_user_func_array([$this, 'onConsecutiveCalls'], $tables));
    }

    /**
     * @return MockObject|SearchQueryMatch
     */
    private function createMatchQuery()
    {
        $query = $this->getMockBuilder(SearchQueryMatch::class)
            ->setMethods(['getType'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $query->expects($this->once())->method('getType')
            ->willReturn(QueryInterface::TYPE_MATCH);
        return $query;
    }

    /**
     * @param string $referenceType
     * @param mixed $reference
     * @return MockObject|Filter
     */
    private function createFilterQuery($referenceType, $reference)
    {
        $query = $this->getMockBuilder(Filter::class)
            ->setMethods(['getType', 'getReferenceType', 'getReference'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $query->expects($this->exactly(1))
            ->method('getType')
            ->willReturn(QueryInterface::TYPE_FILTER);
        $query->expects($this->once())->method('getReferenceType')
            ->willReturn($referenceType);
        $query->expects($this->once())->method('getReference')
            ->willReturn($reference);
        return $query;
    }

    /**
     * @param array $must
     * @param array $should
     * @param array $mustNot
     * @return BoolExpression|MockObject
     */
    private function createBoolQuery(array $must, array $should, array $mustNot)
    {
        $query = $this->getMockBuilder(BoolExpression::class)
            ->setMethods(['getMust', 'getShould', 'getMustNot', 'getType'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $query->expects($this->exactly(1))
            ->method('getType')
            ->willReturn(QueryInterface::TYPE_BOOL);
        $query->expects($this->once())
            ->method('getMust')
            ->willReturn($must);
        $query->expects($this->once())
            ->method('getShould')
            ->willReturn($should);
        $query->expects($this->once())
            ->method('getMustNot')
            ->willReturn($mustNot);
        return $query;
    }

    /**
     * @return MockObject|FilterInterface
     */
    private function createFilter()
    {
        return $this->getMockBuilder(FilterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    /**
     * @param $request
     * @param $conditionType
     * @return MockObject|MatchContainer
     */
    private function createMatchContainer($request, $conditionType)
    {
        $matchContainer = $this->getMockBuilder(MatchContainer::class)
            ->setMethods(['getRequest', 'getConditionType'])
            ->disableOriginalConstructor()
            ->getMock();
        $matchContainer->expects($this->any())
            ->method('getRequest')
            ->willReturn($request);
        $matchContainer->expects($this->any())
            ->method('getConditionType')
            ->willReturn($conditionType);
        return $matchContainer;
    }

    /**
     * @param Select $select
     */
    private function mockBuilders(Select $select)
    {
        $helper = new ObjectManager($this);

        $this->scoreBuilder = $this->getMockBuilder(ScoreBuilder::class)
            ->setMethods(['clear'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->scoreBuilderFactory = $this->getMockBuilder(
            ScoreBuilderFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->scoreBuilderFactory->expects($this->any())->method('create')
            ->willReturn($this->scoreBuilder);
        $this->filterBuilder = $this->getMockBuilder(Builder::class)
            ->setMethods(['build'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->matchBuilder = $this->getMockBuilder(Match::class)
            ->setMethods(['build'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->matchBuilder->expects($this->any())
            ->method('build')
            ->willReturnArgument(1);
        $this->indexBuilder = $this->getMockBuilder(
            IndexBuilderInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['build'])
            ->getMockForAbstractClass();
        $this->indexBuilder->expects($this->any())
            ->method('build')
            ->willReturn($select);
        $temporaryStorageFactory = $this->getMockBuilder(
            TemporaryStorageFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $temporaryStorageFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->temporaryStorage);
        $queryContainerFactory = $this->getMockBuilder(
            QueryContainerFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $queryContainerFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->queryContainer);
        $entityMetadata = $this->getMockBuilder(EntityMetadata::class)
            ->setMethods(['getEntityId'])
            ->disableOriginalConstructor()
            ->getMock();
        $entityMetadata->expects($this->any())
            ->method('getEntityId')
            ->willReturn(self::METADATA_ENTITY_ID);
        $this->mapper = $helper->getObject(
            Mapper::class,
            [
                'resource' => $this->resource,
                'scoreBuilderFactory' => $this->scoreBuilderFactory,
                'queryContainerFactory' => $queryContainerFactory,
                'filterBuilder' => $this->filterBuilder,
                'matchBuilder' => $this->matchBuilder,
                'indexProviders' => [self::INDEX_NAME => $this->indexBuilder],
                'temporaryStorageFactory' => $temporaryStorageFactory,
                'entityMetadata' => $entityMetadata,
            ]
        );
    }

    /**
     * @param MockObject|Select|null $from
     * @param bool $isInternal
     * @param bool $isGrouped
     * @return Select|MockObject
     * @internal param bool $isOrderExpected
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function createSelectMock(Select $from = null, $isInternal = true, $isGrouped = true)
    {
        $select = $this->getMockBuilder(Select::class)
            ->setMethods(['group', 'limit', 'where', 'columns', 'from', 'join', 'joinInner', 'order'])
            ->disableOriginalConstructor()
            ->getMock();

        if ($from) {
            $select->expects($this->once())
                ->method('from')
                ->with(['main_select' => $from])
                ->willReturnSelf();
        }

        $select->expects($this->any())
            ->method('limit')
            ->with($isInternal ? self::REQUEST_LIMIT : 10000000000)
            ->willReturnSelf();
        $select->expects($isInternal ? $this->exactly(2) : $this->never())
            ->method('order')
            ->with(
                $this->logicalOr(
                    'relevance DESC',
                    'entity_id DESC'
                )
            )
            ->willReturnSelf();
        $select->expects($isGrouped ? $this->once() : $this->never())
            ->method('group')
            ->with(self::METADATA_ENTITY_ID)
            ->willReturnSelf();

        return $select;
    }

    public function testUnsupportedRelevanceCalculationMethod()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('Unsupported relevance calculation method used.');
        $helper = new ObjectManager($this);
        $helper->getObject(
            Mapper::class,
            [
                'indexProviders' => [],
                'relevanceCalculationMethod' => 'UNSUPPORTED'
            ]
        );
    }
}
