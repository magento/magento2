<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Test\Unit;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\Response\QueryResponse;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class SearchResponseBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Search\SearchResponseBuilder
     */
    private $model;

    /**
     * @var \Magento\Framework\Api\Search\SearchResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultFactory;

    /**
     * @var \Magento\Framework\Api\Search\DocumentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $documentFactory;

    protected function setUp()
    {
        $this->searchResultFactory = $this->getMockBuilder('Magento\Framework\Api\Search\SearchResultFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->documentFactory = $this->getMockBuilder('Magento\Framework\Api\Search\DocumentFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = (new ObjectManager($this))->getObject('Magento\Framework\Search\SearchResponseBuilder', [
            'documentFactory' => $this->documentFactory,
            'searchResultFactory' => $this->searchResultFactory,
        ]);
    }

    public function testBuild()
    {
        $documentId = 333;
        $fieldName = 'fieldName';
        $fieldValue = 'fieldValue';
        $aggregations = ['aggregations'];

        $document = $this->getMockBuilder('Magento\Framework\Api\Search\DocumentInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $document->expects($this->once())
            ->method('setCustomAttribute')
            ->with($fieldName, $fieldValue);
        $document->expects($this->once())
            ->method('setId')
            ->with($documentId);

        $this->documentFactory->expects($this->once())
            ->method('create')
            ->willReturn($document);

        /** @var SearchResultInterface|\PHPUnit_Framework_MockObject_MockObject $searchResult */
        $searchResult = $this->getMockBuilder('Magento\Framework\Api\Search\SearchResultInterface')
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

        $field = $this->getMockBuilder('Magento\Framework\Search\DocumentField')
            ->disableOriginalConstructor()
            ->getMock();
        $field->expects($this->once())
            ->method('getName')
            ->willReturn($fieldName);
        $field->expects($this->once())
            ->method('getValue')
            ->willReturn($fieldValue);

        $responseDocument = $this->getMockBuilder('Magento\Framework\Search\Document')
            ->disableOriginalConstructor()
            ->getMock();
        $responseDocument->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$field]));
        $responseDocument->expects($this->once())
            ->method('getId')
            ->willReturn($documentId);

        /** @var QueryResponse|\PHPUnit_Framework_MockObject_MockObject $response */
        $response = $this->getMockBuilder('Magento\Framework\Search\Response\QueryResponse')
            ->setMethods(['getIterator', 'getAggregations'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $response->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$responseDocument]));
        $response->expects($this->once())
            ->method('getAggregations')
            ->willReturn($aggregations);

        $result = $this->model->build($response);

        $this->assertInstanceOf('Magento\Framework\Api\Search\SearchResultInterface', $result);
    }
}
