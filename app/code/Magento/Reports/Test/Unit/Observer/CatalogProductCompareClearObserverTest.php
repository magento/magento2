<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Reports\Model\Event;
use Magento\Reports\Model\Product\Index\Compared;
use Magento\Reports\Model\Product\Index\ComparedFactory;
use Magento\Reports\Model\ReportStatus;
use Magento\Reports\Observer\CatalogProductCompareClearObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\Reports\Test\Unit\Observer\CatalogProductCompareClearObserver
 */
class CatalogProductCompareClearObserverTest extends TestCase
{
    /**
     * Testable Object
     *
     * @var CatalogProductCompareClearObserver
     */
    private $observer;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Observer|MockObject
     */
    private $observerMock;

    /**
     * @var ReportStatus|MockObject
     */
    private $reportStatusMock;

    /**
     * @var ComparedFactory|MockObject
     */
    private $productCompFactoryMock;

    /**
     * @var Compared|MockObject
     */
    private $productCompModelMock;

    /**
     * @var Event|MockObject
     */
    private $reportEventMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->observerMock = $this->createMock(Observer::class);

        $this->reportStatusMock = $this->getMockBuilder(ReportStatus::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isReportEnabled'])
            ->getMock();

        $this->reportEventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productCompFactoryMock = $this->getMockBuilder(ComparedFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->productCompModelMock = $this->getMockBuilder(Compared::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->observer = $this->objectManager->getObject(
            CatalogProductCompareClearObserver::class,
            [
                'reportStatus' => $this->reportStatusMock,
                'productCompFactory' => $this->productCompFactoryMock
            ]
        );
    }

    /**
     *  Test for execute(), covers test case for remove all products from compare products
     */
    public function testExecuteRemoveProducts(): void
    {
        $this->reportStatusMock
            ->expects($this->once())
            ->method('isReportEnabled')
            ->with(Event::EVENT_PRODUCT_VIEW)
            ->willReturn(true);

        $this->productCompFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->productCompModelMock);

        $this->productCompModelMock
            ->expects($this->once())
            ->method('calculate')
            ->willReturnSelf();

        $this->observer->execute($this->observerMock);
    }

    /**
     *  Test for execute(), covers test case for remove all products from compare products with report disabled
     */
    public function testExecuteRemoveProductsWithReportDisable(): void
    {
        $this->reportStatusMock
            ->expects($this->once())
            ->method('isReportEnabled')
            ->with(Event::EVENT_PRODUCT_VIEW)
            ->willReturn(false);

        $this->productCompFactoryMock
            ->expects($this->never())
            ->method('create');

        $this->productCompModelMock
            ->expects($this->never())
            ->method('calculate');

        $this->observer->execute($this->observerMock);
    }
}
