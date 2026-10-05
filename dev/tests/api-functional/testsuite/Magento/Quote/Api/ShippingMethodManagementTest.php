<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Quote\Api;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Customer\Test\Fixture\CustomerGroup;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Tax\Test\Fixture\CustomerTaxClass;
use Magento\Tax\Test\Fixture\ProductTaxClass;
use Magento\Tax\Test\Fixture\TaxRate as TaxRateFixture;
use Magento\Tax\Test\Fixture\TaxRule as TaxRuleFixture;
use Magento\TestFramework\App\ApiMutableScopeConfig;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Quote\Api\Data\ShippingMethodInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingMethodManagementTest extends WebapiAbstract
{
    public const SERVICE_VERSION = 'V1';
    public const SERVICE_NAME = 'quoteShippingMethodManagementV1';
    public const RESOURCE_PATH = '/V1/carts/';

    private const SHIPPING_TAX_RULE_DATA = [
        'customer_tax_class_ids' => [3, '$customer_group.tax_class_id$'],
        'product_tax_class_ids' => ['$shipping_tax_class.classId$'],
        'tax_rate_ids' => ['$rate.id$']
    ];

    private const SHIPPING_ADDRESS_DATA = [
        'cart_id' => '$cart.id$',
        'address' => ['postcode' => '75477']
    ];

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsCollector
     */
    protected $totalsCollector;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $this->totalsCollector = $this->objectManager->create(\Magento\Quote\Model\Quote\TotalsCollector::class);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_and_address.php
     *
     */
    public function testGetListForVirtualCart()
    {
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $cartId = $quote->load('test_order_with_virtual_product', 'reserved_order_id')->getId();

        $this->assertEquals([], $this->_webApiCall($this->getListServiceInfo($cartId), ["cartId" => $cartId]));
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testGetList()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test_order_1', 'reserved_order_id');
        $cartId = $quote->getId();
        if (!$cartId) {
            $this->fail('quote fixture failed');
        }
        $quote->getShippingAddress()->collectShippingRates();
        $expectedRates = $quote->getShippingAddress()->getGroupedAllShippingRates();

        $expectedData = $this->convertRates($expectedRates, $quote->getQuoteCurrencyCode());

        $requestData = ["cartId" => $cartId];

        $returnedRates = $this->_webApiCall($this->getListServiceInfo($cartId), $requestData);
        $this->assertEquals($expectedData, $returnedRates);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testGetListForMyCart()
    {
        $this->_markTestAsRestOnly();

        $this->quote->load('test_order_1', 'reserved_order_id');

        /** @var \Magento\Integration\Api\CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(
            \Magento\Integration\Api\CustomerTokenServiceInterface::class
        );
        $token = $customerTokenService->createCustomerAccessToken('customer@example.com', 'password');

        /** @var \Magento\Quote\Model\ShippingMethodManagementInterface $shippingMethodManagementService */
        $shippingMethodManagementService = $this->objectManager->create(
            \Magento\Quote\Model\ShippingMethodManagementInterface::class
        );
        $shippingMethodManagementService->set($this->quote->getId(), 'flatrate', 'flatrate');

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/carts/mine/shipping-methods',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
                'token' => $token
            ]
        ];

        $result = $this->_webApiCall($serviceInfo, []);
        $this->assertNotEmpty($result);
        $this->assertCount(1, $result);

        $shippingMethod = $shippingMethodManagementService->get($this->quote->getId());
        $expectedData = [
            ShippingMethodInterface::KEY_CARRIER_CODE => $shippingMethod->getCarrierCode(),
            ShippingMethodInterface::KEY_METHOD_CODE => $shippingMethod->getMethodCode(),
            ShippingMethodInterface::KEY_CARRIER_TITLE => $shippingMethod->getCarrierTitle(),
            ShippingMethodInterface::KEY_METHOD_TITLE => $shippingMethod->getMethodTitle(),
            ShippingMethodInterface::KEY_SHIPPING_AMOUNT => $shippingMethod->getAmount(),
            ShippingMethodInterface::KEY_BASE_SHIPPING_AMOUNT => $shippingMethod->getBaseAmount(),
            ShippingMethodInterface::KEY_AVAILABLE => $shippingMethod->getAvailable(),
            ShippingMethodInterface::KEY_ERROR_MESSAGE => null,
            ShippingMethodInterface::KEY_PRICE_EXCL_TAX => $shippingMethod->getPriceExclTax(),
            ShippingMethodInterface::KEY_PRICE_INCL_TAX => $shippingMethod->getPriceInclTax(),
        ];

        $this->assertEquals($expectedData, $result[0]);
    }

    /**
     * @magentoConfigFixture default_store tax/calculation/price_includes_tax 0
     * @magentoConfigFixture default_store tax/calculation/shipping_includes_tax 1
     * @magentoConfigFixture default_store tax/calculation/based_on shipping
     * @magentoConfigFixture default_store tax/cart_display/price 1
     * @magentoConfigFixture default_store tax/cart_display/shipping 1
     * @magentoConfigFixture default_store tax/display/type 2
     * @magentoConfigFixture default_store tax/display/shipping 1
     * @magentoConfigFixture default_store shipping/origin/country_id US
     * @magentoConfigFixture default_store shipping/origin/region_id 1
     * @magentoConfigFixture default_store shipping/origin/postcode 75477
     * @magentoConfigFixture default_store carriers/flatrate/active 1
     * @magentoConfigFixture default_store carriers/flatrate/price 10
     * @magentoConfigFixture default_store carriers/flatrate/type O
     */
    #[
        Config('tax/classes/shipping_tax_class', 0, 'store', 'default'),
        DataFixture(CustomerTaxClass::class, as: 'customer_tax_class'),
        DataFixture(CustomerGroup::class, ['tax_class_id' => '$customer_tax_class.classId$'], 'customer_group'),
        DataFixture(CustomerFixture::class, ['group_id' => '$customer_group.id$'], 'customer'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(ProductTaxClass::class, as: 'shipping_tax_class'),
        DataFixture(TaxRateFixture::class, ['tax_region_id' => 1, 'rate' => 7.5], 'rate'),
        DataFixture(TaxRuleFixture::class, self::SHIPPING_TAX_RULE_DATA),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 2]),
        DataFixture(SetShippingAddress::class, self::SHIPPING_ADDRESS_DATA)
    ]
    public function testGetListWithInclusiveShippingInputAndInclusiveCatalogDisplay(): void
    {
        $this->assertShippingListPrices(9.3, 10);
    }

    /**
     * @magentoConfigFixture default_store tax/calculation/price_includes_tax 0
     * @magentoConfigFixture default_store tax/calculation/shipping_includes_tax 1
     * @magentoConfigFixture default_store tax/calculation/based_on shipping
     * @magentoConfigFixture default_store tax/cart_display/price 1
     * @magentoConfigFixture default_store tax/cart_display/shipping 1
     * @magentoConfigFixture default_store tax/display/type 3
     * @magentoConfigFixture default_store tax/display/shipping 1
     * @magentoConfigFixture default_store shipping/origin/country_id US
     * @magentoConfigFixture default_store shipping/origin/region_id 1
     * @magentoConfigFixture default_store shipping/origin/postcode 75477
     * @magentoConfigFixture default_store carriers/flatrate/active 1
     * @magentoConfigFixture default_store carriers/flatrate/price 10
     * @magentoConfigFixture default_store carriers/flatrate/type O
     */
    #[
        Config('tax/classes/shipping_tax_class', 0, 'store', 'default'),
        DataFixture(CustomerTaxClass::class, as: 'customer_tax_class'),
        DataFixture(CustomerGroup::class, ['tax_class_id' => '$customer_tax_class.classId$'], 'customer_group'),
        DataFixture(CustomerFixture::class, ['group_id' => '$customer_group.id$'], 'customer'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(ProductTaxClass::class, as: 'shipping_tax_class'),
        DataFixture(TaxRateFixture::class, ['tax_region_id' => 1, 'rate' => 7.5], 'rate'),
        DataFixture(TaxRuleFixture::class, self::SHIPPING_TAX_RULE_DATA),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 2]),
        DataFixture(SetShippingAddress::class, self::SHIPPING_ADDRESS_DATA)
    ]
    public function testGetListWithInclusiveShippingInputAndBothCatalogDisplays(): void
    {
        $this->assertShippingListPrices(9.3, 10);
    }

    /**
     * @magentoConfigFixture default_store tax/calculation/price_includes_tax 1
     * @magentoConfigFixture default_store tax/calculation/shipping_includes_tax 0
     * @magentoConfigFixture default_store tax/calculation/based_on shipping
     * @magentoConfigFixture default_store tax/cart_display/price 1
     * @magentoConfigFixture default_store tax/cart_display/shipping 1
     * @magentoConfigFixture default_store tax/display/type 2
     * @magentoConfigFixture default_store tax/display/shipping 1
     * @magentoConfigFixture default_store shipping/origin/country_id US
     * @magentoConfigFixture default_store shipping/origin/region_id 1
     * @magentoConfigFixture default_store shipping/origin/postcode 75477
     * @magentoConfigFixture default_store carriers/flatrate/active 1
     * @magentoConfigFixture default_store carriers/flatrate/price 10
     * @magentoConfigFixture default_store carriers/flatrate/type O
     */
    #[
        Config('tax/classes/shipping_tax_class', 0, 'store', 'default'),
        DataFixture(CustomerTaxClass::class, as: 'customer_tax_class'),
        DataFixture(CustomerGroup::class, ['tax_class_id' => '$customer_tax_class.classId$'], 'customer_group'),
        DataFixture(CustomerFixture::class, ['group_id' => '$customer_group.id$'], 'customer'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(ProductTaxClass::class, as: 'shipping_tax_class'),
        DataFixture(TaxRateFixture::class, ['tax_region_id' => 1, 'rate' => 7.5], 'rate'),
        DataFixture(TaxRuleFixture::class, self::SHIPPING_TAX_RULE_DATA),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 2]),
        DataFixture(SetShippingAddress::class, self::SHIPPING_ADDRESS_DATA)
    ]
    public function testGetListWithExclusiveShippingInputAndInclusiveCatalogDisplay(): void
    {
        $this->assertShippingListPrices(10, 10.75);
    }

    /**
     * @magentoConfigFixture default_store tax/calculation/price_includes_tax 1
     * @magentoConfigFixture default_store tax/calculation/shipping_includes_tax 0
     * @magentoConfigFixture default_store tax/calculation/based_on shipping
     * @magentoConfigFixture default_store tax/cart_display/price 1
     * @magentoConfigFixture default_store tax/cart_display/shipping 1
     * @magentoConfigFixture default_store tax/display/type 3
     * @magentoConfigFixture default_store tax/display/shipping 1
     * @magentoConfigFixture default_store shipping/origin/country_id US
     * @magentoConfigFixture default_store shipping/origin/region_id 1
     * @magentoConfigFixture default_store shipping/origin/postcode 75477
     * @magentoConfigFixture default_store carriers/flatrate/active 1
     * @magentoConfigFixture default_store carriers/flatrate/price 10
     * @magentoConfigFixture default_store carriers/flatrate/type O
     */
    #[
        Config('tax/classes/shipping_tax_class', 0, 'store', 'default'),
        DataFixture(CustomerTaxClass::class, as: 'customer_tax_class'),
        DataFixture(CustomerGroup::class, ['tax_class_id' => '$customer_tax_class.classId$'], 'customer_group'),
        DataFixture(CustomerFixture::class, ['group_id' => '$customer_group.id$'], 'customer'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(ProductTaxClass::class, as: 'shipping_tax_class'),
        DataFixture(TaxRateFixture::class, ['tax_region_id' => 1, 'rate' => 7.5], 'rate'),
        DataFixture(TaxRuleFixture::class, self::SHIPPING_TAX_RULE_DATA),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 2]),
        DataFixture(SetShippingAddress::class, self::SHIPPING_ADDRESS_DATA)
    ]
    public function testGetListWithExclusiveShippingInputAndBothCatalogDisplays(): void
    {
        $this->assertShippingListPrices(10, 10.75);
    }

    private function assertShippingListPrices(float $expectedExclTax, float $expectedInclTax): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        Bootstrap::getObjectManager()->get(ApiMutableScopeConfig::class)->setValue(
            'tax/classes/shipping_tax_class',
            (string)$fixtures->get('shipping_tax_class')->getClassId(),
            'store',
            'default'
        );
        $cartId = $fixtures->get('cart')->getId();
        self::assertNotEmpty($cartId, 'quote fixture failed');

        $rates = $this->_webApiCall($this->getListServiceInfo($cartId), ['cartId' => $cartId]);
        $flatRates = array_values(array_filter(
            $rates,
            static fn (array $rate): bool => $rate['carrier_code'] === 'flatrate'
                && $rate['method_code'] === 'flatrate'
        ));
        self::assertCount(1, $flatRates);
        $rate = $flatRates[0];
        self::assertEquals(10, $rate[ShippingMethodInterface::KEY_SHIPPING_AMOUNT]);
        self::assertEquals(10, $rate[ShippingMethodInterface::KEY_BASE_SHIPPING_AMOUNT]);
        self::assertTrue($rate[ShippingMethodInterface::KEY_AVAILABLE]);
        self::assertEquals($expectedExclTax, $rate[ShippingMethodInterface::KEY_PRICE_EXCL_TAX]);
        self::assertEquals($expectedInclTax, $rate[ShippingMethodInterface::KEY_PRICE_INCL_TAX]);
    }

    /**
     * Service info
     *
     * @param int $cartId
     * @return array
     */
    protected function getListServiceInfo($cartId)
    {
        return [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/shipping-methods',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];
    }

    /**
     * Convert rate models array to data array
     *
     * @param string $currencyCode
     * @param \Magento\Quote\Model\Quote\Address\Rate[] $groupedRates
     * @return array
     */
    protected function convertRates($groupedRates, $currencyCode)
    {
        $result = [];
        /** @var \Magento\Quote\Model\Cart\ShippingMethodConverter $converter */
        $converter = $this->objectManager->create(\Magento\Quote\Model\Cart\ShippingMethodConverter::class);
        foreach ($groupedRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $result[] = $converter->modelToDataObject($rate, $currencyCode)->__toArray();
            }
        }
        return $result;
    }
}
