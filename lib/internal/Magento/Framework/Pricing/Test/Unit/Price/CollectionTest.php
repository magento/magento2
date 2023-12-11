<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Pricing\Test\Unit\Price;

use Magento\Framework\Pricing\Price\Collection;
use Magento\Framework\Pricing\Price\Factory;
use Magento\Framework\Pricing\Price\Pool;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\SaleableInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for class Collection
 */
class CollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var PriceInterface|MockObject
     */
    protected $priceMock;

    /**
     * @var SaleableInterface|MockObject
     */
    protected $saleableItemMock;

    /**
     * @var Factory|MockObject
     */
    protected $factoryMock;

    /**
     * @var float
     */
    protected $quantity;

    /**
     * Test setUp
     */
    protected function setUp(): void
    {
        $this->pool = new Pool(
            [
                'regular_price' => 'RegularPrice',
                'special_price' => 'SpecialPrice',
            ]
        );

        $this->saleableItemMock = $this->getMockForAbstractClass(SaleableInterface::class);
        $this->priceMock = $this->getMockForAbstractClass(PriceInterface::class);
        $this->factoryMock = $this->createMock(Factory::class);

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
                $this->saleableItemMock,
                'RegularPrice',
                $this->quantity
            )
            ->willReturn($this->priceMock);
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
                $this->saleableItemMock,
                $this->pool->current(),
                $this->quantity
            )
            ->willReturn($this->priceMock);
        $this->assertEquals($this->priceMock, $this->collection->current());
    }
}
