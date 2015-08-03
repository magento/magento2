<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Test\Unit\Adapter\Mysql;

use \Magento\Framework\Search\Adapter\Mysql\Mapper;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Framework\App\Resource;
use Magento\Framework\Search\Request\Query\Bool;
use Magento\Framework\Search\Request\Query\Filter;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MapperTest extends \PHPUnit_Framework_TestCase
{
    const INDEX_NAME = 'test_index_fulltext';
    private $matchBuilder;

    /**
     * @var \Magento\Framework\Search\RequestInterface|MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\DB\Select|MockObject
     */
    private $select;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\ScoreBuilder|MockObject
     */
    private $scoreBuilder;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\ScoreBuilderFactory|MockObject
     */
    private $scoreBuilderFactory;

    /**
     * @var \Magento\Framework\App\Resource|MockObject
     */
    private $resource;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match|MockObject
     */
    private $queryContainer;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Filter\Builder|MockObject
     */
    private $filterBuilder;

    /**
     * @var Mapper
     */
    private $mapper;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->select = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->setMethods(['group', 'limit', 'where', 'columns', 'from', 'join'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->select->expects($this->any())
            ->method('from')
            ->willReturnSelf();

        $connectionAdapter = $this->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')
            ->setMethods(['select'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $connectionAdapter->expects($this->any())->method('select')->will($this->returnValue($this->select));

        $this->resource = $this->getMockBuilder('Magento\Framework\App\Resource')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource->expects($this->any())->method('getConnection')
            ->with(Resource::DEFAULT_READ_RESOURCE)
            ->will($this->returnValue($connectionAdapter));

        $this->scoreBuilder = $this->getMockBuilder('Magento\Framework\Search\Adapter\Mysql\ScoreBuilder')
            ->setMethods(['clear'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->scoreBuilderFactory = $this->getMockBuilder('Magento\Framework\Search\Adapter\Mysql\ScoreBuilderFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->scoreBuilderFactory->expects($this->any())->method('create')
            ->will($this->returnValue($this->scoreBuilder));

        $this->request = $this->getMockBuilder('Magento\Framework\Search\RequestInterface')
            ->setMethods(['getQuery', 'getIndex', 'getSize'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->queryContainer = $this->getMockBuilder('Magento\Framework\Search\Adapter\Mysql\Query\QueryContainer')
            ->setMethods(['addMatchQuery', 'getDerivedQueries'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryContainer->expects($this->any())
            ->method('addMatchQuery')
            ->willReturnArgument(0);
        $queryContainerFactory = $this->getMockBuilder(
            'Magento\Framework\Search\Adapter\Mysql\Query\QueryContainerFactory'
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $queryContainerFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->queryContainer);

        $this->filterBuilder = $this->getMockBuilder('Magento\Framework\Search\Adapter\Mysql\Filter\Builder')
            ->setMethods(['build'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->matchBuilder = $this->getMockBuilder('\Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match')
            ->setMethods(['build'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->matchBuilder->expects($this->any())
            ->method('build')
            ->willReturnArgument(1);

        /** @var MockObject|\Magento\Framework\Search\Adapter\Mysql\IndexBuilderInterface $indexBuilder */
        $indexBuilder = $this->getMockBuilder('\Magento\Framework\Search\Adapter\Mysql\IndexBuilderInterface')
            ->disableOriginalConstructor()
            ->setMethods(['build'])
            ->getMockForAbstractClass();
        $indexBuilder->expects($this->any())
            ->method('build')
            ->will($this->returnValue($this->select));

        $index = self::INDEX_NAME;
        $this->request->expects($this->exactly(2))
            ->method('getIndex')
            ->will($this->returnValue($index));

        $this->mapper = $helper->getObject(
            'Magento\Framework\Search\Adapter\Mysql\Mapper',
            [
                'resource' => $this->resource,
                'scoreBuilderFactory' => $this->scoreBuilderFactory,
                'queryContainerFactory' => $queryContainerFactory,
                'filterBuilder' => $this->filterBuilder,
                'matchBuilder' => $this->matchBuilder,
                'indexProviders' => [$index => $indexBuilder],
            ]
        );
    }

    public function testBuildMatchQuery()
    {
        $query = $this->createMatchQuery();

        $this->queryContainer->expects($this->once())
            ->method('getDerivedQueries')
            ->willReturn([]);

        $this->queryContainer->expects($this->any())->method('addMatchQuery')
            ->with(
                $this->equalTo($this->select),
                $this->equalTo($query),
                $this->equalTo(Bool::QUERY_CONDITION_MUST)
            )
            ->will($this->returnValue($this->select));

        $this->request->expects($this->once())->method('getQuery')->will($this->returnValue($query));

        $this->select->expects($this->any())->method('columns')->will($this->returnValue($this->select));

        $response = $this->mapper->buildQuery($this->request);

        $this->assertEquals($this->select, $response);
    }

    public function testBuildFilterQuery()
    {
        $query = $this->createFilterQuery(Filter::REFERENCE_FILTER, $this->createFilter());

        $this->queryContainer->expects($this->once())
            ->method('getDerivedQueries')
            ->willReturn([]);

        $this->select->expects($this->any())->method('columns')->will($this->returnValue($this->select));

        $this->request->expects($this->once())->method('getQuery')->will($this->returnValue($query));

        $this->filterBuilder->expects($this->once())->method('build')->will($this->returnValue('(1)'));

        $response = $this->mapper->buildQuery($this->request);

        $this->assertEquals($this->select, $response);
    }

    /**
     * @param $query
     * @throws \Exception
     * @dataProvider buildQueryDataProvider
     */
    public function testBuildQuery($query, $derivedQueries = [])
    {
        $this->filterBuilder->expects($this->any())->method('build')->will($this->returnValue('(1)'));

        $this->queryContainer->expects($this->any())
            ->method('getDerivedQueries')
            ->willReturn($derivedQueries);

        $this->select->expects($this->any())->method('columns')->will($this->returnValue($this->select));

        $this->request->expects($this->once())->method('getQuery')->will($this->returnValue($query));

        $response = $this->mapper->buildQuery($this->request);

        $this->assertEquals($this->select, $response);
    }

    public function buildQueryDataProvider()
    {
        return [
            [
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
            [
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
            [
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
            ],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown query type 'unknownQuery'
     */
    public function testGetUnknownQueryType()
    {
        $query = $this->getMockBuilder('Magento\Framework\Search\Request\QueryInterface')
            ->setMethods(['getType'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $query->expects($this->exactly(2))
            ->method('getType')
            ->will($this->returnValue('unknownQuery'));

        $this->request->expects($this->once())->method('getQuery')->will($this->returnValue($query));

        $this->mapper->buildQuery($this->request);
    }

    /**
     * @return MockObject
     */
    private function createMatchQuery()
    {
        $query = $this->getMockBuilder('Magento\Framework\Search\Request\Query\Match')
            ->setMethods(['getType'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $query->expects($this->once())->method('getType')
            ->will($this->returnValue(QueryInterface::TYPE_MATCH));
        return $query;
    }

    /**
     * @param string $referenceType
     * @param mixed $reference
     * @return MockObject
     */
    private function createFilterQuery($referenceType, $reference)
    {
        $query = $this->getMockBuilder('Magento\Framework\Search\Request\Query\Filter')
            ->setMethods(['getType', 'getReferenceType', 'getReference'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $query->expects($this->exactly(1))
            ->method('getType')
            ->will($this->returnValue(QueryInterface::TYPE_FILTER));
        $query->expects($this->once())->method('getReferenceType')
            ->will($this->returnValue($referenceType));
        $query->expects($this->once())->method('getReference')
            ->will($this->returnValue($reference));
        return $query;
    }

    /**
     * @return MockObject
     */
    private function createBoolQuery(array $must, array $should, array $mustNot)
    {
        $query = $this->getMockBuilder('Magento\Framework\Search\Request\Query\Bool')
            ->setMethods(['getMust', 'getShould', 'getMustNot', 'getType'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $query->expects($this->exactly(1))
            ->method('getType')
            ->will($this->returnValue(QueryInterface::TYPE_BOOL));
        $query->expects($this->once())
            ->method('getMust')
            ->will($this->returnValue($must));
        $query->expects($this->once())
            ->method('getShould')
            ->will($this->returnValue($should));
        $query->expects($this->once())
            ->method('getMustNot')
            ->will($this->returnValue($mustNot));
        return $query;
    }

    /**
     * @return MockObject
     */
    private function createFilter()
    {
        return $this->getMockBuilder('Magento\Framework\Search\Request\FilterInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    /**
     * @param $request
     * @param $conditionType
     */
    private function createMatchContainer($request, $conditionType)
    {
        $matchContainer = $this->getMockBuilder('\Magento\Framework\Search\Adapter\Mysql\Query\MatchContainer')
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
}
