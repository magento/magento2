<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Model\Checkout\Type;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Test\Fixture\SetBillingAddress;
use Magento\Checkout\Test\Fixture\SetPaymentMethod;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Multishipping\Test\Fixture\AddAddressToCart;
use Magento\Multishipping\Test\Fixture\ShippingAssignments;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Rate as QuoteAddressRate;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Backend multishipping parity with storefront multishipping + online carriers
 *
 * Aligns with MFTF StorefrontMultishippingCheckoutWithOnlineShippingMethodTest (no UI).
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MultishippingOnlineCarriersTest extends TestCase
{
    /**
     * Expected shipping cities (one order per address), sorted for comparison.
     */
    private const EXPECTED_CITIES_SORTED = ['Berlin', 'Culver City', 'London', 'New York'];

    /**
     * Deterministic stub price per address (no API).
     */
    private const STUB_SHIPPING_PRICE = 12.34;

    /**
     * City => [carrier code, carrier method segment, human-readable title for rate row].
     *
     * Maps one online carrier per address like MFTF selecting the first available method per city.
     */
    private const CITY_ONLINE_METHODS = [
        'Berlin' => ['fedex', 'FEDEX_INTERNATIONAL_PRIORITY', 'FedEx International Priority'],
        'London' => ['ups', '07', 'UPS Worldwide Express'],
        'New York' => ['usps', 'PRIORITY_MAIL_MACHINABLE_SINGLE-PIECE', 'USPS Priority Mail'],
        'Culver City' => ['dhl', 'P', 'DHL Express Worldwide'],
    ];

    /**
     * @var ObjectManager
     */
    private ObjectManager $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchBuilder;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;

    /**
     * Initialize shared services and clear checkout session before each test.
     *
     * @return void
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->searchBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $this->orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $this->cartRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $this->objectManager->get(CheckoutSession::class)->clearStorage();
    }

    /**
     * Create Multishipping checkout model with email sender mocked.
     *
     * @return Multishipping
     */
    private function createMultishippingCheckout(): Multishipping
    {
        $orderSender = $this->createMock(OrderSender::class);

        return $this->objectManager->create(
            Multishipping::class,
            ['orderSender' => $orderSender]
        );
    }

    /**
     * Load orders created from the given quote entity id.
     *
     * @param int $quoteId
     * @return Order[]
     */
    private function getOrdersByQuoteId(int $quoteId): array
    {
        $searchCriteria = $this->searchBuilder
            ->addFilter('quote_id', $quoteId)
            ->create();

        /** @var Order[] $orders */
        $orders = $this->orderRepository->getList($searchCriteria)->getItems();

        return $orders;
    }

    /**
     * Inject one stub rate per shipping address (FedEx / UPS / USPS / DHL) and freeze rate collection.
     *
     * @param Quote $quote
     * @return void
     */
    private function injectOnlineCarrierStubRatesPerAddress(Quote $quote): void
    {
        $checkoutSession = $this->objectManager->get(CheckoutSession::class);
        /** @var Quote $fresh */
        $fresh = $this->cartRepository->get((int)$quote->getId());

        foreach ($fresh->getAllShippingAddresses() as $address) {
            $city = (string)$address->getCity();
            $this->assertArrayHasKey(
                $city,
                self::CITY_ONLINE_METHODS,
                'Fixture address city must match MFTF multishipping scenario.'
            );
            [$carrier, $method, $methodTitle] = self::CITY_ONLINE_METHODS[$city];
            $fullCode = $carrier . '_' . $method;

            $address->removeAllShippingRates();
            $rate = $this->objectManager->create(QuoteAddressRate::class);
            $rate->setCarrier($carrier)
                ->setCarrierTitle($carrier)
                ->setMethod($method)
                ->setCode($fullCode)
                ->setMethodTitle($methodTitle)
                ->setPrice(self::STUB_SHIPPING_PRICE);
            $address->addShippingRate($rate);
            $address->setShippingMethod($fullCode);
            $address->setBaseShippingAmount(self::STUB_SHIPPING_PRICE);
            $address->setShippingAmount(self::STUB_SHIPPING_PRICE);
            $address->setCollectShippingRates(false);
        }

        $fresh->setTotalsCollectedFlag(false);
        $fresh->collectTotals();
        $this->cartRepository->save($fresh);
        $checkoutSession->replaceQuote($fresh);
    }

    /**
     * Multishipping creates four orders; shipping methods mirror MFTF online carriers
     *
     * @return void
     */
    #[
        ConfigFixture('payment/checkmo/active', '1', 'store', 'default'),
        ConfigFixture('multishipping/options/checkout_multiple', '1', 'store', 'default'),
        ConfigFixture('carriers/flatrate/active', '0', 'store', 'default'),
        ConfigFixture('shipping/origin/country_id', 'US', 'store', 'default'),
        ConfigFixture('shipping/origin/region_id', '12', 'store', 'default'),
        ConfigFixture('shipping/origin/postcode', '90230', 'store', 'default'),
        ConfigFixture('shipping/origin/city', 'Culver City', 'store', 'default'),
        ConfigFixture('shipping/origin/street_line1', '6161 West Centinela Avenue', 'store', 'default'),
        ConfigFixture('general/store_information/name', 'Test Store', 'store', 'default'),
        ConfigFixture('general/store_information/phone', '5551234567', 'store', 'default'),
        ConfigFixture('carriers/fedex/active', '1', 'store', 'default'),
        ConfigFixture('carriers/fedex/sandbox_mode', '1', 'store', 'default'),
        ConfigFixture('carriers/fedex/debug', '1', 'store', 'default'),
        ConfigFixture('carriers/fedex/showmethod', '1', 'store', 'default'),
        ConfigFixture('carriers/fedex/api_key', 'test_api_key', 'store', 'default'),
        ConfigFixture('carriers/fedex/secret_key', 'test_secret_key', 'store', 'default'),
        ConfigFixture('carriers/fedex/account', 'test_account', 'store', 'default'),
        ConfigFixture('carriers/fedex/meter_number', 'test_meter', 'store', 'default'),
        ConfigFixture('carriers/fedex/smartpost_hubid', '55312', 'store', 'default'),
        ConfigFixture(
            'carriers/fedex/allowed_methods',
            'FEDEX_GROUND,FEDEX_2_DAY,FEDEX_INTERNATIONAL_PRIORITY',
            'store',
            'default'
        ),
        ConfigFixture('carriers/ups/active', '1', 'store', 'default'),
        ConfigFixture('carriers/ups/type', 'UPS_REST', 'store', 'default'),
        ConfigFixture('carriers/ups/allowed_methods', '03,07,11', 'store', 'default'),
        ConfigFixture('carriers/ups/shipper_number', '12345', 'store', 'default'),
        ConfigFixture('carriers/ups/origin_shipment', 'Shipments Originating in the United States', 'store', 'default'),
        ConfigFixture('carriers/ups/username', 'test_ups_user', 'store', 'default'),
        ConfigFixture('carriers/ups/password', 'test_ups_pass', 'store', 'default'),
        ConfigFixture('carriers/ups/debug', '1', 'store', 'default'),
        ConfigFixture('carriers/ups/is_account_live', '0', 'store', 'default'),
        ConfigFixture('carriers/usps/active', '1', 'store', 'default'),
        ConfigFixture('carriers/usps/usps_type', 'USPS_REST', 'store', 'default'),
        ConfigFixture('carriers/usps/showmethod', '1', 'store', 'default'),
        ConfigFixture('carriers/usps/debug', '1', 'store', 'default'),
        ConfigFixture('carriers/usps/client_id', 'test_usps_client', 'store', 'default'),
        ConfigFixture('carriers/usps/client_secret', 'test_usps_secret', 'store', 'default'),
        ConfigFixture('carriers/usps/mode', '0', 'store', 'default'),
        ConfigFixture('carriers/usps/machinable', '1', 'store', 'default'),
        ConfigFixture(
            'carriers/usps/rest_allowed_methods',
            'PRIORITY_MAIL_MACHINABLE_SINGLE-PIECE,USPS_GROUND_ADVANTAGE_MACHINABLE_SINGLE-PIECE',
            'store',
            'default'
        ),
        ConfigFixture('carriers/dhl/active', '1', 'store', 'default'),
        ConfigFixture('carriers/dhl/type', 'DHL_REST', 'store', 'default'),
        ConfigFixture('carriers/dhl/sallowspecific', '0', 'store', 'default'),
        ConfigFixture('carriers/dhl/showmethod', '1', 'store', 'default'),
        ConfigFixture('carriers/dhl/debug', '1', 'store', 'default'),
        ConfigFixture('carriers/dhl/sandbox_mode', '1', 'store', 'default'),
        ConfigFixture('carriers/dhl/gateway_rest_url', 'https://express.api.dhl.com/mydhlapi', 'store', 'default'),
        ConfigFixture('carriers/dhl/id', 'test_dhl_id', 'store', 'default'),
        ConfigFixture('carriers/dhl/password', 'test_dhl_password', 'store', 'default'),
        ConfigFixture('carriers/dhl/api_key', 'test_dhl_api_key', 'store', 'default'),
        ConfigFixture('carriers/dhl/api_secret', 'test_dhl_api_secret', 'store', 'default'),
        ConfigFixture('carriers/dhl/account', '998765432', 'store', 'default'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'multiship-stub-simple',
                'name' => 'Multiship Stub Simple',
                'price' => 10.00,
                'weight' => 1.00,
            ],
            'product'
        ),
        DataFixture(Customer::class, [], 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'cart'),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1],
            'lineItem1'
        ),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1],
            'lineItem2'
        ),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1],
            'lineItem3'
        ),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1],
            'lineItem4'
        ),
        DataFixture(
            AddAddressToCart::class,
            [
                'cart_id' => '$cart.id$',
                'address' => [
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'telephone' => '333-33-333-33',
                    'street' => ['Augsburger Strabe 41'],
                    'city' => 'Berlin',
                    'region' => 'Berlin',
                    'region_id' => 82,
                    'postcode' => '10789',
                    'country_id' => 'DE',
                    'email' => 'berlin@multiship.stub.test',
                ],
            ],
            'shipAddr1'
        ),
        DataFixture(
            AddAddressToCart::class,
            [
                'cart_id' => '$cart.id$',
                'address' => [
                    'firstname' => 'Jane',
                    'lastname' => 'Doe',
                    'telephone' => '444-44-444-44',
                    'street' => ['172, Westminster Bridge Rd'],
                    'city' => 'London',
                    'postcode' => 'SE1 7RW',
                    'country_id' => 'GB',
                    'region_id' => null,
                    'email' => 'london@multiship.stub.test',
                ],
            ],
            'shipAddr2'
        ),
        DataFixture(
            AddAddressToCart::class,
            [
                'cart_id' => '$cart.id$',
                'address' => [
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'telephone' => '512-345-6789',
                    'street' => ['368 Broadway St.', 'Apt. 113'],
                    'city' => 'New York',
                    'region' => 'NY',
                    'region_id' => 43,
                    'postcode' => '10001',
                    'country_id' => 'US',
                    'email' => 'ny@multiship.stub.test',
                ],
            ],
            'shipAddr3'
        ),
        DataFixture(
            AddAddressToCart::class,
            [
                'cart_id' => '$cart.id$',
                'address' => [
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'telephone' => '0333-233-221',
                    'street' => ['6161 West Centinela Avenue', '16'],
                    'city' => 'Culver City',
                    'region' => 'CA',
                    'region_id' => 12,
                    'postcode' => '90232',
                    'country_id' => 'US',
                    'email' => 'culver@multiship.stub.test',
                ],
            ],
            'shipAddr4'
        ),
        DataFixture(
            ShippingAssignments::class,
            [
                'cart_id' => '$cart.id$',
                'assignments' => [
                    ['item_id' => '$lineItem1.id$', 'address_id' => '$shipAddr1.id$', 'qty' => 1],
                    ['item_id' => '$lineItem2.id$', 'address_id' => '$shipAddr2.id$', 'qty' => 1],
                    ['item_id' => '$lineItem3.id$', 'address_id' => '$shipAddr3.id$', 'qty' => 1],
                    ['item_id' => '$lineItem4.id$', 'address_id' => '$shipAddr4.id$', 'qty' => 1],
                ],
            ]
        ),
        DataFixture(SetBillingAddress::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethod::class, ['cart_id' => '$cart.id$']),
    ]
    public function testCreateFourOrdersWithFedexUpsUspsDhlMethodsPerAddress(): void
    {
        /** @var Quote $quote */
        $quote = $this->fixtures->get('cart');
        $this->injectOnlineCarrierStubRatesPerAddress($quote);

        $this->createMultishippingCheckout()->createOrders();

        $orders = $this->getOrdersByQuoteId((int)$quote->getId());
        $this->assertCount(4, $orders, 'Multishipping must create one order per shipping address.');

        $cities = [];
        $carriersSeen = [];
        foreach ($orders as $order) {
            $this->assertInstanceOf(Order::class, $order);
            $shipping = $order->getShippingAddress();
            $this->assertInstanceOf(OrderAddressInterface::class, $shipping);
            $city = (string)$shipping->getCity();
            $this->assertNotSame('', $city);
            $cities[] = $city;

            $method = (string)$order->getShippingMethod();
            $this->assertNotSame('', $method);
            $carrier = strstr($method, '_', true);
            $this->assertNotFalse($carrier);
            $carriersSeen[$carrier] = true;

            $expectedPrefix = self::CITY_ONLINE_METHODS[$city][0] . '_';
            $this->assertStringStartsWith(
                $expectedPrefix,
                $method,
                'Order shipping method must match the online carrier stubbed for this city.'
            );
        }

        $this->assertCount(4, $carriersSeen, 'Each of four orders must use a distinct online carrier.');
        foreach (['dhl', 'fedex', 'ups', 'usps'] as $carrierCode) {
            $this->assertArrayHasKey(
                $carrierCode,
                $carriersSeen,
                sprintf('Expected shipping method from carrier "%s" on one of the orders.', $carrierCode)
            );
        }

        sort($cities);
        $this->assertSame(self::EXPECTED_CITIES_SORTED, $cities);
    }
}
