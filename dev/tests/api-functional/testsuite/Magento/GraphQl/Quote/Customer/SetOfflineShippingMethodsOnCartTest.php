<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for setting offline shipping methods on cart
 */
class SetOfflineShippingMethodsOnCartTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

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
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/OfflineShipping/_files/tablerates_weight.php
     * @magentoConfigFixture default_store carriers/flatrate/active 1
     * @magentoConfigFixture default_store carriers/tablerate/active 1
     * @magentoConfigFixture default_store carriers/freeshipping/active 1
     *
     * @param string $carrierCode
     * @param string $methodCode
     * @param string $carrierTitle
     * @param string $methodTitle
     * @param array $amount
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @dataProvider offlineShippingMethodDataProvider
     */
    public function testSetOfflineShippingMethod(
        string $carrierCode,
        string $methodCode,
        string $carrierTitle,
        string $methodTitle,
        array $amount
    ) {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = $this->getQuery(
            $maskedQuoteId,
            $methodCode,
            $carrierCode
        );
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

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

        self::assertArrayHasKey('carrier_title', $shippingAddress['selected_shipping_method']);
        self::assertEquals($carrierTitle, $shippingAddress['selected_shipping_method']['carrier_title']);

        self::assertArrayHasKey('method_title', $shippingAddress['selected_shipping_method']);
        self::assertEquals($methodTitle, $shippingAddress['selected_shipping_method']['method_title']);

        self::assertArrayHasKey('amount', $shippingAddress['selected_shipping_method']);
        self::assertEquals($amount, $shippingAddress['selected_shipping_method']['amount']);
    }

    /**
     * @return array
     */
    public function offlineShippingMethodDataProvider(): array
    {
        return [
            'flatrate_flatrate' => [
                'flatrate',
                'flatrate',
                'Flat Rate',
                'Fixed',
                ['value' => 10, 'currency' => 'USD'],
            ],
            'tablerate_bestway' => [
                'tablerate',
                'bestway',
                'Best Way',
                'Table Rate',
                ['value' => 10, 'currency' => 'USD'],
            ],
            'freeshipping_freeshipping' => [
                'freeshipping',
                'freeshipping',
                'Free Shipping',
                'Free',
                ['value' => 0, 'currency' => 'USD'],
            ],
        ];
    }

    /**
     * @param string $maskedQuoteId
     * @param string $shippingMethodCode
     * @param string $shippingCarrierCode
     * @return string
     */
    private function getQuery(
        string $maskedQuoteId,
        string $shippingMethodCode,
        string $shippingCarrierCode
    ): string {
        return <<<QUERY
mutation {
  setShippingMethodsOnCart(input: 
    {
      cart_id: "$maskedQuoteId", 
      shipping_methods: [{
        carrier_code: "$shippingCarrierCode"
        method_code: "$shippingMethodCode"
      }]
    }) {
    cart {
      shipping_addresses {
        selected_shipping_method {
          carrier_code
          method_code
          carrier_title
          method_title
          amount {
            value
            currency
          }
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
