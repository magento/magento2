<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Dhl\Test\Unit\Model;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Framework\Xml\Security;

class CarrierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_httpResponse;

    /**
     * @var \Magento\Dhl\Model\Carrier
     */
    protected $_model;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\Error|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $error;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $errorFactory;

    /**
     * @var \Magento\Dhl\Model\Carrier|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $carrier;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scope;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setUp()
    {
        $this->_helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->scope = $this->getMockBuilder(
            '\Magento\Framework\App\Config\ScopeConfigInterface'
        )->disableOriginalConstructor()->getMock();

        $this->scope->expects(
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
                    $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

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
            '\Magento\Quote\Model\Quote\Address\RateResult\MethodFactory'
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();
        $rateMethod = $this->getMockBuilder(
            'Magento\Quote\Model\Quote\Address\RateResult\Method'
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
        $modulesDirectory = $this->getMockBuilder(
            '\Magento\Framework\Filesystem\Directory\Read'
        )->disableOriginalConstructor()->setMethods(
            ['getRelativePath', 'readFile']
        )->getMock();
        $modulesDirectory->expects(
            $this->any()
        )->method(
            'readFile'
        )->will(
            $this->returnValue(file_get_contents(__DIR__ . '/_files/countries.xml'))
        );
        $readFactory = $this->getMock('Magento\Framework\Filesystem\Directory\ReadFactory', [], [], '', false);
        $readFactory->expects($this->any())->method('create')->willReturn($modulesDirectory);
        $storeManager = $this->getMockBuilder(
            '\Magento\Store\Model\StoreManager'
        )->disableOriginalConstructor()->setMethods(
            ['getWebsite']
        )->getMock();
        $website = $this->getMockBuilder(
            '\Magento\Store\Model\Website'
        )->disableOriginalConstructor()->setMethods(
            ['getBaseCurrencyCode', '__wakeup']
        )->getMock();
        $website->expects($this->any())->method('getBaseCurrencyCode')->will($this->returnValue('USD'));
        $storeManager->expects($this->any())->method('getWebsite')->will($this->returnValue($website));

        $this->error = $this->getMockBuilder('\Magento\Quote\Model\Quote\Address\RateResult\Error')
            ->setMethods(['setCarrier', 'setCarrierTitle', 'setErrorMessage'])
            ->getMock();

        $this->errorFactory = $this->getMockBuilder('Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->errorFactory->expects($this->any())->method('create')->willReturn($this->error);

        $this->_model = $this->_helper->getObject(
            'Magento\Dhl\Model\Carrier',
            [
                'scopeConfig' => $this->scope,
                'xmlSecurity' => new Security(),
                'xmlElFactory' => $xmlElFactory,
                'rateFactory' => $rateFactory,
                'rateErrorFactory' => $this->errorFactory,
                'rateMethodFactory' => $rateMethodFactory,
                'httpClientFactory' => $httpClientFactory,
                'readFactory' => $readFactory,
                'storeManager' => $storeManager,
                'data' => ['id' => 'dhl', 'store' => '1']
            ]
        );
    }

    /**
     * Callback function, emulates getValue function
     * @param $path
     * @return null|string
     */
    public function scopeConfiggetValue($path)
    {
        $pathMap = [
            'carriers/dhl/shipment_days' => 'Mon,Tue,Wed,Thu,Fri,Sat',
            'carriers/dhl/intl_shipment_days' => 'Mon,Tue,Wed,Thu,Fri,Sat',
            'carriers/dhl/allowed_methods' => 'IE',
            'carriers/dhl/international_searvice' => 'IE',
            'carriers/dhl/gateway_url' => 'https://xmlpi-ea.dhl.com/XMLShippingServlet',
            'carriers/dhl/id' => 'some ID',
            'carriers/dhl/password' => 'some password',
            'carriers/dhl/content_type' => 'N',
            'carriers/dhl/nondoc_methods' => '1,3,4,8,P,Q,E,F,H,J,M,V,Y',
            'carriers/dhl/showmethod' => 1,
            'carriers/dhl/title' => 'dhl Title',
            'carriers/dhl/specificerrmsg' => 'dhl error message',
        ];
        return isset($pathMap[$path]) ? $pathMap[$path] : null;
    }

    public function testPrepareShippingLabelContent()
    {
        $xml = simplexml_load_file(
            __DIR__ . '/_files/response_shipping_label.xml'
        );
        $result = $this->_invokePrepareShippingLabelContent($xml);
        $this->assertEquals(1111, $result->getTrackingNumber());
        $this->assertEquals(base64_decode('OutputImageContent'), $result->getShippingLabelContent());
    }

    /**
     * @dataProvider prepareShippingLabelContentExceptionDataProvider
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Unable to retrieve shipping label
     */
    public function testPrepareShippingLabelContentException(\SimpleXMLElement $xml)
    {
        $this->_invokePrepareShippingLabelContent($xml);
    }

    /**
     * @return array
     */
    public function prepareShippingLabelContentExceptionDataProvider()
    {
        $filesPath = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR;
        $empty = $billingNumberOnly = $outputImageOnly = simplexml_load_file(
            $filesPath . 'response_shipping_label.xml'
        );
        unset(
            $empty->{'AirwayBillNumber'},
            $empty->{'LabelImage'},
            $billingNumberOnly->{'LabelImage'},
            $outputImageOnly->{'AirwayBillNumber'}
        );

        return [[$empty], [$billingNumberOnly], [$outputImageOnly]];
    }

    /**
     * @param \SimpleXMLElement $xml
     * @return \Magento\Framework\DataObject
     */
    protected function _invokePrepareShippingLabelContent(\SimpleXMLElement $xml)
    {
        $model = $this->_helper->getObject('Magento\Dhl\Model\Carrier');
        $method = new \ReflectionMethod($model, '_prepareShippingLabelContent');
        $method->setAccessible(true);
        return $method->invoke($model, $xml);
    }

    public function testCollectRates()
    {
        $this->scope->expects($this->any())->method('isSetFlag')->willReturn(true);

        $this->_httpResponse->expects(
            $this->any()
        )->method(
            'getBody'
        )->will(
            $this->returnValue(file_get_contents(__DIR__ . '/_files/success_dhl_response_rates.xml'))
        );
        // for setRequest
        $request = $this->_helper->getObject(
            'Magento\Quote\Model\Quote\Address\RateRequest',
            require __DIR__ . '/_files/rates_request_data_dhl.php'
        );
        $this->assertNotEmpty($this->_model->collectRates($request)->getAllRates());
    }

    public function testCollectRatesErrorMessage()
    {
        $this->scope->expects($this->once())->method('isSetFlag')->willReturn(false);

        $this->error->expects($this->once())->method('setCarrier')->with('dhl');
        $this->error->expects($this->once())->method('setCarrierTitle');
        $this->error->expects($this->once())->method('setErrorMessage');

        $request = new RateRequest();
        $request->setPackageWeight(1);

        $this->assertSame($this->error, $this->_model->collectRates($request));
    }

    public function testCollectRatesFail()
    {
        $this->scope->expects($this->once())->method('isSetFlag')->willReturn(true);

        $request = new RateRequest();
        $request->setPackageWeight(1);

        $this->assertFalse(false, $this->_model->collectRates($request));
    }
}
