<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Usps\Test\Unit\Model;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\HTTP\AsyncClient\HttpResponseDeferredInterface;
use Magento\Framework\HTTP\AsyncClient\Response;
use Magento\Framework\HTTP\AsyncClientInterface;
use Magento\Framework\Measure\Length;
use Magento\Framework\Measure\Weight;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Helper\Carrier as CarrierHelper;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Shipping\Model\Rate\Result\ProxyDeferredFactory;
use Magento\Usps\Model\Carrier;
use Magento\Usps\Model\ShipmentService;
use Magento\Usps\Model\ShippingMethodManager;
use Magento\Usps\Model\UspsPaymentAuthToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit test for ShipmentService class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ShipmentServiceTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var ShipmentService
     */
    private $shipmentService;

    /**
     * @var ResultFactory|MockObject
     */
    private $rateFactoryMock;

    /**
     * @var MethodFactory|MockObject
     */
    private $rateMethodFactoryMock;

    /**
     * @var ErrorFactory|MockObject
     */
    private $rateErrorFactoryMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private $productCollectionFactoryMock;

    /**
     * @var CarrierHelper|MockObject
     */
    private $carrierHelperMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var UspsPaymentAuthToken|MockObject
     */
    private $uspsPaymentAuthTokenMock;

    /**
     * @var ShippingMethodManager|MockObject
     */
    private $shippingMethodManagerMock;

    /**
     * @var AsyncClientInterface|MockObject
     */
    private $httpClientMock;

    /**
     * @var ProxyDeferredFactory|MockObject
     */
    private $proxyDeferredFactoryMock;

    /**
     * @var Carrier|MockObject
     */
    private $carrierModelMock;

    /**
     * @var \ReflectionClass|string
     */
    private $_defaultGatewayUrl;

    /**
     * Set up test dependencies
     */
    protected function setUp(): void
    {
        $this->rateFactoryMock = $this->createMock(ResultFactory::class);
        $this->rateMethodFactoryMock = $this->createMock(MethodFactory::class);
        $this->rateErrorFactoryMock = $this->createMock(ErrorFactory::class);
        $this->productCollectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->carrierHelperMock = $this->createMock(CarrierHelper::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->uspsPaymentAuthTokenMock = $this->createMock(UspsPaymentAuthToken::class);
        $this->shippingMethodManagerMock = $this->createMock(ShippingMethodManager::class);
        $this->httpClientMock = $this->createMock(AsyncClientInterface::class);
        $this->proxyDeferredFactoryMock = $this->createMock(ProxyDeferredFactory::class);
        $this->carrierModelMock = $this->createMock(Carrier::class);
        $carrierModelReflection = new \ReflectionClass(Carrier::class);
        $_defaultGatewayUrl = $carrierModelReflection->getProperty('_defaultRestUrl');
        $this->_defaultGatewayUrl = $_defaultGatewayUrl->getValue($this->carrierModelMock);
        $this->shipmentService = new ShipmentService(
            $this->rateFactoryMock,
            $this->rateMethodFactoryMock,
            $this->rateErrorFactoryMock,
            $this->productCollectionFactoryMock,
            $this->carrierHelperMock,
            $this->loggerMock,
            $this->uspsPaymentAuthTokenMock,
            $this->shippingMethodManagerMock,
            $this->httpClientMock,
            $this->proxyDeferredFactoryMock
        );

        $this->shipmentService->setCarrierModel($this->carrierModelMock);
    }

    /**
     * Test replaceSpaceWithUnderscore method with spaces
     */
    public function testReplaceSpaceWithUnderscore(): void
    {
        $input = 'Priority Mail Express';
        $expected = 'Priority_Mail_Express';
        $result = $this->shipmentService->replaceSpaceWithUnderscore($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test replaceSpaceWithUnderscore with empty string
     */
    public function testReplaceSpaceWithUnderscoreEmptyString(): void
    {
        $input = '';
        $expected = '';
        $result = $this->shipmentService->replaceSpaceWithUnderscore($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test replaceSpaceWithUnderscore with multiple consecutive spaces
     */
    public function testReplaceSpaceWithUnderscoreMultipleSpaces(): void
    {
        $input = 'Priority  Mail   Express';
        $expected = 'Priority__Mail___Express';
        $result = $this->shipmentService->replaceSpaceWithUnderscore($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test _parseZip method with valid ZIP+4 format
     */
    public function testParseZipWithValidZipPlusFour(): void
    {
        $zipString = '12345-6789';
        $result = $this->shipmentService->_parseZip($zipString);

        $this->assertEquals(['12345', '6789'], $result);
    }

    /**
     * Test _parseZip method with 5-digit ZIP
     */
    public function testParseZipWithFiveDigitZip(): void
    {
        $zipString = '12345';
        $result = $this->shipmentService->_parseZip($zipString);

        $this->assertEquals(['12345', ''], $result);
    }

    /**
     * Test _parseZip method with invalid format but returnFull=true
     */
    public function testParseZipWithReturnFullInvalidFormat(): void
    {
        $zipString = '123';
        $result = $this->shipmentService->_parseZip($zipString, true);

        $this->assertEquals(['123', ''], $result);
    }

    /**
     * Test _parseZip method with alphanumeric ZIP
     */
    public function testParseZipWithAlphanumericZip(): void
    {
        $zipString = 'A1B2C-3D4E';
        $result = $this->shipmentService->_parseZip($zipString);

        $this->assertEquals(['A1B2C', '3D4E'], $result);
    }

    /**
     * Test _convertPoundOunces method with standard weight
     */
    public function testConvertPoundOunces(): void
    {
        $weightInPounds = 2.5;
        $result = $this->shipmentService->_convertPoundOunces($weightInPounds);

        // 2.5 pounds = 40 ounces = 2 pounds 8 ounces
        $this->assertEquals([2, 8], $result);
    }

    /**
     * Test _convertPoundOunces with zero weight
     */
    public function testConvertPoundOuncesZeroWeight(): void
    {
        $weightInPounds = 0.0;
        $result = $this->shipmentService->_convertPoundOunces($weightInPounds);

        $this->assertEquals([0, 0], $result);
    }

    /**
     * Test _convertPoundOunces with exact pound
     */
    public function testConvertPoundOuncesExactPound(): void
    {
        $weightInPounds = 3.0;
        $result = $this->shipmentService->_convertPoundOunces($weightInPounds);

        $this->assertEquals([3, 0], $result);
    }

    /**
     * Test _convertPoundOunces with fractional ounces that round up
     */
    public function testConvertPoundOuncesFractionalRoundUp(): void
    {
        $weightInPounds = 1.03125; // 16.5 ounces, should round up to 17
        $result = $this->shipmentService->_convertPoundOunces($weightInPounds);

        $this->assertEquals([1, 1], $result);
    }

    /**
     * Test getRestAllowedMethods
     */
    public function testGetRestAllowedMethods(): void
    {
        $allowedMethodsConfig = 'PRIORITY_MAIL,FIRST-CLASS_PACKAGE_SERVICE,USPS_GROUND_ADVANTAGE';
        $expectedCodes = [
            'PRIORITY_MAIL' => 'Priority Mail',
            'FIRST-CLASS_PACKAGE_SERVICE' => 'First-Class Package Service',
            'USPS_GROUND_ADVANTAGE' => 'USPS Ground Advantage'
        ];

        $this->carrierModelMock->expects($this->once())
            ->method('getConfigData')
            ->with('rest_allowed_methods')
            ->willReturn($allowedMethodsConfig);

        $this->carrierModelMock->expects($this->exactly(3))
            ->method('getCode')
            ->willReturnCallback(function ($type, $code) use ($expectedCodes) {
                if ($type === 'rest_method' && isset($expectedCodes[$code])) {
                    return $expectedCodes[$code];
                }
                return null;
            });

        $result = $this->shipmentService->getRestAllowedMethods();

        $this->assertEquals($expectedCodes, $result);
    }

    /**
     * Test getRestAllowedMethods with empty configuration
     */
    public function testGetRestAllowedMethodsEmpty(): void
    {
        $this->carrierModelMock->expects($this->once())
            ->method('getConfigData')
            ->with('rest_allowed_methods')
            ->willReturn('');
        $result = $this->shipmentService->getRestAllowedMethods();
        $this->assertEquals(['' => null], $result);
    }

    /**
     * Test handleErrorResponse with multiple errors
     */
    public function testHandleErrorResponseWithMultipleErrors(): void
    {
        $response = [
            'error' => [
                'errors' => [
                    ['detail' => 'Invalid ZIP code'],
                    ['detail' => 'Invalid weight']
                ]
            ]
        ];

        $result = $this->shipmentService->handleErrorResponse($response);
        $expected = ['Invalid ZIP code', 'Invalid weight'];

        $this->assertSame($expected, $result);
    }

    /**
     * Test handleErrorResponse with single error
     */
    public function testHandleErrorResponseSingleError(): void
    {
        $response = [
            'error' => [
                'errors' => [
                    ['detail' => 'Service temporarily unavailable']
                ]
            ]
        ];

        $result = $this->shipmentService->handleErrorResponse($response);
        $expected = ['Service temporarily unavailable'];

        $this->assertEquals($expected, $result);
    }

    /**
     * Test handleErrorResponse with no errors structure
     */
    public function testHandleErrorResponseNoErrorsStructure(): void
    {
        $response = ['success' => true];

        $result = $this->shipmentService->handleErrorResponse($response);

        $this->assertEquals([], $result);
    }

    /**
     * Test handleErrorResponse with malformed error structure
     */
    public function testHandleErrorResponseMalformedStructure(): void
    {
        $response = [
            'error' => [
                'message' => 'General error'
            ]
        ];

        $result = $this->shipmentService->handleErrorResponse($response);

        $this->assertEquals([], $result);
    }

    /**
     * Test preparePackageDimensions with valid dimensions (no conversion needed)
     */
    public function testPreparePackageDimensionsNoConversion(): void
    {
        $request = new DataObject([
            'package_weight' => 5.0
        ]);

        $packageParams = new DataObject([
            'height' => 10,
            'width' => 8,
            'length' => 12,
            'girth' => 30,
            'weight_units' => Weight::POUND,
            'dimension_units' => Length::INCH,
            'girth_dimension_units' => Length::INCH
        ]);

        $result = $this->shipmentService->preparePackageDimensions($request, $packageParams);

        $expected = [
            'height' => 10,
            'width' => 8,
            'length' => 12,
            'girth' => 30,
            'weight' => 5.0
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * Test preparePackageDimensions with unit conversion
     */
    public function testPreparePackageDimensionsWithConversion(): void
    {
        $request = new DataObject([
            'package_weight' => 2.27 // kg
        ]);

        $packageParams = new DataObject([
            'height' => 25.4, // cm
            'width' => 20.32, // cm
            'length' => 30.48, // cm
            'girth' => 76.2, // cm
            'weight_units' => Weight::KILOGRAM,
            'dimension_units' => Length::CENTIMETER,
            'girth_dimension_units' => Length::CENTIMETER
        ]);

        // Mock the carrier helper conversion methods
        $this->carrierHelperMock->expects($this->once())
            ->method('convertMeasureWeight')
            ->with(2.27, Weight::KILOGRAM, Weight::POUND)
            ->willReturn(5.0);

        $this->carrierHelperMock->expects($this->exactly(4))
            ->method('convertMeasureDimension')
            ->willReturnMap([
                [25.4, Length::CENTIMETER, Length::INCH, 10.0],
                [20.32, Length::CENTIMETER, Length::INCH, 8.0],
                [30.48, Length::CENTIMETER, Length::INCH, 12.0],
                [76.2, Length::CENTIMETER, Length::INCH, 30.0]
            ]);

        $result = $this->shipmentService->preparePackageDimensions($request, $packageParams);

        $expected = [
            'height' => 10,
            'width' => 8,
            'length' => 12,
            'girth' => 30,
            'weight' => 5.0
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * Test preparePackageDimensions with measure exception
     */
    public function testPreparePackageDimensionsWithMeasureException(): void
    {
        $request = new DataObject(['package_weight' => 5.0]);
        $packageParams = new DataObject([
            'height' => 10,
            'width' => 'test',
            'length' => 12,
            'girth' => 0,
            'weight_units' => Weight::KILOGRAM,
            'dimension_units' => Length::INCH,
            'girth_dimension_units' => Length::INCH
        ]);

        $this->expectException(LocalizedException::class);

        $this->carrierHelperMock->expects($this->once())
            ->method('convertMeasureWeight')
            ->willThrowException(new
                \Magento\Framework\Measure\Exception\MeasureException(__('Conversion failed')));

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Error converting package dimensions or weight'));

        $result = $this->shipmentService->preparePackageDimensions($request, $packageParams);
        $this->assertInstanceOf(DataObject::class, $result);
    }

    /**
     * Test preparePackageDimensions with generic exception
     */
    public function testPreparePackageDimensionsWithGenericException(): void
    {
        $this->expectException(LocalizedException::class);

        $request = new DataObject(['package_weight' => 5.0]);
        $packageParams = new DataObject([
            'height' => 10,
            'width' => 8,
            'length' => 12,
            'girth' => 0,
            'weight_units' => Weight::KILOGRAM, // Different units to trigger conversion
            'dimension_units' => Length::INCH,
            'girth_dimension_units' => Length::INCH
        ]);

        // Mock carrier helper to throw a generic exception during weight conversion
        $this->carrierHelperMock->expects($this->once())
            ->method('convertMeasureWeight')
            ->willThrowException(new Exception('Unexpected error'));

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($this->stringContains('Unexpected error'));

        $this->shipmentService->preparePackageDimensions($request, $packageParams);
    }

    /**
     * Test setPackageRequest for large package
     */
    public function testSetPackageRequestLargePackage(): void
    {
        $request = new DataObject([
            'shipping_method' => 'Priority Mail'
        ]);
        $request->setUspsSize('LARGE');
        $request->setHeight(15);
        $request->setLength(20);
        $request->setWidth(12);
        $request->setGirth(35);

        $this->carrierModelMock->expects($this->once())
            ->method('getConfigData')
            ->with('container')
            ->willReturn('NONRECTANGULAR');

        $result = $this->shipmentService->setPackageRequest($request);

        $this->assertEquals('LARGE', $result->getPackageSize());
        $this->assertEquals(15, $result->getPackageHeight());
        $this->assertEquals(20, $result->getPackageLength());
        $this->assertEquals(12, $result->getPackageWidth());
        $this->assertEquals(35, $result->getPackageGirth());
    }

    /**
     * Test setPackageRequest for flat rate envelope
     */
    public function testSetPackageRequestFlatRateEnvelope(): void
    {
        $request = new DataObject([
            'shipping_method' => 'Priority Mail Express Flat Rate Envelope'
        ]);

        // Set usps_size to null explicitly to trigger config fallback
        $request->setData('usps_size', null);

        $this->carrierModelMock->expects($this->atLeastOnce())
            ->method('getConfigData')
            ->willReturnCallback(function ($key) {
                $map = [
                    'size' => 'REGULAR',
                    'height' => 1,
                    'length' => 12,
                    'width' => 9
                ];
                return $map[$key] ?? null;
            });

        $this->shippingMethodManagerMock->expects($this->once())
            ->method('getMethodMinDimensions')
            ->with('Priority Mail Express Flat Rate Envelope')
            ->willReturn([
                'height' => 0.5,
                'width' => 9.5,
                'length' => 12.5
            ]);

        $result = $this->shipmentService->setPackageRequest($request);

        $this->assertEquals('REGULAR', $result->getPackageSize());
        $this->assertEquals(0.5, $result->getPackageHeight());
        $this->assertEquals(9.5, $result->getPackageWidth());
        $this->assertEquals(12.5, $result->getPackageLength());
    }

    /**
     * Test setPackageRequest with null request
     */
    public function testSetPackageRequestNullRequest(): void
    {
        $request = null;

        $this->expectException(\Error::class);
        $this->shipmentService->setPackageRequest($request);
    }

    /**
     * Test _prepareDomesticShipmentLabelRestRequest with valid data
     */
    public function testPrepareDomesticShipmentLabelRestRequestValid(): void
    {
        $packageParams = new DataObject([
            'weight_units' => Weight::POUND,
            'dimension_units' => Length::INCH,
            'girth_dimension_units' => Length::INCH,
            'height' => 10,
            'width' => 8,
            'length' => 12,
            'girth' => 0
        ]);

        $request = new DataObject();
        $request->setShippingMethod('PRIORITY_MAIL');
        $request->setPackageWeight(2.5);
        $request->setPackageHeight(10);
        $request->setPackageWidth(8);
        $request->setPackageLength(12);
        $request->setPackageGirth(0);
        $request->setShipperAddressPostalCode('90210-1234');
        $request->setRecipientAddressPostalCode('10001-5678');
        $request->setShipperAddressStreet1('123 Main St');
        $request->setShipperAddressStreet2('Suite 100');
        $request->setShipperAddressCity('Beverly Hills');
        $request->setShipperAddressStateOrProvinceCode('CA');
        $request->setShipperContactPersonFirstName('John');
        $request->setShipperContactPersonLastName('Doe');
        $request->setShipperContactCompanyName('Test Company');
        $request->setRecipientAddressStreet1('456 Broadway');
        $request->setRecipientAddressStreet2('Apt 4B');
        $request->setRecipientAddressCity('New York');
        $request->setRecipientAddressStateOrProvinceCode('NY');
        $request->setRecipientContactPersonFirstName('Jane');
        $request->setRecipientContactPersonLastName('Smith');
        $request->setRecipientContactPhoneNumber('555-123-4567');

        $request->setPackageParams($packageParams);

        $this->shippingMethodManagerMock->expects($this->once())
            ->method('getMethodMailClass')
            ->with('PRIORITY_MAIL')
            ->willReturn('PM');

        $this->shippingMethodManagerMock->expects($this->once())
            ->method('getMethodProcessingCategory')
            ->with('PRIORITY_MAIL')
            ->willReturn('MACHINABLE');

        $this->shippingMethodManagerMock->expects($this->once())
            ->method('getMethodDestinationEntryFacilityType')
            ->with('PRIORITY_MAIL')
            ->willReturn('NONE');

        $this->shippingMethodManagerMock->expects($this->once())
            ->method('getRateIndicator')
            ->with('PRIORITY_MAIL')
            ->willReturn('D0');

        $result = $this->shipmentService->_prepareDomesticShipmentLabelRestRequest($request);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('fromAddress', $result);
        $this->assertArrayHasKey('toAddress', $result);
        $this->assertArrayHasKey('packageDescription', $result);

        // Verify fromAddress
        $this->assertEquals('123 Main St', $result['fromAddress']['streetAddress']);
        $this->assertEquals('Suite 100', $result['fromAddress']['secondaryAddress']);
        $this->assertEquals('Beverly Hills', $result['fromAddress']['city']);
        $this->assertEquals('CA', $result['fromAddress']['state']);
        $this->assertEquals('90210', $result['fromAddress']['ZIPCode']);
        $this->assertEquals('John', $result['fromAddress']['firstName']);
        $this->assertEquals('Doe', $result['fromAddress']['lastName']);
        $this->assertEquals('Test Company', $result['fromAddress']['firm']);

        // Verify toAddress
        $this->assertEquals('456 Broadway', $result['toAddress']['streetAddress']);
        $this->assertEquals('Apt 4B', $result['toAddress']['secondaryAddress']);
        $this->assertEquals('New York', $result['toAddress']['city']);
        $this->assertEquals('NY', $result['toAddress']['state']);
        $this->assertEquals('10001', $result['toAddress']['ZIPCode']);
        $this->assertEquals('Jane', $result['toAddress']['firstName']);
        $this->assertEquals('Smith', $result['toAddress']['lastName']);
        $this->assertEquals('555-123-4567', $result['toAddress']['phone']);

        // Verify packageDescription
        $this->assertEquals(12.0, $result['packageDescription']['length']);
        $this->assertEquals(10.0, $result['packageDescription']['height']);
        $this->assertEquals(8.0, $result['packageDescription']['width']);
        $this->assertEquals(2.5, $result['packageDescription']['weight']);
        $this->assertEquals('PM', $result['packageDescription']['mailClass']);
        $this->assertEquals('MACHINABLE', $result['packageDescription']['processingCategory']);
        $this->assertEquals('NONE', $result['packageDescription']['destinationEntryFacilityType']);
        $this->assertEquals('D0', $result['packageDescription']['rateIndicator']);
    }

    /**
     * Test _prepareDomesticShipmentLabelRestRequest with exception
     */
    public function testPrepareDomesticShipmentLabelRestRequestWithException(): void
    {
        $request = new DataObject([
            'shipping_method' => 'PRIORITY_MAIL',
            'package_params' => new DataObject([
                'weight_units' => Weight::POUND,
                'dimension_units' => Length::INCH,
                'girth_dimension_units' => Length::INCH
            ]),
            'shipper_address_postal_code' => '90210',
            'recipient_address_postal_code' => '10001'
        ]);

        $this->shippingMethodManagerMock->expects($this->once())
            ->method('getMethodMailClass')
            ->with('PRIORITY_MAIL')
            ->willThrowException(new LocalizedException(__('Method not found')));

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with('Method not found');

        $result = $this->shipmentService->_prepareDomesticShipmentLabelRestRequest($request);

        $this->assertInstanceOf(DataObject::class, $result);
        $this->assertNotEmpty($result->getErrors());
    }

    /**
     * Test _prepareIntlShipmentLabelRestRequest with valid data
     */
    public function testPrepareIntlShipmentLabelRestRequestValid(): void
    {
        $request = new DataObject([
            'shipping_method' => 'PRIORITY_MAIL_INTERNATIONAL',
            'package_params' => new DataObject([
                'weight_units' => Weight::POUND,
                'dimension_units' => Length::INCH,
                'girth_dimension_units' => Length::INCH,
                'content_type' => 'MERCHANDISE'
            ]),
            'package_weight' => 3.0,
            'package_height' => 12,
            'package_width' => 10,
            'package_length' => 14,
            'package_girth' => 0,
            'shipper_address_postal_code' => '90210',
            'shipper_address_street1' => '123 Main St',
            'shipper_address_street2' => '',
            'shipper_address_city' => 'Beverly Hills',
            'shipper_address_state_or_province_code' => 'CA',
            'shipper_contact_person_first_name' => 'John',
            'shipper_contact_person_last_name' => 'Doe',
            'shipper_contact_company_name' => 'Test Company',
            'recipient_address_street1' => '456 King St',
            'recipient_address_street2' => '',
            'recipient_address_city' => 'Toronto',
            'recipient_address_state_or_province_code' => 'ON',
            'recipient_address_postal_code' => 'M5V 1J5',
            'recipient_address_country_code' => 'CA',
            'recipient_contact_person_first_name' => 'Jane',
            'recipient_contact_person_last_name' => 'Smith',
            'recipient_contact_phone_number' => '416-123-4567',
            'package_items' => [
                [
                    'qty' => 2,
                    'customs_value' => 25.00,
                    'name' => 'Test Product',
                    'weight' => 1.5,
                    'product_id' => 1
                ]
            ],
            'store_id' => 1
        ]);

        // Mock product collection
        $productMock = $this->createPartialMockWithReflection(
            Product::class,
            ['getId', 'getCountryOfManufacture']
        );
        $productMock->method('getId')->willReturn(1);
        $productMock->method('getCountryOfManufacture')->willReturn('US');

        $collectionMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        $collectionMock->method('addStoreFilter')->willReturnSelf();
        $collectionMock->method('addFieldToFilter')->willReturnSelf();
        $collectionMock->method('addAttributeToSelect')->willReturnSelf();
        $collectionMock->method('getIterator')->willReturn(new \ArrayIterator([$productMock]));

        $this->productCollectionFactoryMock->method('create')->willReturn($collectionMock);

        // Mock carrier helper for weight conversion
        $this->carrierHelperMock->method('convertMeasureWeight')->willReturn(1.0);

        $this->carrierModelMock->method('_getCountryName')
            ->with('CA')
            ->willReturn('Canada');

        $this->carrierModelMock->method('getConfigData')
            ->with('aesitn')
            ->willReturn('NOEEI 30.36');

        $this->setShippingMethodManagerMock();

        $result = $this->shipmentService->_prepareIntlShipmentLabelRestRequest($request);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('fromAddress', $result);
        $this->assertArrayHasKey('toAddress', $result);
        $this->assertArrayHasKey('packageDescription', $result);
        $this->assertArrayHasKey('customsForm', $result);

        // Verify toAddress has international fields
        $this->assertEquals('Canada', $result['toAddress']['country']);
        $this->assertEquals('CA', $result['toAddress']['countryISOAlpha2Code']);

        // Verify customs form
        $this->assertEquals('MERCHANDISE', $result['customsForm']['customsContentType']);
        $this->assertEquals('NOEEI 30.36', $result['customsForm']['AESITN']);
        $this->assertArrayHasKey('contents', $result['customsForm']);
        $this->assertCount(1, $result['customsForm']['contents']);

        $content = $result['customsForm']['contents'][0];
        $this->assertEquals('Test Product', $content['itemDescription']);
        $this->assertEquals(2, $content['itemQuantity']);
        $this->assertEquals(3, $content['itemTotalWeight']); // 2 * 1.5 = 3 pounds
        $this->assertEquals(50.00, $content['itemTotalValue']); // 2 * 25.00 = 50
        $this->assertEquals('US', $content['countryofOrigin']);
    }

    /**
     * Set up the shipping method manager mock
     * @return void
     */
    public function setShippingMethodManagerMock(): void
    {
        $this->shippingMethodManagerMock->expects($this->atLeastOnce())
            ->method('getMethodDestinationEntryFacilityType')
            ->with('PRIORITY_MAIL_INTERNATIONAL')
            ->willReturn('NONE');

        $this->shippingMethodManagerMock->expects($this->atLeastOnce())
            ->method('getMethodMailClass')
            ->with('PRIORITY_MAIL_INTERNATIONAL')
            ->willReturn('PMI');

        $this->shippingMethodManagerMock->expects($this->atLeastOnce())
            ->method('getMethodProcessingCategory')
            ->with('PRIORITY_MAIL_INTERNATIONAL')
            ->willReturn('MACHINABLE');

        $this->shippingMethodManagerMock->expects($this->atLeastOnce())
            ->method('getRateIndicator')
            ->with('PRIORITY_MAIL_INTERNATIONAL')
            ->willReturn('D0');

        $this->shippingMethodManagerMock->expects($this->atLeastOnce())
            ->method('getMethodMinDimensions')
            ->with('PRIORITY_MAIL_INTERNATIONAL')
            ->willReturn(['height' => 1, 'width' => 6, 'length' => 8]);

        $this->shippingMethodManagerMock->expects($this->atLeastOnce())
            ->method('getMethodMaxDimensions')
            ->with('PRIORITY_MAIL_INTERNATIONAL')
            ->willReturn(['height' => 60, 'width' => 60, 'length' => 60]);
    }

    /**
     * Test _prepareIntlShipmentLabelRestRequest with high value exception
     */
    public function testPrepareIntlShipmentLabelRestRequestHighValueException(): void
    {

        $packageParams = new DataObject([
            'content_type' => 'MERCHANDISE',
            'weight_units' => Weight::POUND,
            'dimension_units' => Length::INCH,
            'girth_dimension_units' => Length::INCH,
            'height' => 10,
            'width' => 8,
            'length' => 12,
            'girth' => 30
        ]);

        $request = new DataObject();
        $request->setShippingMethod('PRIORITY_MAIL_INTERNATIONAL');
        $request->setPackageItems([
            [
                'qty' => 1,
                'customs_value' => 3000.00, // Over $2500 limit
                'name' => 'Expensive Product',
                'weight' => 1.0,
                'product_id' => 1
            ]
        ]);
        $request->setStoreId(1);
        $request->setRecipientAddressCountryCode('GB'); // Non-Canada country for AESITN check
        $request->setPackageWeight(5.0);
        $request->setShipperAddressCountryCode('US');
        $request->setShipperAddressPostalCode('90210');
        $request->setShipperAddressStreet1('123 Main St');
        $request->setShipperAddressCity('Beverly Hills');
        $request->setShipperAddressStateOrProvinceCode('CA');
        $request->setShipperContactPersonFirstName('John');
        $request->setShipperContactPersonLastName('Doe');
        $request->setShipperContactCompanyName('Test Company');
        $request->setRecipientAddressStreet1('456 King St');
        $request->setRecipientAddressCity('London');
        $request->setRecipientAddressStateOrProvinceCode('');
        $request->setRecipientAddressPostalCode('SW1A 1AA');
        $request->setRecipientContactPersonFirstName('Jane');
        $request->setRecipientContactPersonLastName('Smith');
        $request->setRecipientContactPhoneNumber('+44-20-1234-5678');

        $request->setPackageParams($packageParams);

        $this->carrierModelMock->method('getConfigData')
            ->with('aesitn')
            ->willReturn(null); // No AESITN configured - this triggers the exception for high values

        $this->carrierModelMock->method('_getCountryName')
            ->with('GB')
            ->willReturn('United Kingdom');

        $productMock = $this->createPartialMockWithReflection(
            Product::class,
            ['getId', 'getCountryOfManufacture']
        );
        $productMock->method('getId')->willReturn(1);
        $productMock->method('getCountryOfManufacture')->willReturn('US');

        $collectionMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        $collectionMock->method('addStoreFilter')->willReturnSelf();
        $collectionMock->method('addFieldToFilter')->willReturnSelf();
        $collectionMock->method('addAttributeToSelect')->willReturnSelf();
        $collectionMock->method('getIterator')->willReturn(new \ArrayIterator([$productMock]));

        $this->productCollectionFactoryMock->method('create')->willReturn($collectionMock);

        // Mock carrier helper for weight conversion
        $this->carrierHelperMock->method('convertMeasureWeight')->willReturn(1.0);

        $this->shippingMethodManagerMock->method('getMethodDestinationEntryFacilityType')->willReturn('NONE');
        $this->shippingMethodManagerMock->method('getMethodMailClass')->willReturn('PMI');
        $this->shippingMethodManagerMock->method('getMethodProcessingCategory')->willReturn('MACHINABLE');
        $this->shippingMethodManagerMock->method('getRateIndicator')->willReturn('D0');
        $this->shippingMethodManagerMock->method('getMethodMinDimensions')->willReturn([]);
        $this->shippingMethodManagerMock->method('getMethodMaxDimensions')->willReturn([]);

        $result = $this->shipmentService->_prepareIntlShipmentLabelRestRequest($request);

        // The method should return a DataObject with errors instead of throwing an exception
        $this->assertInstanceOf(DataObject::class, $result);
        $this->assertNotEmpty($result->getErrors());
        $this->assertStringContainsString('Schedule B Export Codes', implode(' ', $result->getErrors()));
    }

    /**
     * Test _doShipmentRequestRest for domestic shipment success
     */
    public function testDoShipmentRequestRestDomesticSuccess(): void
    {
        $request = new DataObject([
            'recipient_address_country_code' => 'US',
            'shipping_method' => 'PRIORITY_MAIL',
            'package_params' => new DataObject([
                'weight_units' => Weight::POUND,
                'dimension_units' => Length::INCH,
                'girth_dimension_units' => Length::INCH,
                'height' => 10,
                'width' => 8,
                'length' => 12,
                'girth' => 30
            ]),
            'package_weight' => 5.0,
            'shipper_address_postal_code' => '90210',
            'recipient_address_postal_code' => '10001'
        ]);

        $this->carrierModelMock->expects($this->once())
            ->method('_isUSCountry')
            ->with('US')
            ->willReturn(true);

        $this->carrierModelMock->expects($this->once())
            ->method('getOauthAccessRequest')
            ->willReturn('test_access_token');
        $this->carrierModelMock->expects($this->exactly(2))
            ->method('getUrl')
            ->willReturnOnConsecutiveCalls(
                $this->_defaultGatewayUrl,
                $this->_defaultGatewayUrl
            );

        $this->uspsPaymentAuthTokenMock->expects($this->once())
            ->method('getPaymentAuthToken')
            ->willReturn('test_payment_token');

        // Mock successful HTTP response
        $responseMock = $this->createMock(HttpResponseDeferredInterface::class);
        $httpResponseMock = $this->createMock(Response::class);

        $httpResponseMock->method('getStatusCode')->willReturn(200);
        $httpResponseMock->method('getBody')->willReturn(json_encode([
            'labelImage' => base64_encode('fake_label_content'),
            'trackingNumber' => '1234567890123456'
        ]));

        $responseMock->method('get')->willReturn($httpResponseMock);
        $this->httpClientMock->method('request')->willReturn($responseMock);

        $this->shippingMethodManagerMock->method('getMethodMailClass')->willReturn('PM');
        $this->shippingMethodManagerMock->method('getMethodProcessingCategory')->willReturn('MACHINABLE');
        $this->shippingMethodManagerMock->method('getMethodDestinationEntryFacilityType')->willReturn('NONE');
        $this->shippingMethodManagerMock->method('getRateIndicator')->willReturn('D0');

        $result = $this->shipmentService->_doShipmentRequestRest($request);

        $this->assertInstanceOf(DataObject::class, $result);
        $this->assertEquals('fake_label_content', $result->getShippingLabelContent());
        $this->assertEquals('1234567890123456', $result->getTrackingNumber());
        $this->assertNotNull($result->getGatewayResponse());
    }

    /**
     * Test _doShipmentRequestRest for international shipment success
     */
    public function testDoShipmentRequestRestInternationalSuccess(): void
    {
        $request = $this->setShipperAndRecipientAddressMock();

        $this->carrierModelMock->expects($this->once())
            ->method('_isUSCountry')
            ->with('CA')
            ->willReturn(false);

        $this->carrierModelMock->expects($this->once())
            ->method('getOauthAccessRequest')
            ->willReturn('test_access_token');

        $this->carrierModelMock->expects($this->exactly(2))
            ->method('getUrl')
            ->willReturnOnConsecutiveCalls(
                $this->_defaultGatewayUrl,
                $this->_defaultGatewayUrl
            );

        $this->carrierModelMock->method('_getCountryName')
            ->with('CA')
            ->willReturn('Canada');

        $this->carrierModelMock->method('getConfigData')
            ->willReturnCallback(function ($key) {
                $configMap = [
                    'aesitn' => 'NOEEI 30.36',
                    'size' => 'REGULAR',
                    'height' => 1,
                    'length' => 12,
                    'width' => 9,
                    'container' => 'RECTANGULAR',
                    'girth' => 0
                ];
                return $configMap[$key] ?? null;
            });

        $this->uspsPaymentAuthTokenMock->expects($this->once())
            ->method('getPaymentAuthToken')
            ->willReturn('test_payment_token');

        // Mock product collection
        $productMock = $this->createPartialMockWithReflection(
            Product::class,
            ['getId', 'getCountryOfManufacture']
        );
        $productMock->method('getId')->willReturn(1);
        $productMock->method('getCountryOfManufacture')->willReturn('US');

        $collectionMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        $collectionMock->method('addStoreFilter')->willReturnSelf();
        $collectionMock->method('addFieldToFilter')->willReturnSelf();
        $collectionMock->method('addAttributeToSelect')->willReturnSelf();
        $collectionMock->method('getIterator')->willReturn(new \ArrayIterator([$productMock]));

        $this->productCollectionFactoryMock->method('create')->willReturn($collectionMock);

        // Mock carrier helper for weight conversion
        $this->carrierHelperMock->method('convertMeasureWeight')->willReturn(1.0);

        // Mock successful HTTP response
        $responseMock = $this->createMock(HttpResponseDeferredInterface::class);
        $httpResponseMock = $this->createMock(Response::class);

        $httpResponseMock->method('getStatusCode')->willReturn(201);
        $httpResponseMock->method('getBody')->willReturn(json_encode([
            'labelImage' => base64_encode('fake_intl_label_content'),
            'internationalTrackingNumber' => 'INTL1234567890'
        ]));

        $responseMock->method('get')->willReturn($httpResponseMock);
        $this->httpClientMock->method('request')->willReturn($responseMock);

        $this->shippingMethodManagerMock->method('getMethodDestinationEntryFacilityType')->willReturn('NONE');
        $this->shippingMethodManagerMock->method('getMethodMailClass')->willReturn('PMI');
        $this->shippingMethodManagerMock->method('getMethodProcessingCategory')->willReturn('MACHINABLE');
        $this->shippingMethodManagerMock->method('getRateIndicator')->willReturn('D0');
        $this->shippingMethodManagerMock->method('getMethodMinDimensions')->willReturn([]);
        $this->shippingMethodManagerMock->method('getMethodMaxDimensions')->willReturn([]);

        $result = $this->shipmentService->_doShipmentRequestRest($request);

        $this->assertInstanceOf(DataObject::class, $result);
        $this->assertEquals('fake_intl_label_content', $result->getShippingLabelContent());
        $this->assertEquals('INTL1234567890', $result->getTrackingNumber());
        $this->assertNotNull($result->getGatewayResponse());
    }

    /**
     * Set up the shipper and recipient address mock
     * @return DataObject
     */
    public function setShipperAndRecipientAddressMock(): DataObject
    {
        $packageParams = new DataObject([
            'content_type' => 'MERCHANDISE',
            'weight_units' => Weight::POUND,
            'dimension_units' => Length::INCH,
            'girth_dimension_units' => Length::INCH,
            'height' => 10,
            'width' => 8,
            'length' => 12,
            'girth' => 30
        ]);

        $request = new DataObject();
        $request->setRecipientAddressCountryCode('CA');
        $request->setShippingMethod('PRIORITY_MAIL_INTERNATIONAL');
        $request->setPackageItems([
            [
                'qty' => 1,
                'customs_value' => 50.00,
                'name' => 'Test Product',
                'weight' => 1.0,
                'product_id' => 1
            ]
        ]);
        $request->setStoreId(1);
        $request->setPackageWeight(5.0);
        $request->setShipperAddressCountryCode('US');
        $request->setShipperAddressPostalCode('90210');
        $request->setShipperAddressStreet1('123 Main St');
        $request->setShipperAddressCity('Beverly Hills');
        $request->setShipperAddressStateOrProvinceCode('CA');
        $request->setShipperContactPersonFirstName('John');
        $request->setShipperContactPersonLastName('Doe');
        $request->setShipperContactCompanyName('Test Company');
        $request->setRecipientAddressStreet1('456 King St');
        $request->setRecipientAddressCity('Toronto');
        $request->setRecipientAddressStateOrProvinceCode('ON');
        $request->setRecipientAddressPostalCode('M5V 1J5');
        $request->setRecipientContactPersonFirstName('Jane');
        $request->setRecipientContactPersonLastName('Smith');
        $request->setRecipientContactPhoneNumber('416-123-4567');

        $request->setPackageParams($packageParams);

        return $request;
    }

    /**
     * Test _doShipmentRequestRest with authentication failure
     */
    public function testDoShipmentRequestRestAuthFailure(): void
    {
        $request = new DataObject([
            'recipient_address_country_code' => 'US',
            'shipping_method' => 'PRIORITY_MAIL'
        ]);

        $this->carrierModelMock->expects($this->once())
            ->method('getOauthAccessRequest')
            ->willReturn(null); // Auth failure

        $this->loggerMock->expects($this->once())
            ->method('critical');

        $result = $this->shipmentService->_doShipmentRequestRest($request);

        $this->assertInstanceOf(DataObject::class, $result);
        $this->assertNotEmpty($result->getErrors());
    }

    /**
     * Test _doShipmentRequestRest with HTTP error response
     */
    public function testDoShipmentRequestRestHttpError(): void
    {
        $request = new DataObject([
            'recipient_address_country_code' => 'US',
            'shipping_method' => 'PRIORITY_MAIL',
            'package_params' => new DataObject([
                'weight_units' => Weight::POUND,
                'dimension_units' => Length::INCH,
                'girth_dimension_units' => Length::INCH,
                'height' => 10,
                'width' => 8,
                'length' => 12,
                'girth' => 30
            ]),
            'package_weight' => 5.0,
            'shipper_address_postal_code' => '90210',
            'recipient_address_postal_code' => '10001'
        ]);

        $this->carrierModelMock->method('_isUSCountry')->willReturn(true);
        $this->carrierModelMock->method('getOauthAccessRequest')->willReturn('test_access_token');
        $this->carrierModelMock->method('getUrl')->willReturn($this->_defaultGatewayUrl);
        $this->uspsPaymentAuthTokenMock->method('getPaymentAuthToken')->willReturn('test_payment_token');

        // Mock error HTTP response
        $responseMock = $this->createMock(HttpResponseDeferredInterface::class);
        $httpResponseMock = $this->createMock(Response::class);

        $httpResponseMock->method('getStatusCode')->willReturn(400);
        $httpResponseMock->method('getBody')->willReturn(json_encode([
            'error' => [
                'code' => '400',
                'errors' => [
                    ['detail' => 'Invalid request parameters']
                ]
            ]
        ]));

        $responseMock->method('get')->willReturn($httpResponseMock);
        $this->httpClientMock->method('request')->willReturn($responseMock);

        $this->shippingMethodManagerMock->method('getMethodMailClass')->willReturn('PM');
        $this->shippingMethodManagerMock->method('getMethodProcessingCategory')->willReturn('MACHINABLE');
        $this->shippingMethodManagerMock->method('getMethodDestinationEntryFacilityType')->willReturn('NONE');
        $this->shippingMethodManagerMock->method('getRateIndicator')->willReturn('D0');

        $result = $this->shipmentService->_doShipmentRequestRest($request);

        $this->assertInstanceOf(DataObject::class, $result);
        $this->assertNotEmpty($result->getErrors());
    }
}
