<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Creditmemo\Total;

use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Item;
use Magento\Sales\Model\Order\Creditmemo\Total\Cost;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CostTest extends TestCase
{
    /**
     * @var Cost
     */
    protected $total;

    /**
     * @var Creditmemo|MockObject
     */
    protected $creditmemoMock;

    /**
     * @var Item|MockObject
     */
    protected $creditmemoItemMock;

    protected function setUp(): void
    {
        $this->creditmemoMock = $this->getMockBuilder(Creditmemo::class)
            ->addMethods(['setBaseCost'])
            ->onlyMethods(['getAllItems'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoItemMock = $this->getMockBuilder(Item::class)
            ->addMethods(['getHasChildren'])
            ->onlyMethods(['getBaseCost', 'getQty'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->total = new Cost();
    }

    public function testCollect()
    {
        $this->creditmemoMock->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$this->creditmemoItemMock, $this->creditmemoItemMock]);
        $this->creditmemoItemMock->expects($this->exactly(2))
            ->method('getHasChildren')
            ->willReturn(false);
        $this->creditmemoItemMock->expects($this->exactly(2))
            ->method('getBaseCost')
            ->willReturn(10);
        $this->creditmemoItemMock->expects($this->exactly(2))
            ->method('getQty')
            ->willReturn(2);
        $this->creditmemoMock->expects($this->once())
            ->method('setBaseCost')
            ->with(40)
            ->willReturnSelf();
        $this->assertEquals($this->total, $this->total->collect($this->creditmemoMock));
    }
}
