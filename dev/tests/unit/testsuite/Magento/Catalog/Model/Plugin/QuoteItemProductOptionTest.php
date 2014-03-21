<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Plugin;

class QuoteItemProductOptionTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $quoteItemMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $orderItemMock;

    /** @var \Magento\Catalog\Model\Plugin\QuoteItemProductOption */
    protected $model;

    /**
     * @var \Closure
     */
    protected $closureMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->orderItemMock = $this->getMock('Magento\Sales\Model\Order\Item', array(), array(), '', false);
        $this->quoteItemMock = $this->getMock('Magento\Sales\Model\Quote\Item', array(), array(), '', false);
        $orderItem = $this->orderItemMock;
        $this->subjectMock = $this->getMock('Magento\Sales\Model\Convert\Quote', array(), array(), '', false);
        $this->closureMock = function () use ($orderItem) {
            return $orderItem;
        };
        $this->model = new \Magento\Catalog\Model\Plugin\QuoteItemProductOption();
    }

    public function testAroundItemToOrderItemEmptyOptions()
    {
        $this->quoteItemMock->expects($this->exactly(2))->method('getOptions')->will($this->returnValue(array()));

        $orderItem = $this->model->aroundItemToOrderItem($this->subjectMock, $this->closureMock, $this->quoteItemMock);
        $this->assertSame($this->orderItemMock, $orderItem);
    }

    public function testAroundItemToOrderItemWithOptions()
    {
        $itemOption = $this->getMock(
            'Magento\Sales\Model\Quote\Item\Option',
            array('getCode', '__wakeup'),
            array(),
            '',
            false
        );
        $this->quoteItemMock->expects(
            $this->exactly(2)
        )->method(
            'getOptions'
        )->will(
            $this->returnValue(array($itemOption, $itemOption))
        );

        $itemOption->expects($this->at(0))->method('getCode')->will($this->returnValue('someText_8'));
        $itemOption->expects($this->at(1))->method('getCode')->will($this->returnValue('not_int_text'));

        $productMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $optionMock = $this->getMock('stdClass', array('getType'));
        $optionMock->expects($this->once())->method('getType');

        $productMock->expects($this->once())->method('getOptionById')->will($this->returnValue($optionMock));

        $this->quoteItemMock->expects($this->once())->method('getProduct')->will($this->returnValue($productMock));

        $orderItem = $this->model->aroundItemToOrderItem($this->subjectMock, $this->closureMock, $this->quoteItemMock);
        $this->assertSame($this->orderItemMock, $orderItem);
    }
}
