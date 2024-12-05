<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Dhl\Test\Unit\Model;

use Laminas\Http\Response;
use Magento\Dhl\Model\Carrier;
use Magento\Dhl\Model\Validator\XmlValidator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\HTTP\LaminasClient;
use Magento\Framework\HTTP\LaminasClientFactory;
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
use Magento\Shipping\Helper\Carrier as CarrierHelper;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Shipping\Model\Simplexml\Element;
use Magento\Shipping\Model\Simplexml\ElementFactory;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CarrierTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Response|MockObject
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
     * @var LaminasClient|MockObject
     */
    private $httpClient;

    /**
     * @var XmlValidator|MockObject
     */
    private $xmlValidator;

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
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->scope = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->error = $this->getMockBuilder(Error::class)
            ->addMethods(['setCarrier', 'setCarrierTitle', 'setErrorMessage'])
            ->getMock();
        $this->errorFactory = $this->getMockBuilder(ErrorFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
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
            ->getMockForAbstractClass();
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
            'carriers/dhl/international_service' => 'IE',
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
        return $pathMap[$path] ?? null;
    }

    /**
     * Prepare shipping label content test
     *
     * @throws \ReflectionException
     */
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
     * Prepare shipping label content exception test
     *
     * @dataProvider prepareShippingLabelContentExceptionDataProvider
     */
    public function testPrepareShippingLabelContentException(\SimpleXMLElement $xml)
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Unable to retrieve shipping label');
        $this->_invokePrepareShippingLabelContent($xml);
    }

    /**
     * Prepare shipping label content exception data provider
     *
     * @return array
     */
    public static function prepareShippingLabelContentExceptionDataProvider()
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
     * Invoke prepare shipping label content
     *
     * @param \SimpleXMLElement $xml
     * @return DataObject
     * @throws \ReflectionException
     */
    protected function _invokePrepareShippingLabelContent(\SimpleXMLElement $xml)
    {
        $model = $this->objectManager->getObject(Carrier::class);
        $method = new \ReflectionMethod($model, '_prepareShippingLabelContent');
        $method->setAccessible(true);
        return $method->invoke($model, $xml);
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
     * Get DHL products test
     *
     * @dataProvider dhlProductsDataProvider
     * @param string $docType
     * @param array $products
     */
    public function testGetDhlProducts(string $docType, array $products)
    {
        $this->assertEquals($products, $this->model->getDhlProducts($docType));
    }

    /**
     * DHL products data provider
     *
     * @return array
     */
    public static function dhlProductsDataProvider(): array
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
                    'N' => 'Domestic express',
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
     * Build message reference data provider
     *
     * @return array
     */
    public static function buildMessageReferenceDataProvider()
    {
        return [
            'quote_prefix' => ['QUOT'],
            'shipval_prefix' => ['SHIP'],
            'tracking_prefix' => ['TRCK']
        ];
    }

    /**
     * Tests that an exception is thrown when an invalid service prefix is provided.
     */
    public function testBuildMessageReferenceInvalidPrefix()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Invalid service prefix');
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
     * Data provider for testBuildSoftwareName
     *
     * @return array
     */
    public static function buildSoftwareNameDataProvider()
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
     * Data provider for testBuildSoftwareVersion
     *
     * @return array
     */
    public static function buildSoftwareVersionProvider()
    {
        return [
            'valid_length' => ['2.3.1'],
            'exceeds_length' => ['dev-MC-1000']
        ];
    }

    /**
     * Tests if the DHL client returns the appropriate API URL.
     *
     * @dataProvider getGatewayURLProvider
     * @param $sandboxMode
     * @param $expectedURL
     * @throws \ReflectionException
     */
    public function testGetGatewayURL($sandboxMode, $expectedURL)
    {
        $scopeConfigValueMap = [
            ['carriers/dhl/gateway_url', 'store', null, 'https://xmlpi-ea.dhl.com/XMLShippingServlet'],
            ['carriers/dhl/sandbox_url', 'store', null, 'https://xmlpitest-ea.dhl.com/XMLShippingServlet'],
            ['carriers/dhl/sandbox_mode', 'store', null, $sandboxMode]
        ];

        $this->scope->method('getValue')
            ->willReturnMap($scopeConfigValueMap);

        $this->model = $this->objectManager->getObject(
            Carrier::class,
            [
                'scopeConfig' => $this->scope
            ]
        );

        $method = new \ReflectionMethod($this->model, 'getGatewayURL');
        $method->setAccessible(true);
        $this->assertEquals($expectedURL, $method->invoke($this->model));
    }

    /**
     * Data provider for testGetGatewayURL
     *
     * @return array
     */
    public static function getGatewayURLProvider()
    {
        return [
            'standard_url' => [0, 'https://xmlpi-ea.dhl.com/XMLShippingServlet'],
            'sandbox_url' => [1, 'https://xmlpitest-ea.dhl.com/XMLShippingServlet']
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
            ->onlyMethods(['create'])
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
            ->onlyMethods(['create'])
            ->getMock();
        $rateResult = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
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
            ->onlyMethods(['create'])
            ->getMock();

        $rateMethodFactory->method('create')
            ->willReturnCallback(
                function () {
                    $rateMethod = $this->getMockBuilder(Method::class)
                        ->disableOriginalConstructor()
                        ->onlyMethods(['setPrice'])
                        ->getMock();
                    $rateMethod->method('setPrice')
                        ->willReturnSelf();

                    return $rateMethod;
                }
            );

        return $rateMethodFactory;
    }

    /**
     * Get config reader
     *
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
     * Get read factory
     *
     * @return MockObject
     */
    private function getReadFactory(): MockObject
    {
        $modulesDirectory = $this->getMockBuilder(Read::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRelativePath', 'readFile'])
            ->getMock();
        $modulesDirectory->method('readFile')
            ->willReturn(file_get_contents(__DIR__ . '/_files/countries.xml'));
        $readFactory = $this->createMock(ReadFactory::class);
        $readFactory->method('create')
            ->willReturn($modulesDirectory);

        return $readFactory;
    }

    /**
     * Get store manager
     *
     * @return MockObject
     */
    private function getStoreManager(): MockObject
    {
        $storeManager = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getWebsite'])
            ->getMock();
        $website = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBaseCurrencyCode', '__wakeup'])
            ->getMock();
        $website->method('getBaseCurrencyCode')
            ->willReturn('USD');
        $storeManager->method('getWebsite')
            ->willReturn($website);

        return $storeManager;
    }

    /**
     * Get carrier helper
     *
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
     * Get HTTP client factory
     *
     * @return MockObject
     */
    private function getHttpClientFactory(): MockObject
    {
        $this->httpResponse = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->httpClient = $this->getMockBuilder(LaminasClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['send'])
            ->getMock();
        $this->httpClient->method('send')
            ->willReturn($this->httpResponse);
        $httpClientFactory = $this->getMockBuilder(LaminasClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $httpClientFactory->method('create')
            ->willReturn($this->httpClient);

        return $httpClientFactory;
    }
}
