<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Model\Plugin;

class QuoteItemTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Bundle\Model\Plugin\QuoteItem */
    protected $model;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $quoteItemMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $orderItemMock;

    /**
     * @var /PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * @var /Closure
     */
    protected $closureMock;

    protected function setUp()
    {
        $this->orderItemMock = $this->getMock('Magento\Sales\Model\Order\Item', [], [], '', false);
        $this->quoteItemMock = $this->getMock('Magento\Quote\Model\Quote\Item', [], [], '', false);
        $orderItem = $this->orderItemMock;
        $this->closureMock = function () use ($orderItem) {
            return $orderItem;
        };
        $this->subjectMock = $this->getMock('Magento\Quote\Model\Quote\Item\ToOrderItem', [], [], '', false);
        $this->model = new \Magento\Bundle\Model\Plugin\QuoteItem();
    }

    public function testAroundItemToOrderItemPositive()
    {
        $productMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $bundleAttribute = $this->getMock(
            'Magento\Catalog\Model\Product\Configuration\Item\Option',
            [],
            [],
            '',
            false
        );
        $productMock->expects(
            $this->once()
        )->method(
            'getCustomOption'
        )->with(
            'bundle_selection_attributes'
        )->will(
            $this->returnValue($bundleAttribute)
        );
        $this->quoteItemMock->expects($this->once())->method('getProduct')->will($this->returnValue($productMock));
        $this->orderItemMock->expects($this->once())->method('setProductOptions');

        $orderItem = $this->model->aroundConvert($this->subjectMock, $this->closureMock, $this->quoteItemMock, []);
        $this->assertSame($this->orderItemMock, $orderItem);
    }

    public function testAroundItemToOrderItemNegative()
    {
        $productMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $productMock->expects(
            $this->once()
        )->method(
            'getCustomOption'
        )->with(
            'bundle_selection_attributes'
        )->will(
            $this->returnValue(false)
        );
        $this->quoteItemMock->expects($this->once())->method('getProduct')->will($this->returnValue($productMock));
        $this->orderItemMock->expects($this->never())->method('setProductOptions');

        $orderItem = $this->model->aroundConvert($this->subjectMock, $this->closureMock, $this->quoteItemMock, []);
        $this->assertSame($this->orderItemMock, $orderItem);
    }
}
