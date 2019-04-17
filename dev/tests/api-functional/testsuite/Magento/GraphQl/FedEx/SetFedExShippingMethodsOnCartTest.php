<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\FedEx;

use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\GraphQl\Quote\GetQuoteShippingAddressIdByReservedQuoteId;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for setting "FedEx" shipping method on cart
 * | Code         | Label
 * --------------------------------------
 * | FEDEX_GROUND | Ground
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
     * @var GetQuoteShippingAddressIdByReservedQuoteId
     */
    private $getQuoteShippingAddressIdByReservedQuoteId;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->getQuoteShippingAddressIdByReservedQuoteId = $objectManager->get(
            GetQuoteShippingAddressIdByReservedQuoteId::class
        );
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
        $shippingAddressId = $this->getQuoteShippingAddressIdByReservedQuoteId->execute($quoteReservedId);

        $query = $this->getQuery($maskedQuoteId, $shippingAddressId, self::CARRIER_CODE, $methodCode);
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

        self::assertArrayHasKey('label', $shippingAddress['selected_shipping_method']);
        self::assertEquals(
            self::CARRIER_LABEL . ' - ' . $methodLabel,
            $shippingAddress['selected_shipping_method']['label']
        );
    }

    /**
     * @return array
     */
    public function dataProviderShippingMethods(): array
    {
        return [
            'Ground' => ['FEDEX_GROUND', 'Ground'],
        ];
    }

    /**
     * Generates query for setting the specified shipping method on cart
     *
     * @param int $shippingAddressId
     * @param string $maskedQuoteId
     * @param string $carrierCode
     * @param string $methodCode
     * @return string
     */
    private function getQuery(
        string $maskedQuoteId,
        int $shippingAddressId,
        string $carrierCode,
        string $methodCode
    ): string {
        return <<<QUERY
mutation {
  setShippingMethodsOnCart(input: {
    cart_id: "$maskedQuoteId"
    shipping_methods: [
      {
        cart_address_id: $shippingAddressId
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
          label
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
