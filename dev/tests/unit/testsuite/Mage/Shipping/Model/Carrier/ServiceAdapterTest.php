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

class Mage_Shipping_Model_Carrier_ServiceAdapterTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Shipping_Model_Carrier_ServiceAdapter */
    protected $_serviceAdapter;

    /** @var Mage_Shipping_Model_Rate_Request */
    protected $_request;

    /** @var Mage_Shipping_Model_Carrier_Metadata */
    protected $_carrierMetadata;

    /** @var Magento_ObjectManager */
    protected $_serviceFactory;

    /** @var Mage_Shipping_Model_Carrier_Service_Interface */
    protected $_testService;

    /** @var Mage_Shipping_Model_Rate_Result */
    protected $_rateResult;

    /** @var Mage_Shipping_Model_Carrier_Service_Result */
    protected $_serviceResult;

    protected $_carrierCode = 'the_code';

    const EXTENSION_ID = 'extension_id_goes_here';

    protected $_carrierConfig = array('config' => 'true', 'subscriber' => self::EXTENSION_ID);

    protected $_groundShipping = array(
        'carrier' => 'ups',
        'carrier_title' => 'United Parcel Service',
        'method' => 'ground',
        'method_title' => 'UPS Ground',
        'price' => 3.25,
    );

    protected $_airShipping = array(
        'carrier' => 'ups',
        'carrier_title' => 'United Parcel Service',
        'method' => '2nd day air',
        'method_title' => 'UPS 2nd Day Air',
        'price' => 13.25,
        'cost' => 10.25
    );

    public function setUp()
    {
        parent::setUp();

        $this->_serviceFactory = $this->getMockBuilder('Magento_ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_testService = $this->getMock('Mage_Shipping_Model_Carrier_Service_Interface', array('getRates'));

        $this->_serviceFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_testService));

        $this->_request = $this->getMockBuilder('Mage_Shipping_Model_Rate_Request')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_rateResult = $this->getMockBuilder('Mage_Shipping_Model_Rate_Result')
            ->disableOriginalConstructor()
            ->setMethods(array('setError'))
            ->getMock();

        $this->_serviceResult = $this->getMockBuilder('Mage_Shipping_Model_Carrier_Service_Result')
            ->disableOriginalConstructor()
            ->setMethods(array('createRateResult', "createErrorRateResult"))
            ->getMock();

        $this->_carrierMetadata = new Mage_Shipping_Model_Carrier_Metadata($this->_carrierCode, $this->_carrierConfig);

        $logger = $this->getMockBuilder('Mage_Core_Model_Logger')
                       ->disableOriginalConstructor()
                       ->setMethods(array('logException'))
                       ->getMock();

        $this->_serviceAdapter = $this->getMockBuilder('Mage_Shipping_Model_Carrier_ServiceAdapter')
            ->setConstructorArgs(array($logger, "testService", $this->_serviceFactory, $this->_serviceResult))
            ->setMethods(array('isActive', '_getCarrierMetadata',
                '_constructInput', '_getRateResult', '_getServiceResult' ))
            ->getMock();
        $this->_serviceAdapter->expects($this->any())
            ->method('isActive')
            ->will($this->returnValue(true));
        $this->_serviceAdapter->expects($this->any())
        ->method('_constructInput')
        ->will($this->returnValue($this->_mockServiceData()));
        $this->_serviceAdapter->expects($this->any())
            ->method('_getCarrierMetadata')
            ->will($this->returnValue($this->_carrierMetadata));
    }

    public function testCollectRatesWrapsMethods()
    {
        $methods = array($this->_groundShipping, $this->_airShipping);

        $rateResult = $this->_collectRates($methods);

        $this->assertSame($this->_rateResult, $rateResult);
    }

    public function testCollectRatesReturnsEmptyWhenEmptyResponse()
    {
        $methods = array();

        $rateResult = $this->_collectRates($methods);

        $this->assertSame($this->_rateResult, $rateResult);
    }

    public function testCollectRatesReturnsMethodWhenPartialResponse()
    {
        $methods = array(array('carrier' => 'partial'));

        $rateResult = $this->_collectRates($methods);

        $this->assertSame($this->_rateResult, $rateResult);
    }

    public function testCollectRatesReturnsEmptyWhenExceptionThrown()
    {
        $this->_expectExceptionThrown();
        $methods = array($this->_groundShipping, $this->_airShipping);

        $this->getMockBuilder('Mage_Core_Model_Logger')
                       ->disableOriginalConstructor()
                       ->setMethods(array('logException'))
                       ->getMock();
        Mage::getObjectManager(); //->addSharedInstance($logger, 'Mage_Core_Model_Logger');

        $this->_collectRates($methods, false);
    }

    public function testCollectRatesGeneratesProperServiceInput()
    {
        $this->_expectProperServiceInputs();
        $this->_createRateResultReturns(null);

        $this->_collectRates();
    }

    protected function _expectProperServiceInputs()
    {
        $this->_getEventName();
        $data = $this->_mockServiceData();
        $this->_testService->expects($this->once())
            ->method('getRates')
            ->with($this->equalTo($data));
    }

    protected function _expectExceptionThrown()
    {
        /* this is required to stop Mage::logException from complaining */
        Mage::getObjectManager();

        $this->_testService->expects($this->once())
            ->method('getRates')
            ->will($this->throwException(new Exception('Test Exception')));

        $this->_serviceAdapter->expects($this->once())
            ->method('_getServiceResult')
            ->will($this->returnValue($this->_serviceResult));

        $this->_serviceResult->expects($this->once())
            ->method('createErrorRateResult');
    }

    protected function _getEventName()
    {
        return 'shipping/get_rates';
    }

    protected function _mockServiceData()
    {
        $serviceData = array();
        $serviceData['carrier_configuration'] = $this->_carrierConfig;
        $serviceData['rate_request'] = $this->_request;

        return $serviceData;
    }

    protected function _serviceReturns($methods)
    {
        $output = $this->_packageOutput($methods);
        $this->_testService->expects($this->once())
            ->method('getRates')
            ->will($this->returnValue($output));
        return $output;
    }

    protected function _createRateResultReturns($serviceOutput)
    {
        $this->_serviceAdapter->expects($this->once())
            ->method('_getServiceResult')
            ->will($this->returnValue($this->_serviceResult));
        $this->_serviceResult->expects($this->once())
            ->method('createRateResult')
            ->with($serviceOutput)
            ->will($this->returnValue($this->_rateResult));
    }

    protected function _collectRates($methods = null, $expectRateResult = true)
    {
        if (!is_null($methods)) {
            $serviceOutput = $this->_serviceReturns($methods);
            if ($expectRateResult) {
                $this->_createRateResultReturns($serviceOutput);
            }
        }
        return $this->_serviceAdapter->collectRates($this->_request);
    }

    protected function _packageOutput($methods)
    {
        $shippingMethods = array();
        foreach ($methods as $i => $method) {
            $shippingMethods['method_'.$i] = $method;
        }
        $output = array(
            'shipping_methods' => $shippingMethods
        );
        return $output;
    }

}
