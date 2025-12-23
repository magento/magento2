<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\GraphQl\Quote\GetQuoteItemIdByReservedQuoteIdAndSku;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for getting is_virtual from cart
 */
class GetShippingAddressWhenCartIsVirtualTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @var GetQuoteItemIdByReservedQuoteIdAndSku
     */
    private $getQuoteItemIdByReservedQuoteIdAndSku;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->getQuoteItemIdByReservedQuoteIdAndSku = $objectManager->get(
            GetQuoteItemIdByReservedQuoteIdAndSku::class
        );
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_virtual_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     */
    public function testGetShippingAddressForVirtualCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query = $this->getQuery($maskedQuoteId);
        $response = $this->graphQlQuery($query);
        $itemId = $this->getQuoteItemIdByReservedQuoteIdAndSku->execute('test_quote', 'simple_product');

        $expectedShippingAddressData = [
            'firstname' => 'John',
            'lastname' => 'Smith',
            'company' => 'CompanyName',
            'street' => [
                'Green str, 67'
            ],
            'city' => 'CityM',
            'region' => [
                'code' => 'AL',
                'label' => 'Alabama',
            ],
            'postcode' => '75477',
            'country' => [
                'code' => 'US',
                'label' => 'US',
            ],
            'telephone' => '3468676',
            '__typename' => 'ShippingCartAddress',
        ];

        $this->assertArrayHasKey('cart', $response);
        $this->assertArrayHasKey('is_virtual', $response['cart']);
        $this->assertFalse($response['cart']['is_virtual']);
        $this->assertArrayHasKey('shipping_addresses', $response['cart']);
        $this->assertEquals($expectedShippingAddressData, current($response['cart']['shipping_addresses']));

        $query2 = $this->getQueryForItemRemove($maskedQuoteId, $itemId);
        $this->graphQlMutation($query2);
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey('cart', $response);
        $this->assertArrayHasKey('is_virtual', $response['cart']);
        $this->assertTrue($response['cart']['is_virtual']);
        $this->assertArrayHasKey('shipping_addresses', $response['cart']);
        $this->assertFalse(current($response['cart']['shipping_addresses']));
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
    is_virtual
    total_quantity
    items {
      id
      product {
        name
        sku
      }
      quantity
      errors {
        code
        message
      }
    }
    shipping_addresses {
      firstname
      lastname
      company
      street
      city
      region
      {
        code
        label
      }
      postcode
      country
      {
        code
        label
      }
      telephone
      __typename
    }
  }
}
QUERY;
    }

    /**
     * @param string $maskedQuoteId
     * @param int $itemId
     * @return string
     */
    private function getQueryForItemRemove(string $maskedQuoteId, int $itemId): string
    {
        return <<<QUERY
mutation {
  removeItemFromCart(
    input: {
      cart_id: "{$maskedQuoteId}"
      cart_item_id: {$itemId}
    }
  ) {
    cart {
      items {
        quantity
      }
    }
  }
}
QUERY;
    }
}
