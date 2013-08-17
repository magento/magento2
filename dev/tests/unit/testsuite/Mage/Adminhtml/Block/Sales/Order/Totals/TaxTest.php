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
 * @category    Magento
 * @package     Mage_Adminhtml
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Adminhtml_Block_Sales_Order_Totals_TaxTest
 */
class Mage_Adminhtml_Block_Sales_Order_Totals_TaxTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Adminhtml_Block_Sales_Order_Totals_Tax
     */
    protected $_block;

    /**
     * @var Magento_ObjectManager|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * Instantiate Mage_Adminhtml_Block_Sales_Order_Totals_Tax block
     */
    protected function setUp()
    {
        $this->_block = $this->getMockBuilder('Mage_Adminhtml_Block_Sales_Order_Totals_Tax')
            ->setConstructorArgs($this->_getModelArgument())
            ->setMethods(array('getOrder'))
            ->getMock();
    }

    /**
     * Module arguments for Mage_Adminhtml_Block_Sales_Order_Totals_Tax
     *
     * @return array
     */
    protected function _getModelArgument()
    {
        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);
        return $objectManagerHelper->getConstructArguments(
            'Mage_Adminhtml_Block_Sales_Order_Totals_Tax',
            array(
                'context'         => $this->getMock('Mage_Backend_Block_Template_Context', array(), array(), '', false),
                'taxConfig'       => $this->getMock('Mage_Tax_Model_Config', array(), array(), '', false),
                'taxHelper'       => $this->_getTaxHelperMock(),
                'taxCalculation'  => $this->getMock('Mage_Tax_Model_Calculation', array(), array(), '', false),
                'taxOrderFactory' => $this->getMock('Mage_Tax_Model_Sales_Order_Tax_Factory', array(), array(), '',
                    false),
            )
        );
    }

    /**
     * @return Mage_Sales_Model_Order|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getSalesOrderMock()
    {
        $orderMock = $this->getMockBuilder('Mage_Sales_Model_Order')
            ->setMethods(array('getItemsCollection'))
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->any())
            ->method('getItemsCollection')
            ->will($this->returnValue(array()));
        return $orderMock;
    }

    /**
     * @return Mage_Tax_Helper_Data|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getTaxHelperMock()
    {
        $taxHelper = $this->getMockBuilder('Mage_Tax_Helper_Data')
            ->disableOriginalConstructor()
            ->setMethods(array('__'))
            ->getMock();
        $taxHelper->expects($this->any())->method('__')->will($this->returnArgument(0));
        return $taxHelper;
    }

    /**
     * Test MAGETWO-1653: Incorrect tax summary for partial credit memos/invoices
     *
     * @dataProvider getSampleData
     */
    public function testAddAttributesToForm($actual, $expected)
    {
        $orderMock = $this->_getSalesOrderMock();
        $orderMock->setData($actual);
        $this->_block->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($orderMock));
        $fullTaxInfo = $this->_block->getFullTaxInfo();
        $this->assertEquals(reset($fullTaxInfo), $expected);
        $this->assertTrue(true);
    }

    /**
     * Data provider with sample data for tax order
     *
     * @return array
     */
    public function getSampleData()
    {
        return array(
            array(
                'actual'   => array(
                    'calculated_taxes'         => array(),
                    'shipping_tax'             => array(),
                    'shipping_tax_amount'      => 1.25,
                    'base_shipping_tax_amount' => 3.25,
                    'tax_amount'               => 0.16,
                    'base_tax_amount'          => 2
                ),
                'expected' => array(
                    'tax_amount'      => 1.25,
                    'base_tax_amount' => 3.25,
                    'title'           => 'Shipping & Handling Tax',
                    'percent'         => NULL,
                )
            )
        );
    }
}
