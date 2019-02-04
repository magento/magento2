<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Dhl\Model;

use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Simplexml\Element;
use Magento\Shipping\Model\Tracking\Result\Error;
use Magento\Shipping\Model\Tracking\Result\Status;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class CarrierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Dhl\Model\Carrier
     */
    private $dhlCarrier;

    /**
     * @var ZendClient|MockObject
     */
    private $httpClientMock;

    /**
     * @var \Zend_Http_Response|MockObject
     */
    private $httpResponseMock;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->dhlCarrier = $objectManager->create(
            \Magento\Dhl\Model\Carrier::class,
            ['httpClientFactory' => $this->getHttpClientFactory()]
        );
    }

    /**
     * @magentoConfigFixture default_store carriers/dhl/id CustomerSiteID
     * @magentoConfigFixture default_store carriers/dhl/password CustomerPassword
     * @param string[] $trackingNumbers
     * @param string $responseXml
     * @param $expectedTrackingData
     * @param string $expectedRequestXml
     * @dataProvider getTrackingDataProvider
     */
    public function testGetTracking(
        $trackingNumbers,
        string $responseXml,
        $expectedTrackingData,
        string $expectedRequestXml = ''
    ) {
        $this->httpResponseMock->method('getBody')
            ->willReturn($responseXml);
        $trackingResult = $this->dhlCarrier->getTracking($trackingNumbers);
        $this->assertTrackingResult($expectedTrackingData, $trackingResult->getAllTrackings());
        if ($expectedRequestXml !== '') {
            $method = new \ReflectionMethod($this->httpClientMock, '_prepareBody');
            $method->setAccessible(true);
            $requestXml = $method->invoke($this->httpClientMock);
            $this->assertRequest($expectedRequestXml, $requestXml);
        }
    }

    /**
     * Get tracking data provider
     * @return array
     */
    public function getTrackingDataProvider() : array
    {
        $expectedMultiAWBRequestXml = file_get_contents(__DIR__ . '/../_files/TrackingRequest_MultipleAWB.xml');
        $multiAWBResponseXml = file_get_contents(__DIR__ . '/../_files/TrackingResponse_MultipleAWB.xml');
        $expectedSingleAWBRequestXml = file_get_contents(__DIR__ . '/../_files/TrackingRequest_SingleAWB.xml');
        $singleAWBResponseXml = file_get_contents(__DIR__ . '/../_files/TrackingResponse_SingleAWB.xml');
        $singleNoDataResponseXml = file_get_contents(__DIR__ . '/../_files/SingleknownTrackResponse-no-data-found.xml');
        $failedResponseXml = file_get_contents(__DIR__ . '/../_files/Track-res-XML-Parse-Err.xml');
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
     * Get mocked Http Client Factory
     *
     * @return MockObject
     */
    private function getHttpClientFactory(): MockObject
    {
        $this->httpResponseMock = $this->getMockBuilder(\Zend_Http_Response::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->httpClientMock = $this->getMockBuilder(ZendClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['request'])
            ->getMock();
        $this->httpClientMock->method('request')
            ->willReturn($this->httpResponseMock);
        /** @var ZendClientFactory|MockObject $httpClientFactoryMock */
        $httpClientFactoryMock = $this->getMockBuilder(ZendClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $httpClientFactoryMock->method('create')
            ->willReturn($this->httpClientMock);

        return $httpClientFactoryMock;
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
}
