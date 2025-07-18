<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Payment\Model\Cart;
use Magento\Payment\Model\Cart\SalesModel\Factory;
use Magento\Payment\Model\Cart\SalesModel\SalesModelInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CartTest extends TestCase
{
    /** @var \Magento\Payment\Model\Cart */
    protected $_model;

    /**  @var MockObject */
    protected $_eventManagerMock;

    /**  @var MockObject */
    protected $_salesModelMock;

    protected function setUp(): void
    {
        $this->_eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->_salesModelMock = $this->getMockForAbstractClass(SalesModelInterface::class);
        $factoryMock = $this->createMock(Factory::class);
        $factoryMock->expects($this->once())->method('create')->willReturn($this->_salesModelMock);

        $this->_model = new Cart($factoryMock, $this->_eventManagerMock, null);
    }

    /**
     * Test sales model getter
     */
    public function testGetSalesModel()
    {
        $this->assertSame($this->_model->getSalesModel(), $this->_salesModelMock);
    }

    /**
     * Test addCustomItem()
     */
    public function testAddCustomItem()
    {
        $this->_salesModelMock->expects(
            $this->once()
        )->method(
            'getAllItems'
        )->willReturn(
            $this->_getSalesModelItems()
        );
        $this->_model->getAllItems();
        $this->_model->addCustomItem('test', 10, 10.5, 'some_id');
        $items = $this->_model->getAllItems();
        $customItem = array_pop($items);
        $this->assertTrue(
            $customItem->getName() == 'test' &&
            $customItem->getQty() == 10 &&
            $customItem->getAmount() == 10.5 &&
            $customItem->getId() == 'some_id'
        );
    }

    /**
     * @param array $transferFlags
     * @param array $salesModelItems
     * @param array $salesModelAmounts
     * @param array $expected
     * @dataProvider cartDataProvider
     */
    public function testGetAmounts($transferFlags, $salesModelItems, $salesModelAmounts, $expected)
    {
        $amounts = $this->_collectItemsAndAmounts($transferFlags, $salesModelItems, $salesModelAmounts);
        $this->assertEquals($expected, $amounts);

        // check that method just return calculated result for further calls
        $this->_eventManagerMock->expects($this->never())->method('dispatch');
        $amounts = $this->_model->getAmounts();
        $this->assertEquals($expected, $amounts);
    }

    /**
     * @param array $transferFlags
     * @param array $salesModelItems
     * @param array $salesModelAmounts
     * @dataProvider cartDataProvider
     */
    public function testGetAllItems($transferFlags, $salesModelItems, $salesModelAmounts)
    {
        $this->_collectItemsAndAmounts($transferFlags, $salesModelItems, $salesModelAmounts);

        $customItems = [];
        if ($transferFlags['transfer_shipping']) {
            $customItems[] = new DataObject(
                ['name' => 'Shipping', 'qty' => 1, 'amount' => $salesModelAmounts['BaseShippingAmount']]
            );
        }
        if ($transferFlags['transfer_discount']) {
            $customItems[] = new DataObject(
                ['name' => 'Discount', 'qty' => 1, 'amount' => -1.00 * $salesModelAmounts['BaseDiscountAmount']]
            );
        }

        $cartItems = $this->_convertToCartItems($salesModelItems);
        $expected = array_merge($cartItems, $customItems);
        $areEqual = $this->_compareSalesItems($expected, $this->_model->getAllItems());
        $this->assertTrue($areEqual);
    }

    /**
     * Test all amount specific methods i.e. add...(), set...(), get...()
     */
    public function testAmountSettersAndGetters()
    {
        foreach (['Discount', 'Shipping', 'Tax'] as $amountType) {
            $setMethod = 'set' . $amountType;
            $getMethod = 'get' . $amountType;
            $addMethod = 'add' . $amountType;

            $this->_model->{$setMethod}(10);
            $this->assertEquals(10, $this->_model->{$getMethod}());

            $this->_model->{$addMethod}(5);
            $this->assertEquals(15, $this->_model->{$getMethod}());

            $this->_model->{$addMethod}(-20);
            $this->assertEquals(-5, $this->_model->{$getMethod}());

            $this->_model->{$setMethod}(10);
            $this->assertEquals(10, $this->_model->{$getMethod}());
        }

        // there is no method setSubtotal(), so test the following separately
        $this->_model->addSubtotal(10);
        $this->assertEquals(10, $this->_model->getSubtotal());

        $this->_model->addSubtotal(2);
        $this->assertEquals(12, $this->_model->getSubtotal());

        $this->_model->addSubtotal(-20);
        $this->assertEquals(-8, $this->_model->getSubtotal());
    }

    /**
     * Data provider
     *
     * @return array
     */
    public static function cartDataProvider()
    {
        return [
            // 1. All transfer flags set to true
            [
                ['transfer_shipping' => true, 'transfer_discount' => true],
                self::_getSalesModelItems(),
                [
                    'BaseDiscountAmount' => 15.0,
                    'BaseShippingAmount' => 20.0,
                    'BaseSubtotal' => 100.0,
                    'BaseTaxAmount' => 8.0
                ],
                [
                    Cart::AMOUNT_DISCOUNT => 0.0,
                    Cart::AMOUNT_SHIPPING => 0.0,
                    Cart::AMOUNT_SUBTOTAL => 105.0, // = 100.5 + shipping - discount
                    Cart::AMOUNT_TAX => 8.0
                ]
            ],
            // 2. All transfer flags set to false
            [
                ['transfer_shipping' => false, 'transfer_discount' => false],
                self::_getSalesModelItems(),
                [
                    'BaseDiscountAmount' => 15.0,
                    'BaseShippingAmount' => 20.0,
                    'BaseSubtotal' => 100.0,
                    'BaseTaxAmount' => 8.0
                ],
                [
                    Cart::AMOUNT_DISCOUNT => 15.0,
                    Cart::AMOUNT_SHIPPING => 20.0,
                    Cart::AMOUNT_SUBTOTAL => 100.0,
                    Cart::AMOUNT_TAX => 8.0
                ]
            ],
            // 3. Shipping transfer flag set to true, discount to false, sales items are empty (don't affect result)
            [
                ['transfer_shipping' => true, 'transfer_discount' => false],
                [],
                [
                    'BaseDiscountAmount' => 15.0,
                    'BaseShippingAmount' => 20.0,
                    'BaseSubtotal' => 100.0,
                    'BaseTaxAmount' => 8.0
                ],
                [
                    Cart::AMOUNT_DISCOUNT => 15.0,
                    Cart::AMOUNT_SHIPPING => 0.0,
                    Cart::AMOUNT_SUBTOTAL => 120.0,
                    Cart::AMOUNT_TAX => 8.0
                ]
            ]
        ];
    }

    /**
     * Return true if arrays of cart sales items are equal, false otherwise. Elements order not considered
     *
     * @param array $salesItemsA
     * @param array $salesItemsB
     * @return bool
     */
    protected function _compareSalesItems(array $salesItemsA, array $salesItemsB)
    {
        if (count($salesItemsA) != count($salesItemsB)) {
            return false;
        }

        $toStringCallback = function (&$item) {
            $item = $item->toString();
        };

        array_walk($salesItemsA, $toStringCallback);
        array_walk($salesItemsB, $toStringCallback);

        sort($salesItemsA);
        sort($salesItemsB);

        return implode('', $salesItemsA) == implode('', $salesItemsB);
    }

    /**
     * Collect sales model items and calculate amounts of sales model
     *
     * @param array $transferFlags
     * @param array $salesModelItems
     * @param array $salesModelAmounts
     * @return array Cart amounts
     */
    protected function _collectItemsAndAmounts($transferFlags, $salesModelItems, $salesModelAmounts)
    {
        if ($transferFlags['transfer_shipping']) {
            $this->_model->setTransferShippingAsItem();
        }
        if ($transferFlags['transfer_discount']) {
            $this->_model->setTransferDiscountAsItem();
        }

        $this->_eventManagerMock->expects(
            $this->once()
        )->method(
            'dispatch'
        )->with(
            'payment_cart_collect_items_and_amounts',
            ['cart' => $this->_model]
        );

        $this->_salesModelMock->expects(
            $this->once()
        )->method(
            'getAllItems'
        )->willReturn(
            $salesModelItems
        );

        foreach ($salesModelAmounts as $key => $value) {
            $this->_salesModelMock->expects($this->once())->method('get' . $key)->willReturn($value);
        }

        return $this->_model->getAmounts();
    }

    /**
     * Return sales model items
     *
     * @return array
     */
    protected static function _getSalesModelItems()
    {
        $product = new DataObject(['id' => '1']);
        return [
            new DataObject(
                ['name' => 'name 1', 'qty' => 1, 'price' => 0.1, 'original_item' => $product]
            ),
            new DataObject(
                ['name' => 'name 2', 'qty' => 2, 'price' => 1.2, 'original_item' => $product]
            ),
            new DataObject(
                [
                    'parent_item' => 'parent item 3',
                    'name' => 'name 3',
                    'qty' => 3,
                    'price' => 2.3,
                    'original_item' => $product,
                ]
            )
        ];
    }

    /**
     * Convert sales model items to cart items
     *
     * @param array $salesModelItems
     * @return array
     */
    protected function _convertToCartItems(array $salesModelItems)
    {
        $result = [];
        foreach ($salesModelItems as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            $result[] = new DataObject(
                [
                    'name' => $item->getName(),
                    'qty' => $item->getQty(),
                    'amount' => $item->getPrice(),
                    'id' => $item->getOriginalItem()->getId(),
                ]
            );
        }
        return $result;
    }
}
