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
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for setting "UPS" shipping method on cart
 */
class SetUpsShippingMethodsOnCartTest extends GraphQlAbstract
{
    /**
     * Defines carrier label for "UPS" shipping method
     */
    const CARRIER_LABEL = 'United Parcel Service';

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
        $this->getQuoteShippingAddressIdByReservedQuoteId = $objectManager->get(GetQuoteShippingAddressIdByReservedQuoteId::class);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Ups/_files/enable_ups_shipping_method.php
     *
     * @param string $methodCode
     * @param string $methodLabel
     * @dataProvider availableForCartShippingMethods
     */
    public function testSetAvailableUpsShippingMethodOnCart(string $methodCode, string $methodLabel)
    {
        $quoteReservedId = 'test_quote';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($quoteReservedId);
        $shippingAddressId = $this->getQuoteShippingAddressIdByReservedQuoteId->execute($quoteReservedId);

        $query = $this->getQuery($maskedQuoteId, $shippingAddressId, self::CARRIER_CODE, $methodCode);
        $response = $this->graphQlQuery($query);

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
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Ups/_files/enable_ups_shipping_method.php
     *
     * @param string $carrierMethodCode
     * @param string $carrierMethodLabel
     * @dataProvider notAvailableForCartShippingMethods
     */
    public function testSetNotAvailableForCartUpsShippingMethod(string $carrierMethodCode, string $carrierMethodLabel)
    {
        $quoteReservedId = 'test_quote';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($quoteReservedId);
        $shippingAddressId = $this->getQuoteShippingAddressIdByReservedQuoteId->execute($quoteReservedId);

        $query = $this->getQuery(
            $maskedQuoteId,
            $shippingAddressId,
            self::CARRIER_CODE,
            $carrierMethodCode
        );

        $this->expectExceptionMessage(
            "GraphQL response contains errors: Carrier with such method not found: " . self::CARRIER_CODE . ", " . $carrierMethodCode
        );

        $response = $this->sendRequestWithToken($query);

        $addressesInformation = $response['setShippingMethodsOnCart']['cart']['shipping_addresses'];
        $expectedResult = [
            'carrier_code' => self::CARRIER_CODE,
            'method_code' => $carrierMethodCode,
            'label' => self::CARRIER_LABEL . ' - ' . $carrierMethodLabel,
        ];
        self::assertEquals($addressesInformation[0]['selected_shipping_method'], $expectedResult);
    }

    /**
     * @return array
     */
    public function availableForCartShippingMethods(): array
    {
        $shippingMethods = ['1DM', '1DA', '2DA', '3DS', 'GND'];

        return $this->filterShippingMethodsByCodes($shippingMethods);
    }

    /**
     * @return array
     */
    public function notAvailableForCartShippingMethods(): array
    {
        $shippingMethods = ['1DML', '1DAL', '1DAPI', '1DP', '1DPL', '2DM', '2DML', '2DAL', 'GNDCOM', 'GNDRES', 'STD', 'XPR', 'WXS', 'XPRL', 'XDM', 'XDML', 'XPD'];

        return $this->filterShippingMethodsByCodes($shippingMethods);
    }

    /**
     * @param array $filter
     * @return array
     */
    private function filterShippingMethodsByCodes(array $filter):array
    {
        $result = [];
        foreach ($this->getAllUpsShippingMethods() as $shippingMethod) {
            if (in_array($shippingMethod[0], $filter)) {
                $result[] = $shippingMethod;
            }
        }
        return $result;
    }

    private function getAllUpsShippingMethods():array
    {
        return [
            ['1DM', 'Next Day Air Early AM'],
            ['1DML', 'Next Day Air Early AM Letter'],
            ['1DA', 'Next Day Air'],
            ['1DAL', 'Next Day Air Letter'],
            ['1DAPI', 'Next Day Air Intra (Puerto Rico)'],
            ['1DP', 'Next Day Air Saver'],
            ['1DPL', 'Next Day Air Saver Letter'],
            ['2DM', '2nd Day Air AM'],
            ['2DML', '2nd Day Air AM Letter'],
            ['2DA', '2nd Day Air'],
            ['2DAL', '2nd Day Air Letter'],
            ['3DS', '3 Day Select'],
            ['GND', 'Ground'],
            ['GNDCOM', 'Ground Commercial'],
            ['GNDRES', 'Ground Residential'],
            ['STD', 'Canada Standard'],
            ['XPR', 'Worldwide Express'],
            ['WXS', 'Worldwide Express Saver'],
            ['XPRL', 'Worldwide Express Letter'],
            ['XDM', 'Worldwide Express Plus'],
            ['XDML', 'Worldwide Express Plus Letter'],
            ['XPD', 'Worldwide Expedited'],
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

        return $this->graphQlQuery($query, [], '', $headerMap);
    }
}
