<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\ConfigurableProduct;

use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Add configurable product to cart testcases
 */
class AddConfigurableProductToCartTest extends GraphQlAbstract
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
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddConfigurableProductToCart()
    {
        $searchResponse = $this->graphQlQuery($this->getFetchProductQuery('configurable'));
        $product = current($searchResponse['products']['items']);

        $quantity = 2;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');
        $sku = $product['sku'];
        $attributeId = (int) $product['configurable_options'][0]['attribute_id'];
        $optionId = $product['configurable_options'][0]['values'][1]['value_index'];

        $query = $this->getQuery(
            $maskedQuoteId,
            $sku,
            $attributeId,
            $optionId,
            $quantity
        );

        $response = $this->graphQlMutation($query);

        $cartItem = current($response['addConfigurableProductsToCart']['cart']['items']);
        self::assertEquals($quantity, $cartItem['quantity']);
        self::assertEquals($sku, $cartItem['product']['sku']);
        self::assertArrayHasKey('configurable_options', $cartItem);

        $option = current($cartItem['configurable_options']);
        self::assertEquals($attributeId, $option['id']);
        self::assertEquals($optionId, $option['value_id']);
        self::assertArrayHasKey('option_label', $option);
        self::assertArrayHasKey('value_label', $option);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     * @expectedException \Exception
     * @expectedExceptionMessage You need to choose options for your item
     */
    public function testAddProductWithInvalidOptions()
    {
        $searchResponse = $this->graphQlQuery($this->getFetchProductQuery('configurable'));
        $product = current($searchResponse['products']['items']);

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');
        $sku = $product['sku'];
        $attributeId = (int) $product['configurable_options'][0]['attribute_id'];

        $query = $this->getQuery(
            $maskedQuoteId,
            $sku,
            $attributeId,
            9999,
            1
        );

        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable_sku.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     * @expectedException \Exception
     * @expectedExceptionMessage The requested qty is not available
     */
    public function testAddProductIfQuantityIsNotAvailable()
    {
        $searchResponse = $this->graphQlQuery($this->getFetchProductQuery('configurable'));
        $product = current($searchResponse['products']['items']);

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');
        $sku = $product['sku'];
        $attributeId = (int) $product['configurable_options'][0]['attribute_id'];
        $optionId = $product['configurable_options'][0]['values'][1]['value_index'];

        $query = $this->getQuery(
            $maskedQuoteId,
            $sku,
            $attributeId,
            $optionId,
            2000
        );

        $this->graphQlMutation($query);
    }

    /**
     * @param string $maskedQuoteId
     * @param string $sku
     * @param int $optionId
     * @param int $value
     * @param int $quantity
     * @return string
     */
    private function getQuery(string $maskedQuoteId, string $sku, int $optionId, int $value, int $quantity): string
    {
        return <<<QUERY
mutation {
  addConfigurableProductsToCart(
    input:{
      cart_id:"{$maskedQuoteId}"
      cart_items:{
        configurable_attributes:[{
            id:{$optionId}
            value:{$value}
          }
        ]
        data:{
          sku:"{$sku}"
          quantity:{$quantity}
        }
      }
    }
  ) {
    cart {
      items {
        id
        quantity
        product {
          sku
        }
        ... on ConfigurableCartItem {
          configurable_options {
            id
            option_label
            value_id
            value_label
          }
        }
      }
    }
  }
}
QUERY;
    }

    private function getFetchProductQuery(string $term): string
    {
        return <<<QUERY
{
  products(
    search:"{$term}"
    pageSize:1
  ) {
    items {
      sku
      ... on ConfigurableProduct {
        configurable_options {
          attribute_id
          attribute_code
          id
          label
          position
          product_id
          use_default
          values {
            default_label
            label
            store_label
            use_default_value
            value_index
          }
        }
      }
    }
  }
}
QUERY;
    }
}
