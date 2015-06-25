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
     * @var \Magento\Framework\Search\Request\FilterInterface|MockObject
     */
    private $filter;

    /**
     * @var Mapper
     */
    private $mapper;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->select = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->setMethods(['group', 'limit', 'where', 'columns', 'from'])
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
            ->setMethods(['addMatchQuery'])
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

        $this->filter = $this->getMockBuilder('Magento\Framework\Search\Request\FilterInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->filterBuilder = $this->getMockBuilder('Magento\Framework\Search\Adapter\Mysql\Filter\Builder')
            ->setMethods(['build'])
            ->disableOriginalConstructor()
            ->getMock();

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
                'indexProviders' => [$index => $indexBuilder]
            ]
        );
    }

    public function testBuildMatchQuery()
    {
        $query = $this->createMatchQuery();

        $this->queryContainer->expects($this->any())->method('addMatchQuery')
            ->with(
                $this->equalTo($this->select),
                $this->equalTo($query),
                $this->equalTo(Bool::QUERY_CONDITION_MUST)
            )
            ->will($this->returnValue($this->select));

        $this->request->expects($this->once())->method('getQuery')->will($this->returnValue($query));

        $this->select->expects($this->once())->method('columns')->will($this->returnValue($this->select));

        $response = $this->mapper->buildQuery($this->request);

        $this->assertEquals($this->select, $response);
    }

    public function testBuildFilterQuery()
    {
        $query = $this->createFilterQuery();
        $query->expects($this->once())->method('getReferenceType')->will($this->returnValue(Filter::REFERENCE_FILTER));
        $query->expects($this->once())->method('getReference')->will($this->returnValue($this->filter));

        $this->select->expects($this->once())->method('columns')->will($this->returnValue($this->select));

        $this->request->expects($this->once())->method('getQuery')->will($this->returnValue($query));

        $this->filterBuilder->expects($this->once())->method('build')->will($this->returnValue('(1)'));

        $response = $this->mapper->buildQuery($this->request);

        $this->assertEquals($this->select, $response);
    }

    public function testBuildBoolQuery()
    {
        $query = $this->createBoolQuery();
        $this->request->expects($this->once())->method('getQuery')->will($this->returnValue($query));

        $matchQuery = $this->createMatchQuery();
        $filterMatchQuery = $this->createFilterQuery();
        $filterMatchQuery->expects($this->once())->method('getReferenceType')
            ->will($this->returnValue(Filter::REFERENCE_QUERY));
        $filterMatchQuery->expects($this->once())->method('getReference')->will($this->returnValue($matchQuery));

        $filterQuery = $this->createFilterQuery();
        $filterQuery->expects($this->once())->method('getReferenceType')
            ->will($this->returnValue(Filter::REFERENCE_FILTER));
        $filterQuery->expects($this->once())->method('getReference')->will($this->returnValue($this->filter));

        $this->request->expects($this->once())->method('getQuery')->will($this->returnValue($query));

        $this->filterBuilder->expects($this->once())->method('build')->will($this->returnValue('(1)'));

        $this->select->expects($this->once())->method('columns')->will($this->returnValue($this->select));

        $query->expects($this->once())
            ->method('getMust')
            ->will(
                $this->returnValue(
                    [
                        $this->createMatchQuery(),
                        $this->createFilterQuery(),
                    ]
                )
            );

        $query->expects($this->once())
            ->method('getShould')
            ->will(
                $this->returnValue(
                    [
                        $this->createMatchQuery(),
                        $filterMatchQuery,
                    ]
                )
            );

        $query->expects($this->once())
            ->method('getMustNot')
            ->will(
                $this->returnValue(
                    [
                        $this->createMatchQuery(),
                        $filterQuery,
                    ]
                )
            );

        $response = $this->mapper->buildQuery($this->request);

        $this->assertEquals($this->select, $response);
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
     * @return MockObject
     */
    private function createFilterQuery()
    {
        $query = $this->getMockBuilder('Magento\Framework\Search\Request\Query\Filter')
            ->setMethods(['getType', 'getReferenceType', 'getReference'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $query->expects($this->exactly(1))
            ->method('getType')
            ->will($this->returnValue(QueryInterface::TYPE_FILTER));
        return $query;
    }

    /**
     * @return MockObject
     */
    private function createBoolQuery()
    {
        $query = $this->getMockBuilder('Magento\Framework\Search\Request\Query\Bool')
            ->setMethods(['getMust', 'getShould', 'getMustNot', 'getType'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $query->expects($this->exactly(1))
            ->method('getType')
            ->will($this->returnValue(QueryInterface::TYPE_BOOL));
        return $query;
    }
}
