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
namespace Magento\Dhl\Model;

class CarrierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
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
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setUp()
    {
        $this->_helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $scopeConfig = $this->getMockBuilder(
            '\Magento\Framework\App\Config\ScopeConfigInterface'
        )->setMethods(
            array('isSetFlag', 'getValue')
        )->disableOriginalConstructor()->getMock();
        $scopeConfig->expects($this->any())->method('isSetFlag')->will($this->returnValue(true));
        $scopeConfig->expects(
            $this->any()
        )->method(
            'getValue'
        )->will(
            $this->returnCallback(array($this, 'scopeConfiggetValue'))
        );

        // xml element factory
        $xmlElFactory = $this->getMockBuilder(
            '\Magento\Shipping\Model\Simplexml\ElementFactory'
        )->disableOriginalConstructor()->setMethods(
            array('create')
        )->getMock();
        $xmlElFactory->expects($this->any())->method('create')->will(
            $this->returnCallback(
                function ($data) {
                    $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
                    return $helper->getObject(
                        '\Magento\Shipping\Model\Simplexml\Element',
                        array('data' => $data['data'])
                    );
                }
            )
        );

        // rate factory
        $rateFactory = $this->getMockBuilder(
            '\Magento\Shipping\Model\Rate\ResultFactory'
        )->disableOriginalConstructor()->setMethods(
            array('create')
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
            array('create')
        )->getMock();
        $rateMethod = $this->getMockBuilder(
            'Magento\Sales\Model\Quote\Address\RateResult\Method'
        )->disableOriginalConstructor()->setMethods(
            array('setPrice')
        )->getMock();
        $rateMethod->expects($this->any())->method('setPrice')->will($this->returnSelf());

        $rateMethodFactory->expects($this->any())->method('create')->will($this->returnValue($rateMethod));

        // http client
        $this->_httpResponse = $this->getMockBuilder(
            '\Zend_Http_Response'
        )->disableOriginalConstructor()->setMethods(
            array('getBody')
        )->getMock();

        $httpClient = $this->getMockBuilder(
            '\Magento\Framework\HTTP\ZendClient'
        )->disableOriginalConstructor()->setMethods(
            array('request')
        )->getMock();
        $httpClient->expects($this->any())->method('request')->will($this->returnValue($this->_httpResponse));

        $httpClientFactory = $this->getMockBuilder(
            '\Magento\Framework\HTTP\ZendClientFactory'
        )->disableOriginalConstructor()->setMethods(
            array('create')
        )->getMock();

        $httpClientFactory->expects($this->any())->method('create')->will($this->returnValue($httpClient));
        $modulesDirectory = $this->getMockBuilder(
            '\Magento\Framework\Filesystem\Directory\Read'
        )->disableOriginalConstructor()->setMethods(
            array('getRelativePath', 'readFile')
        )->getMock();
        $modulesDirectory->expects(
            $this->any()
        )->method(
            'readFile'
        )->will(
            $this->returnValue(file_get_contents(__DIR__ . '/_files/countries.xml'))
        );
        $filesystem = $this->getMockBuilder(
            '\Magento\Framework\App\Filesystem'
        )->disableOriginalConstructor()->setMethods(
            array('getDirectoryRead')
        )->getMock();
        $filesystem->expects($this->any())->method('getDirectoryRead')->will($this->returnValue($modulesDirectory));
        $storeManager = $this->getMockBuilder(
            '\Magento\Store\Model\StoreManager'
        )->disableOriginalConstructor()->setMethods(
            array('getWebsite')
        )->getMock();
        $website = $this->getMockBuilder(
            '\Magento\Store\Model\Website'
        )->disableOriginalConstructor()->setMethods(
            array('getBaseCurrencyCode', '__wakeup')
        )->getMock();
        $website->expects($this->any())->method('getBaseCurrencyCode')->will($this->returnValue('USD'));
        $storeManager->expects($this->any())->method('getWebsite')->will($this->returnValue($website));

        $this->_model = $this->_helper->getObject(
            'Magento\Dhl\Model\Carrier',
            array(
                'scopeConfig' => $scopeConfig,
                'xmlElFactory' => $xmlElFactory,
                'rateFactory' => $rateFactory,
                'rateMethodFactory' => $rateMethodFactory,
                'httpClientFactory' => $httpClientFactory,
                'filesystem' => $filesystem,
                'storeManager' => $storeManager,
                'data' => array('id' => 'dhl', 'store' => '1')
            )
        );
    }

    /**
     * Callback function, emulates getValue function
     * @param $path
     * @return null|string
     */
    public function scopeConfiggetValue($path)
    {
        $pathMap = array(
            'carriers/dhl/shipment_days' => 'Mon,Tue,Wed,Thu,Fri,Sat',
            'carriers/dhl/intl_shipment_days' => 'Mon,Tue,Wed,Thu,Fri,Sat',
            'carriers/dhl/allowed_methods' => 'IE',
            'carriers/dhl/international_searvice' => 'IE',
            'carriers/dhl/gateway_url' => 'https://xmlpi-ea.dhl.com/XMLShippingServlet',
            'carriers/dhl/id' => 'some ID',
            'carriers/dhl/password' => 'some password',
            'carriers/dhl/content_type' => 'N',
            'carriers/dhl/nondoc_methods' => '1,3,4,8,P,Q,E,F,H,J,M,V,Y'
        );
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
     * @expectedException \Magento\Framework\Model\Exception
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

        return array(array($empty), array($billingNumberOnly), array($outputImageOnly));
    }

    /**
     * @param \SimpleXMLElement $xml
     * @return \Magento\Framework\Object
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
        $this->_httpResponse->expects(
            $this->any()
        )->method(
            'getBody'
        )->will(
            $this->returnValue(file_get_contents(__DIR__ . '/_files/success_dhl_response_rates.xml'))
        );
        // for setRequest
        $request = $this->_helper->getObject(
            'Magento\Sales\Model\Quote\Address\RateRequest',
            require __DIR__ . '/_files/rates_request_data_dhl.php'
        );
        $this->assertNotEmpty($this->_model->collectRates($request)->getAllRates());
    }
}
