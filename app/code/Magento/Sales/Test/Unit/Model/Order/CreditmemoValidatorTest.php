<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Sales\Model\Order\CreditmemoValidator;
use Magento\Sales\Model\Order\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for creditmemo factory class.
 */
class CreditmemoValidatorTest extends TestCase
{
    /**
     * @var CreditmemoValidator
     */
    private $model;

    /**
     * @var Item|MockObject
     */
    private $orderItemMock;

    /**
     * @var Item|MockObject
     */
    private $orderChildItemOneMock;

    /**
     * @var Item|MockObject
     */
    private $orderChildItemTwoMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->orderItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getChildrenItems', 'isDummy', 'getId', 'getParentItemId'])
            ->addMethods(['getHasChildren'])
            ->getMock();
        $this->orderChildItemOneMock = $this->createPartialMock(
            Item::class,
            ['getQtyToRefund', 'getId']
        );
        $this->orderChildItemTwoMock = $this->createPartialMock(
            Item::class,
            ['getQtyToRefund', 'getId']
        );
        $this->model = new CreditmemoValidator();
    }

    /**
     * Check if order item can be refunded
     * @return void
     */
    public function testCanRefundItem(): void
    {
        $orderItemQtys = [
            2 => 0,
            3 => 0
        ];
        $invoiceQtysRefundLimits = [];

        $this->orderItemMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $this->orderItemMock->expects($this->any())
            ->method('getParentItemId')
            ->willReturn(false);
        $this->orderItemMock->expects($this->any())
            ->method('isDummy')
            ->willReturn(true);
        $this->orderItemMock->expects($this->any())
            ->method('getHasChildren')
            ->willReturn(true);

        $this->orderChildItemOneMock->expects($this->any())
            ->method('getQtyToRefund')
            ->willReturn(1);
        $this->orderChildItemOneMock->expects($this->any())
            ->method('getId')
            ->willReturn(2);

        $this->orderChildItemTwoMock->expects($this->any())
            ->method('getQtyToRefund')
            ->willReturn(1);
        $this->orderChildItemTwoMock->expects($this->any())
            ->method('getId')
            ->willReturn(3);
        $this->orderItemMock->expects($this->any())
            ->method('getChildrenItems')
            ->willReturn([$this->orderChildItemOneMock, $this->orderChildItemTwoMock]);

        $this->assertTrue(
            $this->model->canRefundItem(
                $this->orderItemMock,
                $orderItemQtys,
                $invoiceQtysRefundLimits
            )
        );
    }
}
