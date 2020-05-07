<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter;

use Magento\Elasticsearch\SearchAdapter\Filter\Builder as FilterBuilder;
use Magento\Elasticsearch\SearchAdapter\Mapper;
use Magento\Elasticsearch\SearchAdapter\Query\Builder as QueryBuilder;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\Match as MatchQueryBuilder;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\Search\Request\Query\Filter;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MapperTest extends TestCase
{
    /**
     * @var Mapper
     */
    protected $model;

    /**
     * @var QueryBuilder|MockObject
     */
    protected $queryBuilder;

    /**
     * @var MatchQueryBuilder|MockObject
     */
    protected $matchQueryBuilder;

    /**
     * @var FilterBuilder|MockObject
     */
    protected $filterBuilder;

    /**
     * Setup method
     * @return void
     */
    protected function setUp(): void
    {
        $this->queryBuilder = $this->getMockBuilder(\Magento\Elasticsearch\SearchAdapter\Query\Builder::class)
            ->setMethods([
                'initQuery',
                'initAggregations',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $this->matchQueryBuilder = $this->getMockBuilder(
            \Magento\Elasticsearch\SearchAdapter\Query\Builder\Match::class
        )
            ->setMethods(['build'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterBuilder = $this->getMockBuilder(\Magento\Elasticsearch\SearchAdapter\Filter\Builder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryBuilder->expects($this->any())
            ->method('initQuery')
            ->willReturn([
                'body' => [
                    'query' => [],
                ],
            ]);
        $this->queryBuilder->expects($this->any())
            ->method('initAggregations')
            ->willReturn([
                'body' => [
                    'query' => [],
                ],
            ]);
        $this->matchQueryBuilder->expects($this->any())
            ->method('build')
            ->willReturn([]);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            Mapper::class,
            [
                'queryBuilder' => $this->queryBuilder,
                'matchQueryBuilder' => $this->matchQueryBuilder,
                'filterBuilder' => $this->filterBuilder
            ]
        );
    }

    /**
     * Test buildQuery() method with exception
     */
    public function testBuildQueryFailure()
    {
        $this->expectException(\InvalidArgumentException::class);

        $request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $query = $this->getMockBuilder(QueryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $request->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
        $query->expects($this->atLeastOnce())
            ->method('getType')
            ->willReturn('unknown');

        $this->model->buildQuery($request);
    }

    /**
     * Test buildQuery() method
     *
     * @param string $queryType
     * @param string $queryMock
     * @param string $referenceType
     * @param string $filterMock
     * @dataProvider buildQueryDataProvider
     */
    public function testBuildQuery($queryType, $queryMock, $referenceType, $filterMock)
    {
        $request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $query = $this->getMockBuilder($queryMock)
            ->setMethods(['getMust', 'getMustNot', 'getType', 'getShould', 'getReferenceType', 'getReference'])
            ->disableOriginalConstructor()
            ->getMock();
        $matchQuery = $this->getMockBuilder(\Magento\Framework\Search\Request\Query\Match::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filterQuery = $this->getMockBuilder($filterMock)
            ->disableOriginalConstructor()
            ->getMock();
        $request->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $query->expects($this->atLeastOnce())
            ->method('getType')
            ->willReturn($queryType);
        $query->expects($this->any())
            ->method('getMust')
            ->willReturn([$matchQuery]);
        $query->expects($this->any())
            ->method('getShould')
            ->willReturn([]);
        $query->expects($this->any())
            ->method('getMustNot')
            ->willReturn([]);
        $query->expects($this->any())
            ->method('getReferenceType')
            ->willReturn($referenceType);
        $query->expects($this->any())
            ->method('getReference')
            ->willReturn($filterQuery);
        $matchQuery->expects($this->any())
            ->method('getType')
            ->willReturn('matchQuery');
        $filterQuery->expects($this->any())
            ->method('getType')
            ->willReturn('matchQuery');
        $filterQuery->expects($this->any())
            ->method('getType')
            ->willReturn('matchQuery');
        $this->filterBuilder->expects(($this->any()))
            ->method('build')
            ->willReturn([
                'bool' => [
                    'must' => [],
                ],
            ]);

        $this->model->buildQuery($request);
    }

    /**
     * @return array
     */
    public function buildQueryDataProvider()
    {
        return [
            [
                'matchQuery', \Magento\Framework\Search\Request\Query\Match::class,
                'query', QueryInterface::class,
            ],
            [
                'boolQuery', BoolExpression::class,
                'query', QueryInterface::class,
            ],
            [
                'filteredQuery', Filter::class,
                'query', QueryInterface::class,
            ],
            [
                'filteredQuery', Filter::class,
                'filter', FilterInterface::class,
            ],
        ];
    }
}
