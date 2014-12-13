<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Payment\Model\Config\Source;

class AllmethodsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Payment data
     *
     * @var \Magento\Payment\Helper\Data | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_paymentData;

    /**
     * @var Allmethods
     */
    protected $_model;

    public function setUp()
    {
        $this->_paymentData = $this->getMockBuilder(
            'Magento\Payment\Helper\Data'
        )->disableOriginalConstructor()->setMethods([])->getMock();

        $this->_model = new Allmethods($this->_paymentData);
    }

    public function testToOptionArray()
    {
        $expectedArray = ['key' => 'value'];
        $this->_paymentData->expects($this->once())->method('getPaymentMethodList')->with(
            true, true, true
        )->will($this->returnValue($expectedArray));
        $this->assertEquals($expectedArray, $this->_model->toOptionArray());
    }
}
