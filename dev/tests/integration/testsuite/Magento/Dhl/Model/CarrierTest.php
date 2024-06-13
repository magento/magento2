<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Dhl\Model;

use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\DataObject;
use Magento\Framework\HTTP\AsyncClient\HttpException;
use Magento\Framework\HTTP\AsyncClient\HttpResponseDeferredInterface;
use Magento\Framework\HTTP\AsyncClient\Response;
use Magento\Framework\HTTP\AsyncClientInterface;
use Magento\Framework\Simplexml\Element;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Shipping\Model\Shipment\Request;
use Magento\Shipping\Model\Simplexml\Element as ShippingElement;
use Magento\Shipping\Model\Tracking\Result\Status;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\HTTP\AsyncClientInterfaceMock;
use PHPUnit\Framework\TestCase;

/**
 * Test for DHL integration.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CarrierTest extends TestCase
{
    private const PRODUCT_NAME_SPECIAL_CHARS = 'Φυστίκι Ψημένο με Αλάτι Συσκευασία';

    /**
     * @var Carrier
     */
    private $dhlCarrier;

    /**
     * @var AsyncClientInterfaceMock
     */
    private $httpClient;

    /**
     * @var ReinitableConfigInterface
     */
    private $config;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var string
     */
    private $restoreCountry;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->dhlCarrier = $objectManager->get(Carrier::class);
        $this->httpClient = $objectManager->get(AsyncClientInterface::class);
        $this->config = $objectManager->get(ReinitableConfigInterface::class);
        $this->productMetadata = $objectManager->get(ProductMetadataInterface::class);
        $this->restoreCountry = $this->config->getValue('shipping/origin/country_id', 'store', 'default_store');
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->config->setValue(
            'shipping/origin/country_id',
            $this->restoreCountry,
            'store',
            'default_store'
        );
    }

    /**
     * Test sending tracking requests.
     *
     * @magentoConfigFixture default_store carriers/dhl/id CustomerSiteID
     * @magentoConfigFixture default_store carriers/dhl/password CustomerPassword
     * @param string[] $trackingNumbers
     * @param string $responseXml
     * @param array $expectedTrackingData
     * @param string $expectedRequestXml
     * @dataProvider trackingDataProvider
     */
    public function testGetTracking(
        $trackingNumbers,
        string $responseXml,
        $expectedTrackingData,
        string $expectedRequestXml = ''
    ) {
        $this->httpClient->nextResponses([new Response(200, [], $responseXml)]);
        $trackingResult = $this->dhlCarrier->getTracking($trackingNumbers);
        $this->assertTrackingResult($expectedTrackingData, $trackingResult->getAllTrackings());
        if ($expectedRequestXml !== '') {
            $this->assertRequest($expectedRequestXml, $this->httpClient->getLastRequest()->getBody());
        }
    }

    /**
     * Get tracking data provider
     *
     * @return array
     */
    public static function trackingDataProvider() : array
    {
        // phpcs:disable Magento2.Functions.DiscouragedFunction
        $expectedMultiAWBRequestXml = file_get_contents(__DIR__ . '/../_files/TrackingRequest_MultipleAWB.xml');
        $multiAWBResponseXml = file_get_contents(__DIR__ . '/../_files/TrackingResponse_MultipleAWB.xml');
        $expectedSingleAWBRequestXml = file_get_contents(__DIR__ . '/../_files/TrackingRequest_SingleAWB.xml');
        $singleAWBResponseXml = file_get_contents(__DIR__ . '/../_files/TrackingResponse_SingleAWB.xml');
        $singleNoDataResponseXml = file_get_contents(__DIR__ . '/../_files/SingleknownTrackResponse-no-data-found.xml');
        $failedResponseXml = file_get_contents(__DIR__ . '/../_files/Track-res-XML-Parse-Err.xml');
        //phpcs:enable Magento2.Functions.DiscouragedFunction
        $expectedTrackingDataA = [
            'carrier' => 'dhl',
            'carrier_title' => 'DHL',
            'tracking' => 4781584780,
            'service' => 'DOCUMENT',
            'progressdetail' => [
                [
                    'activity' => 'SD Shipment information received',
                    'deliverydate' => '2017-12-25',
                    'deliverytime' => '14:38:00',
                    'deliverylocation' => 'BEIJING-CHN [PEK]'
                ]
            ],
            'weight' => '0.5 K',
        ];
        $expectedTrackingDataB = [
            'carrier' => 'dhl',
            'carrier_title' => 'DHL',
            'tracking' => 4781585060,
            'service' => 'NOT RESTRICTED FOR TRANSPORT,',
            'progressdetail' => [
                [
                    'activity' => 'SD Shipment information received',
                    'deliverydate' => '2017-12-24',
                    'deliverytime' => '13:35:00',
                    'deliverylocation' => 'HONG KONG-HKG [HKG]'
                ]
            ],
            'weight' => '2.0 K',
        ];
        $expectedTrackingDataC = [
            'carrier' => 'dhl',
            'carrier_title' => 'DHL',
            'tracking' => 5702254250,
            'service' => 'CD',
            'progressdetail' => [
                [
                    'activity' => 'SD Shipment information received',
                    'deliverydate' => '2017-12-24',
                    'deliverytime' => '04:12:00',
                    'deliverylocation' => 'BIRMINGHAM-GBR [BHX]'
                ]
            ],
            'weight' => '0.12 K',
        ];
        $expectedTrackingDataD = [
            'carrier' => 'dhl',
            'carrier_title' => 'DHL',
            'tracking' => 4781585060,
            'error_message' => __('Unable to retrieve tracking')
        ];
        $expectedTrackingDataE = [
            'carrier' => 'dhl',
            'carrier_title' => 'DHL',
            'tracking' => 111,
            'error_message' => __(
                'Error #%1 : %2',
                '111',
                ' Error Parsing incoming request XML
                    Error: The content of element type
                    "ShipperReference" must match
                    "(ReferenceID,ReferenceType?)". at line
                    16, column 22'
            )
        ];
        return [
            'multi-AWB' => [
                ['4781584780', '4781585060', '5702254250'],
                $multiAWBResponseXml,
                [$expectedTrackingDataA, $expectedTrackingDataB, $expectedTrackingDataC],
                $expectedMultiAWBRequestXml
            ],
            'single-AWB' => [
                ['4781585060'],
                $singleAWBResponseXml,
                [$expectedTrackingDataB],
                $expectedSingleAWBRequestXml
            ],
            'single-AWB-no-data' => [['4781585061'], $singleNoDataResponseXml, [$expectedTrackingDataD]],
            'failed-response' => [['4781585060-failed'], $failedResponseXml, [$expectedTrackingDataE]]
        ];
    }

    /**
     * Assert request
     *
     * @param string $expectedRequestXml
     * @param string $requestXml
     */
    private function assertRequest(string $expectedRequestXml, string $requestXml): void
    {
        $expectedRequestElement = new Element($expectedRequestXml);
        $requestElement = new Element($requestXml);
        $requestMessageTime = $requestElement->Request->ServiceHeader->MessageTime->__toString();
        $this->assertEquals(
            1,
            preg_match("/\d{4}\-\d{2}\-\d{2}T\d{2}\:\d{2}\:\d{2}\+\d{2}\:\d{2}/", $requestMessageTime)
        );
        $expectedRequestElement->Request->ServiceHeader->MessageTime = $requestMessageTime;
        $messageReference = $requestElement->Request->ServiceHeader->MessageReference->__toString();
        $this->assertStringStartsWith('MAGE_TRCK_', $messageReference);
        $this->assertGreaterThanOrEqual(28, strlen($messageReference));
        $this->assertLessThanOrEqual(32, strlen($messageReference));
        $requestElement->Request->ServiceHeader->MessageReference = 'MAGE_TRCK_28TO32_Char_CHECKED';
        $this->assertXmlStringEqualsXmlString($expectedRequestElement->asXML(), $requestElement->asXML());
    }

    /**
     * Assert tracking
     *
     * @param array|null $expectedTrackingData
     * @param Status[]|null $trackingResults
     * @return void
     */
    private function assertTrackingResult($expectedTrackingData, $trackingResults): void
    {
        if (null === $expectedTrackingData) {
            $this->assertNull($trackingResults);
        } else {
            $ctr = 0;
            foreach ($trackingResults as $trackingResult) {
                $this->assertEquals($expectedTrackingData[$ctr++], $trackingResult->getData());
            }
        }
    }

    /**
     * Test sending shipping requests.
     *
     * @magentoConfigFixture default_store carriers/dhl/id some ID
     * @magentoConfigFixture default_store carriers/dhl/type DHL_XML
     * @magentoConfigFixture default_store carriers/dhl/password some password
     * @magentoConfigFixture default_store carriers/dhl/account 1234567890
     * @magentoConfigFixture default_store carriers/dhl/gateway_xml_url https://xmlpi-ea.dhl.com/XMLShippingServlet
     * @magentoConfigFixture default_store carriers/dhl/content_type N
     * @magentoConfigFixture default_store carriers/dhl/nondoc_methods 1,3,4,8,P,Q,E,F,H,J,M,V,Y
     * @magentoConfigFixture default_store carriers/dhl/unit_of_measure C
     * @param string $origCountryId
     * @param string $expectedRegionCode
     * @param string $destCountryId
     * @param bool|null $isProductNameContainsSpecialChars
     * @return void
     * @dataProvider requestToShipmentDataProvider
     */
    public function testRequestToShip(
        string $origCountryId,
        string $expectedRegionCode,
        string $destCountryId,
        bool $isProductNameContainsSpecialChars = false
    ): void {
        $this->config->setValue(
            'shipping/origin/country_id',
            $origCountryId,
            'store',
            null
        );
        $convmap = [0x80, 0x10FFFF, 0, 0x1FFFFF];
        $content = mb_encode_numericentity(
            file_get_contents(__DIR__ . '/../_files/response_shipping_label.xml'),
            $convmap,
            'UTF-8'
        );
        //phpcs:disable Magento2.Functions.DiscouragedFunction
        $this->httpClient->nextResponses(
            [
                new Response(
                    200,
                    [],
                    $content
                )
            ]
        );
        $productName = $isProductNameContainsSpecialChars ? self::PRODUCT_NAME_SPECIAL_CHARS : 'item_name';

        //phpcs:enable Magento2.Functions.DiscouragedFunction
        $request = new Request(
            [
                'packages' => [
                    'package' => [
                        'params' => [
                            'width' => '3',
                            'length' => '3',
                            'height' => '3',
                            'dimension_units' => 'CENTIMETER',
                            'weight_units' => 'KILOGRAM',
                            'weight' => '0.454000000001',
                            'customs_value' => '10.00',
                            'container' => Carrier::DHL_CONTENT_TYPE_NON_DOC,
                        ],
                        'items' => [
                            'item1' => [
                                'name' => $productName,
                                'qty' => 1,
                                'weight' => '0.454000000001',
                                'price' => '10.00',
                            ],
                        ],
                    ],
                ],
                'orig_country_id' => $origCountryId,
                'dest_country_id' => $destCountryId,
                'shipper_address_country_code' => $origCountryId,
                'recipient_address_country_code' => $destCountryId,
                'package_weight' => '0.454000000001',
                'free_method_weight' => '0.454000000001',
                'recipient_address_street_1' => '15099 Some Blvd',
                'shipper_address_street_1' => '4956 Some Way',
                'order_shipment' => new DataObject(
                    [
                        'order' => new DataObject(
                            [
                                'subtotal' => '10.00'
                            ]
                        )
                    ]
                )
            ]
        );

        //Generating labels
        $labels = $this->dhlCarrier->requestToShipment($request);
        $this->assertNotEmpty($labels);
        $this->assertNotEmpty($labels->getInfo());
        $request = $this->httpClient->getLastRequest()->getBody();
        $requestElement = new Element($request);
        $messageReference = $requestElement->Request->ServiceHeader->MessageReference->__toString();
        $this->assertStringStartsWith('MAGE_SHIP_', $messageReference);
        $this->assertGreaterThanOrEqual(28, strlen($messageReference));
        $this->assertLessThanOrEqual(32, strlen($messageReference));
        $requestElement->Request->ServiceHeader->MessageReference = 'MAGE_SHIP_28TO32_Char_CHECKED';
        $requestElement->Request->ServiceHeader->MessageTime = 'currentTime';
        $requestElement->ShipmentDetails->Date = 'currentTime';

        $expectedLabelRequest = $this->getExpectedLabelRequestXml(
            $origCountryId,
            $destCountryId,
            $expectedRegionCode,
            $isProductNameContainsSpecialChars
        );

        $this->assertXmlStringEqualsXmlString($expectedLabelRequest, $requestElement->asXML());
    }

    /**
     * Cases with different countries.
     *
     * @return array
     */
    public static function requestToShipmentDataProvider(): array
    {
        return [
            [
                'GB', 'EU', 'US'
            ],
            [
                'SG', 'AP', 'US'
            ],
            [
                'DE', 'EU', 'DE'
            ],
            [
                'GB', 'EU', 'US', true
            ],
        ];
    }

    /**
     * Generate expected labels request XML.
     *
     * @param string $origCountryId
     * @param string $destCountryId
     * @param string $regionCode
     * @param bool $isProductNameContainsSpecialChars
     * @return string
     */
    private function getExpectedLabelRequestXml(
        string $origCountryId,
        string $destCountryId,
        string $regionCode,
        bool $isProductNameContainsSpecialChars
    ): string {
        $countryNames = [
            'US' => 'United States Of America',
            'SG' => 'Singapore',
            'GB' => 'United Kingdom',
            'DE' => 'Germany',
        ];
        $requestXmlPath = $origCountryId == $destCountryId
            ? '/../_files/domestic_shipment_request.xml'
            : '/../_files/shipment_request.xml';

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $expectedRequestElement = new ShippingElement(file_get_contents(__DIR__ . $requestXmlPath));

        $expectedRequestElement->Request->MetaData->SoftwareVersion = $this->buildSoftwareVersion();
        $expectedRequestElement->Consignee->CountryCode = $destCountryId;
        $expectedRequestElement->Consignee->CountryName = $countryNames[$destCountryId];
        $expectedRequestElement->Shipper->CountryCode = $origCountryId;
        $expectedRequestElement->Shipper->CountryName = $countryNames[$origCountryId];
        $expectedRequestElement->RegionCode = $regionCode;

        if ($origCountryId !== $destCountryId) {
            $expectedRequestElement->ExportDeclaration->ExportLineItem->ManufactureCountryCode = $origCountryId;
        }

        if ($isProductNameContainsSpecialChars) {
            $expectedRequestElement->ShipmentDetails->Pieces->Piece->PieceContents = self::PRODUCT_NAME_SPECIAL_CHARS;
            $expectedRequestElement->ExportDeclaration->ExportLineItem->Description = self::PRODUCT_NAME_SPECIAL_CHARS;
        }

        return $expectedRequestElement->asXML();
    }

    /**
     * Builds a string to be used as the request SoftwareVersion.
     *
     * @return string
     */
    private function buildSoftwareVersion(): string
    {
        return substr($this->productMetadata->getVersion(), 0, 10);
    }

    /**
     * Tests that valid rates are returned when sending a quotes request.
     *
     * @magentoConfigFixture default_store carriers/dhl/active 1
     * @magentoConfigFixture default_store carriers/dhl/type DHL_XML
     * @magentoConfigFixture default_store carriers/dhl/id some ID
     * @magentoConfigFixture default_store carriers/dhl/shipment_days Mon,Tue,Wed,Thu,Fri,Sat
     * @magentoConfigFixture default_store carriers/dhl/intl_shipment_days Mon,Tue,Wed,Thu,Fri,Sat
     * @magentoConfigFixture default_store carriers/dhl/allowed_methods IE
     * @magentoConfigFixture default_store carriers/dhl/international_service IE
     * @magentoConfigFixture default_store carriers/dhl/gateway_xml_url https://xmlpi-ea.dhl.com/XMLShippingServlet
     * @magentoConfigFixture default_store carriers/dhl/id some ID
     * @magentoConfigFixture default_store carriers/dhl/password some password
     * @magentoConfigFixture default_store carriers/dhl/content_type N
     * @magentoConfigFixture default_store carriers/dhl/nondoc_methods 1,3,4,8,P,Q,E,F,H,J,M,V,Y
     * @magentoConfigFixture default_store carriers/dhl/showmethod' => 1,
     * @magentoConfigFixture default_store carriers/dhl/title DHL Title
     * @magentoConfigFixture default_store carriers/dhl/specificerrmsg dhl error message
     * @magentoConfigFixture default_store carriers/dhl/unit_of_measure K
     * @magentoConfigFixture default_store carriers/dhl/size 1
     * @magentoConfigFixture default_store carriers/dhl/height 1.6
     * @magentoConfigFixture default_store carriers/dhl/width 1.6
     * @magentoConfigFixture default_store carriers/dhl/depth 1.6
     * @magentoConfigFixture default_store carriers/dhl/debug 1
     * @magentoConfigFixture default_store shipping/origin/country_id GB
     */
    public function testCollectRates()
    {
        $this->setNextResponse(__DIR__ . '/../_files/dhl_quote_response.xml');
        $request = $this->createRequest();
        $expectedRates = [
            ['carrier' => 'dhl', 'carrier_title' => 'DHL Title', 'cost' => 45.85, 'method' => 'E', 'price' => 45.85],
            ['carrier' => 'dhl', 'carrier_title' => 'DHL Title', 'cost' => 35.26, 'method' => 'Q', 'price' => 35.26],
            ['carrier' => 'dhl', 'carrier_title' => 'DHL Title', 'cost' => 37.38, 'method' => 'Y', 'price' => 37.38],
            ['carrier' => 'dhl', 'carrier_title' => 'DHL Title', 'cost' => 35.26, 'method' => 'P', 'price' => 35.26]
        ];

        $actualRates = $this->dhlCarrier->collectRates($request)->getAllRates();

        self::assertEquals(count($expectedRates), count($actualRates));
        foreach ($actualRates as $i => $actualRate) {
            $actualRate = $actualRate->getData();
            unset($actualRate['method_title']);
            self::assertEquals($expectedRates[$i], $actualRate);
        }
        $requestXml = $this->httpClient->getLastRequest()->getBody();
        self::assertStringContainsString('<Weight>18.223</Weight>', $requestXml);
        self::assertStringContainsString('<Height>0.63</Height>', $requestXml);
        self::assertStringContainsString('<Width>0.63</Width>', $requestXml);
        self::assertStringContainsString('<Depth>0.63</Depth>', $requestXml);
    }

    /**
     * Tests that quotes request doesn't contain dimensions when it shouldn't.
     *
     * @param string|null $size
     * @param string|null $height
     * @param string|null $width
     * @param string|null $depth
     * @magentoConfigFixture default_store carriers/dhl/active 1
     * @magentoConfigFixture default_store carriers/dhl/type DHL_XML
     * @dataProvider collectRatesWithoutDimensionsDataProvider
     */
    public function testCollectRatesWithoutDimensions(?string $size, ?string $height, ?string $width, ?string $depth)
    {
        $this->setDhlConfig(['size' => $size, 'height' => $height, 'width' => $width, 'depth' => $depth]);

        $request = $this->createRequest();
        $this->dhlCarrier = Bootstrap::getObjectManager()->create(Carrier::class);
        $this->dhlCarrier->collectRates($request)->getAllRates();

        $requestXml = $this->httpClient->getLastRequest()->getBody();
        $this->assertStringNotContainsString('<Width>', $requestXml);
        $this->assertStringNotContainsString('<Height>', $requestXml);
        $this->assertStringNotContainsString('<Depth>', $requestXml);

        $this->config->reinit();
    }

    /**
     * Test get carriers rates if has HttpException.
     *
     * @magentoConfigFixture default_store carriers/dhl/active 1
     * @magentoConfigFixture default_store carriers/dhl/type DHL_XML
     *
     */
    public function testGetRatesWithHttpException(): void
    {
        $this->setDhlConfig(['showmethod' => 1]);
        $deferredResponse = $this->getMockBuilder(HttpResponseDeferredInterface::class)
            ->onlyMethods(['get'])
            ->getMockForAbstractClass();
        $exception = new HttpException('Exception message');
        $deferredResponse->method('get')->willThrowException($exception);
        $this->httpClient->setDeferredResponseMock($deferredResponse);
        $request = $this->createRequest();
        $this->dhlCarrier = Bootstrap::getObjectManager()->create(Carrier::class);
        $resultRate = $this->dhlCarrier->collectRates($request)->getAllRates()[0];
        $error = Bootstrap::getObjectManager()->get(Error::class);
        $error->setCarrier('dhl');
        $error->setCarrierTitle($this->dhlCarrier->getConfigData('title'));
        $error->setErrorMessage($this->dhlCarrier->getConfigData('specificerrmsg'));

        $this->assertEquals($error, $resultRate);
    }

    /**
     * @return array
     */
    public static function collectRatesWithoutDimensionsDataProvider()
    {
        return [
            ['size' => '0', 'height' => '1.1', 'width' => '0.6', 'depth' => '0.7'],
            ['size' => '1', 'height' => '', 'width' => '', 'depth' => ''],
            ['size' => null, 'height' => '1.1', 'width' => '0.6', 'depth' => '0.7'],
            ['size' => '1', 'height' => '1', 'width' => '', 'depth' => ''],
            ['size' => null, 'height' => null, 'width' => null, 'depth' => null],
        ];
    }

    /**
     * Sets DHL config value.
     *
     * @param array $params
     * @return void
     */
    private function setDhlConfig(array $params)
    {
        foreach ($params as $name => $val) {
            if ($val !== null) {
                $this->config->setValue(
                    'carriers/dhl/' . $name,
                    $val,
                    ScopeInterface::SCOPE_STORE
                );
            }
        }
    }

    /**
     * Tests that the free rate is returned when sending a quotes request
     *
     * @param array $addRequestData
     * @param bool $freeShippingExpects
     * @magentoConfigFixture default_store carriers/dhl/active 1
     * @magentoConfigFixture default_store carriers/dhl/type DHL_XML
     * @magentoConfigFixture default_store carriers/dhl/id some ID
     * @magentoConfigFixture default_store carriers/dhl/shipment_days Mon,Tue,Wed,Thu,Fri,Sat
     * @magentoConfigFixture default_store carriers/dhl/intl_shipment_days Mon,Tue,Wed,Thu,Fri,Sat
     * @magentoConfigFixture default_store carriers/dhl/allowed_methods IE
     * @magentoConfigFixture default_store carriers/dhl/international_service IE
     * @magentoConfigFixture default_store carriers/dhl/gateway_xml_url https://xmlpi-ea.dhl.com/XMLShippingServlet
     * @magentoConfigFixture default_store carriers/dhl/id some ID
     * @magentoConfigFixture default_store carriers/dhl/password some password
     * @magentoConfigFixture default_store carriers/dhl/content_type N
     * @magentoConfigFixture default_store carriers/dhl/nondoc_methods 1,3,4,8,P,Q,E,F,H,J,M,V,Y
     * @magentoConfigFixture default_store carriers/dhl/showmethod' => 1,
     * @magentoConfigFixture default_store carriers/dhl/title DHL Title
     * @magentoConfigFixture default_store carriers/dhl/specificerrmsg dhl error message
     * @magentoConfigFixture default_store carriers/dhl/unit_of_measure K
     * @magentoConfigFixture default_store carriers/dhl/size 1
     * @magentoConfigFixture default_store carriers/dhl/height 1.6
     * @magentoConfigFixture default_store carriers/dhl/width 1.6
     * @magentoConfigFixture default_store carriers/dhl/depth 1.6
     * @magentoConfigFixture default_store carriers/dhl/debug 1
     * @magentoConfigFixture default_store carriers/dhl/free_method_nondoc P
     * @magentoConfigFixture default_store carriers/dhl/free_shipping_enable 1
     * @magentoConfigFixture default_store carriers/dhl/free_shipping_subtotal 25
     * @magentoConfigFixture default_store shipping/origin/country_id GB
     * @magentoAppIsolation enabled
     * @dataProvider collectRatesWithFreeShippingDataProvider
     */
    public function testCollectRatesWithFreeShipping(array $addRequestData, bool $freeShippingExpects): void
    {
        $this->setNextResponse(__DIR__ . '/../_files/dhl_quote_response.xml');
        $request = $this->createRequest($addRequestData);

        $actualRates = $this->dhlCarrier->collectRates($request)->getAllRates();
        $freeRateExists = false;
        foreach ($actualRates as $actualRate) {
            $actualRate = $actualRate->getData();
            if ($actualRate['method'] === 'P' && (float)$actualRate['price'] === 0.0) {
                $freeRateExists = true;
                break;
            }
        }

        self::assertEquals($freeShippingExpects, $freeRateExists);
    }

    /**
     * @return array
     */
    public static function collectRatesWithFreeShippingDataProvider(): array
    {
        return [
            [
                ['package_value' => 25, 'package_value_with_discount' => 22],
                false
            ],
            [
                ['package_value' => 25, 'package_value_with_discount' => 25],
                true
            ],
            [
                ['package_value' => 28, 'package_value_with_discount' => 25],
                true
            ],
        ];
    }

    /**
     * Returns request data.
     *
     * @return array
     */
    private function getRequestData(): array
    {
        return [
            'dest_country_id' => 'DE',
            'dest_region_id' => '82',
            'dest_region_code' => 'BER',
            'dest_street' => 'Turmstraße 17',
            'dest_city' => 'Berlin',
            'dest_postcode' => '10559',
            'dest_postal' => '10559',
            'package_value' => '5',
            'package_value_with_discount' => '5',
            'package_weight' => '8.2657',
            'package_qty' => '1',
            'package_physical_value' => '5',
            'free_method_weight' => '5',
            'store_id' => '1',
            'website_id' => '1',
            'free_shipping' => '0',
            'limit_carrier' => null,
            'base_subtotal_incl_tax' => '5',
            'orig_country_id' => 'US',
            'orig_region_id' => '12',
            'orig_city' => 'Fremont',
            'orig_postcode' => '94538',
            'dhl_id' => 'MAGEN_8501',
            'dhl_password' => 'QR2GO1U74X',
            'dhl_account' => '799909537',
            'dhl_api_key' => 'ab01cD2eF3gH4j',
            'dhl_api_secret' => 'A!1bC@3dE#4fG$5h',
            'dhl_shipping_intl_key' => '54233F2B2C4E5C4B4C5E5A59565530554B405641475D5659',
            'girth' => null,
            'height' => null,
            'length' => null,
            'width' => null,
            'weight' => 1,
            'dhl_shipment_type' => 'P',
            'dhl_duitable' => 0,
            'dhl_duty_payment_type' => 'R',
            'dhl_content_desc' => 'Big Box',
            'limit_method' => 'IE',
            'ship_date' => '2014-01-09',
            'action' => 'RateEstimate',
            'all_items' => [],
        ];
    }

    /**
     * Set next response content from file
     *
     * @param string $file
     */
    private function setNextResponse(string $file): void
    {
        //phpcs:disable Magento2.Functions.DiscouragedFunction
        $response = new Response(
            200,
            [],
            file_get_contents($file)
        );
        //phpcs:enable Magento2.Functions.DiscouragedFunction
        $this->httpClient->nextResponses(
            array_fill(0, Carrier::UNAVAILABLE_DATE_LOOK_FORWARD + 1, $response)
        );
    }

    /**
     * Create Rate Request
     *
     * @param array $addRequestData
     * @return RateRequest
     */
    private function createRequest(array $addRequestData = []): RateRequest
    {
        $requestData = $this->getRequestData();
        if (!empty($addRequestData)) {
            $requestData = array_merge($requestData, $addRequestData);
        }

        return Bootstrap::getObjectManager()->create(RateRequest::class, ['data' => $requestData]);
    }

    /**
     * Tests that valid rates are returned when sending a quotes request.
     *
     * @magentoConfigFixture default_store carriers/dhl/active 1
     * @magentoConfigFixture default_store carriers/dhl/type DHL_REST
     * @magentoConfigFixture default_store carriers/dhl/api_key some KEY
     * @magentoConfigFixture default_store carriers/dhl/shipment_days Mon,Tue,Wed,Thu,Fri,Sat
     * @magentoConfigFixture default_store carriers/dhl/intl_shipment_days Mon,Tue,Wed,Thu,Fri,Sat
     * @magentoConfigFixture default_store carriers/dhl/allowed_methods IE
     * @magentoConfigFixture default_store carriers/dhl/international_service IE
     * @magentoConfigFixture default_store carriers/dhl/gateway_rest_url https://express.api.dhl.com/mydhlapi
     * @magentoConfigFixture default_store carriers/dhl/api_key some KEY
     * @magentoConfigFixture default_store carriers/dhl/api_secret some secret
     * @magentoConfigFixture default_store carriers/dhl/content_type N
     * @magentoConfigFixture default_store carriers/dhl/nondoc_methods 1,3,4,8,P,Q,E,F,H,J,M,V,Y
     * @magentoConfigFixture default_store carriers/dhl/showmethod' => 1,
     * @magentoConfigFixture default_store carriers/dhl/title DHL Title
     * @magentoConfigFixture default_store carriers/dhl/specificerrmsg dhl error message
     * @magentoConfigFixture default_store carriers/dhl/unit_of_measure K
     * @magentoConfigFixture default_store carriers/dhl/size 1
     * @magentoConfigFixture default_store carriers/dhl/height 1.6
     * @magentoConfigFixture default_store carriers/dhl/width 1.6
     * @magentoConfigFixture default_store carriers/dhl/length 1.6
     * @magentoConfigFixture default_store carriers/dhl/debug 1
     * @magentoConfigFixture default_store shipping/origin/country_id GB
     */
    public function testCollectRestRates()
    {
        $this->setNextResponse(__DIR__ . '/../_files/dhl_quote_response.json');
        $request = $this->createRequest();
        $expectedRates = [
            [
                'carrier' => 'dhl',
                'carrier_title' => 'DHL Title',
                'price' => 4810.92,
                'method' => 'P',
                'cost' => 4810.92
            ],
            [
                'carrier' => 'dhl',
                'carrier_title' => 'DHL Title',
                'price' => 5980.74,
                'method' => 'Q',
                'cost' => 5980.74
            ]
        ];

        $actualRates = $this->dhlCarrier->collectRates($request)->getAllRates();

        self::assertEquals(count($expectedRates), count($actualRates));
        foreach ($actualRates as $i => $actualRate) {
            $actualRate = $actualRate->getData();
            unset($actualRate['method_title']);
            self::assertEquals($expectedRates[$i], $actualRate);
        }
        $requestRest = $this->httpClient->getLastRequest()->getBody();
        self::assertStringContainsString('"weight": 18', $requestRest);
        self::assertStringContainsString('"height": 1.181', $requestRest);
        self::assertStringContainsString('"width": 1.181', $requestRest);
        self::assertStringContainsString('"length": 1.181', $requestRest);
    }

    /**
     * Test sending shipping requests.
     *
     * @magentoConfigFixture default_store carriers/dhl/api_key some KEY
     * @magentoConfigFixture default_store carriers/dhl/type DHL_REST
     * @magentoConfigFixture default_store carriers/dhl/api_secret some secret
     * @magentoConfigFixture default_store carriers/dhl/account 998765432
     * @magentoConfigFixture default_store carriers/dhl/gateway_rest_url https://express.api.dhl.com/mydhlapi
     * @magentoConfigFixture default_store carriers/dhl/content_type N
     * @magentoConfigFixture default_store carriers/dhl/nondoc_methods 1,3,4,8,P,Q,E,F,H,J,M,V,Y
     * @magentoConfigFixture default_store carriers/dhl/unit_of_measure C
     * @param string $origCountryId
     * @param string $destCountryId
     * @param string $shipperPostalCode
     * @param string $recipientPostalCode
     * @param string $shipperCity
     * @param string $recipientCity
     * @return void
     * @dataProvider requestToRestShipmentDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testRequestRestToShip(
        string $origCountryId,
        string $destCountryId,
        string $shipperPostalCode,
        string $recipientPostalCode,
        string $shipperCity,
        string $recipientCity
    ): void {
        $this->config->setValue(
            'shipping/origin/country_id',
            $origCountryId,
            'store',
            null
        );
        $content = file_get_contents(__DIR__ . '/../_files/dhl_shipping_response.json');
        //phpcs:disable Magento2.Functions.DiscouragedFunction
        $this->httpClient->nextResponses(
            [
                new Response(
                    200,
                    [],
                    $content
                )
            ]
        );
        $productName = 'item_name';

        //phpcs:enable Magento2.Functions.DiscouragedFunction
        $request = new Request(
            [
                'packages' => [
                    'package' => [
                        'params' => [
                            'width' => '2',
                            'length' => '2',
                            'height' => '2',
                            'dimension_units' => 'IN',
                            'weight_units' => 'L',
                            'weight' => '220',
                            'customs_value' => '100.00',
                            'container' => Carrier::DHL_CONTENT_TYPE_NON_DOC,
                        ],
                        'items' => [
                            'item1' => [
                                'name' => $productName,
                                'qty' => 1,
                                'weight' => '100',
                                'price' => '100.00',
                            ],
                        ],
                    ],
                ],
                'orig_country_id' => $origCountryId,
                'dest_country_id' => $destCountryId,
                'shipper_address_country_code' => $origCountryId,
                'recipient_address_country_code' => $destCountryId,
                'package_weight' => '100',
                'free_method_weight' => '100',
                'recipient_address_street_1' => $recipientCity,
                'shipper_address_street_1' => $shipperCity,
                'order_shipment' => new DataObject(
                    [
                        'order' => new DataObject(
                            [
                                'subtotal' => '10.00'
                            ]
                        )
                    ]
                )
            ]
        );

        //Generating labels
        $labels = $this->dhlCarrier->requestToShipment($request);
        $this->assertNotEmpty($labels);
        $this->assertNotEmpty($labels->getInfo());
        $request = $this->httpClient->getLastRequest()->getBody();
        $requestElement = json_decode($request, true);
        $requestElement['plannedShippingDateAndTime'] = 'currentTime';
        $requestElement['productCode'] = 'P';
        $requestElement['customerDetails']['shipperDetails']['postalAddress']['postalCode'] = $shipperPostalCode;
        $requestElement['customerDetails']['shipperDetails']['postalAddress']['cityName'] = $shipperCity;
        $requestElement['customerDetails']['shipperDetails']['contactInformation']['phone'] = '1234567890';
        $requestElement['customerDetails']['shipperDetails']['contactInformation']['companyName'] = 'demo store';
        $requestElement['customerDetails']['shipperDetails']['contactInformation']['fullName'] = 'admin admin';

        $requestElement['customerDetails']['receiverDetails']['postalAddress']['postalCode'] = $recipientPostalCode;
        $requestElement['customerDetails']['receiverDetails']['postalAddress']['cityName'] = $recipientCity;
        $requestElement['customerDetails']['receiverDetails']['contactInformation']['phone'] = '1234567890';
        $requestElement['customerDetails']['receiverDetails']['contactInformation']['companyName'] = 'store new';
        $requestElement['customerDetails']['receiverDetails']['contactInformation']['fullName'] = 'John Doe';

        $requestElement['content']['exportDeclaration']['invoice']['number'] = '123';
        $requestElement['content']['exportDeclaration']['invoice']['date'] = 'currentDate';

        $actualRequest = json_encode($requestElement);
        $expectedLabelRequest = $this->getExpectedLabelRequestRest(
            $origCountryId,
            $destCountryId
        );

        $this->assertJsonStringEqualsJsonString($expectedLabelRequest, $actualRequest);
    }

    /**
     * Cases with different countries.
     *
     * @return array
     */
    public static function requestToRestShipmentDataProvider(): array
    {
        return [
            [
                'US', 'CA', '90034', 'G1A 0A8', 'los angeles', 'quebec'
            ]
        ];
    }

    /**
     * Generate expected labels request REST.
     *
     * @param string $origCountryId
     * @param string $destCountryId
     * @return string
     */
    private function getExpectedLabelRequestRest(
        string $origCountryId,
        string $destCountryId
    ): string {
        $requestRestPath = $origCountryId == $destCountryId
            ? '/../_files/domestic_shipment_request.json'
            : '/../_files/shipment_request.json';

        $expectedRequestElement = json_decode(file_get_contents(__DIR__ . $requestRestPath), true);
        $expectedRequestElement['plannedShippingDateAndTime'] = 'currentTime';
        $expectedRequestElement['content']['exportDeclaration']['invoice']['date'] = 'currentDate';
        $expectedRequestElement['customerDetails']['receiverDetails']['postalAddress']['countryCode'] = $destCountryId;
        $expectedRequestElement['customerDetails']['shipperDetails']['postalAddress']['countryCode'] = $origCountryId;

        return json_encode($expectedRequestElement);
    }

    /**
     * Test sending tracking REST API requests.
     *
     * @magentoConfigFixture default_store carriers/dhl/api_key customerKey
     * @magentoConfigFixture default_store carriers/dhl/api_secret CustomerSecret
     * @magentoConfigFixture default_store carriers/dhl/type DHL_REST
     * @param string[] $trackingNumbers
     * @param string $responseRest
     * @param array $expectedTrackingData
     * @param string $expectedRequestRest
     * @dataProvider trackingRestDataProvider
     */
    public function testGetRestTracking(
        $trackingNumbers,
        string $responseRest,
        $expectedTrackingData,
        string $expectedRequestRest = ''
    ) {
        $this->httpClient->nextResponses([new Response(200, [], $responseRest)]);
        $trackingResult = $this->dhlCarrier->getTracking(implode(',', $trackingNumbers));
        $this->assertRestTrackingResult($expectedTrackingData, $trackingResult->getAllTrackings());
        if ($expectedRequestRest !== '') {
            $expectedRequestRest = json_decode($expectedRequestRest, true);

            $lastRequest = $this->httpClient->getLastRequest();
            $lastRequestUrl = $lastRequest->getUrl();
            $parsedUrl = parse_url($lastRequestUrl);
            $queryString = $parsedUrl['query'] ?? '';
            parse_str($queryString, $actualParams);

            $this->assertEquals(
                $expectedRequestRest['params']['shipmentTrackingNumber'],
                explode(',', $actualParams['shipmentTrackingNumber'])
            );
            $this->assertEquals($expectedRequestRest['headers'], $lastRequest->getHeaders());
        }
    }

    /**
     * Get tracking data provider
     *
     * @return array
     */
    public static function trackingRestDataProvider() : array
    {
        // phpcs:disable Magento2.Functions.DiscouragedFunction
        $expectedMultiShipRequestRest = file_get_contents(__DIR__ . '/../_files/TrackingRequest_MultipleShipment.json');
        $multiShipResponseRest = file_get_contents(__DIR__ . '/../_files/TrackingResponse_MultipleShipment.json');
        $expectedSingleShipRequestRest = file_get_contents(__DIR__ . '/../_files/TrackingRequest_SingleShipment.json');
        $singleShipResponseRest = file_get_contents(__DIR__ . '/../_files/TrackingResponse_SingleShipment.json');
        $singleNoDataResponseRest = file_get_contents(__DIR__ . '/../_files/SingleTrackResponse-no-data-found.json');
        //phpcs:enable Magento2.Functions.DiscouragedFunction
        $expectedTrackingDataA = [
            'carrier' => 'dhl',
            'carrier_title' => 'DHL',
            'tracking' => 2725476530,
            'service' => 'Shipment',
            'progressdetail' => [],
            'weight' => '222 LB',
        ];
        $expectedTrackingDataB = [
            'carrier' => 'dhl',
            'carrier_title' => 'DHL',
            'tracking' => 5539315121,
            'service' => 'Shipment',
            'progressdetail' => [],
            'weight' => '222 LB',
        ];
        $expectedTrackingDataC = [
            'carrier' => 'dhl',
            'carrier_title' => 'DHL',
            'tracking' => '1234567892',
            'error_message' => __('Unable to retrieve tracking'),
        ];

        return [
            'multi-Ship' => [
                ['2725476530', '5539315121'],
                $multiShipResponseRest,
                [$expectedTrackingDataA, $expectedTrackingDataB],
                $expectedMultiShipRequestRest
            ],
            'single-Ship' => [
                ['2725476530'],
                $singleShipResponseRest,
                [$expectedTrackingDataA],
                $expectedSingleShipRequestRest
            ],
            'single-Ship-no-data' => [['1234567892'], $singleNoDataResponseRest, [$expectedTrackingDataC]]
        ];
    }

    /**
     * Assert tracking REST API
     *
     * @param array|null $expectedTrackingData
     * @param Status[]|null $trackingResults
     * @return void
     */
    private function assertRestTrackingResult($expectedTrackingData, $trackingResults): void
    {
        if (null === $expectedTrackingData) {
            $this->assertNull($trackingResults);
        } else {
            $ctr = 0;
            foreach ($trackingResults as $trackingResult) {
                $this->assertEquals($expectedTrackingData[$ctr++], $trackingResult->getData());
            }
        }
    }
}
