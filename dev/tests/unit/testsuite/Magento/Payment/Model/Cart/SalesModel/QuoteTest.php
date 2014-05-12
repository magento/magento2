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
namespace Magento\Payment\Model\Cart\SalesModel;

class QuoteTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Payment\Model\Cart\SalesModel\Quote */
    protected $_model;

    /** @var \Magento\Sales\Model\Quote|\PHPUnit_Framework_MockObject_MockObject */
    protected $_quoteMock;

    protected function setUp()
    {
        $this->_quoteMock = $this->getMock('Magento\Sales\Model\Quote', array(), array(), '', false);
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
     * @param int $isNominal
     * @param string $pItem
     * @param string $name
     * @param int $qty
     * @param float $price
     * @dataProvider getAllItemsDataProvider
     */
    public function testGetAllItems($isNominal, $pItem, $name, $qty, $price)
    {
        $itemMock = $this->getMock('Magento\Sales\Model\Quote\Item\AbstractItem', array(), array(), '', false);
        $itemMock->expects($this->any())->method('isNominal')->will($this->returnValue($isNominal));
        $itemMock->expects($this->any())->method('getParentItem')->will($this->returnValue($pItem));
        $itemMock->expects($this->once())->method('__call')->with('getName')->will($this->returnValue($name));
        $itemMock->expects($this->any())->method('getTotalQty')->will($this->returnValue($qty));
        $itemMock->expects($this->any())->method('getBaseCalculationPrice')->will($this->returnValue($price));
        $expected = array(
            new \Magento\Framework\Object(
                array(
                    'parent_item' => $pItem,
                    'name' => $name,
                    'qty' => $qty,
                    'price' => $isNominal ? 0 : $price,
                    'original_item' => $itemMock
                )
            )
        );
        $this->_quoteMock->expects($this->once())->method('getAllItems')->will($this->returnValue(array($itemMock)));
        $this->assertEquals($expected, $this->_model->getAllItems());
    }

    public function getAllItemsDataProvider()
    {
        return array(
            array(0, 'parent item 1', 'name 1', 1, 0.1),
            array(1, 'parent item 1', 'name 1', 1, 0.1),
            array(0, 'parent item 2', 'name 2', 2, 1.2),
            array(1, 'parent item 2', 'name 2', 2, 1.2),
            array(0, 'parent item 3', 'name 3', 3, 2.3),
            array(1, 'parent item 3', 'name 3', 3, 2.3)
        );
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
        $address = $this->getMock('Magento\Sales\Model\Quote\Address', array(), array(), '', false);
        $address->expects(
            $this->any()
        )->method(
            '__call'
        )->with(
            $getterMethod
        )->will(
            $this->returnValue($getterMethod)
        );
        $quoteMock = $this->getMock('Magento\Sales\Model\Quote', array(), array(), '', false);
        $quoteMock->expects($this->any())->method('getIsVirtual')->will($this->returnValue($isVirtual));
        $method = 'getShippingAddress';
        if ($isVirtual) {
            $method = 'getBillingAddress';
        }
        $quoteMock->expects($this->any())->method($method)->will($this->returnValue($address));
        $model = new \Magento\Payment\Model\Cart\SalesModel\Quote($quoteMock);
        $this->assertEquals($getterMethod, $model->{$getterMethod}());
    }

    public function getterDataProvider()
    {
        return array(
            array(0, 'getBaseTaxAmount'),
            array(1, 'getBaseTaxAmount'),
            array(0, 'getBaseShippingAmount'),
            array(1, 'getBaseShippingAmount'),
            array(0, 'getBaseDiscountAmount'),
            array(1, 'getBaseDiscountAmount')
        );
    }
}
