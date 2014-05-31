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

/**
 * Test class for \Magento\Sales\Block\Adminhtml\Order\Totals\TaxTest
 */
namespace Magento\Sales\Block\Adminhtml\Order\Totals;

class TaxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test method for getFullTaxInfo
     *
     * @param \Magento\Sales\Model\Order $source
     * @param array $getCalculatedTax
     * @param array $getShippingTax
     * @param array $expectedResult
     *
     * @dataProvider getFullTaxInfoDataProvider
     */
    public function testGetFullTaxInfo($source, $getCalculatedTax, $getShippingTax, $expectedResult)
    {
        $taxHelperMock = $this->getMockBuilder('Magento\Tax\Helper\Data')
            ->setMethods(array('getCalculatedTaxes', 'getShippingTax'))
            ->disableOriginalConstructor()
            ->getMock();
        $taxHelperMock->expects($this->any())
            ->method('getCalculatedTaxes')
            ->will($this->returnValue($getCalculatedTax));
        $taxHelperMock->expects($this->any())
            ->method('getShippingTax')
            ->will($this->returnValue($getShippingTax));

        $mockObject = $this->getMockBuilder('Magento\Sales\Block\Adminhtml\Order\Totals\Tax')
            ->setConstructorArgs($this->_getConstructArguments($taxHelperMock))
            ->setMethods(array('getOrder'))
            ->getMock();
        $mockObject->expects($this->once())
            ->method('getOrder')
            ->will($this->returnValue($source));

        $actualResult = $mockObject->getFullTaxInfo();
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Provide the tax helper mock as a constructor argument
     *
     * @param $taxHelperMock
     * @return array
     */
    protected function _getConstructArguments($taxHelperMock)
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        return $objectManagerHelper->getConstructArguments(
            'Magento\Sales\Block\Adminhtml\Order\Totals\Tax',
            array('taxHelper' => $taxHelperMock)
        );
    }

    /**
     * Data provider.
     * 1st Case : $source is not an instance of \Magento\Sales\Model\Order
     * 2nd Case : getCalculatedTaxes and getShippingTax return value
     *
     * @return array
     */
    public function getFullTaxInfoDataProvider()
    {
        $notAnInstanceOfASalesModelOrder = $this->getMock('stdClass');

        $salesModelOrderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock();

        $getCalculatedTax = array(
            'tax' => 'tax',
            'shipping_tax' => 'shipping_tax'
        );
        $getShippingTax = array(
            'shipping_tax' => 'shipping_tax',
            'shipping_and_handing' => 'shipping_and_handing'
        );

        return array(
            'source is not an instance of \Magento\Sales\Model\Order' =>
                array($notAnInstanceOfASalesModelOrder, $getCalculatedTax, $getShippingTax, array()),
            'source is an instance of \Magento\Sales\Model\Order and has reasonable data' =>
                array($salesModelOrderMock, $getCalculatedTax, $getShippingTax, array('tax' => 'tax',
                'shipping_tax' => 'shipping_tax', 'shipping_and_handing' => 'shipping_and_handing'))
        );
    }
}
