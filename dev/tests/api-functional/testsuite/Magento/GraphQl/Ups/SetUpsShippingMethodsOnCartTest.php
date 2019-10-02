<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Ups;

use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for setting "UPS" shipping method on cart. Current class covers the next UPS shipping methods:
 *
 * | Code      | Label
 * --------------------------------------
 * | 1DM       | Next Day Air Early AM
 * | 1DA       | Next Day Air
 * | 2DA       | 2nd Day Air
 * | 3DS       | 3 Day Select
 * | GND       | Ground
 * | STD       | Canada Standard
 * | XPR       | Worldwide Express
 * | WXS       | Worldwide Express Saver
 * | XDM       | Worldwide Express Plus
 * | XPD       | Worldwide Expedited
 *
 * Current class does not cover these UPS shipping methods (depends on address and sandbox settings)
 *
 * | Code      | Label
 * --------------------------------------
 * | 1DML      | Next Day Air Early AM Letter
 * | 1DAL      | Next Day Air Letter
 * | 1DAPI     | Next Day Air Intra (Puerto Rico)
 * | 1DP       | Next Day Air Saver
 * | 1DPL      | Next Day Air Saver Letter
 * | 2DM       | 2nd Day Air AM
 * | 2DML      | 2nd Day Air AM Letter
 * | 2DAL      | 2nd Day Air Letter
 * | GNDCOM    | Ground Commercial
 * | GNDRES    | Ground Residential
 * | XPRL      | Worldwide Express Letter
 * | XDML      | Worldwide Express Plus Letter
 */
class SetUpsShippingMethodsOnCartTest extends GraphQlAbstract
{
    /**
     * Defines carrier title for "UPS" shipping method
     */
    const CARRIER_TITLE = 'United Parcel Service';

    /**
     * Defines carrier code for "UPS" shipping method
     */
    const CARRIER_CODE = 'ups';

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
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoConfigFixture default_store carriers/ups/active 1
     * @magentoConfigFixture default_store carriers/ups/type UPS
     *
     * @dataProvider dataProviderShippingMethods
     * @param string $methodCode
     * @param string $methodTitle
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testSetUpsShippingMethod(string $methodCode, string $methodTitle)
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
        self::assertEquals(self::CARRIER_TITLE, $shippingAddress['selected_shipping_method']['carrier_title']);

        self::assertArrayHasKey('method_title', $shippingAddress['selected_shipping_method']);
        self::assertEquals($methodTitle, $shippingAddress['selected_shipping_method']['method_title']);
    }

    /**
     * @return array
     */
    public function dataProviderShippingMethods(): array
    {
        return [
            'Next Day Air Early AM' => ['1DM', 'Next Day Air Early AM'],
            'Next Day Air' => ['1DA', 'Next Day Air'],
            '2nd Day Air' => ['2DA', '2nd Day Air'],
            '3 Day Select' => ['3DS', '3 Day Select'],
            'Ground' => ['GND', 'Ground'],
        ];
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_canada_address.php
     * @magentoConfigFixture default_store carriers/ups/active 1
     * @magentoConfigFixture default_store carriers/ups/type UPS
     *
     * @dataProvider dataProviderShippingMethodsBasedOnCanadaAddress
     * @param string $methodCode
     * @param string $methodTitle
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testSetUpsShippingMethodBasedOnCanadaAddress(string $methodCode, string $methodTitle)
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
        self::assertEquals(self::CARRIER_TITLE, $shippingAddress['selected_shipping_method']['carrier_title']);

        self::assertArrayHasKey('method_title', $shippingAddress['selected_shipping_method']);
        self::assertEquals($methodTitle, $shippingAddress['selected_shipping_method']['method_title']);
    }

    /**
     * @return array
     */
    public function dataProviderShippingMethodsBasedOnCanadaAddress(): array
    {
        return [
            'Canada Standard' => ['STD', 'Canada Standard'],
            'Worldwide Express' => ['XPR', 'Worldwide Express'],
            'Worldwide Express Saver' => ['WXS', 'Worldwide Express Saver'],
            'Worldwide Express Plus' => ['XDM', 'Worldwide Express Plus'],
            'Worldwide Expedited' => ['XPD', 'Worldwide Expedited'],
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
