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
 * @package     Magento_Sales
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\Sales\Block\Adminhtml\Order\Totals\TaxTest
 */
namespace Magento\Sales\Block\Adminhtml\Order\Totals;

class TaxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Block\Adminhtml\Order\Totals\Tax
     */
    protected $_block;

    /**
     * @var \Magento\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * Instantiate \Magento\Sales\Block\Adminhtml\Order\Totals\Tax block
     */
    protected function setUp()
    {
        $this->_block = $this->getMockBuilder(
            'Magento\Sales\Block\Adminhtml\Order\Totals\Tax'
        )->setConstructorArgs(
            $this->_getModelArgument()
        )->setMethods(
            array('getOrder')
        )->getMock();
    }

    /**
     * Module arguments for \Magento\Sales\Block\Adminhtml\Order\Totals\Tax
     *
     * @return array
     */
    protected function _getModelArgument()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $attributeFactory = $this->getMock(
            'Magento\Eav\Model\Entity\AttributeFactory',
            array('create'),
            array(),
            '',
            false
        );
        $taxItemFactory = $this->getMock(
            'Magento\Tax\Model\Resource\Sales\Order\Tax\ItemFactory',
            array('create'),
            array(),
            '',
            false
        );
        $taxHelperMock = $objectManagerHelper->getObject(
            'Magento\Tax\Helper\Data',
            array('attributeFactory' => $attributeFactory, 'taxItemFactory' => $taxItemFactory)
        );

        $taxOrderFactory = $this->getMock(
            'Magento\Tax\Model\Sales\Order\TaxFactory',
            array('create'),
            array(),
            '',
            false
        );

        return $objectManagerHelper->getConstructArguments(
            'Magento\Sales\Block\Adminhtml\Order\Totals\Tax',
            array('taxHelper' => $taxHelperMock, 'taxOrderFactory' => $taxOrderFactory)
        );
    }

    /**
     * @return \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getSalesOrderMock()
    {
        $orderMock = $this->getMockBuilder(
            'Magento\Sales\Model\Order'
        )->setMethods(
            array('getItemsCollection', '__wakeup')
        )->disableOriginalConstructor()->getMock();
        $orderMock->expects($this->any())->method('getItemsCollection')->will($this->returnValue(array()));
        return $orderMock;
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
        $this->_block->expects($this->any())->method('getOrder')->will($this->returnValue($orderMock));
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
                'actual' => array(
                    'calculated_taxes' => array(),
                    'shipping_tax' => array(),
                    'shipping_tax_amount' => 1.25,
                    'base_shipping_tax_amount' => 3.25,
                    'tax_amount' => 0.16,
                    'base_tax_amount' => 2
                ),
                'expected' => array(
                    'tax_amount' => 1.25,
                    'base_tax_amount' => 3.25,
                    'title' => 'Shipping & Handling Tax',
                    'percent' => null
                )
            )
        );
    }
}
