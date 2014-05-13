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

namespace Magento\Framework\Pricing\Price;

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
     * @var \Magento\Framework\Pricing\Object\SaleableInterface|\PHPUnit_Framework_MockObject_MockObject
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
                'group_price' => 'GroupPrice'
            ]
        );

        $this->saleableItemMock = $this->getMockForAbstractClass('Magento\Framework\Pricing\Object\SaleableInterface');
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
