<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Usps\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\HTTP\AsyncClient\HttpException;
use Magento\Framework\HTTP\AsyncClient\HttpResponseDeferredInterface;
use Magento\Framework\HTTP\AsyncClient\Request;
use Magento\Framework\HTTP\AsyncClient\Response;
use Magento\Framework\HTTP\AsyncClientInterface;
use Magento\Framework\Registry;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Api\GuestCouponManagementInterface;
use Magento\Quote\Api\GuestShipmentEstimationInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\SalesRule\Model\Rule\Condition\Product;
use Magento\SalesRule\Model\Rule\Condition\Product\Combine;
use Magento\Shipping\Model\Simplexml\Element;
use Magento\Shipping\Model\Simplexml\ElementFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\HTTP\AsyncClientInterfaceMock;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;
use PHPUnit\Framework\TestCase;

/**
 * Test for USPS integration.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CarrierTest extends TestCase
{
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
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->carrier = $this->objectManager->get(Carrier::class);
        $this->httpClient = $this->objectManager->get(AsyncClientInterface::class);
        $this->management = $this->objectManager->get(GuestCouponManagementInterface::class);
        $this->getQuoteByReservedOrderId = $this->objectManager->get(GetQuoteByReservedOrderId::class);
        $this->getMaskedIdByQuoteId = $this->objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
    }

    /**
     * Test collecting rates from the provider.
     *
     * @magentoConfigFixture default_store carriers/usps/allowed_methods 0_FCLE,0_FCL,0_FCP,1,2,3,4,6,7,13,16,17,22,23,25,27,28,33,34,35,36,37,42,43,53,55,56,57,61,INT_1,INT_2,INT_4,INT_6,INT_7,INT_8,INT_9,INT_10,INT_11,INT_12,INT_13,INT_14,INT_15,INT_16,INT_20,INT_26
     * @magentoConfigFixture default_store carriers/usps/showmethod 1
     * @magentoConfigFixture default_store carriers/usps/debug 1
     * @magentoConfigFixture default_store carriers/usps/userid test
     * @magentoConfigFixture default_store carriers/usps/mode 0
     * @magentoConfigFixture default_store carriers/usps/active 1
     * @magentoConfigFixture default_store shipping/origin/country_id US
     * @magentoConfigFixture default_store shipping/origin/postcode 90034
     * @magentoConfigFixture default_store carriers/usps/machinable true
     */
    public function testCollectRates(): void
    {
        $requestXml = '<?xml version="1.0" encoding="UTF-8"?><RateV4Request USERID="213MAGEN6752">'
            .'<Revision>2</Revision><Package ID="0"><Service>ALL</Service><ZipOrigination>90034</ZipOrigination>'
            .'<ZipDestination>90032</ZipDestination><Pounds>4</Pounds><Ounces>4.2512000000</Ounces>'
            .'<Container>VARIABLE</Container><Size>REGULAR</Size><Machinable>true</Machinable></Package>'
            .'</RateV4Request>';
        $requestXml = (new \SimpleXMLElement($requestXml))->asXml();
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $responseBody = file_get_contents(__DIR__ .'/../Fixtures/success_usps_response_rates.xml');
        $this->httpClient->nextResponses([new Response(200, [], $responseBody)]);
        /** @var RateRequest $request */
        $request = Bootstrap::getObjectManager()->create(
            RateRequest::class,
            [
                'data' => [
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
                    'orig_country_id' => 'US',
                    'country_id' => 'US',
                    'region_id' => '12',
                    'city' => 'Culver City',
                    'postcode' => '90034',
                    'usps_userid' => '213MAGEN6752',
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
        $httpRequest = $this->httpClient->getLastRequest();
        $this->assertNotEmpty($httpRequest);
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $uri = parse_url($httpRequest->getUrl(), PHP_URL_QUERY);
        $this->assertNotEmpty(preg_match('/API\=([A-z0-9]+)/', $uri, $matches));
        $apiV = $matches[1];
        unset($matches);
        $this->assertEquals('RateV4', $apiV);
        $this->assertNotEmpty(preg_match('/XML\=([^\&]+)/', $uri, $matches));
        $xml = urldecode($matches[1]);
        $this->assertEquals($requestXml, $xml);
        $this->assertNotEmpty($rates->getAllRates());
        $this->assertEquals(5.6, $rates->getAllRates()[2]->getPrice());
        $this->assertEquals(
            "Priority Mail 1-Day\nSmall Flat Rate Envelope",
            $rates->getAllRates()[2]->getMethodTitle()
        );
    }

    /**
     * Test collecting rates only for available services.
     *
     * @magentoConfigFixture default_store carriers/usps/allowed_methods 0_FCLE,0_FCL,0_FCP,1,2,3,4,6,7,13,16,17,22,23,25,27,28,33,34,35,36,37,42,43,53,55,56,57,61,INT_1,INT_2,INT_4,INT_6,INT_7,INT_8,INT_9,INT_10,INT_11,INT_12,INT_13,INT_14,INT_15,INT_16,INT_20,INT_26
     * @magentoConfigFixture default_store carriers/usps/showmethod 1
     * @magentoConfigFixture default_store carriers/usps/debug 1
     * @magentoConfigFixture default_store carriers/usps/userid test
     * @magentoConfigFixture default_store carriers/usps/mode 0
     * @magentoConfigFixture default_store carriers/usps/active 1
     * @magentoConfigFixture default_store shipping/origin/country_id US
     * @magentoConfigFixture default_store shipping/origin/postcode 90034
     * @magentoConfigFixture default_store carriers/usps/machinable true
     */
    public function testCollectUnavailableRates(): void
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $responseBody = file_get_contents(__DIR__ .'/../Fixtures/response_rates.xml');
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
                    'usps_userid' => '213MAGEN6752',
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
        $this->assertCount(5, $rates->getAllRates());
    }

    /**
     * Test get carriers rates if has HttpException.
     *
     * @magentoConfigFixture default_store carriers/usps/allowed_methods 0_FCLE,0_FCL,0_FCP,1,2,3,4,6,7,13,16,17,22,23,25,27,28,33,34,35,36,37,42,43,53,55,56,57,61,INT_1,INT_2,INT_4,INT_6,INT_7,INT_8,INT_9,INT_10,INT_11,INT_12,INT_13,INT_14,INT_15,INT_16,INT_20,INT_26
     * @magentoConfigFixture default_store carriers/usps/showmethod 1
     * @magentoConfigFixture default_store carriers/usps/debug 1
     * @magentoConfigFixture default_store carriers/usps/userid test
     * @magentoConfigFixture default_store carriers/usps/mode 0
     * @magentoConfigFixture default_store carriers/usps/active 1
     * @magentoConfigFixture default_store shipping/origin/country_id US
     * @magentoConfigFixture default_store shipping/origin/postcode 90034
     * @magentoConfigFixture default_store carriers/usps/machinable true
     */
    public function testGetRatesWithHttpException(): void
    {
        $deferredResponse = $this->getMockBuilder(HttpResponseDeferredInterface::class)
            ->onlyMethods(['get'])
            ->getMockForAbstractClass();
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
                    'usps_userid' => '213MAGEN6752',
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
     * Test that the shipping cost from the product in the cart rule should be deducted from the shipping amount
     *
     * @magentoConfigFixture default_store carriers/usps/active 1
     * @magentoConfigFixture default_store carriers/usps/free_method 1
     * @magentoConfigFixture default_store carriers/usps/debug 1
     * @magentoDataFixture Magento/Catalog/_files/products_list.php
     * @magentoDataFixture Magento/Usps/Fixtures/cart_rule_coupon_free_shipping.php
     * @magentoDataFixture setFreeShippingForProduct1
     * @magentoDataFixture createEmptyCart
     * @magentoDataFixture addProduct1ToCart
     * @magentoDataFixture addProduct2ToCart
     * @return void
     */
    public function testPartialFreeShippingWithCoupon(): void
    {
        $quote = $this->getQuoteByReservedOrderId->execute(self::RESERVED_ORDER_ID);
        $cartId = $this->getMaskedIdByQuoteId->execute((int)$quote->getId());

        //phpcs:disable
        $this->httpClient->setDeferredResponseMock(null)
            ->nextResponses(
                [
                    new Response(200, [], file_get_contents(__DIR__ . '/../Fixtures/success_usps_response_rates.xml')),
                    new Response(200, [], file_get_contents(__DIR__ . '/../Fixtures/rates_response.xml'))
                ]
            );
        //phpcs:enable
        $requestsCount = count($this->httpClient->getRequests());
        $this->management->set($cartId, self::FREE_SHIPPING_COUPON_CODE);
        $methods = $this->estimateShipping($cartId);
        $freeMethods = $this->filterFreeShippingMethods($methods);
        self::assertEmpty($freeMethods);
        $requests = array_slice($this->httpClient->getRequests(), $requestsCount);
        self::assertCount(2, $requests);
        $firstRequest = $this->getXmlElement($this->getRequestBody($requests[0]));
        $secondRequest = $this->getXmlElement($this->getRequestBody($requests[1]));
        $this->assertEquals('ALL', $firstRequest->Package->Service);
        $this->assertEquals('20', $firstRequest->Package->Pounds);
        $this->assertEquals('Priority', $secondRequest->Package->Service);
        $this->assertEquals('10', $secondRequest->Package->Pounds);
        $price = $this->getShippingMethodAmount($methods, 'usps', '1');
        $this->assertEquals(6.70, $price);
    }

    /**
     * Get XML request body
     *
     * @param Request $request
     * @return string
     */
    private function getRequestBody(Request $request): string
    {
        //phpcs:disable
        $url = $request->getUrl();
        $query = parse_url($url, PHP_URL_QUERY);
        parse_str($query, $params);
        //phpcs:enable
        return urldecode($params['XML']);
    }

    /**
     * Create XML object for provided string
     *
     * @param string $xmlString
     * @return Element
     */
    private function getXmlElement(string $xmlString): Element
    {
        $xmlElementFactory = $this->objectManager->get(ElementFactory::class);

        return $xmlElementFactory->create(
            ['data' => $xmlString]
        );
    }

    /**
     * Get shipping method amount by carrier code and method code
     *
     * @param array $methods
     * @param string $carrierCode
     * @param string $methodCode
     * @return float|null
     */
    private function getShippingMethodAmount(array $methods, string $carrierCode, string $methodCode): ?float
    {
        /** @var ShippingMethodInterface $method */
        foreach ($methods as $method) {
            if ($method->getCarrierCode() === $carrierCode && (string)$method->getMethodCode() === $methodCode) {
                return $method->getAmount();
            }
        }
        return null;
    }

    /**
     * Estimates shipment for guest cart.
     *
     * @param string $cartId
     * @return array ShippingMethodInterface[]
     */
    private function estimateShipping(string $cartId): array
    {
        $addressFactory = $this->objectManager->get(AddressInterfaceFactory::class);
        /** @var AddressInterface $address */
        $address = $addressFactory->create();
        $address->setCountryId('US');
        $address->setRegionId(12);
        $address->setPostcode(90230);

        /** @var GuestShipmentEstimationInterface $estimation */
        $estimation = $this->objectManager->get(GuestShipmentEstimationInterface::class);
        return $estimation->estimateByExtendedAddress($cartId, $address);
    }

    /**
     * Filters free shipping methods.
     *
     * @param array $methods
     * @return array
     */
    private function filterFreeShippingMethods(array $methods): array
    {
        $result = [];
        /** @var ShippingMethodInterface $method */
        foreach ($methods as $method) {
            if ($method->getAmount() == 0) {
                $result[] = $method->getMethodTitle();
            }
        }
        return $result;
    }

    /**
     * Add product to cart fixture helper
     *
     * @param string $sku
     */
    private static function addToCart(string $sku): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $objectManager->get(ProductRepositoryInterface::class);
        /** @var GetQuoteByReservedOrderId $getQuoteByReservedOrderId */
        $getQuoteByReservedOrderId = $objectManager->get(GetQuoteByReservedOrderId::class);
        /** @var CartRepositoryInterface $cartRepository */
        $cartRepository = $objectManager->get(CartRepositoryInterface::class);
        $product = $productRepository->get($sku);
        $quote = $getQuoteByReservedOrderId->execute(self::RESERVED_ORDER_ID);
        $quote->addProduct($product, 1);
        $cartRepository->save($quote);
    }

    /**
     * Create empty cart fixture
     */
    public static function createEmptyCart(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var GuestCartManagementInterface $guestCartManagement */
        $guestCartManagement = $objectManager->get(GuestCartManagementInterface::class);
        /** @var CartRepositoryInterface $cartRepository */
        $cartRepository = $objectManager->get(CartRepositoryInterface::class);
        /** @var MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId */
        $maskedQuoteIdToQuoteId = $objectManager->get(MaskedQuoteIdToQuoteIdInterface::class);
        $cartHash = $guestCartManagement->createEmptyCart();
        $cartId = $maskedQuoteIdToQuoteId->execute($cartHash);
        $cart = $cartRepository->get($cartId);
        $cart->setReservedOrderId(self::RESERVED_ORDER_ID);
        $cartRepository->save($cart);
    }

    /**
     * Create empty cart fixture rollback
     */
    public static function createEmptyCartRollback(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var QuoteFactory $quoteFactory */
        $quoteFactory = Bootstrap::getObjectManager()->get(QuoteFactory::class);
        /** @var QuoteResource $quoteResource */
        $quoteResource = $objectManager->get(QuoteResource::class);
        /** @var QuoteIdMaskFactory $quoteIdMaskFactory */
        $quoteIdMaskFactory = $objectManager->get(QuoteIdMaskFactory::class);
        $quote = $quoteFactory->create();
        $quoteResource->load($quote, self::RESERVED_ORDER_ID, 'reserved_order_id');
        $quoteResource->delete($quote);
        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = $quoteIdMaskFactory->create();
        $quoteIdMask->setQuoteId($quote->getId())
            ->delete();
    }

    /**
     * Set free shipping for product 2 fixture
     */
    public static function setFreeShippingForProduct1(): void
    {
        /** @var ObjectManager $objectManager */
        $objectManager = Bootstrap::getObjectManager();
        /** @var Registry $registry */
        $registry = $objectManager->get(Registry::class);
        $salesRule = $registry->registry('cart_rule_free_shipping');
        $data = [
            'actions' => [
                1 => [
                    'type' => Combine::class,
                    'attribute' => null,
                    'operator' => null,
                    'value' => '1',
                    'is_value_processed' => null,
                    'aggregator' => 'all',
                    'actions' => [
                        1 => [
                            'type' => Product::class,
                            'attribute' => 'sku',
                            'operator' => '==',
                            'value' => self::PRODUCT_1,
                            'is_value_processed' => false,
                        ]
                    ]
                ]
            ],
        ];
        $salesRule->loadPost($data);
        $salesRule->save();
    }

    /**
     * Add product 2 to cart fixture
     */
    public static function addProduct1ToCart(): void
    {
        static::addToCart(self::PRODUCT_1);
    }

    /**
     * Add product 3 to cart fixture
     */
    public static function addProduct2ToCart(): void
    {
        static::addToCart(self::PRODUCT_2);
    }
}
