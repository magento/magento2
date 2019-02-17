<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Dhl\Test\Unit\Model;

use Magento\Dhl\Model\Carrier;
use Magento\Dhl\Model\Validator\XmlValidator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Stdlib\DateTime\DateTime;
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
     * @var DateTime|MockObject
     */
    private $coreDateMock;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadataMock;

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

        $this->coreDateMock = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->coreDateMock->method('date')
            ->willReturn('currentTime');

        $this->productMetadataMock = $this->getMockBuilder(ProductMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMetadataMock->method('getName')
            ->willReturn('Software_Product_Name_30_Char_123456789');
        $this->productMetadataMock->method('getVersion')
            ->willReturn('10Char_Ver123456789');

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
                'configReader' => $this->getConfigReader(),
                'storeManager' => $this->getStoreManager(),
                'readFactory' => $this->getReadFactory(),
                'httpClientFactory' => $this->getHttpClientFactory(),
                'data' => ['id' => 'dhl', 'store' => '1'],
                'xmlValidator' => $this->xmlValidator,
                'coreDate' => $this->coreDateMock,
                'productMetadata' => $this->productMetadataMock
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

        $this->coreDateMock->method('date')
           ->willReturnCallback(function () {
               return date(\DATE_RFC3339);
           });

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
     *
     * @param string $origCountryId
     * @param string $expectedRegionCode
     * @dataProvider requestToShipmentDataProvider
     */
    public function testRequestToShipment(string $origCountryId, string $expectedRegionCode)
    {
        $expectedRequestXml = file_get_contents(__DIR__ . '/_files/shipment_request.xml');
        $scopeConfigValueMap = [
            ['carriers/dhl/account', 'store', null, '1234567890'],
            ['carriers/dhl/gateway_url', 'store', null, 'https://xmlpi-ea.dhl.com/XMLShippingServlet'],
            ['carriers/dhl/id', 'store', null, 'some ID'],
            ['carriers/dhl/password', 'store', null, 'some password'],
            ['carriers/dhl/content_type', 'store', null, 'N'],
            ['carriers/dhl/nondoc_methods', 'store', null, '1,3,4,8,P,Q,E,F,H,J,M,V,Y'],
            ['shipping/origin/country_id', 'store', null, $origCountryId],
        ];

        $this->scope->method('getValue')
            ->willReturnMap($scopeConfigValueMap);

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
            ->willReturn($origCountryId);
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
        $requestXml = $rawPostData->getValue($this->httpClient);
        $requestElement = new Element($requestXml);
        $this->assertEquals($expectedRegionCode, $requestElement->RegionCode->__toString());
        $requestElement->RegionCode = 'Checked';
        $messageReference = $requestElement->Request->ServiceHeader->MessageReference->__toString();
        $this->assertStringStartsWith('MAGE_SHIP_', $messageReference);
        $this->assertGreaterThanOrEqual(28, strlen($messageReference));
        $this->assertLessThanOrEqual(32, strlen($messageReference));
        $requestElement->Request->ServiceHeader->MessageReference = 'MAGE_SHIP_28TO32_Char_CHECKED';
        $expectedRequestElement = new Element($expectedRequestXml);
        $this->assertXmlStringEqualsXmlString($expectedRequestElement->asXML(), $requestElement->asXML());
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
                'GB', 'EU'
            ],
            [
                'SG', 'AP'
            ]
        ];
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
     * Tests that the built MessageReference string is of the appropriate format.
     *
     * @dataProvider buildMessageReferenceDataProvider
     * @param $servicePrefix
     * @throws \ReflectionException
     */
    public function testBuildMessageReference($servicePrefix)
    {
        $method = new \ReflectionMethod($this->model, 'buildMessageReference');
        $method->setAccessible(true);

        $messageReference = $method->invoke($this->model, $servicePrefix);
        $this->assertGreaterThanOrEqual(28, strlen($messageReference));
        $this->assertLessThanOrEqual(32, strlen($messageReference));
    }

    /**
     * @return array
     */
    public function buildMessageReferenceDataProvider()
    {
        return [
            'quote_prefix' => ['QUOT'],
            'shipval_prefix' => ['SHIP'],
            'tracking_prefix' => ['TRCK']
        ];
    }

    /**
     * Tests that an exception is thrown when an invalid service prefix is provided.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Invalid service prefix
     */
    public function testBuildMessageReferenceInvalidPrefix()
    {
        $method = new \ReflectionMethod($this->model, 'buildMessageReference');
        $method->setAccessible(true);

        $method->invoke($this->model, 'INVALID');
    }

    /**
     * Tests that the built software name string is of the appropriate format.
     *
     * @dataProvider buildSoftwareNameDataProvider
     * @param $productName
     * @throws \ReflectionException
     */
    public function testBuildSoftwareName($productName)
    {
        $method = new \ReflectionMethod($this->model, 'buildSoftwareName');
        $method->setAccessible(true);

        $this->productMetadataMock->method('getName')->willReturn($productName);

        $softwareName = $method->invoke($this->model);
        $this->assertLessThanOrEqual(30, strlen($softwareName));
    }

    /**
     * @return array
     */
    public function buildSoftwareNameDataProvider()
    {
        return [
            'valid_length' => ['Magento'],
            'exceeds_length' => ['Product_Name_Longer_Than_30_Char']
        ];
    }

    /**
     * Tests that the built software version string is of the appropriate format.
     *
     * @dataProvider buildSoftwareVersionProvider
     * @param $productVersion
     * @throws \ReflectionException
     */
    public function testBuildSoftwareVersion($productVersion)
    {
        $method = new \ReflectionMethod($this->model, 'buildSoftwareVersion');
        $method->setAccessible(true);

        $this->productMetadataMock->method('getVersion')->willReturn($productVersion);

        $softwareVersion = $method->invoke($this->model);
        $this->assertLessThanOrEqual(10, strlen($softwareVersion));
    }

    /**
     * @return array
     */
    public function buildSoftwareVersionProvider()
    {
        return [
            'valid_length' => ['2.3.1'],
            'exceeds_length' => ['dev-MC-1000']
        ];
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
}
