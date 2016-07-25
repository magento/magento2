<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Plugin;

class QuoteItemProductOptionTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $quoteItemMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $orderItemMock;

    /** @var \Magento\Catalog\Model\Plugin\QuoteItemProductOption */
    protected $model;

    /**
     * @var \Magento\Quote\Model\Quote\Item\ToOrderItem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->orderItemMock = $this->getMock('Magento\Sales\Model\Order\Item', [], [], '', false);
        $this->quoteItemMock = $this->getMock('Magento\Quote\Model\Quote\Item', [], [], '', false);
        $this->subjectMock = $this->getMock('Magento\Quote\Model\Quote\Item\ToOrderItem', [], [], '', false);
        $this->model = new \Magento\Catalog\Model\Plugin\QuoteItemProductOption();
    }

    public function testBeforeItemToOrderItemEmptyOptions()
    {
        $this->quoteItemMock->expects($this->exactly(2))->method('getOptions')->will($this->returnValue([]));

        $this->assertNull($this->model->beforeConvert($this->subjectMock, $this->quoteItemMock));
    }

    public function testBeforeItemToOrderItemWithOptions()
    {
        $itemOption = $this->getMock(
            'Magento\Quote\Model\Quote\Item\Option',
            ['getCode', '__wakeup'],
            [],
            '',
            false
        );
        $this->quoteItemMock->expects(
            $this->exactly(2)
        )->method(
            'getOptions'
        )->will(
            $this->returnValue([$itemOption, $itemOption])
        );

        $itemOption->expects($this->at(0))->method('getCode')->will($this->returnValue('someText_8'));
        $itemOption->expects($this->at(1))->method('getCode')->will($this->returnValue('not_int_text'));

        $productMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $optionMock = $this->getMock('stdClass', ['getType']);
        $optionMock->expects($this->once())->method('getType');

        $productMock->expects($this->once())->method('getOptionById')->will($this->returnValue($optionMock));

        $this->quoteItemMock->expects($this->once())->method('getProduct')->will($this->returnValue($productMock));

        $this->assertNull($this->model->beforeConvert($this->subjectMock, $this->quoteItemMock));
    }
}
