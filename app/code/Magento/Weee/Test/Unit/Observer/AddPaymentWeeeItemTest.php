<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Payment\Model\Cart;
use Magento\Payment\Model\Cart\SalesModel\SalesModelInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Weee\Helper\Data;
use Magento\Weee\Observer\AddPaymentWeeeItem;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class AddPaymentWeeeItemTest
 */
class AddPaymentWeeeItemTest extends TestCase
{
    /**
     * Testable object
     *
     * @var AddPaymentWeeeItem
     */
    private $observer;

    /**
     * @var Data|MockObject
     */
    private $weeeHelperMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * Set Up
     */
    protected function setUp()
    {
        $this->weeeHelperMock = $this->createMock(Data::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);

        $this->observer = new AddPaymentWeeeItem(
            $this->weeeHelperMock,
            $this->storeManagerMock
        );
    }

    /**
     * Test execute
     *
     * @dataProvider dataProvider
     * @param bool $isEnabled
     * @param bool $includeInSubtotal
     * @return void
     */
    public function testExecute(bool $isEnabled, bool $includeInSubtotal): void
    {
        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->createMock(Observer::class);
        $cartModelMock = $this->createMock(Cart::class);
        $salesModelMock = $this->createMock(SalesModelInterface::class);
        $itemMock = $this->createPartialMock(Item::class, ['getOriginalItem']);
        $originalItemMock = $this->createPartialMock(Item::class, ['getParentItem']);
        $parentItemMock = $this->createMock(Item::class);
        $eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCart'])
            ->getMock();

        $asCustomItem = $this->prepareShouldBeAddedAsCustomItem($isEnabled, $includeInSubtotal);
        $toBeCalled = 1;
        if (!$asCustomItem) {
            $toBeCalled = 0;
        }

        $eventMock->expects($this->exactly($toBeCalled))
            ->method('getCart')
            ->willReturn($cartModelMock);
        $observerMock->expects($this->exactly($toBeCalled))
            ->method('getEvent')
            ->willReturn($eventMock);
        $itemMock->expects($this->exactly($toBeCalled))
            ->method('getOriginalItem')
            ->willReturn($originalItemMock);
        $originalItemMock->expects($this->exactly($toBeCalled))
            ->method('getParentItem')
            ->willReturn($parentItemMock);
        $salesModelMock->expects($this->exactly($toBeCalled))
            ->method('getAllItems')
            ->willReturn([$itemMock]);
        $cartModelMock->expects($this->exactly($toBeCalled))
            ->method('getSalesModel')
            ->willReturn($salesModelMock);

        $this->observer->execute($observerMock);
    }

    /**
     * @return array
     */
    public function dataProvider(): array
    {
        return [
            [true, false],
            [true, true],
            [false, true],
            [false, false],
        ];
    }

    /**
     * Prepare if FPT should be added to payment cart as custom item or not.
     *
     * @param bool $isEnabled
     * @param bool $includeInSubtotal
     * @return bool
     */
    private function prepareShouldBeAddedAsCustomItem(bool $isEnabled, bool $includeInSubtotal): bool
    {
        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn(Store::DEFAULT_STORE_ID);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $this->weeeHelperMock->expects($this->once())
            ->method('isEnabled')
            ->with(Store::DEFAULT_STORE_ID)
            ->willReturn($isEnabled);

        if ($isEnabled) {
            $this->weeeHelperMock->expects($this->once())
                ->method('includeInSubtotal')
                ->with(Store::DEFAULT_STORE_ID)
                ->willReturn($includeInSubtotal);
        }

        return $isEnabled && !$includeInSubtotal;
    }
}
