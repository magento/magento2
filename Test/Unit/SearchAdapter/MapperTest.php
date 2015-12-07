<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\SearchAdapter;

use Magento\Elasticsearch\SearchAdapter\Mapper;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\Match as MatchQueryBuilder;
use Magento\Elasticsearch\SearchAdapter\Filter\Builder as FilterBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class MapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Mapper
     */
    protected $model;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientConfig;

    /**
     * @var MatchQueryBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $matchQueryBuilder;

    /**
     * @var FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterBuilder;

    /**
     * Setup method
     * @return void
     */
    public function setUp()
    {
        $this->clientConfig = $this->getMockBuilder('Magento\Elasticsearch\Model\Config')
            ->setMethods([
                'getIndexName',
                'getEntityType',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $this->matchQueryBuilder = $this->getMockBuilder('Magento\Elasticsearch\SearchAdapter\Query\Builder\Match')
            ->setMethods(['build'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterBuilder = $this->getMockBuilder('Magento\Elasticsearch\SearchAdapter\Filter\Builder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->clientConfig->expects($this->any())
            ->method('getIndexName')
            ->willReturn('indexName');
        $this->clientConfig->expects($this->any())
            ->method('getEntityType')
            ->willReturn('product');
        $this->matchQueryBuilder->expects($this->any())
            ->method('build')
            ->willReturn([]);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            '\Magento\Elasticsearch\SearchAdapter\Mapper',
            [
                'clientConfig' => $this->clientConfig,
                'matchQueryBuilder' => $this->matchQueryBuilder,
                'filterBuilder' => $this->filterBuilder
            ]
        );
    }

    /**
     * Test buildQuery() method with exception
     * @expectedException \InvalidArgumentException
     */
    public function testBuildQueryFailure()
    {
        $request = $this->getMockBuilder('Magento\Framework\Search\RequestInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $dimension = $this->getMockBuilder('Magento\Framework\Search\Request\Dimension')
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $query = $this->getMockBuilder('Magento\Framework\Search\Request\QueryInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $request->expects($this->once())
            ->method('getDimensions')
            ->willReturn([$dimension]);
        $dimension->expects($this->once())
            ->method('getValue')
            ->willReturn(1);
        $request->expects($this->once())
            ->method('getFrom')
            ->willReturn(0);
        $request->expects($this->once())
            ->method('getSize')
            ->willReturn(10);
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
        $request = $this->getMockBuilder('Magento\Framework\Search\RequestInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $dimension = $this->getMockBuilder('Magento\Framework\Search\Request\Dimension')
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $query = $this->getMockBuilder($queryMock)
            ->disableOriginalConstructor()
            ->getMock();
        $matchQuery = $this->getMockBuilder('Magento\Framework\Search\Request\Query\Match')
            ->disableOriginalConstructor()
            ->getMock();
        $filterQuery = $this->getMockBuilder($filterMock)
            ->disableOriginalConstructor()
            ->getMock();

        $request->expects($this->once())
            ->method('getDimensions')
            ->willReturn([$dimension]);
        $dimension->expects($this->once())
            ->method('getValue')
            ->willReturn(1);
        $request->expects($this->once())
            ->method('getFrom')
            ->willReturn(0);
        $request->expects($this->once())
            ->method('getSize')
            ->willReturn(10);
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
            ->willReturn([]);

        $this->model->buildQuery($request);
    }

    /**
     * @return array
     */
    public function buildQueryDataProvider()
    {
        return [
            [
                'matchQuery',
                'Magento\Framework\Search\Request\Query\Match',
                'query',
                'Magento\Framework\Search\Request\QueryInterface',
            ],
            [
                'boolQuery',
                'Magento\Framework\Search\Request\Query\BoolExpression',
                'query',
                'Magento\Framework\Search\Request\QueryInterface',
            ],
            [
                'filteredQuery',
                'Magento\Framework\Search\Request\Query\Filter',
                'query',
                'Magento\Framework\Search\Request\QueryInterface',
            ],
            [
                'filteredQuery',
                'Magento\Framework\Search\Request\Query\Filter',
                'filter',
                'Magento\Framework\Search\Request\FilterInterface',
            ],
        ];
    }
}
