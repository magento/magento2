<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Elasticsearch5\SearchAdapter\Query;

use Magento\Elasticsearch\Elasticsearch5\SearchAdapter\Query\Builder;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\Aggregation as AggregationBuilder;
use Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\RequestInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BuilderTest extends TestCase
{
    /**
     * @var Builder
     */
    protected $model;

    /**
     * @var Config|MockObject
     */
    protected $clientConfig;

    /**
     * @var SearchIndexNameResolver|MockObject
     */
    protected $searchIndexNameResolver;

    /**
     * @var AggregationBuilder|MockObject
     */
    protected $aggregationBuilder;

    /**
     * @var RequestInterface|MockObject
     */
    protected $request;

    /**
     * @var ScopeResolverInterface|MockObject
     */
    protected $scopeResolver;

    /**
     * @var ScopeInterface|MockObject
     */
    protected $scopeInterface;

    /**
     * Setup method
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->clientConfig = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getEntityType'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchIndexNameResolver = $this
            ->getMockBuilder(SearchIndexNameResolver::class)
            ->onlyMethods(['getIndexName'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->aggregationBuilder = $this
            ->getMockBuilder(\Magento\Elasticsearch\SearchAdapter\Query\Builder\Aggregation::class)
            ->onlyMethods(['build'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->scopeResolver = $this->getMockForAbstractClass(
            ScopeResolverInterface::class,
            [],
            '',
            false
        );
        $this->scopeInterface = $this->getMockForAbstractClass(
            ScopeInterface::class,
            [],
            '',
            false
        );

        $this->model = new Builder(
            $this->clientConfig,
            $this->searchIndexNameResolver,
            $this->aggregationBuilder,
            $this->scopeResolver,
        );
    }

    /**
     * Test initQuery() method
     */
    public function testInitQuery()
    {
        $dimensionValue = 1;
        $dimension = $this->getMockBuilder(Dimension::class)
            ->onlyMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->request->expects($this->once())
            ->method('getDimensions')
            ->willReturn([$dimension]);
        $dimension->expects($this->once())
            ->method('getValue')
            ->willReturn($dimensionValue);
        $this->scopeResolver->expects($this->once())
            ->method('getScope')
            ->willReturn($this->scopeInterface);
        $this->scopeInterface->expects($this->once())
            ->method('getId')
            ->willReturn($dimensionValue);
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
     * Test initQuery() method with update from value
     */
    public function testInitQueryLimitFrom()
    {
        $dimensionValue = 1;
        $dimension = $this->getMockBuilder(Dimension::class)
            ->onlyMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->request->expects($this->once())
            ->method('getDimensions')
            ->willReturn([$dimension]);
        $dimension->expects($this->once())
            ->method('getValue')
            ->willReturn($dimensionValue);
        $this->scopeResolver->expects($this->once())
            ->method('getScope')
            ->willReturn($this->scopeInterface);
        $this->scopeInterface->expects($this->once())
            ->method('getId')
            ->willReturn($dimensionValue);
        $this->request->expects($this->once())
            ->method('getFrom')
            ->willReturn(PHP_INT_MAX);
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
        $query = $this->model->initQuery($this->request);
        $this->assertLessThanOrEqual(2147483647, $query['body']['from']);
    }
}
