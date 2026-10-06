<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Customer\Test\Fixture\CustomerGroup;
use Magento\Framework\Exception\AuthenticationException;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Quote\Test\Fixture\QuoteIdMask;
use Magento\Tax\Test\Fixture\CustomerTaxClass;
use Magento\Tax\Test\Fixture\ProductTaxClass;
use Magento\Tax\Test\Fixture\TaxRate as TaxRateFixture;
use Magento\Tax\Test\Fixture\TaxRule as TaxRuleFixture;
use Magento\TestFramework\App\ApiMutableScopeConfig;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for get selected shipping method
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GetSelectedShippingMethodTest extends GraphQlAbstract
{
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
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    #[
        Config('tax/calculation/price_includes_tax', 1, 'store', 'default'),
        Config('tax/calculation/shipping_includes_tax', 1, 'store', 'default'),
        Config('tax/calculation/based_on', 'shipping', 'store', 'default'),
        Config('tax/cart_display/shipping', 2, 'store', 'default'),
        Config('tax/display/shipping', 2, 'store', 'default'),
        Config('shipping/origin/country_id', 'US', 'store', 'default'),
        Config('shipping/origin/region_id', 12, 'store', 'default'),
        Config('shipping/origin/postcode', '90034', 'store', 'default'),
        Config('carriers/flatrate/active', 1, 'store', 'default'),
        Config('carriers/flatrate/price', 10, 'store', 'default'),
        Config('carriers/flatrate/type', 'O', 'store', 'default'),
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
        DataFixture(SetShippingAddress::class, self::SHIPPING_ADDRESS_DATA),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'masked_cart')
    ]
    public function testGetSelectedShippingMethodWithTax(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $maskedQuoteId = $fixtures->get('masked_cart')->getMaskedId();
        $headers = $this->getHeaderMap($fixtures->get('customer')->getEmail());
        $this->setFixtureShippingMethod($maskedQuoteId, $headers);

        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query, [], '', $headers);

        self::assertArrayHasKey('cart', $response);
        self::assertArrayHasKey('shipping_addresses', $response['cart']);
        self::assertCount(1, $response['cart']['shipping_addresses']);

        $shippingAddress = current($response['cart']['shipping_addresses']);
        self::assertArrayHasKey('selected_shipping_method', $shippingAddress);

        self::assertArrayHasKey('carrier_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals('flatrate', $shippingAddress['selected_shipping_method']['carrier_code']);

        self::assertArrayHasKey('method_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals('flatrate', $shippingAddress['selected_shipping_method']['method_code']);

        self::assertArrayHasKey('carrier_title', $shippingAddress['selected_shipping_method']);
        self::assertEquals('Flat Rate', $shippingAddress['selected_shipping_method']['carrier_title']);

        self::assertArrayHasKey('method_title', $shippingAddress['selected_shipping_method']);
        self::assertEquals('Fixed', $shippingAddress['selected_shipping_method']['method_title']);

        self::assertArrayHasKey('amount', $shippingAddress['selected_shipping_method']);
        $amount = $shippingAddress['selected_shipping_method']['amount'];

        self::assertArrayHasKey('value', $amount);
        self::assertEquals(10, $amount['value']);
        self::assertArrayHasKey('currency', $amount);
        self::assertEquals('USD', $amount['currency']);

        self::assertArrayHasKey('price_excl_tax', $shippingAddress['selected_shipping_method']);
        $priceExclTax = $shippingAddress['selected_shipping_method']['price_excl_tax'];

        self::assertArrayHasKey('value', $priceExclTax);
        self::assertEquals(10, $priceExclTax['value']);
        self::assertArrayHasKey('currency', $priceExclTax);
        self::assertEquals('USD', $priceExclTax['currency']);

        self::assertArrayHasKey('amount', $shippingAddress['selected_shipping_method']);
        $priceInclTax = $shippingAddress['selected_shipping_method']['price_incl_tax'];

        self::assertArrayHasKey('value', $priceInclTax);
        self::assertEquals(10.75, $priceInclTax['value']);
        self::assertArrayHasKey('currency', $priceInclTax);
        self::assertEquals('USD', $priceInclTax['currency']);
    }

    #[
        Config('tax/calculation/price_includes_tax', 0, 'store', 'default'),
        Config('tax/calculation/shipping_includes_tax', 1, 'store', 'default'),
        Config('tax/calculation/based_on', 'shipping', 'store', 'default'),
        Config('tax/cart_display/shipping', 2, 'store', 'default'),
        Config('tax/display/shipping', 2, 'store', 'default'),
        Config('shipping/origin/country_id', 'US', 'store', 'default'),
        Config('shipping/origin/region_id', 12, 'store', 'default'),
        Config('shipping/origin/postcode', '90034', 'store', 'default'),
        Config('carriers/flatrate/active', 1, 'store', 'default'),
        Config('carriers/flatrate/price', 10, 'store', 'default'),
        Config('carriers/flatrate/type', 'O', 'store', 'default'),
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
        DataFixture(SetShippingAddress::class, [
            'cart_id' => '$cart.id$',
            'address' => [
                'country_id' => 'CA',
                'region_id' => null,
                'region_code' => 'ON',
                'postcode' => 'M4L 1V3',
                'city' => 'Toronto'
            ]
        ]),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'masked_cart')
    ]
    public function testGetSelectedShippingMethodWithAddressWithoutTax(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $maskedQuoteId = $fixtures->get('masked_cart')->getMaskedId();
        $headers = $this->getHeaderMap($fixtures->get('customer')->getEmail());
        $this->setFixtureShippingMethod($maskedQuoteId, $headers);

        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query, [], '', $headers);

        self::assertArrayHasKey('cart', $response);
        self::assertArrayHasKey('shipping_addresses', $response['cart']);
        self::assertCount(1, $response['cart']['shipping_addresses']);

        $shippingAddress = current($response['cart']['shipping_addresses']);
        self::assertArrayHasKey('selected_shipping_method', $shippingAddress);

        self::assertArrayHasKey('carrier_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals('flatrate', $shippingAddress['selected_shipping_method']['carrier_code']);

        self::assertArrayHasKey('method_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals('flatrate', $shippingAddress['selected_shipping_method']['method_code']);

        self::assertArrayHasKey('carrier_title', $shippingAddress['selected_shipping_method']);
        self::assertEquals('Flat Rate', $shippingAddress['selected_shipping_method']['carrier_title']);

        self::assertArrayHasKey('method_title', $shippingAddress['selected_shipping_method']);
        self::assertEquals('Fixed', $shippingAddress['selected_shipping_method']['method_title']);

        self::assertArrayHasKey('amount', $shippingAddress['selected_shipping_method']);
        $amount = $shippingAddress['selected_shipping_method']['amount'];

        self::assertArrayHasKey('value', $amount);
        self::assertEquals(10, $amount['value']);
        self::assertArrayHasKey('currency', $amount);
        self::assertEquals('USD', $amount['currency']);

        self::assertArrayHasKey('price_excl_tax', $shippingAddress['selected_shipping_method']);
        $priceExclTax = $shippingAddress['selected_shipping_method']['price_excl_tax'];

        self::assertArrayHasKey('value', $priceExclTax);
        self::assertEquals(10, $priceExclTax['value']);
        self::assertArrayHasKey('currency', $priceExclTax);
        self::assertEquals('USD', $priceExclTax['currency']);

        self::assertArrayHasKey('amount', $shippingAddress['selected_shipping_method']);
        $priceInclTax = $shippingAddress['selected_shipping_method']['price_incl_tax'];

        self::assertArrayHasKey('value', $priceInclTax);
        self::assertEquals(10, $priceInclTax['value']);
        self::assertArrayHasKey('currency', $priceInclTax);
        self::assertEquals('USD', $priceInclTax['currency']);
    }

    /**
     * @magentoConfigFixture default_store tax/calculation/shipping_includes_tax 0
     * @magentoConfigFixture default_store tax/cart_display/shipping 1
     * @magentoConfigFixture default_store tax/classes/shipping_tax_class 0
     * @magentoConfigFixture default_store tax/display/shipping 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     */
    public function testGetSelectedShippingMethodWithoutTax(): void
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('cart', $response);
        self::assertArrayHasKey('shipping_addresses', $response['cart']);
        self::assertCount(1, $response['cart']['shipping_addresses']);

        $shippingAddress = current($response['cart']['shipping_addresses']);
        self::assertArrayHasKey('selected_shipping_method', $shippingAddress);

        self::assertArrayHasKey('carrier_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals('flatrate', $shippingAddress['selected_shipping_method']['carrier_code']);

        self::assertArrayHasKey('method_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals('flatrate', $shippingAddress['selected_shipping_method']['method_code']);

        self::assertArrayHasKey('carrier_title', $shippingAddress['selected_shipping_method']);
        self::assertEquals('Flat Rate', $shippingAddress['selected_shipping_method']['carrier_title']);

        self::assertArrayHasKey('method_title', $shippingAddress['selected_shipping_method']);
        self::assertEquals('Fixed', $shippingAddress['selected_shipping_method']['method_title']);

        self::assertArrayHasKey('amount', $shippingAddress['selected_shipping_method']);
        $amount = $shippingAddress['selected_shipping_method']['amount'];

        self::assertArrayHasKey('value', $amount);
        self::assertEquals(10, $amount['value']);
        self::assertArrayHasKey('currency', $amount);
        self::assertEquals('USD', $amount['currency']);

        self::assertArrayHasKey('price_excl_tax', $shippingAddress['selected_shipping_method']);
        $priceExclTax = $shippingAddress['selected_shipping_method']['price_excl_tax'];

        self::assertArrayHasKey('value', $priceExclTax);
        self::assertEquals(10, $priceExclTax['value']);
        self::assertArrayHasKey('currency', $priceExclTax);
        self::assertEquals('USD', $priceExclTax['currency']);

        self::assertArrayHasKey('amount', $shippingAddress['selected_shipping_method']);
        $priceInclTax = $shippingAddress['selected_shipping_method']['price_incl_tax'];

        self::assertArrayHasKey('value', $priceInclTax);
        self::assertEquals(10, $priceInclTax['value']);
        self::assertArrayHasKey('currency', $priceInclTax);
        self::assertEquals('USD', $priceInclTax['currency']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     */
    public function testGetSelectedShippingMethodBeforeSet(): void
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('cart', $response);
        self::assertArrayHasKey('shipping_addresses', $response['cart']);
        self::assertCount(1, $response['cart']['shipping_addresses']);

        $shippingAddress = current($response['cart']['shipping_addresses']);
        self::assertArrayHasKey('selected_shipping_method', $shippingAddress);
        self::assertNull($shippingAddress['selected_shipping_method']);
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     */
    public function testGetSelectedShippingMethodFromGuestCart(): void
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId);

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$maskedQuoteId\""
        );
        $this->graphQlQuery($query, [], '', $this->getHeaderMap());
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/Customer/_files/three_customers.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     */
    public function testGetSelectedShippingMethodFromAnotherCustomerCart(): void
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId);

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$maskedQuoteId\""
        );
        $this->graphQlQuery($query, [], '', $this->getHeaderMap('customer3@search.example.com'));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     */
    public function testGetGetSelectedShippingMethodIfShippingMethodIsNotSet(): void
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId);

        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('cart', $response);
        self::assertArrayHasKey('shipping_addresses', $response['cart']);
        self::assertCount(1, $response['cart']['shipping_addresses']);

        $shippingAddress = current($response['cart']['shipping_addresses']);
        self::assertArrayHasKey('selected_shipping_method', $shippingAddress);
        self::assertNull($shippingAddress['selected_shipping_method']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     *
     */
    public function testGetGetSelectedShippingMethodOfNonExistentCart(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Could not find a cart with ID "non_existent_masked_id"');

        $maskedQuoteId = 'non_existent_masked_id';
        $query = $this->getQuery($maskedQuoteId);

        $this->graphQlQuery($query, [], '', $this->getHeaderMap());
    }

    #[
        Config('tax/calculation/price_includes_tax', 0, 'store', 'default'),
        Config('tax/calculation/shipping_includes_tax', 1, 'store', 'default'),
        Config('tax/calculation/based_on', 'shipping', 'store', 'default'),
        Config('tax/cart_display/price', 1, 'store', 'default'),
        Config('tax/cart_display/shipping', 1, 'store', 'default'),
        Config('tax/display/type', 2, 'store', 'default'),
        Config('tax/display/shipping', 1, 'store', 'default'),
        Config('shipping/origin/country_id', 'US', 'store', 'default'),
        Config('shipping/origin/region_id', 1, 'store', 'default'),
        Config('shipping/origin/postcode', '75477', 'store', 'default'),
        Config('carriers/flatrate/active', 1, 'store', 'default'),
        Config('carriers/flatrate/price', 10, 'store', 'default'),
        Config('carriers/flatrate/type', 'O', 'store', 'default'),
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
        DataFixture(SetShippingAddress::class, self::SHIPPING_ADDRESS_DATA),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'masked_cart')
    ]
    public function testInclusiveShippingInputWithInclusiveCatalogDisplay(): void
    {
        $this->assertSelectedShippingPrices(9.3, 10, 9.3);
    }

    #[
        Config('tax/calculation/price_includes_tax', 0, 'store', 'default'),
        Config('tax/calculation/shipping_includes_tax', 1, 'store', 'default'),
        Config('tax/calculation/based_on', 'shipping', 'store', 'default'),
        Config('tax/cart_display/price', 1, 'store', 'default'),
        Config('tax/cart_display/shipping', 1, 'store', 'default'),
        Config('tax/display/type', 3, 'store', 'default'),
        Config('tax/display/shipping', 1, 'store', 'default'),
        Config('shipping/origin/country_id', 'US', 'store', 'default'),
        Config('shipping/origin/region_id', 1, 'store', 'default'),
        Config('shipping/origin/postcode', '75477', 'store', 'default'),
        Config('carriers/flatrate/active', 1, 'store', 'default'),
        Config('carriers/flatrate/price', 10, 'store', 'default'),
        Config('carriers/flatrate/type', 'O', 'store', 'default'),
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
        DataFixture(SetShippingAddress::class, self::SHIPPING_ADDRESS_DATA),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'masked_cart')
    ]
    public function testInclusiveShippingInputWithBothCatalogDisplays(): void
    {
        $this->assertSelectedShippingPrices(9.3, 10, 9.3);
    }

    #[
        Config('tax/calculation/price_includes_tax', 1, 'store', 'default'),
        Config('tax/calculation/shipping_includes_tax', 0, 'store', 'default'),
        Config('tax/calculation/based_on', 'shipping', 'store', 'default'),
        Config('tax/cart_display/price', 1, 'store', 'default'),
        Config('tax/cart_display/shipping', 1, 'store', 'default'),
        Config('tax/display/type', 2, 'store', 'default'),
        Config('tax/display/shipping', 1, 'store', 'default'),
        Config('shipping/origin/country_id', 'US', 'store', 'default'),
        Config('shipping/origin/region_id', 1, 'store', 'default'),
        Config('shipping/origin/postcode', '75477', 'store', 'default'),
        Config('carriers/flatrate/active', 1, 'store', 'default'),
        Config('carriers/flatrate/price', 10, 'store', 'default'),
        Config('carriers/flatrate/type', 'O', 'store', 'default'),
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
        DataFixture(SetShippingAddress::class, self::SHIPPING_ADDRESS_DATA),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'masked_cart')
    ]
    public function testExclusiveShippingInputWithInclusiveCatalogDisplay(): void
    {
        $this->assertSelectedShippingPrices(10, 10.75, 10);
    }

    #[
        Config('tax/calculation/price_includes_tax', 1, 'store', 'default'),
        Config('tax/calculation/shipping_includes_tax', 0, 'store', 'default'),
        Config('tax/calculation/based_on', 'shipping', 'store', 'default'),
        Config('tax/cart_display/price', 1, 'store', 'default'),
        Config('tax/cart_display/shipping', 1, 'store', 'default'),
        Config('tax/display/type', 3, 'store', 'default'),
        Config('tax/display/shipping', 1, 'store', 'default'),
        Config('shipping/origin/country_id', 'US', 'store', 'default'),
        Config('shipping/origin/region_id', 1, 'store', 'default'),
        Config('shipping/origin/postcode', '75477', 'store', 'default'),
        Config('carriers/flatrate/active', 1, 'store', 'default'),
        Config('carriers/flatrate/price', 10, 'store', 'default'),
        Config('carriers/flatrate/type', 'O', 'store', 'default'),
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
        DataFixture(SetShippingAddress::class, self::SHIPPING_ADDRESS_DATA),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'masked_cart')
    ]
    public function testExclusiveShippingInputWithBothCatalogDisplays(): void
    {
        $this->assertSelectedShippingPrices(10, 10.75, 10);
    }

    private function assertSelectedShippingPrices(
        float $expectedExclTax,
        float $expectedInclTax,
        float $expectedAmount
    ): void {
        $fixtures = DataFixtureStorageManager::getStorage();
        $maskedQuoteId = $fixtures->get('masked_cart')->getMaskedId();
        $headers = $this->getHeaderMap($fixtures->get('customer')->getEmail());
        $this->setFixtureShippingMethod($maskedQuoteId, $headers);
        $response = $this->graphQlQuery($this->getQuery($maskedQuoteId), [], '', $headers);

        self::assertCount(1, $response['cart']['shipping_addresses']);
        $method = $response['cart']['shipping_addresses'][0]['selected_shipping_method'];
        self::assertEquals('flatrate', $method['carrier_code']);
        self::assertEquals('flatrate', $method['method_code']);
        foreach ([
            'price_excl_tax' => $expectedExclTax,
            'price_incl_tax' => $expectedInclTax,
            'amount' => $expectedAmount
        ] as $field => $expected) {
            self::assertEquals($expected, $method[$field]['value'], $field);
            self::assertEquals('USD', $method[$field]['currency'], $field);
        }
    }

    private function setFixtureShippingMethod(string $maskedQuoteId, array $headers): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        Bootstrap::getObjectManager()->get(ApiMutableScopeConfig::class)->setValue(
            'tax/classes/shipping_tax_class',
            (string)$fixtures->get('shipping_tax_class')->getClassId(),
            'store',
            'default'
        );
        // API config fixtures run after data fixtures, so collect totals with the test configuration.
        $mutation = <<<MUTATION
mutation {
  setShippingMethodsOnCart(input: {
    cart_id: "$maskedQuoteId",
    shipping_methods: [{carrier_code: "flatrate", method_code: "flatrate"}]
  }) {
    cart {
      id
    }
  }
}
MUTATION;
        $this->graphQlMutation($mutation, [], '', $headers);
    }

    /**
     * @param string $username
     * @param string $password
     * @return array
     * @throws AuthenticationException
     */
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        return ['Authorization' => 'Bearer ' . $customerToken];
    }

    /**
     * @param string $maskedQuoteId
     * @return string
     */
    private function getQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
{
  cart(cart_id: "$maskedQuoteId") {
    shipping_addresses {
      available_shipping_methods {
        carrier_code
        method_code
      }
      selected_shipping_method {
        carrier_code
        method_code
        carrier_title
        method_title
        amount {
            value
            currency
        }
        price_excl_tax {
          value
          currency
        }
        price_incl_tax {
          value
          currency
        }
      }
    }
  }
}
QUERY;
    }
}
