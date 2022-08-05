<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model\Cart\SalesModel;

use Magento\Framework\DataObject;
use Magento\Payment\Model\Cart\SalesModel\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QuoteTest extends TestCase
{
    /** @var Quote */
    protected $_model;

    /** @var \Magento\Quote\Model\Quote|MockObject */
    protected $_quoteMock;

    protected function setUp(): void
    {
        $this->_quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $this->_model = new Quote($this->_quoteMock);
    }

    public function testGetDataUsingMethod()
    {
        $this->_quoteMock->expects(
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
        $this->_quoteMock->expects(
            $this->any()
        )->method(
            'getBillingAddress'
        )->willReturn(
            'billing address'
        );
        $this->_quoteMock->expects(
            $this->any()
        )->method(
            'getShippingAddress'
        )->willReturn(
            'shipping address'
        );
        $this->assertEquals('shipping address', $this->_model->getTaxContainer());
        $this->_quoteMock->expects($this->any())->method('getIsVirtual')->willReturn(1);
        $this->assertEquals('billing address', $this->_model->getTaxContainer());
    }

    /**
     * @param string $pItem
     * @param string $name
     * @param int $qty
     * @param float $price
     * @dataProvider getAllItemsDataProvider
     */
    public function testGetAllItems($pItem, $name, $qty, $price)
    {
        $itemMock = $this->createMock(AbstractItem::class);
        $itemMock->expects($this->any())->method('getParentItem')->willReturn($pItem);
        $itemMock->expects($this->once())->method('__call')->with('getName')->willReturn($name);
        $itemMock->expects($this->any())->method('getTotalQty')->willReturn($qty);
        $itemMock->expects($this->any())->method('getBaseCalculationPrice')->willReturn($price);
        $expected = [
            new DataObject(
                [
                    'parent_item' => $pItem,
                    'name' => $name,
                    'qty' => $qty,
                    'price' => $price,
                    'original_item' => $itemMock,
                ]
            ),
        ];
        $this->_quoteMock->expects($this->once())->method('getAllItems')->willReturn([$itemMock]);
        $this->assertEquals($expected, $this->_model->getAllItems());
    }

    /**
     * @return array
     */
    public function getAllItemsDataProvider()
    {
        return [
            ['parent item 1', 'name 1', 1, 0.1],
            ['parent item 2', 'name 2', 2, 1.2],
            ['parent item 3', 'name 3', 3, 2.3],
        ];
    }

    public function testGetBaseSubtotal()
    {
        $this->_quoteMock->expects(
            $this->once()
        )->method(
            '__call'
        )->with(
            'getBaseSubtotal'
        )->willReturn(
            100
        );
        $this->assertEquals(100, $this->_model->getBaseSubtotal());
    }

    /**
     * @param int $isVirtual
     * @param string $getterMethod
     * @dataProvider getterDataProvider
     */
    public function testGetter($isVirtual, $getterMethod)
    {
        $address = $this->createMock(Address::class);
        $address->expects(
            $this->any()
        )->method(
            '__call'
        )->with(
            $getterMethod
        )->willReturn(
            $getterMethod
        );
        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $quoteMock->expects($this->any())->method('getIsVirtual')->willReturn($isVirtual);
        $method = 'getShippingAddress';
        if ($isVirtual) {
            $method = 'getBillingAddress';
        }
        $quoteMock->expects($this->any())->method($method)->willReturn($address);
        $model = new Quote($quoteMock);
        $this->assertEquals($getterMethod, $model->{$getterMethod}());
    }

    /**
     * @return array
     */
    public function getterDataProvider()
    {
        return [
            [0, 'getBaseTaxAmount'],
            [1, 'getBaseTaxAmount'],
            [0, 'getBaseShippingAmount'],
            [1, 'getBaseShippingAmount'],
            [0, 'getBaseDiscountAmount'],
            [1, 'getBaseDiscountAmount']
        ];
    }
}
