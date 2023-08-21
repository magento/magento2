<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Order\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Unit test for creditmemo factory class.
 */
class CreditmemoFactoryTest extends TestCase
{
    /**
     * @var CreditmemoFactory
     */
    protected $subject;

    /**
     * @var ReflectionMethod
     */
    protected $testMethod;

    /**
     * @var Item|MockObject
     */
    protected $orderItemMock;

    /**
     * @var Item|MockObject
     */
    protected $orderChildItemOneMock;

    /**
     * @var Item|MockObject
     */
    protected $orderChildItemTwoMock;

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
        $this->testMethod = new ReflectionMethod(CreditmemoFactory::class, 'canRefundItem');

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->subject = $objectManagerHelper->getObject(CreditmemoFactory::class, []);
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

        $this->testMethod->setAccessible(true);

        $this->assertTrue(
            $this->testMethod->invoke(
                $this->subject,
                $this->orderItemMock,
                $orderItemQtys,
                $invoiceQtysRefundLimits
            )
        );
    }
}
