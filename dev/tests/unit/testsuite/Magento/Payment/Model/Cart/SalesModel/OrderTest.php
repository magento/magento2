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

class OrderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Payment\Model\Cart\SalesModel\Order */
    protected $_model;

    /** @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject */
    protected $_orderMock;

    protected function setUp()
    {
        $this->_orderMock = $this->getMock('Magento\Sales\Model\Order', array(), array(), '', false);
        $this->_model = new \Magento\Payment\Model\Cart\SalesModel\Order($this->_orderMock);
    }

    /**
     * @param string $getterMethod
     * @dataProvider gettersDataProvider
     */
    public function testGetters($getterMethod)
    {
        $this->_orderMock->expects(
            $this->once()
        )->method(
            '__call'
        )->with(
            $getterMethod
        )->will(
            $this->returnValue('some value')
        );
        $this->assertEquals('some value', $this->_model->{$getterMethod}());
    }

    public function gettersDataProvider()
    {
        return array(
            array('getBaseSubtotal'),
            array('getBaseTaxAmount'),
            array('getBaseShippingAmount'),
            array('getBaseDiscountAmount')
        );
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
        $items = array(
            new \Magento\Framework\Object(
                array('parent_item' => 'parent item 1', 'name' => 'name 1', 'qty_ordered' => 1, 'base_price' => 0.1)
            ),
            new \Magento\Framework\Object(
                array('parent_item' => 'parent item 2', 'name' => 'name 2', 'qty_ordered' => 2, 'base_price' => 1.2)
            ),
            new \Magento\Framework\Object(
                array('parent_item' => 'parent item 3', 'name' => 'name 3', 'qty_ordered' => 3, 'base_price' => 2.3)
            )
        );
        $expected = array(
            new \Magento\Framework\Object(
                array(
                    'parent_item' => 'parent item 1',
                    'name' => 'name 1',
                    'qty' => 1,
                    'price' => 0.1,
                    'original_item' => $items[0]
                )
            ),
            new \Magento\Framework\Object(
                array(
                    'parent_item' => 'parent item 2',
                    'name' => 'name 2',
                    'qty' => 2,
                    'price' => 1.2,
                    'original_item' => $items[1]
                )
            ),
            new \Magento\Framework\Object(
                array(
                    'parent_item' => 'parent item 3',
                    'name' => 'name 3',
                    'qty' => 3,
                    'price' => 2.3,
                    'original_item' => $items[2]
                )
            )
        );
        $this->_orderMock->expects($this->once())->method('getAllItems')->will($this->returnValue($items));
        $this->assertEquals($expected, $this->_model->getAllItems());
    }
}
