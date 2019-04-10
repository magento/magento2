<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\GraphQl\Quote\GetQuoteShippingAddressIdByReservedQuoteId;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for setting shipping methods on cart for customer
 */
class SetShippingMethodsOnCartTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @var GetQuoteShippingAddressIdByReservedQuoteId
     */
    private $getQuoteShippingAddressIdByReservedQuoteId;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->getQuoteShippingAddressIdByReservedQuoteId = $objectManager->get(
            GetQuoteShippingAddressIdByReservedQuoteId::class
        );
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     */
    public function testSetShippingMethodOnCartWithSimpleProduct()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $carrierCode = 'flatrate';
        $methodCode = 'flatrate';
        $quoteAddressId = $this->getQuoteShippingAddressIdByReservedQuoteId->execute('test_quote');

        $query = $this->getQuery(
            $maskedQuoteId,
            $methodCode,
            $carrierCode,
            $quoteAddressId
        );
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('setShippingMethodsOnCart', $response);
        self::assertArrayHasKey('cart', $response['setShippingMethodsOnCart']);
        self::assertArrayHasKey('shipping_addresses', $response['setShippingMethodsOnCart']['cart']);
        self::assertCount(1, $response['setShippingMethodsOnCart']['cart']['shipping_addresses']);

        $shippingAddress = current($response['setShippingMethodsOnCart']['cart']['shipping_addresses']);
        self::assertArrayHasKey('selected_shipping_method', $shippingAddress);

        self::assertArrayHasKey('carrier_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals($carrierCode, $shippingAddress['selected_shipping_method']['carrier_code']);

        self::assertArrayHasKey('method_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals($methodCode, $shippingAddress['selected_shipping_method']['method_code']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/enable_offline_shipping_methods.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     */
    public function testReSetShippingMethod()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $carrierCode = 'freeshipping';
        $methodCode = 'freeshipping';
        $quoteAddressId = $this->getQuoteShippingAddressIdByReservedQuoteId->execute('test_quote');

        $query = $this->getQuery(
            $maskedQuoteId,
            $methodCode,
            $carrierCode,
            $quoteAddressId
        );
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('setShippingMethodsOnCart', $response);
        self::assertArrayHasKey('cart', $response['setShippingMethodsOnCart']);
        self::assertArrayHasKey('shipping_addresses', $response['setShippingMethodsOnCart']['cart']);
        self::assertCount(1, $response['setShippingMethodsOnCart']['cart']['shipping_addresses']);

        $shippingAddress = current($response['setShippingMethodsOnCart']['cart']['shipping_addresses']);
        self::assertArrayHasKey('selected_shipping_method', $shippingAddress);

        self::assertArrayHasKey('carrier_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals($carrierCode, $shippingAddress['selected_shipping_method']['carrier_code']);

        self::assertArrayHasKey('method_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals($methodCode, $shippingAddress['selected_shipping_method']['method_code']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     *
     * @param string $input
     * @param string $message
     * @dataProvider dataProviderSetShippingMethodWithWrongParameters
     * @throws \Exception
     */
    public function testSetShippingMethodWithWrongParameters(string $input, string $message)
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $quoteAddressId = $this->getQuoteShippingAddressIdByReservedQuoteId->execute('test_quote');
        $input = str_replace(['cart_id_value', 'cart_address_id_value'], [$maskedQuoteId, $quoteAddressId], $input);

        $query = <<<QUERY
mutation {
  setShippingMethodsOnCart(input:  {
   {$input}     
  }) {
    cart {
      shipping_addresses {
        selected_shipping_method {
          carrier_code
        }
      }
    }
  }
}
QUERY;
        $this->expectExceptionMessage($message);
        $this->graphQlQuery($query, [], '', $this->getHeaderMap());
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function dataProviderSetShippingMethodWithWrongParameters(): array
    {
        return [
            'missed_cart_id' => [
                'shipping_methods: [{
                    cart_address_id: cart_address_id_value
                    carrier_code: "flatrate"
                    method_code: "flatrate"
                }]',
                'Required parameter "cart_id" is missing'
            ],
            'missed_shipping_methods' => [
                'cart_id: "cart_id_value"',
                'Required parameter "shipping_methods" is missing'
            ],
            'shipping_methods_are_empty' => [
                'cart_id: "cart_id_value" shipping_methods: []',
                'Required parameter "shipping_methods" is missing'
            ],
            'missed_cart_address_id' => [
                'cart_id: "cart_id_value", shipping_methods: [{
                    carrier_code: "flatrate"
                    method_code: "flatrate"
                }]',
                'Required parameter "cart_address_id" is missing.'
            ],
            'non_existent_cart_address_id' => [
                'cart_id: "cart_id_value", shipping_methods: [{
                    cart_address_id: -1
                    carrier_code: "flatrate"
                    method_code: "flatrate"
                }]',
                'Could not find a cart address with ID "-1"'
            ],
            'missed_carrier_code' => [
                'cart_id: "cart_id_value", shipping_methods: [{
                    cart_address_id: cart_address_id_value
                    method_code: "flatrate"
                }]',
                'Field ShippingMethodInput.carrier_code of required type String! was not provided.'
            ],
            'empty_carrier_code' => [
                'cart_id: "cart_id_value", shipping_methods: [{
                    cart_address_id: cart_address_id_value
                    carrier_code: ""
                    method_code: "flatrate"
                }]',
                'Required parameter "carrier_code" is missing.'
            ],
            'non_existent_carrier_code' => [
                'cart_id: "cart_id_value", shipping_methods: [{
                    cart_address_id: cart_address_id_value
                    carrier_code: "wrong-carrier-code"
                    method_code: "flatrate"
                }]',
                'Carrier with such method not found: wrong-carrier-code, flatrate'
            ],
            'missed_method_code' => [
                'cart_id: "cart_id_value", shipping_methods: [{
                    cart_address_id: cart_address_id_value
                    carrier_code: "flatrate"
                }]',
                'Required parameter "method_code" is missing.'
            ],
            'empty_method_code' => [
                'cart_id: "cart_id_value", shipping_methods: [{
                    cart_address_id: cart_address_id_value
                    carrier_code: "flatrate"
                    method_code: ""
                }]',
                'Required parameter "method_code" is missing.'
            ],
            'non_existent_method_code' => [
                'cart_id: "cart_id_value", shipping_methods: [{
                    cart_address_id: cart_address_id_value
                    carrier_code: "flatrate"
                    method_code: "wrong-carrier-code"
                }]',
                'Carrier with such method not found: flatrate, wrong-carrier-code'
            ],
            'non_existent_shopping_cart' => [
                'cart_id: "non_existent_masked_id", shipping_methods: [{
                    cart_address_id: cart_address_id_value
                    carrier_code: "flatrate"
                    method_code: "flatrate"
                }]',
                'Could not find a cart with ID "non_existent_masked_id"'
            ],
        ];
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/enable_offline_shipping_methods.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @expectedException \Exception
     * @expectedExceptionMessage You cannot specify multiple shipping methods.
     */
    public function testSetMultipleShippingMethods()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $quoteAddressId = $this->getQuoteShippingAddressIdByReservedQuoteId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setShippingMethodsOnCart(input:  {
   cart_id: "{$maskedQuoteId}", 
   shipping_methods: [
        {
            cart_address_id: {$quoteAddressId}
            carrier_code: "flatrate"
            method_code: "flatrate"
        }
        {
            cart_address_id: {$quoteAddressId}
            carrier_code: "flatrate"
            method_code: "flatrate"
        }
   ]
  }) {
    cart {
      shipping_addresses {
        selected_shipping_method {
          carrier_code
        }
      }
    }
  }
}
QUERY;
        $this->graphQlQuery($query, [], '', $this->getHeaderMap());
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     *
     * @expectedException \Exception
     */
    public function testSetShippingMethodToGuestCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $carrierCode = 'flatrate';
        $methodCode = 'flatrate';
        $quoteAddressId = $this->getQuoteShippingAddressIdByReservedQuoteId->execute('test_quote');
        $query = $this->getQuery(
            $maskedQuoteId,
            $methodCode,
            $carrierCode,
            $quoteAddressId
        );

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
     *
     * @expectedException \Exception
     */
    public function testSetShippingMethodToAnotherCustomerCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $carrierCode = 'flatrate';
        $methodCode = 'flatrate';
        $quoteAddressId = $this->getQuoteShippingAddressIdByReservedQuoteId->execute('test_quote');
        $query = $this->getQuery(
            $maskedQuoteId,
            $methodCode,
            $carrierCode,
            $quoteAddressId
        );

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$maskedQuoteId\""
        );
        $this->graphQlQuery($query, [], '', $this->getHeaderMap('customer2@search.example.com'));
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/quote_with_address.php
     */
    public function testSetShippingMethodIfCustomerIsNotOwnerOfAddress()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $carrierCode = 'flatrate';
        $methodCode = 'flatrate';
        $anotherQuoteAddressId = $this->getQuoteShippingAddressIdByReservedQuoteId->execute('guest_quote_with_address');
        $query = $this->getQuery(
            $maskedQuoteId,
            $methodCode,
            $carrierCode,
            $anotherQuoteAddressId
        );

        $this->expectExceptionMessage(
            "Cart does not contain address with ID \"{$anotherQuoteAddressId}\""
        );
        $this->graphQlQuery($query, [], '', $this->getHeaderMap());
    }

    /**
     * @param string $maskedQuoteId
     * @param string $shippingMethodCode
     * @param string $shippingCarrierCode
     * @param int $shippingAddressId
     * @return string
     */
    private function getQuery(
        string $maskedQuoteId,
        string $shippingMethodCode,
        string $shippingCarrierCode,
        int $shippingAddressId
    ): string {
        return <<<QUERY
mutation {
  setShippingMethodsOnCart(input: 
    {
      cart_id: "$maskedQuoteId", 
      shipping_methods: [{
        cart_address_id: $shippingAddressId
        carrier_code: "$shippingCarrierCode"
        method_code: "$shippingMethodCode"
      }]
    }) {
    cart {
      shipping_addresses {
        selected_shipping_method {
          carrier_code
          method_code
        }
      }
    }
  }
}
QUERY;
    }

    /**
     * @param string $username
     * @param string $password
     * @return array
     */
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        return $headerMap;
    }
}
