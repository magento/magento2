<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for get available shipping methods
 */
class GetAvailableShippingMethodsTest extends GraphQlAbstract
{
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
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
    }

    /**
     * Test case: get available shipping methods from current customer quote
     *
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     */
    public function testGetAvailableShippingMethods()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $response = $this->graphQlQuery($this->getQuery($maskedQuoteId));

        self::assertArrayHasKey('cart', $response);
        self::assertArrayHasKey('shipping_addresses', $response['cart']);
        self::assertCount(1, $response['cart']['shipping_addresses']);
        self::assertArrayHasKey('available_shipping_methods', $response['cart']['shipping_addresses'][0]);
        self::assertCount(1, $response['cart']['shipping_addresses'][0]['available_shipping_methods']);

        $expectedAddressData = [
            'amount' => [
                'value' => 10,
                'currency' => 'USD',
            ],
            'base_amount' => [
                'value' => 10,
                'currency' => 'USD',
            ],
            'carrier_code' => 'flatrate',
            'carrier_title' => 'Flat Rate',
            'error_message' => '',
            'method_code' => 'flatrate',
            'method_title' => 'Fixed',
            'price_incl_tax' => [
                'value' => 10,
                'currency' => 'USD',
            ],
            'price_excl_tax' => [
                'value' => 10,
                'currency' => 'USD',
            ],
        ];
        self::assertEquals(
            $expectedAddressData,
            $response['cart']['shipping_addresses'][0]['available_shipping_methods'][0]
        );
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     */
    public function testGetAvailableShippingMethodsFromCustomerCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$maskedQuoteId\""
        );
        $this->graphQlQuery($this->getQuery($maskedQuoteId));
    }

    /**
     * Test case: get available shipping methods when all shipping methods are disabled
     *
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/disable_offline_shipping_methods.php
     */
    public function testGetAvailableShippingMethodsIfShippingMethodsAreNotPresent()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $response = $this->graphQlQuery($this->getQuery($maskedQuoteId));

        self::assertEmpty($response['cart']['shipping_addresses'][0]['available_shipping_methods']);
    }

    /**
     * Test case: get available shipping methods from non-existent cart
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Could not find a cart with ID "non_existent_masked_id"
     */
    public function testGetAvailableShippingMethodsOfNonExistentCart()
    {
        $maskedQuoteId = 'non_existent_masked_id';
        $query = $this->getQuery($maskedQuoteId);

        $this->graphQlQuery($query);
    }

    /**
     * @param string $maskedQuoteId
     * @return string
     */
    private function getQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
query {
  cart (cart_id: "{$maskedQuoteId}") {
    shipping_addresses {
        available_shipping_methods {
          amount {
            value
            currency
          }
          base_amount {
            value
            currency
          }
          carrier_code
          carrier_title
          error_message
          method_code
          method_title
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
