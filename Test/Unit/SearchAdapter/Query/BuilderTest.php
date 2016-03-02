<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Query;

use Magento\Elasticsearch\SearchAdapter\Query\Builder;
use Magento\Framework\Search\RequestInterface;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\Aggregation as AggregationBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Builder
     */
    protected $model;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientConfig;

    /**
     * @var SearchIndexNameResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchIndexNameResolver;

    /**
     * @var AggregationBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $aggregationBuilder;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * Setup method
     * @return void
     */
    protected function setUp()
    {
        $this->clientConfig = $this->getMockBuilder('Magento\Elasticsearch\Model\Config')
            ->setMethods(['getEntityType'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchIndexNameResolver = $this
            ->getMockBuilder('Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver')
            ->setMethods(['getIndexName'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->aggregationBuilder = $this
            ->getMockBuilder('Magento\Elasticsearch\SearchAdapter\Query\Builder\Aggregation')
            ->setMethods(['build'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder('Magento\Framework\Search\RequestInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            '\Magento\Elasticsearch\SearchAdapter\Query\Builder',
            [
                'clientConfig' => $this->clientConfig,
                'searchIndexNameResolver' => $this->searchIndexNameResolver,
                'aggregationBuilder' => $this->aggregationBuilder
            ]
        );
    }

    /**
     * Test initQuery() method
     */
    public function testInitQuery()
    {
        $dimension = $this->getMockBuilder('Magento\Framework\Search\Request\Dimension')
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->expects($this->once())
            ->method('getDimensions')
            ->willReturn([$dimension]);
        $dimension->expects($this->once())
            ->method('getValue')
            ->willReturn(1);
        $this->request->expects($this->once())
            ->method('getFrom')
            ->willReturn(0);
        $this->request->expects($this->once())
            ->method('getSize')
            ->willReturn(10);
        $this->request->expects($this->once())
            ->method('getIndex')
            ->willReturn('catalogsearch_fulltext');
        $this->searchIndexNameResolver->expects($this->once())
            ->method('getIndexName')
            ->willReturn('indexName');
        $this->clientConfig->expects($this->once())
            ->method('getEntityType')
            ->willReturn('document');
        $this->model->initQuery($this->request);
    }

    /**
     * Test initQuery() method
     */
    public function testInitAggregations()
    {
        $this->aggregationBuilder->expects($this->any())
            ->method('build')
            ->willReturn([]);
        $this->model->initAggregations($this->request, []);
    }
}
