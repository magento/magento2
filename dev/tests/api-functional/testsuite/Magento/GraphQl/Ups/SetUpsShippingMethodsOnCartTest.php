<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Ups;

use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\GraphQl\Quote\GetQuoteShippingAddressIdByReservedQuoteId;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for setting "UPS" shipping method on cart
 */
class SetUpsShippingMethodsOnCartTest extends GraphQlAbstract
{
    /**
     * Defines carrier code for "UPS" shipping method
     */
    const CARRIER_CODE = 'ups';

    /**
     * Defines method code for the "Ground" UPS shipping
     */
    const CARRIER_METHOD_CODE_GROUND = 'GND';

    /**
     * @var GetQuoteShippingAddressIdByReservedQuoteId
     */
    private $getQuoteShippingAddressIdByReservedQuoteId;

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
        $this->getQuoteShippingAddressIdByReservedQuoteId = $objectManager->get(GetQuoteShippingAddressIdByReservedQuoteId::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Catalog/_files/products.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/Ups/_files/enable_ups_shipping_method.php
     */
    public function testSetUpsShippingMethod()
    {
        $quoteReservedId = 'test_quote';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($quoteReservedId);
        $shippingAddressId = $this->getQuoteShippingAddressIdByReservedQuoteId->execute($quoteReservedId);

        $query = $this->getQuery(
            $maskedQuoteId,
            $shippingAddressId,
            self::CARRIER_CODE,
            self::CARRIER_METHOD_CODE_GROUND
        );

        $response = $this->sendRequestWithToken($query);
        $addressesInformation = $response['setShippingMethodsOnCart']['cart']['shipping_addresses'];
        $expectedResult = [
            'carrier_code' => self::CARRIER_CODE,
            'method_code' => self::CARRIER_METHOD_CODE_GROUND,
            'label' => 'United Parcel Service - Ground',
        ];
        self::assertEquals($addressesInformation[0]['selected_shipping_method'], $expectedResult);
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

        return $this->graphQlQuery($query, [], '', $headerMap);
    }
}
