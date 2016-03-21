<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order\Creditmemo\Total;

/**
 * Class CostTest
 */
class CostTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Creditmemo\Total\Cost
     */
    protected $total;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoMock;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoItemMock;

    protected function setUp()
    {
        $this->creditmemoMock = $this->getMock('\Magento\Sales\Model\Order\Creditmemo', [
            'setBaseCost', 'getAllItems'
        ], [], '', false);
        $this->creditmemoItemMock = $this->getMock(
            '\Magento\Sales\Model\Order\Creditmemo\Item',
            ['getHasChildren', 'getBaseCost', 'getQty'],
            [],
            '',
            false
        );
        $this->total = new \Magento\Sales\Model\Order\Creditmemo\Total\Cost();
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
