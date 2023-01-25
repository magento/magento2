<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\Framework\Exception\AuthenticationException;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for get selected shipping method
 */
class GetSelectedShippingMethodTest extends GraphQlAbstract
{
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

    /**
     * @magentoConfigFixture default_store tax/calculation/shipping_includes_tax 1
     * @magentoConfigFixture default_store tax/cart_display/shipping 2
     * @magentoConfigFixture default_store tax/classes/shipping_tax_class 2
     * @magentoConfigFixture default_store tax/display/shipping 2
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/Tax/_files/tax_rule_region_1_al.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     */
    public function testGetSelectedShippingMethodWithTax(): void
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
        self::assertEquals(10.75, $priceInclTax['value']);
        self::assertArrayHasKey('currency', $priceInclTax);
        self::assertEquals('USD', $priceInclTax['currency']);
    }

    /**
     * @magentoConfigFixture default_store tax/calculation/shipping_includes_tax 1
     * @magentoConfigFixture default_store tax/cart_display/shipping 2
     * @magentoConfigFixture default_store tax/classes/shipping_tax_class 2
     * @magentoConfigFixture default_store tax/display/shipping 2
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/Tax/_files/tax_rule_region_1_al.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_canada_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     */
    public function testGetSelectedShippingMethodWithAddressWithoutTax(): void
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
