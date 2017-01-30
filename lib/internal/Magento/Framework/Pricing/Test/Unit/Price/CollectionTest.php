<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Test\Unit\Price;

use \Magento\Framework\Pricing\Price\Collection;
use \Magento\Framework\Pricing\Price\Pool;

/**
 * Test for class Collection
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Pricing\Price\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Framework\Pricing\Price\Pool
     */
    protected $pool;

    /**
     * @var \Magento\Framework\Pricing\Price\PriceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceMock;

    /**
     * @var \Magento\Framework\Pricing\SaleableInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $saleableItemMock;

    /**
     * @var \Magento\Framework\Pricing\Price\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $factoryMock;

    /**
     * @var float
     */
    protected $quantity;

    /**
     * Test setUp
     */
    public function setUp()
    {
        $this->pool = new Pool(
            [
                'regular_price' => 'RegularPrice',
                'special_price' => 'SpecialPrice',
            ]
        );

        $this->saleableItemMock = $this->getMockForAbstractClass('Magento\Framework\Pricing\SaleableInterface');
        $this->priceMock = $this->getMockForAbstractClass('Magento\Framework\Pricing\Price\PriceInterface');
        $this->factoryMock = $this->getMock('Magento\Framework\Pricing\Price\Factory', [], [], '', false);

        $this->collection = new Collection(
            $this->saleableItemMock,
            $this->factoryMock,
            $this->pool,
            $this->quantity
        );
    }

    /**
     * Test get method
     */
    public function testGet()
    {
        $this->factoryMock->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo($this->saleableItemMock),
                $this->equalTo('RegularPrice'),
                $this->quantity
            )
            ->will($this->returnValue($this->priceMock));
        $this->assertEquals($this->priceMock, $this->collection->get('regular_price'));
        //Calling the get method again with the same code, cached copy should be used
        $this->assertEquals($this->priceMock, $this->collection->get('regular_price'));
    }

    /**
     * Test current method
     */
    public function testCurrent()
    {
        $this->factoryMock->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo($this->saleableItemMock),
                $this->equalTo($this->pool->current()),
                $this->quantity
            )
            ->will($this->returnValue($this->priceMock));
        $this->assertEquals($this->priceMock, $this->collection->current());
    }
}
