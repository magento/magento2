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
namespace Magento\Bundle\Model\Plugin;

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
        $this->orderItemMock = $this->getMock('Magento\Sales\Model\Order\Item', array(), array(), '', false);
        $this->quoteItemMock = $this->getMock('Magento\Sales\Model\Quote\Item', array(), array(), '', false);
        $orderItem = $this->orderItemMock;
        $this->closureMock = function () use ($orderItem) {
            return $orderItem;
        };
        $this->subjectMock = $this->getMock('Magento\Sales\Model\Convert\Quote', array(), array(), '', false);
        $this->model = new \Magento\Bundle\Model\Plugin\QuoteItem();
    }

    public function testAroundItemToOrderItemPositive()
    {
        $productMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $bundleAttribute = $this->getMock(
            'Magento\Catalog\Model\Product\Configuration\Item\Option',
            array(),
            array(),
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

        $orderItem = $this->model->aroundItemToOrderItem($this->subjectMock, $this->closureMock, $this->quoteItemMock);
        $this->assertSame($this->orderItemMock, $orderItem);
    }

    public function testAroundItemToOrderItemNegative()
    {
        $productMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
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

        $orderItem = $this->model->aroundItemToOrderItem($this->subjectMock, $this->closureMock, $this->quoteItemMock);
        $this->assertSame($this->orderItemMock, $orderItem);
    }
}
