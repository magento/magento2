<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Dhl\Test\Unit\Model;

use Magento\Dhl\Model\Carrier;
use Magento\Dhl\Model\Validator\XmlValidator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Xml\Security;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Sales\Model\Order;
use Magento\Shipping\Helper\Carrier as CarrierHelper;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Shipping\Model\Shipment\Request;
use Magento\Shipping\Model\Simplexml\Element;
use Magento\Shipping\Model\Simplexml\ElementFactory;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\Website;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\ScopeInterface;

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
     * @var Carrier
     */
    private $model;

    /**
     * @var Error|MockObject
     */
    private $error;

    /**
     * @var ErrorFactory|MockObject
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
     * @var Request|MockObject
     */
    private $request;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->request = $this->getMockBuilder(Request::class)
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

        $this->error = $this->getMockBuilder(Error::class)
            ->setMethods(['setCarrier', 'setCarrierTitle', 'setErrorMessage'])
            ->getMock();
        $this->errorFactory = $this->getMockBuilder(ErrorFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->errorFactory->method('create')
            ->willReturn($this->error);

        $this->xmlValidator = $this->getMockBuilder(XmlValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->model = $this->objectManager->getObject(
            Carrier::class,
            [
                'scopeConfig' => $this->scope,
                'rateErrorFactory' => $this->errorFactory,
                'logger' => $this->logger,
                'xmlSecurity' => new Security(),
                'xmlElFactory' => $this->getXmlFactory(),
                'rateFactory' => $this->getRateFactory(),
                'rateMethodFactory' => $this->getRateMethodFactory(),
                'carrierHelper' => $this->getCarrierHelper(),
                'coreDate' => $this->getCoreDate(),
                'configReader' => $this->getConfigReader(),
                'storeManager' => $this->getStoreManager(),
                'readFactory' => $this->getReadFactory(),
                'httpClientFactory' => $this->getHttpClientFactory(),
                'data' => ['id' => 'dhl', 'store' => '1'],
                'xmlValidator' => $this->xmlValidator,
                'productMetadata' => $this->getProductMetadata()
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
            'carriers/dhl/title' => 'DHL Title',
            'carriers/dhl/specificerrmsg' => 'dhl error message',
            'carriers/dhl/unit_of_measure' => 'K',
            'carriers/dhl/size' => '1',
            'carriers/dhl/height' => '1.6',
            'carriers/dhl/width' => '1.6',
            'carriers/dhl/depth' => '1.6',
            'carriers/dhl/debug' => 1,
            'shipping/origin/country_id' => 'GB'
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
        $model = $this->objectManager->getObject(Carrier::class);
        $method = new \ReflectionMethod($model, '_prepareShippingLabelContent');
        $method->setAccessible(true);
        return $method->invoke($model, $xml);
    }

    /**
     * Tests that valid rates are returned when sending a quotes request.
     */
    public function testCollectRates()
    {
        $requestData = require __DIR__ . '/_files/dhl_quote_request_data.php';
        $responseXml = file_get_contents(__DIR__ . '/_files/dhl_quote_response.xml');

        $this->scope->method('getValue')
            ->willReturnCallback([$this, 'scopeConfigGetValue']);

        $this->scope->method('isSetFlag')
            ->willReturn(true);

        $this->httpResponse->method('getBody')
            ->willReturn($responseXml);

        $request = $this->objectManager->getObject(RateRequest::class, $requestData);

        $reflectionClass = new \ReflectionObject($this->httpClient);
        $rawPostData = $reflectionClass->getProperty('raw_post_data');
        $rawPostData->setAccessible(true);

        $this->logger->expects($this->once())
            ->method('debug')
            ->with($this->stringContains('<SiteID>****</SiteID><Password>****</Password>'));

        $expectedRates = require __DIR__ . '/_files/dhl_quote_response_rates.php';
        $actualRates = $this->model->collectRates($request)->getAllRates();

        self::assertEquals(count($expectedRates), count($actualRates));

        foreach ($actualRates as $i => $actualRate) {
            $actualRate = $actualRate->getData();
            unset($actualRate['method_title']);
            self::assertEquals($expectedRates[$i], $actualRate);
        }

        $requestXml = $rawPostData->getValue($this->httpClient);
        self::assertContains('<Weight>18.223</Weight>', $requestXml);
        self::assertContains('<Height>0.630</Height>', $requestXml);
        self::assertContains('<Width>0.630</Width>', $requestXml);
        self::assertContains('<Depth>0.630</Depth>', $requestXml);
    }

    /**
     * Tests that an error is returned when attempting to collect rates for an inactive shipping method.
     */
    public function testCollectRatesErrorMessage()
    {
        $this->scope->method('getValue')
            ->willReturnCallback([$this, 'scopeConfigGetValue']);

        $this->scope->expects($this->once())->method('isSetFlag')->willReturn(false);

        $this->error->expects($this->once())->method('setCarrier')->with('dhl');
        $this->error->expects($this->once())->method('setCarrierTitle');
        $this->error->expects($this->once())->method('setErrorMessage');

        $request = new RateRequest();
        $request->setPackageWeight(1);

        $this->assertSame($this->error, $this->model->collectRates($request));
    }

    /**
     * Test request to shipment sends valid xml values.
     */
    public function testRequestToShipment()
    {
        $this->scope->method('getValue')
            ->willReturnCallback([$this, 'scopeConfigGetValue']);

        $this->httpResponse->method('getBody')
            ->willReturn(utf8_encode(file_get_contents(__DIR__ . '/_files/response_shipping_label.xml')));

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
                    'container' => Carrier::DHL_CONTENT_TYPE_NON_DOC,
                ],
                'items' => [
                    'item1' => [
                        'name' => 'item_name',
                    ],
                ],
            ],
        ];

        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $order->method('getSubtotal')
            ->willReturn('10.00');

        $shipment = $this->getMockBuilder(Order\Shipment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $shipment->method('getOrder')
            ->willReturn($order);

        $this->request->method('getPackages')
            ->willReturn($packages);
        $this->request->method('getOrigCountryId')
            ->willReturn('GB');
        $this->request->method('setPackages')
            ->willReturnSelf();
        $this->request->method('setPackageWeight')
            ->willReturnSelf();
        $this->request->method('setPackageValue')
            ->willReturnSelf();
        $this->request->method('setValueWithDiscount')
            ->willReturnSelf();
        $this->request->method('setPackageCustomsValue')
            ->willReturnSelf();
        $this->request->method('setFreeMethodWeight')
            ->willReturnSelf();
        $this->request->method('getPackageWeight')
            ->willReturn('0.454000000001');
        $this->request->method('getFreeMethodWeight')
            ->willReturn('0.454000000001');
        $this->request->method('getOrderShipment')
            ->willReturn($shipment);

        $this->logger->method('debug')
            ->with($this->stringContains('<SiteID>****</SiteID><Password>****</Password>'));

        $result = $this->model->requestToShipment($this->request);

        $reflectionClass = new \ReflectionObject($this->httpClient);
        $rawPostData = $reflectionClass->getProperty('raw_post_data');
        $rawPostData->setAccessible(true);

        $this->assertNotNull($result);
        $this->assertContains('<Weight>0.454</Weight>', $rawPostData->getValue($this->httpClient));
    }

    /**
     * Data provider to testRequestToShipment
     *
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

    /**
     * Test that shipping label request for origin country from AP region doesn't contain restricted fields.
     *
     * @return void
     */
    public function testShippingLabelRequestForAsiaPacificRegion()
    {
        $this->scope->method('getValue')
            ->willReturnMap(
                [
                    ['shipping/origin/country_id', ScopeInterface::SCOPE_STORE, null, 'SG'],
                    ['carriers/dhl/gateway_url', ScopeInterface::SCOPE_STORE, null, 'https://xmlpi-ea.dhl.com'],
                ]
            );

        $this->httpResponse->method('getBody')
            ->willReturn(utf8_encode(file_get_contents(__DIR__ . '/_files/response_shipping_label.xml')));

        $packages = [
            'package' => [
                'params' => [
                    'width' => '1',
                    'length' => '1',
                    'height' => '1',
                    'dimension_units' => 'INCH',
                    'weight_units' => 'POUND',
                    'weight' => '0.45',
                    'customs_value' => '10.00',
                    'container' => Carrier::DHL_CONTENT_TYPE_NON_DOC,
                ],
                'items' => [
                    'item1' => [
                        'name' => 'item_name',
                    ],
                ],
            ],
        ];

        $this->request->method('getPackages')->willReturn($packages);
        $this->request->method('getOrigCountryId')->willReturn('SG');
        $this->request->method('setPackages')->willReturnSelf();
        $this->request->method('setPackageWeight')->willReturnSelf();
        $this->request->method('setPackageValue')->willReturnSelf();
        $this->request->method('setValueWithDiscount')->willReturnSelf();
        $this->request->method('setPackageCustomsValue')->willReturnSelf();

        $result = $this->model->requestToShipment($this->request);

        $reflectionClass = new \ReflectionObject($this->httpClient);
        $rawPostData = $reflectionClass->getProperty('raw_post_data');
        $rawPostData->setAccessible(true);

        $this->assertNotNull($result);
        $requestXml = $rawPostData->getValue($this->httpClient);

        $this->assertNotContains(
            'NewShipper',
            $requestXml,
            'NewShipper is restricted field for AP region'
        );
        $this->assertNotContains(
            'Division',
            $requestXml,
            'Division is restricted field for AP region'
        );
        $this->assertNotContains(
            'RegisteredAccount',
            $requestXml,
            'RegisteredAccount is restricted field for AP region'
        );
    }
    
    /**
     * @dataProvider dhlProductsDataProvider
     *
     * @param string $docType
     * @param array $products
     */
    public function testGetDhlProducts(string $docType, array $products)
    {
        $this->assertEquals($products, $this->model->getDhlProducts($docType));
    }

    /**
     * @return array
     */
    public function dhlProductsDataProvider() : array
    {
        return [
            'doc' => [
                'docType' => Carrier::DHL_CONTENT_TYPE_DOC,
                'products' => [
                    '2' => 'Easy shop',
                    '5' => 'Sprintline',
                    '6' => 'Secureline',
                    '7' => 'Express easy',
                    '9' => 'Europack',
                    'B' => 'Break bulk express',
                    'C' => 'Medical express',
                    'D' => 'Express worldwide',
                    'U' => 'Express worldwide',
                    'K' => 'Express 9:00',
                    'L' => 'Express 10:30',
                    'G' => 'Domestic economy select',
                    'W' => 'Economy select',
                    'I' => 'Domestic express 9:00',
                    'N' => 'Domestic express',
                    'O' => 'Others',
                    'R' => 'Globalmail business',
                    'S' => 'Same day',
                    'T' => 'Express 12:00',
                    'X' => 'Express envelope',
                ],
            ],
            'non-doc' => [
                'docType' => Carrier::DHL_CONTENT_TYPE_NON_DOC,
                'products' => [
                    '1' => 'Domestic express 12:00',
                    '3' => 'Easy shop',
                    '4' => 'Jetline',
                    '8' => 'Express easy',
                    'P' => 'Express worldwide',
                    'Q' => 'Medical express',
                    'E' => 'Express 9:00',
                    'F' => 'Freight worldwide',
                    'H' => 'Economy select',
                    'J' => 'Jumbo box',
                    'M' => 'Express 10:30',
                    'V' => 'Europack',
                    'Y' => 'Express 12:00',
                ],
            ],
        ];
    }

    /**
     * Tests that the built message reference string is of the appropriate format.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Invalid service prefix
     * @throws \ReflectionException
     */
    public function testBuildMessageReference()
    {
        $method = new \ReflectionMethod($this->model, 'buildMessageReference');
        $method->setAccessible(true);

        $constPrefixQuote = new \ReflectionClassConstant($this->model, 'SERVICE_PREFIX_QUOTE');
        $constPrefixShipval = new \ReflectionClassConstant($this->model, 'SERVICE_PREFIX_SHIPVAL');
        $constPrefixTracking = new \ReflectionClassConstant($this->model, 'SERVICE_PREFIX_TRACKING');

        $msgRefQuote = $method->invoke($this->model, $constPrefixQuote->getValue());
        self::assertGreaterThanOrEqual(28, strlen($msgRefQuote));
        self::assertLessThanOrEqual(32, strlen($msgRefQuote));

        $msgRefShip = $method->invoke($this->model, $constPrefixShipval->getValue());
        self::assertGreaterThanOrEqual(28, strlen($msgRefShip));
        self::assertLessThanOrEqual(32, strlen($msgRefShip));

        $msgRefTrack = $method->invoke($this->model, $constPrefixTracking->getValue());
        self::assertGreaterThanOrEqual(28, strlen($msgRefTrack));
        self::assertLessThanOrEqual(32, strlen($msgRefTrack));

        $method->invoke($this->model, 'TEST');
    }

    /**
     * Tests that the built software name string is of the appropriate format.
     *
     * @throws \ReflectionException
     */
    public function testBuildSoftwareName()
    {
        $method = new \ReflectionMethod($this->model, 'buildSoftwareName');
        $method->setAccessible(true);

        $name = $method->invoke($this->model);
        self::assertLessThanOrEqual(30, $name);

        $nameExceedsLength = $method->invoke($this->model);
        self::assertLessThanOrEqual(30, $nameExceedsLength);
    }

    /**
     * Tests that the built software version string is of the appropriate format.
     *
     * @throws \ReflectionException
     */
    public function testBuildSoftwareVersion()
    {
        $method = new \ReflectionMethod($this->model, 'buildSoftwareVersion');
        $method->setAccessible(true);

        $version = $method->invoke($this->model);
        self::assertLessThanOrEqual(10, strlen($version));

        $versionExceedsLength = $method->invoke($this->model);
        self::assertLessThanOrEqual(10, strlen($versionExceedsLength));
    }

    /**
     * Creates mock for XML factory.
     *
     * @return ElementFactory|MockObject
     */
    private function getXmlFactory(): MockObject
    {
        $xmlElFactory = $this->getMockBuilder(ElementFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $xmlElFactory->method('create')
            ->willReturnCallback(
                function ($data) {
                    $helper = new ObjectManager($this);

                    return $helper->getObject(
                        Element::class,
                        ['data' => $data['data']]
                    );
                }
            );

        return $xmlElFactory;
    }

    /**
     * Creates mock for rate factory.
     *
     * @return ResultFactory|MockObject
     */
    private function getRateFactory(): MockObject
    {
        $rateFactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $rateResult = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $rateFactory->method('create')
            ->willReturn($rateResult);

        return $rateFactory;
    }

    /**
     * Creates mock for rate method factory.
     *
     * @return MethodFactory|MockObject
     */
    private function getRateMethodFactory(): MockObject
    {
        $rateMethodFactory = $this->getMockBuilder(MethodFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $rateMethodFactory->method('create')
            ->willReturnCallback(function () {
                $rateMethod = $this->getMockBuilder(Method::class)
                    ->disableOriginalConstructor()
                    ->setMethods(['setPrice'])
                    ->getMock();
                $rateMethod->method('setPrice')
                    ->willReturnSelf();

                return $rateMethod;
            });

        return $rateMethodFactory;
    }

    /**
     * @return MockObject
     */
    private function getConfigReader(): MockObject
    {
        $configReader = $this->getMockBuilder(Reader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configReader->method('getModuleDir')
            ->willReturn('/etc/path');

        return $configReader;
    }

    /**
     * @return MockObject
     */
    private function getReadFactory(): MockObject
    {
        $modulesDirectory = $this->getMockBuilder(Read::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRelativePath', 'readFile'])
            ->getMock();
        $modulesDirectory->method('readFile')
            ->willReturn(file_get_contents(__DIR__ . '/_files/countries.xml'));
        $readFactory = $this->createMock(ReadFactory::class);
        $readFactory->method('create')
            ->willReturn($modulesDirectory);

        return $readFactory;
    }

    /**
     * @return MockObject
     */
    private function getStoreManager(): MockObject
    {
        $storeManager = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getWebsite'])
            ->getMock();
        $website = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBaseCurrencyCode', '__wakeup'])
            ->getMock();
        $website->method('getBaseCurrencyCode')
            ->willReturn('USD');
        $storeManager->method('getWebsite')
            ->willReturn($website);

        return $storeManager;
    }

    /**
     * @return CarrierHelper
     */
    private function getCarrierHelper(): CarrierHelper
    {
        $localeResolver = $this->getMockForAbstractClass(ResolverInterface::class);
        $localeResolver->method('getLocale')
            ->willReturn('fr_FR');
        $carrierHelper = $this->objectManager->getObject(
            CarrierHelper::class,
            [
                'localeResolver' => $localeResolver,
            ]
        );

        return $carrierHelper;
    }

    /**
     * @return MockObject
     */
    private function getCoreDate(): MockObject
    {
        $coreDate = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $coreDate->method('date')->willReturnCallback(function () {
            return date(\DATE_RFC3339);
        });

        return $coreDate;
    }

    /**
     * @return MockObject
     */
    private function getHttpClientFactory(): MockObject
    {
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

        return $httpClientFactory;
    }

    /**
     * @return MockObject
     */
    private function getProductMetadata(): MockObject
    {
        $productMetadata = $this->createMock(\Magento\Framework\App\ProductMetadata::class);

        $productMetadata->method('getName')->willReturnOnConsecutiveCalls(
            'Magento',
            str_pad('Magento', 24, '_')
        );

        $productMetadata->method('getVersion')->willReturnOnConsecutiveCalls(
            '2.3.1',
            'dev-MC-1000'
        );

        return $productMetadata;
    }
}
