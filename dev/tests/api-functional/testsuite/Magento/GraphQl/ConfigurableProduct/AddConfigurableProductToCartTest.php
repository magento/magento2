<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\ConfigurableProduct;

use Exception;
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
        $parentSku = $product['sku'];
        $sku = 'simple_20';
        $attributeId = (int) $product['configurable_options'][0]['attribute_id'];
        $optionId = $product['configurable_options'][0]['values'][1]['value_index'];

        $query = $this->getQuery(
            $maskedQuoteId,
            $parentSku,
            $sku,
            $quantity
        );

        $response = $this->graphQlMutation($query);

        $cartItem = current($response['addConfigurableProductsToCart']['cart']['items']);
        self::assertEquals($quantity, $cartItem['quantity']);
        self::assertEquals($parentSku, $cartItem['product']['sku']);
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
     */
    public function testAddMultipleConfigurableProductToCart()
    {
        $searchResponse = $this->graphQlQuery($this->getFetchProductQuery('configurable'));
        $product = current($searchResponse['products']['items']);

        $quantityOne = 1;
        $quantityTwo = 2;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');
        $parentSku = $product['sku'];
        $skuOne = 'simple_10';
        $skuTwo = 'simple_20';
        $valueIdOne = $product['configurable_options'][0]['values'][0]['value_index'];

        $query = <<<QUERY
mutation {
  addConfigurableProductsToCart(input:{
    cart_id:"{$maskedQuoteId}"
    cart_items:[
      {
        parent_sku:"{$parentSku}"
        data:{
          sku:"{$skuOne}"
          quantity:{$quantityOne}
        }
      }
      {
        parent_sku:"{$parentSku}"
        data:{
          sku:"{$skuTwo}"
          quantity:{$quantityTwo}
        }
      }
    ]
  }) {
    cart {
      items {
        id
        quantity
        product {
          sku
        }
        ... on ConfigurableCartItem {
          configurable_options {
            option_label
            value_label
            value_id
          }
        }
      }
    }
  }
}
QUERY;

        $response = $this->graphQlMutation($query);

        $cartItems = $response['addConfigurableProductsToCart']['cart']['items'];
        self::assertCount(2, $cartItems);

        foreach ($cartItems as $cartItem) {
            if ($cartItem['configurable_options'][0]['value_id'] === $valueIdOne) {
                self::assertEquals($quantityOne, $cartItem['quantity']);
            } else {
                self::assertEquals($quantityTwo, $cartItem['quantity']);
            }
        }
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/configurable_products_with_custom_attribute_layered_navigation.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     *
     * @expectedException Exception
     * @expectedExceptionMessage Could not find specified product.
     */
    public function testAddVariationFromAnotherConfigurableProductWithTheSameSuperAttributeToCart()
    {
        $searchResponse = $this->graphQlQuery($this->getFetchProductQuery('configurable_12345'));
        $product = current($searchResponse['products']['items']);

        $quantity = 2;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');
        $parentSku = $product['sku'];

        $sku = 'simple_20';

        $query = $this->getQuery(
            $maskedQuoteId,
            $parentSku,
            $sku,
            $quantity
        );

        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_products_with_different_super_attribute.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     *
     * @expectedException Exception
     * @expectedExceptionMessage Could not find specified product.
     */
    public function testAddVariationFromAnotherConfigurableProductWithDifferentSuperAttributeToCart()
    {
        $searchResponse = $this->graphQlQuery($this->getFetchProductQuery('configurable_12345'));
        $product = current($searchResponse['products']['items']);

        $quantity = 2;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');
        $parentSku = $product['sku'];

        $sku = 'simple_20';

        $query = $this->getQuery(
            $maskedQuoteId,
            $parentSku,
            $sku,
            $quantity
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
        $parentSku = $product['sku'];
        $sku = 'simple_20';

        $query = $this->getQuery(
            $maskedQuoteId,
            $parentSku,
            $sku,
            2000
        );

        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable_sku.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     * @expectedException \Exception
     * @expectedExceptionMessage Could not find a product with SKU "configurable_no_exist"
     */
    public function testAddNonExistentConfigurableProductParentToCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');
        $parentSku = 'configurable_no_exist';
        $sku = 'simple_20';

        $query = $this->getQuery(
            $maskedQuoteId,
            $parentSku,
            $sku,
            2000
        );

        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable_sku.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddNonExistentConfigurableProductVariationToCart()
    {
        $searchResponse = $this->graphQlQuery($this->getFetchProductQuery('configurable'));
        $product = current($searchResponse['products']['items']);

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');
        $parentSku = $product['sku'];
        $sku = 'simple_no_exist';

        $query = $this->getQuery(
            $maskedQuoteId,
            $parentSku,
            $sku,
            2000
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'Could not add the product with SKU configurable to the shopping cart: The product that was requested ' .
            'doesn\'t exist. Verify the product and try again.'
        );

        $this->graphQlMutation($query);
    }

    /**
     * @param string $maskedQuoteId
     * @param string $parentSku
     * @param string $sku
     * @param int $quantity
     * @return string
     */
    private function getQuery(string $maskedQuoteId, string $parentSku, string $sku, int $quantity): string
    {
        return <<<QUERY
mutation {
  addConfigurableProductsToCart(
    input:{
      cart_id:"{$maskedQuoteId}"
      cart_items:{
        parent_sku: "{$parentSku}"
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
