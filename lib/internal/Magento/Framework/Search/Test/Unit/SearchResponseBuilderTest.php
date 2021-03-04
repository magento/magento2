<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Test\Unit;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\Response\QueryResponse;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class SearchResponseBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Search\SearchResponseBuilder
     */
    private $model;

    /**
     * @var \Magento\Framework\Api\Search\SearchResultFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $searchResultFactory;

    /**
     * @var \Magento\Framework\Api\Search\DocumentFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $documentFactory;

    protected function setUp(): void
    {
        $this->searchResultFactory = $this->getMockBuilder(\Magento\Framework\Api\Search\SearchResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->documentFactory = $this->getMockBuilder(\Magento\Framework\Api\Search\DocumentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = (new ObjectManager($this))->getObject(
            \Magento\Framework\Search\SearchResponseBuilder::class,
            ['searchResultFactory' => $this->searchResultFactory]
        );
    }

    public function testBuild()
    {
        $aggregations = ['aggregations'];

        $document = $this->getMockBuilder(\Magento\Framework\Api\Search\DocumentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        /** @var SearchResultInterface|\PHPUnit\Framework\MockObject\MockObject $searchResult */
        $searchResult = $this->getMockBuilder(\Magento\Framework\Api\Search\SearchResultInterface::class)
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

        /** @var QueryResponse|\PHPUnit\Framework\MockObject\MockObject $response */
        $response = $this->getMockBuilder(\Magento\Framework\Search\Response\QueryResponse::class)
            ->setMethods(['getIterator', 'getAggregations', 'getTotal'])
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

        $this->assertInstanceOf(\Magento\Framework\Api\Search\SearchResultInterface::class, $result);
    }
}
