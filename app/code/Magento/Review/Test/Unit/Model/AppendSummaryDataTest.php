<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\DB\Select;
use Magento\Review\Model\AppendSummaryData;
use Magento\Review\Model\ResourceModel\Review\Summary as ResourceSummary;
use Magento\Review\Model\ResourceModel\Review\Summary\Collection as SummaryCollection;
use Magento\Review\Model\ResourceModel\Review\Summary\CollectionFactory as SummaryCollectionFactory;
use Magento\Review\Model\Review\Summary;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for \Magento\Review\Model\AppendSummaryData class
 */
class AppendSummaryDataTest extends TestCase
{
    /**
     * @var SummaryCollectionFactory|MockObject
     */
    private $summaryCollectionFactoryMock;

    /**
     * @var Product|MockObject
     */
    private $productMock;

    /**
     * @var Summary|MockObject
     */
    private $summaryMock;

    /**
     * @var SummaryCollection|MockObject
     */
    private $summaryCollectionMock;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    /**
     * @var ResourceSummary|MockObject
     */
    private $resourceSummaryMock;

    /**
     * @var AppendSummaryData
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->summaryCollectionFactoryMock = $this->getMockBuilder(SummaryCollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'addData'])
            ->getMock();

        $this->summaryMock = $this->getMockBuilder(Summary::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData'])
            ->getMock();

        $this->summaryCollectionMock = $this->getMockBuilder(SummaryCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'addStoreFilter',
                    'getSelect',
                    'getResource',
                    'getFirstItem',
                ]
            )
            ->getMock();

        $this->selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['joinLeft', 'where'])
            ->getMock();

        $this->resourceSummaryMock = $this->getMockBuilder(ResourceSummary::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTable'])
            ->getMock();

        $this->model = new AppendSummaryData(
            $this->summaryCollectionFactoryMock
        );
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $productId = 6;
        $storeId = 4;
        $entityCode = 'product';
        $summaryData = [
            'reviews_count' => 2,
            'rating_summary' => 80,
        ];

        $this->productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);

        $this->productMock->expects($this->once())
            ->method('addData')
            ->with($summaryData)
            ->willReturnSelf();

        $this->summaryMock->expects($this->exactly(2))
            ->method('getData')
            ->willReturnMap(
                [
                    ['reviews_count', null, $summaryData['reviews_count']],
                    ['rating_summary', null, $summaryData['rating_summary']],
                ]
            );

        $this->summaryCollectionMock->expects($this->once())
            ->method('addStoreFilter')
            ->with($storeId)
            ->willReturnSelf();
        $this->summaryCollectionMock->expects($this->once())
            ->method('getSelect')
            ->willReturn($this->selectMock);
        $this->summaryCollectionMock->expects($this->once())
            ->method('getResource')
            ->willReturn($this->resourceSummaryMock);

        $this->resourceSummaryMock->expects($this->once())
            ->method('getTable')
            ->willReturn('table_name');

        $this->summaryCollectionMock->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($this->summaryMock);

        $this->selectMock->expects($this->once())
            ->method('joinLeft')
            ->willReturnSelf();
        $this->selectMock->expects($this->exactly(2))
            ->method('where')
            ->willReturnSelf();

        $this->summaryCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->summaryCollectionMock);

        $this->model->execute($this->productMock, $storeId, $entityCode);
    }
}
