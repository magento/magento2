<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Model\Cart\SalesModel;

class OrderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Payment\Model\Cart\SalesModel\Order */
    protected $_model;

    /** @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject */
    protected $_orderMock;

    protected function setUp()
    {
        $this->_orderMock = $this->getMock(\Magento\Sales\Model\Order::class, [], [], '', false);
        $this->_model = new \Magento\Payment\Model\Cart\SalesModel\Order($this->_orderMock);
    }

    public function gettersDataProvider()
    {
        return [
            ['getBaseSubtotal'],
            ['getBaseTaxAmount'],
            ['getBaseShippingAmount'],
            ['getBaseDiscountAmount']
        ];
    }

    public function testGetDataUsingMethod()
    {
        $this->_orderMock->expects(
            $this->once()
        )->method(
            'getDataUsingMethod'
        )->with(
            'any key',
            'any args'
        )->will(
            $this->returnValue('some value')
        );
        $this->assertEquals('some value', $this->_model->getDataUsingMethod('any key', 'any args'));
    }

    public function testGetTaxContainer()
    {
        $this->assertEquals($this->_orderMock, $this->_model->getTaxContainer());
    }

    public function testGetAllItems()
    {
        $items = [
            new \Magento\Framework\DataObject(
                ['parent_item' => 'parent item 1', 'name' => 'name 1', 'qty_ordered' => 1, 'base_price' => 0.1]
            ),
            new \Magento\Framework\DataObject(
                ['parent_item' => 'parent item 2', 'name' => 'name 2', 'qty_ordered' => 2, 'base_price' => 1.2]
            ),
            new \Magento\Framework\DataObject(
                ['parent_item' => 'parent item 3', 'name' => 'name 3', 'qty_ordered' => 3, 'base_price' => 2.3]
            ),
        ];
        $expected = [
            new \Magento\Framework\DataObject(
                [
                    'parent_item' => 'parent item 1',
                    'name' => 'name 1',
                    'qty' => 1,
                    'price' => 0.1,
                    'original_item' => $items[0],
                ]
            ),
            new \Magento\Framework\DataObject(
                [
                    'parent_item' => 'parent item 2',
                    'name' => 'name 2',
                    'qty' => 2,
                    'price' => 1.2,
                    'original_item' => $items[1],
                ]
            ),
            new \Magento\Framework\DataObject(
                [
                    'parent_item' => 'parent item 3',
                    'name' => 'name 3',
                    'qty' => 3,
                    'price' => 2.3,
                    'original_item' => $items[2],
                ]
            ),
        ];
        $this->_orderMock->expects($this->once())->method('getAllItems')->will($this->returnValue($items));
        $this->assertEquals($expected, $this->_model->getAllItems());
    }
}
