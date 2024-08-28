<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Review\Test\Unit\Observer;

use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Review\Model\ResourceModel\Review\Summary;
use Magento\Review\Model\ResourceModel\Review\SummaryFactory;
use Magento\Review\Observer\CatalogProductListCollectionAppendSummaryFieldsObserver;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Review\Observer\CatalogProductListCollectionAppendSummaryFieldsObserver
 */
class CatalogProductListCollectionAppendSummaryFieldsObserverTest extends TestCase
{
    private const STORE_ID = 1;

    /**
     * @var Event|MockObject
     */
    private $eventMock;

    /**
     * Testable Object
     *
     * @var CatalogProductListCollectionAppendSummaryFieldsObserver
     */
    private $observer;

    /**
     * @var Observer|MockObject
     */
    private $observerMock;

    /**
     * @var Collection|MockObject
     */
    private $productCollectionMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var Summary|MockObject
     */
    private $sumResourceMock;

    /**
     * @var SummaryFactory|MockObject
     */
    private $sumResourceFactoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCollection'])
            ->getMock();

        $this->observerMock = $this->createMock(Observer::class);

        $this->productCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStore'])
            ->getMockForAbstractClass();

        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMockForAbstractClass();

        $this->sumResourceMock = $this->createPartialMock(
            Summary::class,
            ['appendSummaryFieldsToCollection']
        );

        $this->sumResourceFactoryMock = $this->getMockBuilder(SummaryFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->observer = new CatalogProductListCollectionAppendSummaryFieldsObserver(
            $this->sumResourceFactoryMock,
            $this->storeManagerMock
        );
    }

    /**
     * Product listing test
     */
    public function testAddSummaryFieldToProductsCollection() : void
    {
        $this->eventMock
            ->expects($this->once())
            ->method('getCollection')
            ->willReturn($this->productCollectionMock);

        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->storeManagerMock
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->storeMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn(self::STORE_ID);

        $this->sumResourceFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->sumResourceMock);

        $this->sumResourceMock
            ->expects($this->once())
            ->method('appendSummaryFieldsToCollection')
            ->willReturn($this->sumResourceMock);

        $this->observer->execute($this->observerMock);
    }
}
