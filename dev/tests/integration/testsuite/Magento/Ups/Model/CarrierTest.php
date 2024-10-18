<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ups\Model;

use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\HTTP\AsyncClient\HttpException;
use Magento\Framework\HTTP\AsyncClient\HttpResponseDeferredInterface;
use Magento\Framework\HTTP\AsyncClient\Response;
use Magento\Framework\HTTP\AsyncClientInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateRequestFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Shipping\Model\Shipment\Request;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\HTTP\AsyncClientInterfaceMock;
use Magento\Ups\Model\UpsAuth;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Integration tests for Carrier model class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CarrierTest extends TestCase
{
    /**
     * @var Carrier
     */
    private $carrier;

    /**
     * @var AsyncClientInterfaceMock
     */
    private $httpClient;

    /**
     * @var ReinitableConfigInterface
     */
    private $config;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var string[]
     */
    private $logs = [];

    /**
     * @var \Magento\Ups\Model\UpsAuth|MockObject
     */
    private $upsAuthMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->httpClient = Bootstrap::getObjectManager()->get(AsyncClientInterface::class);
        $this->config = Bootstrap::getObjectManager()->get(ReinitableConfigInterface::class);
        $this->logs = [];
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->loggerMock->method('debug')
            ->willReturnCallback(
                function (string $message) {
                    $this->logs[] = $message;
                }
            );
        $this->upsAuthMock = $this->getMockBuilder(UpsAuth::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->carrier = Bootstrap::getObjectManager()->create(Carrier::class, ['logger' => $this->loggerMock,
            'upsAuth' => $this->upsAuthMock]);
    }

    /**
     * @return void
     */
    public function testGetShipAcceptUrl()
    {
        $this->assertEquals('https://wwwcie.ups.com/ups.app/xml/ShipAccept', $this->carrier->getShipAcceptUrl());
    }

    /**
     * Test ship accept url for live site
     *
     * @magentoConfigFixture current_store carriers/ups/is_account_live 1
     */
    public function testGetShipAcceptUrlLive()
    {
        $this->assertEquals('https://onlinetools.ups.com/ups.app/xml/ShipAccept', $this->carrier->getShipAcceptUrl());
    }

    /**
     * @return void
     */
    public function testGetShipConfirmUrl()
    {
        if ($this->carrier->getConfigData('type') == 'UPS_XML') {
            $this->assertEquals('https://wwwcie.ups.com/ups.app/xml/ShipConfirm', $this->carrier->getShipConfirmUrl());
        } else {
            $this->assertEquals('https://wwwcie.ups.com/api/shipments/v1/ship', $this->carrier->getShipConfirmUrl());
        }
    }

    /**
     * Test ship accept url for live site
     *
     * @magentoConfigFixture current_store carriers/ups/is_account_live 1
     */
    public function testGetShipConfirmUrlLive()
    {
        if ($this->carrier->getConfigData('type') == 'UPS_XML') {
            $this->assertEquals(
                'https://onlinetools.ups.com/ups.app/xml/ShipConfirm',
                $this->carrier->getShipConfirmUrl()
            );
        } else {
            $this->assertEquals(
                'https://onlinetools.ups.com/api/shipments/v1/ship',
                $this->carrier->getShipConfirmUrl()
            );
        }
    }

    /**
     * Collect rates for UPS Ground method.
     *
     * @magentoConfigFixture current_store carriers/ups/active 1
     * @magentoConfigFixture current_store carriers/ups/type UPS_REST
     * @magentoConfigFixture current_store carriers/ups/allowed_methods 03
     * @magentoConfigFixture current_store carriers/ups/free_method 03
     * @magentoConfigFixture default_store carriers/ups/shipper_number 12345
     * @magentoConfigFixture default_store carriers/ups/origin_shipment Shipments Originating in the United States
     * @magentoConfigFixture default_store carriers/ups/username user
     * @magentoConfigFixture default_store carriers/ups/password pass
     * @magentoConfigFixture default_store carriers/ups/debug 1
     * @magentoConfigFixture default_store currency/options/allow USD,EUR
     * @magentoConfigFixture default_store currency/options/base USD
     */
    public function testCollectFreeRates()
    {
        $request = Bootstrap::getObjectManager()->create(
            RateRequest::class,
            [
                'data' => [
                    'dest_country' => 'US',
                    'dest_postal' => '90001',
                    'package_weight' => '1',
                    'package_qty' => '1',
                    'free_method_weight' => '5',
                    'product' => '11',
                    'action' => 'Rate',
                    'unit_measure' => 'KGS',
                    'free_shipping' => '1',
                    'base_currency' => new DataObject(['code' => 'USD'])
                ]
            ]
        );

        //phpcs:disable Magento2.Functions.DiscouragedFunction
        $this->httpClient->nextResponses(
            [
                new Response(
                    200,
                    [],
                    file_get_contents(__DIR__ . "/../_files/ups_rates_response_option5.json")
                )
            ]
        );

        $this->upsAuthMock->method('getAccessToken')
            ->willReturn('abcdefghijklmnop');
        $rates = $this->carrier->collectRates($request)->getAllRates();
        $this->assertEquals('115.01', $rates[0]->getPrice());
        $this->assertEquals('03', $rates[0]->getMethod());
    }

    /**
     * Test processing rates response.
     *
     * @param int $negotiable
     * @param int $tax
     * @param int $responseId
     * @param string $method
     * @param float $price
     * @return void
     * @dataProvider collectRatesDataProvider
     * @magentoConfigFixture default_store shipping/origin/country_id GB
     * @magentoConfigFixture default_store carriers/ups/active 1
     * @magentoConfigFixture current_store carriers/ups/type UPS_REST
     * @magentoConfigFixture default_store carriers/ups/shipper_number 12345
     * @magentoConfigFixture default_store carriers/ups/origin_shipment Shipments Originating in the European Union
     * @magentoConfigFixture default_store carriers/ups/username user
     * @magentoConfigFixture default_store carriers/ups/password pass
     * @magentoConfigFixture default_store carriers/ups/debug 1
     * @magentoConfigFixture default_store currency/options/allow GBP,USD,EUR
     * @magentoConfigFixture default_store currency/options/base GBP
     */
    public function testCollectRates(int $negotiable, int $tax, int $responseId, string $method, float $price): void
    {
        $request = Bootstrap::getObjectManager()->create(
            RateRequest::class,
            [
                'data' => [
                    'dest_country' => 'US',
                    'dest_postal' => '90001',
                    'product' => '11',
                    'action' => 'Rate',
                    'unit_measure' => 'KGS',
                    'base_currency' => new DataObject(['code' => 'USD'])
                ]
            ]
        );
        //phpcs:disable Magento2.Functions.DiscouragedFunction
        $this->httpClient->nextResponses(
            [
                new Response(
                    200,
                    [],
                    file_get_contents(__DIR__ . "/../_files/ups_rates_response_option$responseId.json")
                )
            ]
        );
        //phpcs:enable Magento2.Functions.DiscouragedFunction
        $this->config->setValue('carriers/ups/negotiated_active', $negotiable, 'store');
        $this->config->setValue('carriers/ups/include_taxes', $tax, 'store');
        $this->config->setValue('carriers/ups/allowed_methods', $method, 'store');

        $this->upsAuthMock->method('getAccessToken')
            ->willReturn('abcdefghijklmnop');
        $rates = $this->carrier->collectRates($request)->getAllRates();
        $this->assertEquals($price, $rates[0]->getPrice());
        $this->assertEquals($method, $rates[0]->getMethod());

        $requestFound = false;
        foreach ($this->logs as $log) {
            if (mb_stripos($log, 'RateRequest') &&
                mb_stripos($log, 'RateResponse')
            ) {
                $requestFound = true;
                break;
            }
        }
        $this->assertTrue($requestFound);
    }

    /**
     * Test collect rates function without any allowed methods set.
     *
     * @return void
     * @magentoConfigFixture default_store shipping/origin/country_id GB
     * @magentoConfigFixture default_store carriers/ups/active 1
     * @magentoConfigFixture default_store carriers/ups/type UPS_REST
     * @magentoConfigFixture default_store carriers/ups/shipper_number 12345
     * @magentoConfigFixture default_store carriers/ups/origin_shipment Shipments Originating in the European Union
     * @magentoConfigFixture default_store carriers/ups/username user
     * @magentoConfigFixture default_store carriers/ups/password pass
     * @magentoConfigFixture default_store carriers/ups/debug 1
     * @magentoConfigFixture default_store currency/options/allow GBP,USD,EUR
     * @magentoConfigFixture default_store currency/options/base GBP
     */
    public function testCollectRatesWithoutAnyAllowedMethods(): void
    {
        $request = Bootstrap::getObjectManager()->create(
            RateRequest::class,
            [
                'data' => [
                    'dest_country' => 'GB',
                    'dest_postal' => '01104',
                    'product' => '11',
                    'action' => 'Rate',
                    'unit_measure' => 'KGS',
                    'base_currency' => new DataObject(['code' => 'GBP'])
                ]
            ]
        );
        $this->config->setValue('carriers/ups/allowed_methods', '', 'store');
        $this->upsAuthMock->method('getAccessToken')
            ->willReturn('abcdefghijklmnop');
        $rates = $this->carrier->collectRates($request)->getAllRates();
        $this->assertInstanceOf(Error::class, current($rates));
        $this->assertEquals(current($rates)['carrier_title'], $this->carrier->getConfigData('title'));
        $this->assertEquals(current($rates)['error_message'], $this->carrier->getConfigData('specificerrmsg'));
    }

    /**
     * Get list of rates variations
     *
     * @return array
     */
    public static function collectRatesDataProvider()
    {
        return [
            [0, 0, 1, '03', 136.09 ],
            [0, 1, 2, '03', 136.09 ],
            [1, 0, 3, '03', 92.12 ],
            [1, 1, 4, '03', 92.12 ],
            [0, 0, 1, '13', 330.35 ],
            [0, 1, 2, '13', 331.79 ],
            [1, 0, 3, '13', 178.70 ],
            [1, 1, 4, '13', 178.70 ],
        ];
    }

    /**
     * Test shipping a package.
     *
     *
     * @magentoConfigFixture default_store shipping/origin/country_id GB
     * @magentoConfigFixture default_store carriers/ups/active 1
     * @magentoConfigFixture default_store carriers/ups/type UPS_REST
     * @magentoConfigFixture default_store carriers/ups/shipper_number 12345
     * @magentoConfigFixture default_store carriers/ups/origin_shipment Shipments Originating in the European Union
     * @magentoConfigFixture default_store carriers/ups/username user
     * @magentoConfigFixture default_store carriers/ups/password pass
     * @magentoConfigFixture default_store currency/options/allow GBP,USD,EUR
     * @magentoConfigFixture default_store currency/options/base GBP
     * @magentoConfigFixture default_store carriers/ups/min_package_weight 2
     * @magentoConfigFixture default_store carriers/ups/debug 1
     */
    public function testRequestToShipment(): void
    {
        //phpcs:disable Magento2.Functions.DiscouragedFunction
        $expectedShipmentRequest = str_replace(
            "\n",
            "",
            file_get_contents(__DIR__ . '/../_files/ShipmentConfirmRequest.json')
        );
        $shipmentResponse = file_get_contents(__DIR__ . '/../_files/ShipmentConfirmResponse.json');
        //phpcs:enable Magento2.Functions.DiscouragedFunction
        $this->httpClient->nextResponses(
            [
                new Response(200, [], $shipmentResponse)
            ]
        );
        $this->httpClient->clearRequests();

        $request = new Request(
            [
                'packages' => [
                    'package' => [
                        'params' => [
                            'width' => '3',
                            'length' => '3',
                            'height' => '3',
                            'dimension_units' => 'INCH',
                            'weight_units' => 'POUND',
                            'weight' => '0.454000000001',
                            'customs_value' => '10.00',
                            'container' => 'Small Express Box',
                        ],
                        'items' => [
                            'item1' => [
                                'name' => 'item_name',
                            ],
                        ],
                    ],
                    'package2' => [
                        'params' => [
                            'width' => '4',
                            'length' => '4',
                            'height' => '4',
                            'dimension_units' => 'INCH',
                            'weight_units' => 'POUND',
                            'weight' => '0.55',
                            'customs_value' => '20.00',
                            'container' => 'Large Express Box',
                            'delivery_confirmation' => 0,
                        ],
                        'items' => [
                            'item2' => [
                                'name' => 'item2_name',
                            ],
                        ],
                    ],
                ]
            ]
        );
        $request->setRecipientAddressCountryCode('UK');

        $result = $this->carrier->requestToShipment($request);

        $requests = $this->httpClient->getRequests();
        $this->assertNotEmpty($requests);
        $shipmentRequest = $requests[0]->getBody();
        $this->assertEquals(
            $expectedShipmentRequest,
            $shipmentRequest
        );
        $this->assertEmpty($result->getErrors());
        $this->assertNotEmpty($result->getInfo());
        $this->assertEquals(
            '1ZXXXXXXXXXXXXXXXX',
            $result->getInfo()[0]['tracking_number'],
            'Tracking Number must match.'
        );
        $this->httpClient->clearRequests();
    }

    /**
     * Test get carriers rates if has HttpException.
     *
     * @magentoConfigFixture default_store shipping/origin/country_id GB
     * @magentoConfigFixture default_store carriers/ups/active 1
     * @magentoConfigFixture default_store carriers/ups/type UPS_REST
     * @magentoConfigFixture default_store carriers/ups/shipper_number 12345
     * @magentoConfigFixture default_store carriers/ups/origin_shipment Shipments Originating in the European Union
     * @magentoConfigFixture default_store carriers/ups/username user
     * @magentoConfigFixture default_store carriers/ups/password pass
     * @magentoConfigFixture default_store carriers/ups/debug 1
     * @magentoConfigFixture default_store currency/options/allow GBP,USD,EUR
     * @magentoConfigFixture default_store currency/options/base GBP
     */
    public function testGetRatesWithHttpException(): void
    {
        $deferredResponse = $this->getMockBuilder(HttpResponseDeferredInterface::class)
            ->onlyMethods(['get'])
            ->getMockForAbstractClass();
        $exception = new HttpException('Exception message');
        $deferredResponse->method('get')->willThrowException($exception);
        $this->httpClient->setDeferredResponseMock($deferredResponse);
        $request = Bootstrap::getObjectManager()->create(
            RateRequest::class,
            [
                'data' => [
                    'dest_country' => 'GB',
                    'dest_postal' => '01105',
                    'product' => '11',
                    'action' => 'Rate',
                    'unit_measure' => 'KGS',
                    'base_currency' => new DataObject(['code' => 'GBP'])
                ]
            ]
        );
        $resultRate = $this->carrier->collectRates($request)->getAllRates()[0];
        $error = Bootstrap::getObjectManager()->get(Error::class);
        $error->setCarrier('ups');
        $error->setCarrierTitle($this->carrier->getConfigData('title'));
        $error->setErrorMessage($this->carrier->getConfigData('specificerrmsg'));

        $this->assertEquals($error, $resultRate);
    }
}
