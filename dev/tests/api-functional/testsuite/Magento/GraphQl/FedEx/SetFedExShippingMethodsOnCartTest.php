<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\FedEx;

use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for setting FedEx shipping method on cart.
 * Current class covers following base FedEx shipping methods:
 *
 * | Code                   | Label
 * --------------------------------------
 * | FEDEX_GROUND           | Ground
 * | SMART_POST             | Smart Post
 * | FEDEX_EXPRESS_SAVER    | Express Saver
 * | PRIORITY_OVERNIGHT     | Priority Overnight
 * | FEDEX_2_DAY            | 2 Day
 * | FIRST_OVERNIGHT        | First Overnight
 * | INTERNATIONAL_ECONOMY  |International Economy
 * | INTERNATIONAL_PRIORITY | International Priority
 */
class SetFedExShippingMethodsOnCartTest extends GraphQlAbstract
{
    /**
     * Defines carrier label for "FedEx" shipping method
     */
    const CARRIER_LABEL = 'Federal Express';

    /**
     * Defines carrier code for "FedEx" shipping method
     */
    const CARRIER_CODE = 'fedex';

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
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/set_weight_to_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/FedEx/_files/enable_fedex_shipping_method.php
     *
     * @dataProvider dataProviderShippingMethods
     * @param string $methodCode
     * @param string $methodLabel
     */
    public function testSetFedExShippingMethod(string $methodCode, string $methodLabel)
    {
        $quoteReservedId = 'test_quote';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($quoteReservedId);

        $query = $this->getQuery($maskedQuoteId, self::CARRIER_CODE, $methodCode);
        $response = $this->sendRequestWithToken($query);

        self::assertArrayHasKey('setShippingMethodsOnCart', $response);
        self::assertArrayHasKey('cart', $response['setShippingMethodsOnCart']);
        self::assertArrayHasKey('shipping_addresses', $response['setShippingMethodsOnCart']['cart']);
        self::assertCount(1, $response['setShippingMethodsOnCart']['cart']['shipping_addresses']);

        $shippingAddress = current($response['setShippingMethodsOnCart']['cart']['shipping_addresses']);
        self::assertArrayHasKey('selected_shipping_method', $shippingAddress);

        self::assertArrayHasKey('carrier_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals(self::CARRIER_CODE, $shippingAddress['selected_shipping_method']['carrier_code']);

        self::assertArrayHasKey('method_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals($methodCode, $shippingAddress['selected_shipping_method']['method_code']);

        self::assertArrayHasKey('carrier_title', $shippingAddress['selected_shipping_method']);
        self::assertEquals(self::CARRIER_LABEL, $shippingAddress['selected_shipping_method']['carrier_title']);

        self::assertArrayHasKey('method_title', $shippingAddress['selected_shipping_method']);
        self::assertEquals($methodLabel, $shippingAddress['selected_shipping_method']['method_title']);
    }

    /**
     * @return array
     */
    public function dataProviderShippingMethods(): array
    {
        return [
            'Ground' => ['FEDEX_GROUND', 'Ground'],
            'Smart Post' => ['SMART_POST', 'Smart Post'],
            'Express Saver' => ['FEDEX_EXPRESS_SAVER', 'Express Saver'],
            'Priority Overnight' => ['PRIORITY_OVERNIGHT', 'Priority Overnight'],
            '2 Day' => ['FEDEX_2_DAY', '2 Day'],
            'First Overnight' => ['FIRST_OVERNIGHT', 'First Overnight'],
        ];
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/set_weight_to_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_canada_address.php
     * @magentoApiDataFixture Magento/GraphQl/FedEx/_files/enable_fedex_shipping_method.php
     *
     * @dataProvider dataProviderShippingMethodsBasedOnCanadaAddress
     * @param string $methodCode
     * @param string $methodLabel
     */
    public function testSetFedExShippingMethodBasedOnCanadaAddress(string $methodCode, string $methodLabel)
    {
        $quoteReservedId = 'test_quote';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($quoteReservedId);

        $query = $this->getQuery($maskedQuoteId, self::CARRIER_CODE, $methodCode);
        $response = $this->sendRequestWithToken($query);

        self::assertArrayHasKey('setShippingMethodsOnCart', $response);
        self::assertArrayHasKey('cart', $response['setShippingMethodsOnCart']);
        self::assertArrayHasKey('shipping_addresses', $response['setShippingMethodsOnCart']['cart']);
        self::assertCount(1, $response['setShippingMethodsOnCart']['cart']['shipping_addresses']);

        $shippingAddress = current($response['setShippingMethodsOnCart']['cart']['shipping_addresses']);
        self::assertArrayHasKey('selected_shipping_method', $shippingAddress);

        self::assertArrayHasKey('carrier_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals(self::CARRIER_CODE, $shippingAddress['selected_shipping_method']['carrier_code']);

        self::assertArrayHasKey('method_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals($methodCode, $shippingAddress['selected_shipping_method']['method_code']);

        self::assertArrayHasKey('carrier_title', $shippingAddress['selected_shipping_method']);
        self::assertEquals(self::CARRIER_LABEL, $shippingAddress['selected_shipping_method']['carrier_title']);

        self::assertArrayHasKey('method_title', $shippingAddress['selected_shipping_method']);
        self::assertEquals($methodLabel, $shippingAddress['selected_shipping_method']['method_title']);
    }

    /**
     * @return array
     */
    public function dataProviderShippingMethodsBasedOnCanadaAddress(): array
    {
        return [
            'Ground' => ['FEDEX_GROUND', 'Ground'],
            'International Economy' => ['INTERNATIONAL_ECONOMY', 'International Economy'],
            'International Priority' => ['INTERNATIONAL_PRIORITY', 'International Priority'],
        ];
    }

    /**
     * Generates query for setting the specified shipping method on cart
     *
     * @param string $maskedQuoteId
     * @param string $carrierCode
     * @param string $methodCode
     * @return string
     */
    private function getQuery(
        string $maskedQuoteId,
        string $carrierCode,
        string $methodCode
    ): string {
        return <<<QUERY
mutation {
  setShippingMethodsOnCart(input: {
    cart_id: "$maskedQuoteId"
    shipping_methods: [
      {
        carrier_code: "$carrierCode"
        method_code: "$methodCode"
      }
    ]
  }) {
    cart {
      shipping_addresses {
        selected_shipping_method {
          carrier_code
          method_code
          carrier_title
          method_title
        }
      }
    } 
  }
}        
QUERY;
    }

    /**
     * Sends a GraphQL request with using a bearer token
     *
     * @param string $query
     * @return array
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    private function sendRequestWithToken(string $query): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken('customer@example.com', 'password');
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];

        return $this->graphQlMutation($query, [], '', $headerMap);
    }
}
