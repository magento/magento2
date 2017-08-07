<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Dhl\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Xml\Security;
use Magento\Quote\Model\Quote\Address\RateRequest;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Dhl\Model\Validator\XmlValidator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CarrierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \Zend_Http_Response|MockObject
     */
    private $httpResponse;

    /**
     * @var \Magento\Dhl\Model\Carrier
     */
    private $model;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\Error|MockObject
     */
    private $error;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory|MockObject
     */
    private $errorFactory;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scope;

    /**
     * @var ZendClient|MockObject
     */
    private $httpClient;

    /**
     * @var XmlValidator|MockObject
     */
    private $xmlValidator;

    /**
     * @var \Magento\Shipping\Model\Shipment\Request|MockObject
     */
    private $request;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->request = $this->getMockBuilder(\Magento\Shipping\Model\Shipment\Request::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getPackages',
                    'getOrigCountryId',
                    'setPackages',
                    'setPackageWeight',
                    'setPackageValue',
                    'setValueWithDiscount',
                    'setPackageCustomsValue',
                    'setFreeMethodWeight',
                    'getPackageWeight',
                    'getFreeMethodWeight',
                    'getOrderShipment',
                ]
            )
            ->getMock();

        $this->scope = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->scope->method('getValue')
            ->willReturnCallback([$this, 'scopeConfigGetValue']);

        // xml element factory
        $xmlElFactory = $this->getMockBuilder(
            \Magento\Shipping\Model\Simplexml\ElementFactory::class
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();
        $xmlElFactory->expects($this->any())->method('create')->will(
            $this->returnCallback(
                function ($data) {
                    $helper = new ObjectManager($this);

                    return $helper->getObject(
                        \Magento\Shipping\Model\Simplexml\Element::class,
                        ['data' => $data['data']]
                    );
                }
            )
        );

        // rate factory
        $rateFactory = $this->getMockBuilder(
            \Magento\Shipping\Model\Rate\ResultFactory::class
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();
        $rateResult = $this->getMockBuilder(
            \Magento\Shipping\Model\Rate\Result::class
        )->disableOriginalConstructor()->setMethods(
            null
        )->getMock();
        $rateFactory->expects($this->any())->method('create')->will($this->returnValue($rateResult));

        // rate method factory
        $rateMethodFactory = $this->getMockBuilder(
            \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory::class
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();
        $rateMethod = $this->getMockBuilder(
            \Magento\Quote\Model\Quote\Address\RateResult\Method::class
        )->disableOriginalConstructor()->setMethods(
            ['setPrice']
        )->getMock();
        $rateMethod->expects($this->any())->method('setPrice')->will($this->returnSelf());
        $rateMethodFactory->expects($this->any())->method('create')->will($this->returnValue($rateMethod));

        // Http mocks
        $this->httpResponse = $this->getMockBuilder(\Zend_Http_Response::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->httpClient = $this->getMockBuilder(ZendClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['request'])
            ->getMock();
        $this->httpClient->method('request')
            ->willReturn($this->httpResponse);
        $httpClientFactory = $this->getMockBuilder(ZendClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $httpClientFactory->method('create')
            ->willReturn($this->httpClient);

        // Config reader mock
        $configReader = $this->getMockBuilder(\Magento\Framework\Module\Dir\Reader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configReader->method('getModuleDir')->willReturn('/etc/path');

        // XML Reader mock to retrieve list of acceptable countries
        $modulesDirectory = $this->getMockBuilder(
            \Magento\Framework\Filesystem\Directory\Read::class
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
        $readFactory = $this->createMock(\Magento\Framework\Filesystem\Directory\ReadFactory::class);
        $readFactory->expects($this->any())->method('create')->willReturn($modulesDirectory);

        // Website Mock
        $storeManager = $this->getMockBuilder(
            \Magento\Store\Model\StoreManager::class
        )->disableOriginalConstructor()->setMethods(
            ['getWebsite']
        )->getMock();
        $website = $this->getMockBuilder(
            \Magento\Store\Model\Website::class
        )->disableOriginalConstructor()->setMethods(
            ['getBaseCurrencyCode', '__wakeup']
        )->getMock();
        $website->expects($this->any())->method('getBaseCurrencyCode')->will($this->returnValue('USD'));
        $storeManager->expects($this->any())->method('getWebsite')->will($this->returnValue($website));

        // Error Mock
        $this->error = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address\RateResult\Error::class)
            ->setMethods(['setCarrier', 'setCarrierTitle', 'setErrorMessage'])
            ->getMock();
        $this->errorFactory = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->errorFactory->expects($this->any())->method('create')->willReturn($this->error);

        // Locale Mock
        $localeResolver = $this->getMockForAbstractClass(\Magento\Framework\Locale\ResolverInterface::class);
        $localeResolver->method('getLocale')->willReturn('fr_FR');
        $carrierHelper = $this->objectManager->getObject(
            \Magento\Shipping\Helper\Carrier::class,
            [
                'localeResolver' => $localeResolver
            ]
        );

        // Xml Validator Mock
        $this->xmlValidator = $this->getMockBuilder(\Magento\Dhl\Model\Validator\XmlValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->objectManager->getObject(
            \Magento\Dhl\Model\Carrier::class,
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
                'configReader' => $configReader,
                'carrierHelper' => $carrierHelper,
                'data' => ['id' => 'dhl', 'store' => '1'],
                'xmlValidator' => $this->xmlValidator,
            ]
        );
    }

    /**
     * Emulates the config's `getValue` method.
     *
     * @param string $path
     * @return string|null
     */
    public function scopeConfigGetValue($path)
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
            'carriers/dhl/unit_of_measure' => 'K',
            'carriers/dhl/size' => '1',
            'carriers/dhl/height' => '1.6',
            'carriers/dhl/width' => '1.6',
            'carriers/dhl/depth' => '1.6',
            'shipping/origin/country_id' => 'GB',
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
        $model = $this->objectManager->getObject(\Magento\Dhl\Model\Carrier::class);
        $method = new \ReflectionMethod($model, '_prepareShippingLabelContent');
        $method->setAccessible(true);
        return $method->invoke($model, $xml);
    }

    public function testCollectRates()
    {
        $this->scope->method('isSetFlag')
            ->willReturn(true);

        $this->httpResponse->method('getBody')
            ->willReturn(file_get_contents(__DIR__ . '/_files/success_dhl_response_rates.xml'));
        $this->xmlValidator->expects($this->any())->method('validate');

        /** @var RateRequest $request */
        $request = $this->objectManager->getObject(
            RateRequest::class,
            require __DIR__ . '/_files/rates_request_data_dhl.php'
        );

        $reflectionClass = new \ReflectionObject($this->httpClient);
        $rawPostData = $reflectionClass->getProperty('raw_post_data');
        $rawPostData->setAccessible(true);

        self::assertNotEmpty($this->model->collectRates($request)->getAllRates());
        self::assertContains('<Weight>18.223</Weight>', $rawPostData->getValue($this->httpClient));
        self::assertContains('<Height>0.630</Height>', $rawPostData->getValue($this->httpClient));
        self::assertContains('<Width>0.630</Width>', $rawPostData->getValue($this->httpClient));
        self::assertContains('<Depth>0.630</Depth>', $rawPostData->getValue($this->httpClient));
    }

    public function testCollectRatesErrorMessage()
    {
        $this->scope->expects($this->once())->method('isSetFlag')->willReturn(false);

        $this->error->expects($this->once())->method('setCarrier')->with('dhl');
        $this->error->expects($this->once())->method('setCarrierTitle');
        $this->error->expects($this->once())->method('setErrorMessage');

        $request = new RateRequest();
        $request->setPackageWeight(1);

        $this->assertSame($this->error, $this->model->collectRates($request));
    }

    public function testCollectRatesFail()
    {
        $this->scope->expects($this->once())->method('isSetFlag')->willReturn(true);

        $request = new RateRequest();
        $request->setPackageWeight(1);

        $this->assertFalse(false, $this->model->collectRates($request));
    }

    /**
     * Test request to shipment sends valid xml values.
     */
    public function testRequestToShipment()
    {
        $this->httpResponse->method('getBody')
            ->willReturn(utf8_encode(file_get_contents(__DIR__ . '/_files/response_shipping_label.xml')));
        $this->xmlValidator->expects($this->any())->method('validate');

        $packages = [
            'package' => [
                'params' => [
                    'width' => '3',
                    'length' => '3',
                    'height' => '3',
                    'dimension_units' => 'INCH',
                    'weight_units' => 'POUND',
                    'weight' => '0.454000000001',
                    'customs_value' => '10.00',
                    'container' => \Magento\Dhl\Model\Carrier::DHL_CONTENT_TYPE_NON_DOC,
                ],
                'items' => [
                    'item1' => [
                        'name' => 'item_name',
                    ],
                ],
            ]
        ];

        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $order->expects($this->any())->method('getSubtotal')->willReturn('10.00');

        $shipment = $this->getMockBuilder(\Magento\Sales\Model\Order\Shipment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $shipment->expects($this->any())->method('getOrder')->willReturn($order);

        $this->request->expects($this->any())->method('getPackages')->willReturn($packages);
        $this->request->expects($this->any())->method('getOrigCountryId')->willReturn('GB');
        $this->request->expects($this->any())->method('setPackages')->willReturnSelf();
        $this->request->expects($this->any())->method('setPackageWeight')->willReturnSelf();
        $this->request->expects($this->any())->method('setPackageValue')->willReturnSelf();
        $this->request->expects($this->any())->method('setValueWithDiscount')->willReturnSelf();
        $this->request->expects($this->any())->method('setPackageCustomsValue')->willReturnSelf();
        $this->request->expects($this->any())->method('setFreeMethodWeight')->willReturnSelf();
        $this->request->expects($this->any())->method('getPackageWeight')->willReturn('0.454000000001');
        $this->request->expects($this->any())->method('getFreeMethodWeight')->willReturn('0.454000000001');
        $this->request->expects($this->any())->method('getOrderShipment')->willReturn($shipment);

        $result = $this->model->requestToShipment($this->request);

        $reflectionClass = new \ReflectionObject($this->httpClient);
        $rawPostData = $reflectionClass->getProperty('raw_post_data');
        $rawPostData->setAccessible(true);

        $this->assertNotNull($result);
        $this->assertContains('<Weight>0.454</Weight>', $rawPostData->getValue($this->httpClient));
    }

    /**
     * Data provider to testRequestToShipment
     * @return array
     */
    public function requestToShipmentDataProvider()
    {
        return [
            [
                'GB'
            ],
            [
                null
            ]
        ];
    }
}
