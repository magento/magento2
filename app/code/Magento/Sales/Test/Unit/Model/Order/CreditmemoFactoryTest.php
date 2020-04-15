<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order;

/**
 * Unit test for creditmemo factory class.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreditmemoFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Subject of testing.
     *
     * @var \Magento\Sales\Model\Order\CreditmemoFactory
     */
    protected $subject;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->subject = $objectManager->getObject(\Magento\Sales\Model\Order\CreditmemoFactory::class, []);
    }

    /**
     * Check if order item can be refunded
     */
    public function testCanRefundItem()
    {
        $orderItem = $this->createPartialMock(
            \Magento\Sales\Model\Order\Item::class,
            ['getChildrenItems', 'isDummy', 'getHasChildren', 'getId', 'getParentItemId']
        );
        $orderItem->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $orderItem->expects($this->any())->method('getParentItemId')->willReturn(false);
        $orderItem->expects($this->any())->method('isDummy')->willReturn(true);
        $orderItem->expects($this->any())->method('getHasChildren')->willReturn(true);
        $orderChildItemOne = $this->createPartialMock(
            \Magento\Sales\Model\Order\Item::class,
            ['getQtyToRefund', 'getId']
        );
        $orderChildItemOne->expects($this->any())->method('getQtyToRefund')->willReturn(1);
        $orderChildItemOne->expects($this->any())->method('getId')->willReturn(2);
        $orderChildItemTwo = $this->createPartialMock(
            \Magento\Sales\Model\Order\Item::class,
            ['getQtyToRefund', 'getId']
        );
        $orderChildItemTwo->expects($this->any())->method('getQtyToRefund')->willReturn(1);
        $orderChildItemTwo->expects($this->any())->method('getId')->willReturn(3);
        $orderItem->expects($this->any())
            ->method('getChildrenItems')
            ->willReturn([$orderChildItemOne, $orderChildItemTwo]);
        $testMethod = new \ReflectionMethod(
            \Magento\Sales\Model\Order\CreditmemoFactory::class,
            'canRefundItem'
        );
        $orderItemQtys = [
            2 => 0,
            3 => 0
        ];
        $invoiceQtysRefundLimits = [];
        $testMethod->setAccessible(true);
        $result         = $testMethod->invoke($this->subject, $orderItem, $orderItemQtys, $invoiceQtysRefundLimits);
        $expectedResult = true;
        $this->assertEquals($expectedResult, $result);
    }
}
