<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Model\Cart\SalesModel;

class QuoteTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Payment\Model\Cart\SalesModel\Quote */
    protected $_model;

    /** @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject */
    protected $_quoteMock;

    protected function setUp()
    {
        $this->_quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
        $this->_model = new \Magento\Payment\Model\Cart\SalesModel\Quote($this->_quoteMock);
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
        )->will(
            $this->returnValue('some value')
        );
        $this->assertEquals('some value', $this->_model->getDataUsingMethod('any key', 'any args'));
    }

    public function testGetTaxContainer()
    {
        $this->_quoteMock->expects(
            $this->any()
        )->method(
            'getBillingAddress'
        )->will(
            $this->returnValue('billing address')
        );
        $this->_quoteMock->expects(
            $this->any()
        )->method(
            'getShippingAddress'
        )->will(
            $this->returnValue('shipping address')
        );
        $this->assertEquals('shipping address', $this->_model->getTaxContainer());
        $this->_quoteMock->expects($this->any())->method('getIsVirtual')->will($this->returnValue(1));
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
        $itemMock = $this->getMock(\Magento\Quote\Model\Quote\Item\AbstractItem::class, [], [], '', false);
        $itemMock->expects($this->any())->method('getParentItem')->will($this->returnValue($pItem));
        $itemMock->expects($this->once())->method('__call')->with('getName')->will($this->returnValue($name));
        $itemMock->expects($this->any())->method('getTotalQty')->will($this->returnValue($qty));
        $itemMock->expects($this->any())->method('getBaseCalculationPrice')->will($this->returnValue($price));
        $expected = [
            new \Magento\Framework\DataObject(
                [
                    'parent_item' => $pItem,
                    'name' => $name,
                    'qty' => $qty,
                    'price' => $price,
                    'original_item' => $itemMock,
                ]
            ),
        ];
        $this->_quoteMock->expects($this->once())->method('getAllItems')->will($this->returnValue([$itemMock]));
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
        )->will(
            $this->returnValue(100)
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
        $address = $this->getMock(\Magento\Quote\Model\Quote\Address::class, [], [], '', false);
        $address->expects(
            $this->any()
        )->method(
            '__call'
        )->with(
            $getterMethod
        )->will(
            $this->returnValue($getterMethod)
        );
        $quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
        $quoteMock->expects($this->any())->method('getIsVirtual')->will($this->returnValue($isVirtual));
        $method = 'getShippingAddress';
        if ($isVirtual) {
            $method = 'getBillingAddress';
        }
        $quoteMock->expects($this->any())->method($method)->will($this->returnValue($address));
        $model = new \Magento\Payment\Model\Cart\SalesModel\Quote($quoteMock);
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
