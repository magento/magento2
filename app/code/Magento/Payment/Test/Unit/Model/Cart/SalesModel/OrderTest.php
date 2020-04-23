<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model\Cart\SalesModel;

use Magento\Framework\DataObject;
use Magento\Payment\Model\Cart\SalesModel\Order;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    /** @var Order */
    protected $_model;

    /** @var \Magento\Sales\Model\Order|MockObject */
    protected $_orderMock;

    protected function setUp(): void
    {
        $this->_orderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $this->_model = new Order($this->_orderMock);
    }

    /**
     * @return array
     */
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
        )->willReturn(
            'some value'
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
            new DataObject(
                ['parent_item' => 'parent item 1', 'name' => 'name 1', 'qty_ordered' => 1, 'base_price' => 0.1]
            ),
            new DataObject(
                ['parent_item' => 'parent item 2', 'name' => 'name 2', 'qty_ordered' => 2, 'base_price' => 1.2]
            ),
            new DataObject(
                ['parent_item' => 'parent item 3', 'name' => 'name 3', 'qty_ordered' => 3, 'base_price' => 2.3]
            ),
        ];
        $expected = [
            new DataObject(
                [
                    'parent_item' => 'parent item 1',
                    'name' => 'name 1',
                    'qty' => 1,
                    'price' => 0.1,
                    'original_item' => $items[0],
                ]
            ),
            new DataObject(
                [
                    'parent_item' => 'parent item 2',
                    'name' => 'name 2',
                    'qty' => 2,
                    'price' => 1.2,
                    'original_item' => $items[1],
                ]
            ),
            new DataObject(
                [
                    'parent_item' => 'parent item 3',
                    'name' => 'name 3',
                    'qty' => 3,
                    'price' => 2.3,
                    'original_item' => $items[2],
                ]
            ),
        ];
        $this->_orderMock->expects($this->once())->method('getAllItems')->willReturn($items);
        $this->assertEquals($expected, $this->_model->getAllItems());
    }
}
