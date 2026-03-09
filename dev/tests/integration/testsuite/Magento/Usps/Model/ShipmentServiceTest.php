<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Usps\Model;

use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\HTTP\AsyncClient\HttpException;
use Magento\Framework\HTTP\AsyncClient\HttpResponseDeferredInterface;
use Magento\Framework\HTTP\AsyncClient\Response;
use Magento\Framework\HTTP\AsyncClientInterface;
use Magento\Quote\Api\GuestCouponManagementInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\HTTP\AsyncClientInterfaceMock;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test for USPS integration.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShipmentServiceTest extends TestCase
{
    use MockCreationTrait;

    private const DEFAULT_GATEWAY_DEV_END_POINT = 'https://apis-tem.usps.com/';
    private const RESERVED_ORDER_ID = 'usps_test_quote';
    private const FREE_SHIPPING_COUPON_CODE = 'IMPHBR852R61';
    private const PRODUCT_1 = 'simple-249';
    private const PRODUCT_2 = 'simple-156';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Carrier
     */
    private $carrier;

    /**
     * @var AsyncClientInterfaceMock
     */
    private $httpClient;

    /**
     * @var GuestCouponManagementInterface
     */
    private $management;

    /**
     * @var GetQuoteByReservedOrderId
     */
    private $getQuoteByReservedOrderId;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $getMaskedIdByQuoteId;

    /**
     * @var ReinitableConfigInterface
     */
    private $config;

    /**
     * @var string[]
     */
    private $logs = [];

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var UspsAuth|MockObject
     */
    private $uspsAuthMock;

    /**
     * @var \ReflectionClass|string
     */
    private $_defaultGatewayUrl;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->httpClient = $this->objectManager->get(AsyncClientInterface::class);
        $this->management = $this->objectManager->get(GuestCouponManagementInterface::class);
        $this->getQuoteByReservedOrderId = $this->objectManager->get(GetQuoteByReservedOrderId::class);
        $this->getMaskedIdByQuoteId = $this->objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->config = Bootstrap::getObjectManager()->get(ReinitableConfigInterface::class);
        $this->logs = [];
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->loggerMock->method('debug')
            ->willReturnCallback(
                function (string $message) {
                    $this->logs[] = $message;
                }
            );
        $this->uspsAuthMock = $this->getMockBuilder(UspsAuth::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->carrier = Bootstrap::getObjectManager()->create(Carrier::class, ['logger' => $this->loggerMock,
            'uspsAuth' => $this->uspsAuthMock]);
        $carrierModelMock = $this->createMock(Carrier::class);
        $carrierModelReflection = new \ReflectionClass(Carrier::class);
        $_defaultGatewayUrl = $carrierModelReflection->getProperty('_defaultRestUrl');
        $this->_defaultGatewayUrl = $_defaultGatewayUrl->getValue($carrierModelMock);
    }

    /**
     * Test collecting rates from the provider.
     *
     * @param string $shippingMethod
     * @param string $methodTitle
     * @param float $price
     * @return void
     * @magentoConfigFixture default_store carriers/usps/rest_allowed_methods LIBRARY_MAIL_MACHINABLE_5-DIGIT,MEDIA_MAIL_MACHINABLE_5-DIGIT,USPS_GROUND_ADVANTAGE_MACHINABLE_SINGLE-PIECE,USPS_GROUND_ADVANTAGE_MACHINABLE_CUBIC_NON-SOFT_PACK_TIER_1,USPS_GROUND_ADVANTAGE_MACHINABLE_CUBIC_SOFT_PACK_TIER_1,PRIORITY_MAIL_MACHINABLE_SINGLE-PIECE,PRIORITY_MAIL_FLAT_RATE_ENVELOPE,PRIORITY_MAIL_MACHINABLE_MEDIUM_FLAT_RATE_BOX,PRIORITY_MAIL_MACHINABLE_LARGE_FLAT_RATE_BOX,PRIORITY_MAIL_MACHINABLE_SMALL_FLAT_RATE_BOX,PRIORITY_MAIL_PADDED_FLAT_RATE_ENVELOPE,PRIORITY_MAIL_LEGAL_FLAT_RATE_ENVELOPE,PRIORITY_MAIL_EXPRESS_MACHINABLE_SINGLE-PIECE,PRIORITY_MAIL_EXPRESS_FLAT_RATE_ENVELOPE,PRIORITY_MAIL_EXPRESS_LEGAL_FLAT_RATE_ENVELOPE,PRIORITY_MAIL_EXPRESS_LEGAL_FLAT_RATE_ENVELOPE_HOLIDAY_DELIVERY,PRIORITY_MAIL_EXPRESS_PADDED_FLAT_RATE_ENVELOPE,FIRST-CLASS_PACKAGE_INTERNATIONAL_SERVICE_MACHINABLE_ISC_SINGLE-PIECE,PRIORITY_MAIL_INTERNATIONAL_ISC_SINGLE-PIECE,PRIORITY_MAIL_EXPRESS_INTERNATIONAL_ISC_SINGLE-PIECE
     * @magentoConfigFixture default_store carriers/usps/showmethod 1
     * @magentoConfigFixture current_store carriers/usps/usps_type USPS_REST
     * @magentoConfigFixture default_store carriers/usps/debug 1
     * @magentoConfigFixture default_store carriers/usps/client_id test_user
     * @magentoConfigFixture default_store carriers/usps/client_secret test_password
     * @magentoConfigFixture default_store carriers/usps/mode 0
     * @magentoConfigFixture default_store carriers/usps/active 1
     * @magentoConfigFixture default_store shipping/origin/country_id US
     * @magentoConfigFixture default_store shipping/origin/postcode 90034
     * @magentoConfigFixture default_store carriers/usps/machinable true
     */
    #[DataProvider('collectRatesDataProvider')]
    public function testCollectRates(string $shippingMethod, string $methodTitle, float $price): void
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $responseBody = file_get_contents(__DIR__ .'/../Fixtures/success_usps_response_rates.json');
        $this->httpClient->nextResponses([new Response(200, [], $responseBody)]);
        /** @var RateRequest $request */
        $request = Bootstrap::getObjectManager()->create(
            RateRequest::class,
            [
                'data' => [
                    'orig_country_id' => 'US',
                    'orig_postcode' => '90034',
                    'dest_country_id' => 'US',
                    'dest_region_id' => '12',
                    'dest_region_code' => 'CA',
                    'dest_street' => 'main st1',
                    'dest_city' => 'Los Angeles',
                    'dest_postcode' => '90032',
                    'package_value' => '5',
                    'package_value_with_discount' => '5',
                    'package_weight' => '4.2657',
                    'package_qty' => '1',
                    'package_physical_value' => '5',
                    'free_method_weight' => '5',
                    'store_id' => '1',
                    'website_id' => '1',
                    'free_shipping' => '0',
                    'limit_carrier' => 'null',
                    'base_subtotal_incl_tax' => '5',
                    'country_id' => 'US',
                    'region_id' => '12',
                    'city' => 'Culver City',
                    'postcode' => '90034',
                    'usps_container' => 'VARIABLE',
                    'usps_size' => 'REGULAR',
                    'girth' => null,
                    'height' => null,
                    'length' => null,
                    'width' => null,
                ]
            ]
        );
        $this->uspsAuthMock->method('getAccessToken')
            ->willReturn('test_eyJraWQiOiJ5MmRGRGY3eDdFQkFsQXlob0RLYld2ejlNaWxHTzlnaEJZS2c3OV9zRko4IiwidHlw');
        $rates = $this->carrier->collectRates($request)->getAllRates();
        $httpRequest = $this->httpClient->getLastRequest();
        $this->assertNotEmpty($httpRequest);

        $result = array_filter($rates, function ($rate) use ($methodTitle) {
            return $rate->getMethodTitle() === $methodTitle;
        });

        // Convert the result to an indexed array
        $result = array_values($result);

        $this->assertEquals($price, $result[0]->getPrice());
        $this->assertEquals($methodTitle, $result[0]->getMethodTitle());
        $this->assertStringContainsString($shippingMethod, strtoupper($result[0]->getMethod()));
    }

    /**
     * Test collecting rates only for available services.
     *
     * @return void
     * @magentoConfigFixture default_store carriers/usps/rest_allowed_methods LIBRARY_MAIL_MACHINABLE_5-DIGIT,MEDIA_MAIL_MACHINABLE_5-DIGIT,USPS_GROUND_ADVANTAGE_MACHINABLE_SINGLE-PIECE,USPS_GROUND_ADVANTAGE_MACHINABLE_CUBIC_NON-SOFT_PACK_TIER_1,USPS_GROUND_ADVANTAGE_MACHINABLE_CUBIC_SOFT_PACK_TIER_1,PRIORITY_MAIL_MACHINABLE_SINGLE-PIECE,PRIORITY_MAIL_FLAT_RATE_ENVELOPE,PRIORITY_MAIL_MACHINABLE_MEDIUM_FLAT_RATE_BOX,PRIORITY_MAIL_MACHINABLE_LARGE_FLAT_RATE_BOX,PRIORITY_MAIL_MACHINABLE_SMALL_FLAT_RATE_BOX,PRIORITY_MAIL_PADDED_FLAT_RATE_ENVELOPE,PRIORITY_MAIL_LEGAL_FLAT_RATE_ENVELOPE,PRIORITY_MAIL_EXPRESS_MACHINABLE_SINGLE-PIECE,PRIORITY_MAIL_EXPRESS_FLAT_RATE_ENVELOPE,PRIORITY_MAIL_EXPRESS_LEGAL_FLAT_RATE_ENVELOPE,PRIORITY_MAIL_EXPRESS_LEGAL_FLAT_RATE_ENVELOPE_HOLIDAY_DELIVERY,PRIORITY_MAIL_EXPRESS_PADDED_FLAT_RATE_ENVELOPE,FIRST-CLASS_PACKAGE_INTERNATIONAL_SERVICE_MACHINABLE_ISC_SINGLE-PIECE,PRIORITY_MAIL_INTERNATIONAL_ISC_SINGLE-PIECE,PRIORITY_MAIL_EXPRESS_INTERNATIONAL_ISC_SINGLE-PIECE
     * @magentoConfigFixture default_store carriers/usps/showmethod 1
     * @magentoConfigFixture current_store carriers/usps/usps_type USPS_REST
     * @magentoConfigFixture default_store carriers/usps/debug 1
     * @magentoConfigFixture default_store carriers/usps/client_id test_user
     * @magentoConfigFixture default_store carriers/usps/client_secret test_password
     * @magentoConfigFixture default_store carriers/usps/mode 0
     * @magentoConfigFixture default_store carriers/usps/active 1
     * @magentoConfigFixture default_store shipping/origin/country_id US
     * @magentoConfigFixture default_store shipping/origin/postcode 90034
     * @magentoConfigFixture default_store carriers/usps/machinable true
     */
    public function testCollectUnavailableRates(): void
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $responseBody = file_get_contents(__DIR__ .'/../Fixtures/success_usps_response_rates.json');
        $this->uspsAuthMock->method('getAccessToken')
            ->willReturn('test_eyJraWQiOiJ5MmRGRGY3eDdFQkFsQXlob0RLYld2ejlNaWxHTzlnaEJZS2c3OV9zRko4IiwidHlw');
        $this->httpClient->nextResponses([new Response(200, [], $responseBody)]);
        /** @var RateRequest $request */
        $request = Bootstrap::getObjectManager()->create(
            RateRequest::class,
            [
                'data' => [
                    'dest_country_id' => 'CA',
                    'dest_postcode' => 'M5V 3G5',
                    'dest_country_name' => 'Canada',
                    'package_value' => '3.2568',
                    'package_value_with_discount' => '5',
                    'package_weight' => '5',
                    'package_qty' => '1',
                    'package_physical_value' => '5',
                    'free_method_weight' => '5',
                    'store_id' => '1',
                    'website_id' => '1',
                    'free_shipping' => '0',
                    'limit_carrier' => 'null',
                    'base_subtotal_incl_tax' => '5',
                    'orig_country_id' => 'US',
                    'country_id' => 'US',
                    'region_id' => '12',
                    'city' => 'Culver City',
                    'postcode' => '90034',
                    'usps_container' => 'VARIABLE',
                    'usps_size' => 'REGULAR',
                    'girth' => null,
                    'height' => null,
                    'length' => null,
                    'width' => null,
                ]
            ]
        );
        $rates = $this->carrier->collectRates($request);
        $this->assertCount(17, $rates->getAllRates());
    }

    /**
     * Test get carriers rates if has HttpException.
     *
     * @magentoConfigFixture default_store carriers/usps/rest_allowed_methods LIBRARY_MAIL_MACHINABLE_5-DIGIT,MEDIA_MAIL_MACHINABLE_5-DIGIT,USPS_GROUND_ADVANTAGE_MACHINABLE_SINGLE-PIECE,USPS_GROUND_ADVANTAGE_MACHINABLE_CUBIC_NON-SOFT_PACK_TIER_1,USPS_GROUND_ADVANTAGE_MACHINABLE_CUBIC_SOFT_PACK_TIER_1,PRIORITY_MAIL_MACHINABLE_SINGLE-PIECE,PRIORITY_MAIL_FLAT_RATE_ENVELOPE,PRIORITY_MAIL_MACHINABLE_MEDIUM_FLAT_RATE_BOX,PRIORITY_MAIL_MACHINABLE_LARGE_FLAT_RATE_BOX,PRIORITY_MAIL_MACHINABLE_SMALL_FLAT_RATE_BOX,PRIORITY_MAIL_PADDED_FLAT_RATE_ENVELOPE,PRIORITY_MAIL_LEGAL_FLAT_RATE_ENVELOPE,PRIORITY_MAIL_EXPRESS_MACHINABLE_SINGLE-PIECE,PRIORITY_MAIL_EXPRESS_FLAT_RATE_ENVELOPE,PRIORITY_MAIL_EXPRESS_LEGAL_FLAT_RATE_ENVELOPE,PRIORITY_MAIL_EXPRESS_LEGAL_FLAT_RATE_ENVELOPE_HOLIDAY_DELIVERY,PRIORITY_MAIL_EXPRESS_PADDED_FLAT_RATE_ENVELOPE,FIRST-CLASS_PACKAGE_INTERNATIONAL_SERVICE_MACHINABLE_ISC_SINGLE-PIECE,PRIORITY_MAIL_INTERNATIONAL_ISC_SINGLE-PIECE,PRIORITY_MAIL_EXPRESS_INTERNATIONAL_ISC_SINGLE-PIECE
     * @magentoConfigFixture default_store carriers/usps/showmethod 1
     * @magentoConfigFixture current_store carriers/usps/usps_type USPS_REST
     * @magentoConfigFixture default_store carriers/usps/debug 1
     * @magentoConfigFixture default_store carriers/usps/client_id test_user
     * @magentoConfigFixture default_store carriers/usps/client_secret test_password
     * @magentoConfigFixture default_store carriers/usps/mode 0
     * @magentoConfigFixture default_store carriers/usps/active 1
     * @magentoConfigFixture default_store shipping/origin/country_id US
     * @magentoConfigFixture default_store shipping/origin/postcode 90034
     * @magentoConfigFixture default_store carriers/usps/machinable true
     * @magentoConfigFixture default_store carriers/usps/price_type COMMERCIAL
     */
    public function testGetRatesWithHttpException(): void
    {
        $deferredResponse = $this->createPartialMockWithReflection(
            HttpResponseDeferredInterface::class,
            ['get', 'cancel', 'isCancelled', 'isDone']
        );
        $exception = new HttpException('Exception message');
        $deferredResponse->method('get')->willThrowException($exception);
        $this->httpClient->setDeferredResponseMock($deferredResponse);
        /** @var RateRequest $request */
        $request = Bootstrap::getObjectManager()->create(
            RateRequest::class,
            [
                'data' => [
                    'dest_country_id' => 'US',
                    'dest_region_code' => 'NY',
                    'dest_street' => 'main st1',
                    'dest_city' => 'New York',
                    'dest_postcode' => '10029',
                    'package_value' => '5',
                    'package_value_with_discount' => '5',
                    'package_weight' => '4.2657',
                    'package_qty' => '1',
                    'package_physical_value' => '5',
                    'free_method_weight' => '5',
                    'store_id' => '1',
                    'website_id' => '1',
                    'free_shipping' => '0',
                    'limit_carrier' => 'null',
                    'base_subtotal_incl_tax' => '5',
                    'orig_country_id' => 'US',
                    'country_id' => 'US',
                    'region_id' => '12',
                    'city' => 'Culver City',
                    'postcode' => '90034',
                    'usps_container' => 'VARIABLE',
                    'usps_size' => 'REGULAR',
                    'girth' => null,
                    'height' => null,
                    'length' => null,
                    'width' => null,
                ]
            ]
        );
        $rates = $this->carrier->collectRates($request);
        $resultRate = $rates->getAllRates()[0];
        $error = Bootstrap::getObjectManager()->get(Error::class);
        $error->setCarrier('usps');
        $error->setCarrierTitle($this->carrier->getConfigData('title'));
        $error->setErrorMessage($this->carrier->getConfigData('specificerrmsg'));
        $this->assertEquals($error, $resultRate);
    }

    /**
     * Test shipping options url for development site
     *
     * @magentoConfigFixture default_store carriers/usps/mode 0
     * @magentoConfigFixture default_store carriers/usps/usps_type USPS_REST
     */
    public function testGetShippingOptionsUrlDevelopmentMode()
    {
        $this->assertEquals(
            self::DEFAULT_GATEWAY_DEV_END_POINT . ShipmentService::SHIPMENT_REQUEST_END_POINT,
            $this->carrier->getUrl(ShipmentService::SHIPMENT_REQUEST_END_POINT)
        );
    }

    /**
     * Test shipping options url for live site
     *
     * @magentoConfigFixture default_store carriers/usps/mode 1
     * @magentoConfigFixture default_store carriers/usps/usps_type USPS_REST
     */
    public function testGetShippingOptionsUrlLiveMode()
    {
        $this->assertEquals(
            $this->_defaultGatewayUrl . ShipmentService::SHIPMENT_REQUEST_END_POINT,
            $this->carrier->getUrl(ShipmentService::SHIPMENT_REQUEST_END_POINT)
        );
    }

    /**
     * Test domestic shipping options url for development site
     *
     * @magentoConfigFixture default_store carriers/usps/mode 0
     * @magentoConfigFixture default_store carriers/usps/usps_type USPS_REST
     */
    public function testGetDomShippingLabelUrlDevelopmentMode()
    {
        $this->assertEquals(
            self::DEFAULT_GATEWAY_DEV_END_POINT . ShipmentService::DOMESTIC_SHIPMENT_LABEL_REQUEST_END_POINT,
            $this->carrier->getUrl(ShipmentService::DOMESTIC_SHIPMENT_LABEL_REQUEST_END_POINT)
        );
    }

    /**
     * Test domestic shipping options url for live site
     *
     * @magentoConfigFixture default_store carriers/usps/mode 1
     * @magentoConfigFixture default_store carriers/usps/usps_type USPS_REST
     */
    public function testGetDomShippingLabelUrlLiveMode()
    {
        $this->assertEquals(
            $this->_defaultGatewayUrl . ShipmentService::DOMESTIC_SHIPMENT_LABEL_REQUEST_END_POINT,
            $this->carrier->getUrl(ShipmentService::DOMESTIC_SHIPMENT_LABEL_REQUEST_END_POINT)
        );
    }

    /**
     * Test international shipping options url for Development site
     *
     * @magentoConfigFixture default_store carriers/usps/mode 0
     * @magentoConfigFixture default_store carriers/usps/usps_type USPS_REST
     */
    public function testGetIntlShippingLabelUrlDevelopmentMode()
    {
        $this->assertEquals(
            self::DEFAULT_GATEWAY_DEV_END_POINT . ShipmentService::INTERNATIONAL_SHIPMENT_LABEL_REQUEST_END_POINT,
            $this->carrier->getUrl(ShipmentService::INTERNATIONAL_SHIPMENT_LABEL_REQUEST_END_POINT)
        );
    }

    /**
     * Test international shipping options label url for live site
     *
     * @magentoConfigFixture default_store carriers/usps/mode 1
     * @magentoConfigFixture default_store carriers/usps/usps_type USPS_REST
     */
    public function testGetIntlShippingLabelUrlLiveMode()
    {
        $this->assertEquals(
            $this->_defaultGatewayUrl . ShipmentService::INTERNATIONAL_SHIPMENT_LABEL_REQUEST_END_POINT,
            $this->carrier->getUrl(ShipmentService::INTERNATIONAL_SHIPMENT_LABEL_REQUEST_END_POINT)
        );
    }

    /**
     * Get list of rates variations
     *
     * @return array
     */
    public static function collectRatesDataProvider()
    {
        return [
            ['LIBRARY_MAIL_MACHINABLE_5-DIGIT', 'Library Mail', 3.1 ],
            ['MEDIA_MAIL_MACHINABLE_5-DIGIT', 'Media Mail', 3.26],
            ['PRIORITY_MAIL_MACHINABLE_SINGLE-PIECE', 'Priority Mail', 10.21 ],
            ['PRIORITY_MAIL_EXPRESS_MACHINABLE_SINGLE-PIECE', 'Priority Mail Express', 45.45 ],
            ['USPS_GROUND_ADVANTAGE_MACHINABLE_SINGLE-PIECE', 'USPS Ground Advantage', 8.99 ]
        ];
    }
}
