<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Dhl\Model;

use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\HTTP\AsyncClient\Response;
use Magento\Framework\HTTP\AsyncClientInterface;
use Magento\Framework\Simplexml\Element;
use Magento\Shipping\Model\Shipment\Request;
use Magento\Shipping\Model\Tracking\Result\Status;
use Magento\TestFramework\HTTP\AsyncClientInterfaceMock;
use Magento\Shipping\Model\Simplexml\Element as ShippingElement;

/**
 * Test for DHL integration.
 */
class CarrierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Dhl\Model\Carrier
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
     * @var string
     */
    private $restoreCountry;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->dhlCarrier = $objectManager->get(\Magento\Dhl\Model\Carrier::class);
        $this->httpClient = $objectManager->get(AsyncClientInterface::class);
        $this->config = $objectManager->get(ReinitableConfigInterface::class);
        $this->restoreCountry = $this->config->getValue('shipping/origin/country_id', 'store', 'default_store');
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
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
     * @dataProvider getTrackingDataProvider
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
     * @SuppressWarnings(PHPMD.LongMethod)
     */
    public function getTrackingDataProvider() : array
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
            'single-AWB-no-data' => [
                ['4781585061'],
                $singleNoDataResponseXml,
                [$expectedTrackingDataD]
            ],
            'failed-response' => [
                ['4781585060-failed'],
                $failedResponseXml,
                [$expectedTrackingDataE]
            ]
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
     * @magentoConfigFixture default_store carriers/dhl/password some password
     * @magentoConfigFixture default_store carriers/dhl/account 1234567890
     * @magentoConfigFixture default_store carriers/dhl/gateway_url https://xmlpi-ea.dhl.com/XMLShippingServlet
     * @magentoConfigFixture default_store carriers/dhl/content_type N
     * @magentoConfigFixture default_store carriers/dhl/nondoc_methods 1,3,4,8,P,Q,E,F,H,J,M,V,Y
     * @magentoConfigFixture default_store carriers/dhl/unit_of_measure C
     * @param string $origCountryId
     * @param string $expectedRegionCode
     * @param string $destCountryId
     * @dataProvider requestToShipmentDataProvider
     */
    public function testRequestToShip(string $origCountryId, string $expectedRegionCode, string $destCountryId): void
    {
        $this->config->setValue(
            'shipping/origin/country_id',
            $origCountryId,
            'store',
            null
        );
        $this->httpClient->nextResponses(
            [
                new Response(
                    200,
                    [],
                    utf8_encode(file_get_contents(__DIR__ . '/../_files/response_shipping_label.xml'))
                )
            ]
        );
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
                                'name' => 'item_name',
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
                'order_shipment' => new DataObject([
                    'order' => new DataObject([
                        'subtotal' => '10.00'
                    ])
                ])
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
        $this->assertXmlStringEqualsXmlString(
            $this->getExpectedLabelRequestXml($origCountryId, $destCountryId, $expectedRegionCode),
            $requestElement->asXML()
        );
    }

    /**
     * Cases with different countries.
     *
     * @return array
     */
    public function requestToShipmentDataProvider(): array
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
            ]
        ];
    }

    /**
     * Generate expected labels request XML.
     *
     * @param string $origCountryId
     * @param string $destCountryId
     * @param string $regionCode
     * @return string
     */
    private function getExpectedLabelRequestXml(string $origCountryId, string $destCountryId, string $regionCode): string
    {
        $countryNames = [
            'US' => 'United States of America',
            'SG' => 'Singapore',
            'GB' => 'United Kingdom',
            'DE' => 'Germany',
        ];
        $requestXmlPath = $origCountryId == $destCountryId
            ? '/../_files/domestic_shipment_request.xml'
            : '/../_files/shipment_request.xml';

        $expectedRequestElement = new ShippingElement(file_get_contents(__DIR__ . $requestXmlPath));

        $expectedRequestElement->Consignee->CountryCode = $destCountryId;
        $expectedRequestElement->Consignee->CountryName = $countryNames[$destCountryId];
        $expectedRequestElement->Shipper->CountryCode = $origCountryId;
        $expectedRequestElement->Shipper->CountryName = $countryNames[$origCountryId];
        $expectedRequestElement->RegionCode = $regionCode;

        return $expectedRequestElement->asXML();
    }
}
