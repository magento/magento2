<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Model\Cart\SalesModel;

use Magento\Framework\DataObject;
use Magento\Payment\Model\Cart\SalesModel\Order;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    /**
     * @var Order
     */
    private $model;

    /**
     * @var \Magento\Sales\Model\Order|MockObject
     */
    private $orderMock;

    protected function setUp()
    {
        $this->orderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $this->model = new Order($this->orderMock);
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
        $this->orderMock->expects(
            $this->once()
        )->method(
            'getDataUsingMethod'
        )->with(
            'any key',
            'any args'
        )->will(
            $this->returnValue('some value')
        );
        $this->assertEquals('some value', $this->model->getDataUsingMethod('any key', 'any args'));
    }

    public function testGetTaxContainer()
    {
        $this->assertEquals($this->orderMock, $this->model->getTaxContainer());
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
        $this->orderMock->expects($this->once())->method('getAllItems')->will($this->returnValue($items));
        $this->assertEquals($expected, $this->model->getAllItems());
    }
}
