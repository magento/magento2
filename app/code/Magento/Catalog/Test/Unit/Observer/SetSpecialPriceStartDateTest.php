<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Observer;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Observer\SetSpecialPriceStartDate;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Catalog\Observer\SetSpecialPriceStartDate
 */
class SetSpecialPriceStartDateTest extends TestCase
{
    /**
     * Testable Object
     *
     * @var SetSpecialPriceStartDate
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
     * @var Event|MockObject
     */
    private $eventMock;

    /**
     * @var Product|MockObject
     */
    private $productMock;

    /**
     * @var Timezone|MockObject
     */
    private $timezone;

    /**
     * @var \DateTime|MockObject
     */
    private $dateObject;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->observerMock = $this->createMock(Observer::class);
        $this->timezone = $this->createMock(Timezone::class);
        $this->dateObject = $this->createMock(\DateTime::class);

        $this->eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProduct'])
            ->getMock();

        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSpecialPrice', 'getSpecialFromDate', 'setData'])
            ->getMock();

        $this->observer = $this->objectManager->getObject(
            SetSpecialPriceStartDate::class,
            [
                'localeDate' => $this->timezone
            ]
        );
    }

    /**
     * Test observer execute method
     */
    public function testExecuteModifySpecialFromDate(): void
    {
        $specialPrice = 15;
        $specialFromDate = null;
        $localeDateMock = ['special_from_date' => $this->returnValue($this->dateObject)];

        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock
            ->expects($this->once())
            ->method('getProduct')
            ->willReturn($this->productMock);

        $this->dateObject->expects($this->any())
            ->method('setTime')
            ->willReturnSelf();

        $this->timezone
            ->expects($this->once())
            ->method('date')
            ->willReturn($this->dateObject);

        $this->productMock
            ->expects($this->once())
            ->method('getSpecialPrice')
            ->willReturn($specialPrice);

        $this->productMock
            ->expects($this->once())
            ->method('getSpecialFromDate')
            ->willReturn($specialFromDate);

        $this->productMock
            ->expects($this->once())
            ->method('setData')
            ->willReturn($localeDateMock);

        $this->observer->execute($this->observerMock);
    }
}
