<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Usps;

use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for setting USPS shipping method on cart.
 * Current class covers following base USPS shipping methods:
 *
 * | Code      | Label
 * --------------------------------------
 * | 1         | Priority Mail
 * | 2         | Priority Mail Express Hold For Pickup
 * | 3         | Priority Mail Express
 * | 6         | Media Mail
 * | 7         | Library Mail
 * | 13        | Priority Mail Express Flat Rate Envelope
 * | 16        | Priority Mail Flat Rate Envelope
 * | 17        | Priority Mail Medium Flat Rate Box
 * | 22        | Priority Mail Large Flat Rate Box
 * | 27        | Priority Mail Express Flat Rate Envelope Hold For Pickup
 * | 28        | Priority Mail Small Flat Rate Box
 * | INT_1     | Priority Mail Express International
 * | INT_2     | Priority Mail International
 * | INT_8     | Priority Mail International Flat Rate Envelope
 * | INT_9     | Priority Mail International Medium Flat Rate Box
 * | INT_10    | Priority Mail Express International Flat Rate Envelope
 * | INT_11    | Priority Mail International Large Flat Rate Box
 * | INT_12    | USPS GXG Envelopes
 * | INT_15    | First-Class Package International Service
 * | INT_16    | Priority Mail International Small Flat Rate Box
 * | INT_20    | Priority Mail International Small Flat Rate Envelope
 */
class SetUspsShippingMethodsOnCartTest extends GraphQlAbstract
{
    /**
     * Defines carrier label for "USPS" shipping method
     */
    const CARRIER_TITLE = 'United States Postal Service';

    /**
     * Defines carrier code for "USPS" shipping method
     */
    const CARRIER_CODE = 'usps';

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
     * @magentoApiDataFixture Magento/GraphQl/Usps/_files/enable_usps_shipping_method.php
     *
     * @dataProvider dataProviderShippingMethods
     * @param string $methodCode
     * @param string $methodLabel
     */
    public function testSetUspsShippingMethod(string $methodCode, string $methodLabel)
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
        self::assertEquals($methodLabel, $shippingAddress['selected_shipping_method']['method_title']);
    }

    /**
     * @return array
     */
    public function dataProviderShippingMethods(): array
    {
        return [
            'Library Mail Parcel' =>
                ['7', 'Library Mail Parcel'],
            'Media Mail Parcel' => ['6', 'Media Mail Parcel'],
            'Priority Mail 3-Day Small Flat Rate Box' =>
                ['28', 'Priority Mail 3-Day Small Flat Rate Box'],
            'Priority Mail 3-Day Flat Rate Envelope' =>
                ['16', 'Priority Mail 3-Day Flat Rate Envelope'],
            'Priority Mail 3-Day' => ['1', 'Priority Mail 3-Day'],
            'Priority Mail 3-Day Small Flat Rate Envelope' =>
                ['42', 'Priority Mail 3-Day Small Flat Rate Envelope'],
            'Priority Mail 3-Day Medium Flat Rate Box' =>
                ['17', 'Priority Mail 3-Day Medium Flat Rate Box'],
            'Priority Mail 3-Day Large Flat Rate Box' =>
                ['22', 'Priority Mail 3-Day Large Flat Rate Box'],
            'Priority Mail Express 2-Day Flat Rate Envelope' =>
                ['13', 'Priority Mail Express 2-Day Flat Rate Envelope'],
            'Priority Mail Express 2-Day Flat Rate Envelope Hold For Pickup' =>
                ['27', 'Priority Mail Express 2-Day Flat Rate Envelope Hold For Pickup'],
            'Priority Mail Express 2-Day' =>
                ['3', 'Priority Mail Express 2-Day'],
            'Priority Mail Express 2-Day Hold For Pickup' =>
                ['2', 'Priority Mail Express 2-Day Hold For Pickup'],
        ];
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/set_weight_to_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_canada_address.php
     * @magentoApiDataFixture Magento/GraphQl/Usps/_files/enable_usps_shipping_method.php
     *
     * @dataProvider dataProviderShippingMethodsBasedOnCanadaAddress
     * @param string $methodCode
     * @param string $methodLabel
     */
    public function testSetUspsShippingMethodBasedOnCanadaAddress(string $methodCode, string $methodLabel)
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
        self::assertEquals($methodLabel, $shippingAddress['selected_shipping_method']['method_title']);
    }

    /**
     * @return array
     */
    public function dataProviderShippingMethodsBasedOnCanadaAddress(): array
    {
        return [
            'First-Class Package International Service' =>
                ['INT_15', 'First-Class Package International Service'],
            'Priority Mail International Small Flat Rate Envelope' =>
                ['INT_20', 'Priority Mail International Small Flat Rate Envelope'],
            'Priority Mail International Flat Rate Envelope' =>
                ['INT_8', 'Priority Mail International Flat Rate Envelope'],
            'Priority Mail International Small Flat Rate Box' =>
                ['INT_16', 'Priority Mail International Small Flat Rate Box'],
            'Priority Mail International' =>
                ['INT_2', 'Priority Mail International'],
            'Priority Mail Express International Flat Rate Envelope' =>
                ['INT_10', 'Priority Mail Express International Flat Rate Envelope'],
            'Priority Mail Express International' =>
                ['INT_1', 'Priority Mail Express International'],
            'Priority Mail International Medium Flat Rate Box' =>
                ['INT_9', 'Priority Mail International Medium Flat Rate Box'],
            'Priority Mail International Large Flat Rate Box' =>
                ['INT_11', 'Priority Mail International Large Flat Rate Box'],
            'USPS GXG Envelopes' =>
                ['INT_12', 'USPS GXG Envelopes'],
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
