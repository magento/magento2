<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Test\Unit;

use Magento\Framework\Api\Search\DocumentFactory;
use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\Response\QueryResponse;
use Magento\Framework\Search\SearchResponseBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SearchResponseBuilderTest extends TestCase
{
    /**
     * @var SearchResponseBuilder
     */
    private $model;

    /**
     * @var SearchResultFactory|MockObject
     */
    private $searchResultFactory;

    /**
     * @var DocumentFactory|MockObject
     */
    private $documentFactory;

    protected function setUp(): void
    {
        $this->searchResultFactory = $this->getMockBuilder(SearchResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->documentFactory = $this->getMockBuilder(DocumentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = (new ObjectManager($this))->getObject(
            SearchResponseBuilder::class,
            ['searchResultFactory' => $this->searchResultFactory]
        );
    }

    public function testBuild()
    {
        $aggregations = ['aggregations'];

        $document = $this->getMockBuilder(DocumentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        /** @var SearchResultInterface|MockObject $searchResult */
        $searchResult = $this->getMockBuilder(SearchResultInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $searchResult->expects($this->once())
            ->method('setItems')
            ->with([$document]);
        $searchResult->expects($this->once())
            ->method('setAggregations')
            ->with($aggregations);

        $this->searchResultFactory->expects($this->once())
            ->method('create')
            ->willReturn($searchResult);

        /** @var QueryResponse|MockObject $response */
        $response = $this->getMockBuilder(QueryResponse::class)
            ->onlyMethods(['getIterator', 'getAggregations', 'getTotal'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $response->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$document]));
        $response->expects($this->once())
            ->method('getAggregations')
            ->willReturn($aggregations);
        $response->expects($this->any())
            ->method('getTotal')
            ->willReturn(1);

        $result = $this->model->build($response);

        $this->assertInstanceOf(SearchResultInterface::class, $result);
    }
}
