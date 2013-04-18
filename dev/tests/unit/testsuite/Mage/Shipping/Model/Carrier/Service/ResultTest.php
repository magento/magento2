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
 * @package     Mage_Webhook
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Shipping_Model_Carrier_Service_ResultTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Shipping_Model_Carrier_Service_Result */
    protected $_serviceResult;

    /** @var Mage_Shipping_Model_Rate_Result */
    protected $_rateResult;

    public function setUp()
    {
        parent::setUp();

        $this->_rateResult = $this->getMockBuilder('Mage_Shipping_Model_Rate_Result')
            ->setMethods(array('append'))
            ->getMock();

        $this->_serviceResult = $this->getMockBuilder('Mage_Shipping_Model_Carrier_Service_Result')
            ->disableOriginalConstructor()
            ->setMethods(array('_getRateResult', '_getCarrierServiceMethod'))
            ->getMock();

        $this->_serviceResult->expects($this->once())
            ->method('_getRateResult')
            ->will($this->returnValue($this->_rateResult));
    }

    public function testCreateRateResultNull()
    {
        $rateResult = $this->_serviceResult->createRateResult(null);

        $this->assertSame($this->_rateResult, $rateResult);
    }

    public function testCreateRateResultEmptyArray()
    {
        $rateResult = $this->_serviceResult->createRateResult(array());

        $this->assertSame($this->_rateResult, $rateResult);
    }

    public function testCreateRateResultEmptyShippingMethods()
    {
        $rateResult = $this->_serviceResult->createRateResult(array(
            'shippingMethods' => array()
        ));

        $this->assertSame($this->_rateResult, $rateResult);
    }

    public function testCreateRateResultOneShippingMethod()
    {
        $shippingMethod1 = array('random' => 'value');
        $this->_setupCarrierServiceMethod(0, $shippingMethod1);

        $rateResult = $this->_serviceResult->createRateResult(array(
            'shippingMethods' => array(
                $shippingMethod1
            )
        ));

        $this->assertSame($this->_rateResult, $rateResult);
    }

    public function testCreateRateResultMultipleShippingMethod()
    {
        $shippingMethod1 = array('random' => 'value');
        $shippingMethod2 = array('hello' => 'world');

        $this->_setupCarrierServiceMethod(0, $shippingMethod1);
        $this->_setupCarrierServiceMethod(1, $shippingMethod2);

        $rateResult = $this->_serviceResult->createRateResult(array(
            'shippingMethods' => array(
                $shippingMethod1,
                $shippingMethod2,
            )
        ));

        $this->assertSame($this->_rateResult, $rateResult);
    }

    protected function _setupCarrierServiceMethod($atIndex, $args)
    {
        $serviceMethod = $this->getMockBuilder('Mage_Shipping_Model_Carrier_Service_Method')
            ->disableOriginalConstructor()
            ->setMethods(array('createRateResultMethod'))
            ->getMock();

        /* need to add + 1 here because of the _getRateResult call...? PHPUnit bug? */
        $this->_serviceResult->expects($this->at($atIndex + 1))
            ->method('_getCarrierServiceMethod')
            ->with($args)
            ->will($this->returnValue($serviceMethod));

        $rateResult = $this->getMockBuilder('Mage_Shipping_Model_Rate_Result')
            ->disableOriginalConstructor()
            ->getMock();

        $serviceMethod->expects($this->once())
            ->method('createRateResultMethod')
            ->will($this->returnValue($rateResult));

        $this->_rateResult->expects($this->at($atIndex))
            ->method('append')
            ->with($rateResult);
    }
}
