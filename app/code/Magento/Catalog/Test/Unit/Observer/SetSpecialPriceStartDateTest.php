<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Observer;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Observer\SetSpecialPriceStartDate;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Catalog\Observer\SetSpecialPriceStartDate
 */
class SetSpecialPriceStartDateTest extends TestCase
{
    use MockCreationTrait;
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

        $this->eventMock = $this->createPartialMockWithReflection(
            Event::class,
            ['setProductMock', 'getProduct']
        );
        $eventProduct = null;
        $this->eventMock->method('setProductMock')->willReturnCallback(function ($product) use (&$eventProduct) {
            $eventProduct = $product;
        });
        $this->eventMock->method('getProduct')->willReturnCallback(function () use (&$eventProduct) {
            return $eventProduct;
        });

        $this->productMock = $this->createPartialMock(
            Product::class,
            ['getSpecialPrice', 'getSpecialFromDate', 'setData']
        );

        $this->observer = $this->objectManager->getObject(
            SetSpecialPriceStartDate::class,
            [
                'localeDate' => $this->timezone
            ]
        );
    }

    /**
     * Test observer execute method when special_from_date is null
     */
    public function testExecuteModifySpecialFromDate(): void
    {
        $specialPrice = 15;
        $specialFromDate = null;
        $formattedDate = '2023-01-01 00:00:00';

        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock->setProductMock($this->productMock);

        $this->dateObject
            ->expects($this->once())
            ->method('setTime')
            ->with(0, 0)
            ->willReturnSelf();

        $this->dateObject
            ->expects($this->once())
            ->method('format')
            ->with('Y-m-d H:i:s')
            ->willReturn($formattedDate);

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
            ->with('special_from_date', $formattedDate);

        $this->observer->execute($this->observerMock);
    }

    /**
     * Test observer doesn't modify special_from_date when it's already set
     */
    public function testExecuteDoesNotModifyExistingSpecialFromDate(): void
    {
        $specialPrice = 15;
        $existingSpecialFromDate = '2023-01-01 00:00:00';

        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock->setProductMock($this->productMock);

        $this->productMock
            ->expects($this->once())
            ->method('getSpecialPrice')
            ->willReturn($specialPrice);

        $this->productMock
            ->expects($this->once())
            ->method('getSpecialFromDate')
            ->willReturn($existingSpecialFromDate);

        $this->productMock
            ->expects($this->never())
            ->method('setData');

        $this->timezone
            ->expects($this->never())
            ->method('date');

        $this->observer->execute($this->observerMock);
    }

    /**
     * Test observer doesn't set special_from_date when special price is not set
     */
    public function testExecuteDoesNotSetSpecialFromDateWithoutSpecialPrice(): void
    {
        $specialPrice = null;

        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock->setProductMock($this->productMock);

        $this->productMock
            ->expects($this->once())
            ->method('getSpecialPrice')
            ->willReturn($specialPrice);

        $this->productMock
            ->expects($this->never())
            ->method('getSpecialFromDate');

        $this->productMock
            ->expects($this->never())
            ->method('setData');

        $this->observer->execute($this->observerMock);
    }
}
