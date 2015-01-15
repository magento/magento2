<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Usps\Model;

class CarrierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Usps\Model\Carrier
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_httpResponse;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_helper;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setUp()
    {
        $this->_helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $scopeConfig = $this->getMockBuilder(
            '\Magento\Framework\App\Config\ScopeConfigInterface'
        )->setMethods(
            ['isSetFlag', 'getValue']
        )->disableOriginalConstructor()->getMock();
        $scopeConfig->expects($this->any())->method('isSetFlag')->will($this->returnValue(true));
        $scopeConfig->expects(
            $this->any()
        )->method(
            'getValue'
        )->will(
            $this->returnCallback([$this, 'scopeConfiggetValue'])
        );

        // xml element factory
        $xmlElFactory = $this->getMockBuilder(
            '\Magento\Shipping\Model\Simplexml\ElementFactory'
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();
        $xmlElFactory->expects($this->any())->method('create')->will(
            $this->returnCallback(
                function ($data) {
                    $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
                    return $helper->getObject(
                        '\Magento\Shipping\Model\Simplexml\Element',
                        ['data' => $data['data']]
                    );
                }
            )
        );

        // rate factory
        $rateFactory = $this->getMockBuilder(
            '\Magento\Shipping\Model\Rate\ResultFactory'
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();
        $rateResult = $this->getMockBuilder(
            '\Magento\Shipping\Model\Rate\Result'
        )->disableOriginalConstructor()->setMethods(
            null
        )->getMock();
        $rateFactory->expects($this->any())->method('create')->will($this->returnValue($rateResult));

        // rate method factory
        $rateMethodFactory = $this->getMockBuilder(
            '\Magento\Sales\Model\Quote\Address\RateResult\MethodFactory'
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();
        $rateMethod = $this->getMockBuilder(
            'Magento\Sales\Model\Quote\Address\RateResult\Method'
        )->disableOriginalConstructor()->setMethods(
            ['setPrice']
        )->getMock();
        $rateMethod->expects($this->any())->method('setPrice')->will($this->returnSelf());

        $rateMethodFactory->expects($this->any())->method('create')->will($this->returnValue($rateMethod));

        // http client
        $this->_httpResponse = $this->getMockBuilder(
            '\Zend_Http_Response'
        )->disableOriginalConstructor()->setMethods(
            ['getBody']
        )->getMock();

        $httpClient = $this->getMockBuilder(
            '\Magento\Framework\HTTP\ZendClient'
        )->disableOriginalConstructor()->setMethods(
            ['request']
        )->getMock();
        $httpClient->expects($this->any())->method('request')->will($this->returnValue($this->_httpResponse));

        $httpClientFactory = $this->getMockBuilder(
            '\Magento\Framework\HTTP\ZendClientFactory'
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();
        $httpClientFactory->expects($this->any())->method('create')->will($this->returnValue($httpClient));

        $data = ['id' => 'usps', 'store' => '1'];

        $arguments = [
            'scopeConfig' => $scopeConfig,
            'xmlElFactory' => $xmlElFactory,
            'rateFactory' => $rateFactory,
            'rateMethodFactory' => $rateMethodFactory,
            'httpClientFactory' => $httpClientFactory,
            'data' => $data,
        ];

        $this->_model = $this->_helper->getObject('Magento\Usps\Model\Carrier', $arguments);
    }

    /**
     * @dataProvider codeDataProvider
     */
    public function testGetCodeArray($code)
    {
        $this->assertNotEmpty($this->_model->getCode($code));
    }

    public function testGetCodeBool()
    {
        $this->assertFalse($this->_model->getCode('test_code'));
    }

    public function testCollectRates()
    {
        $this->_httpResponse->expects(
            $this->any()
        )->method(
            'getBody'
        )->will(
            $this->returnValue(file_get_contents(__DIR__ . '/_files/success_usps_response_rates.xml'))
        );
        // for setRequest
        $request = $this->_helper->getObject(
            'Magento\Sales\Model\Quote\Address\RateRequest',
            require __DIR__ . '/_files/rates_request_data.php'
        );

        $this->assertNotEmpty($this->_model->collectRates($request)->getAllRates());
    }

    public function testReturnOfShipment()
    {
        $this->_httpResponse->expects(
            $this->any()
        )->method(
            'getBody'
        )->will(
            $this->returnValue(file_get_contents(__DIR__ . '/_files/success_usps_response_return_shipment.xml'))
        );
        $request = $this->_helper->getObject(
            'Magento\Shipping\Model\Shipment\ReturnShipment',
            require __DIR__ . '/_files/return_shipment_request_data.php'
        );
        $this->assertNotEmpty($this->_model->returnOfShipment($request)->getInfo()[0]['tracking_number']);
    }

    /**
     * Callback function, emulates getValue function
     * @param $path
     * @return null|string
     */
    public function scopeConfiggetValue($path)
    {
        switch ($path) {
            case 'carriers/usps/allowed_methods':
                return '0_FCLE,0_FCL,0_FCP,1,2,3,4,6,7,13,16,17,22,23,25,27,28,33,34,35,36,37,42,43,53,' .
                    '55,56,57,61,INT_1,INT_2,INT_4,INT_6,INT_7,INT_8,INT_9,INT_10,INT_11,INT_12,INT_13,INT_14,' .
                    'INT_15,INT_16,INT_20,INT_26';
            default:
                return null;
        }
    }

    /**
     * @return array
     */
    public function codeDataProvider()
    {
        return [['container'], ['machinable'], ['method'], ['size']];
    }
}
