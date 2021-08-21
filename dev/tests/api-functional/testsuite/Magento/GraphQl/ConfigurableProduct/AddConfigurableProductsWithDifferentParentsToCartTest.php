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
class AddConfigurableProductsWithDifferentParentsToCartTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddMultipleConfigurableProductsWithDifferentParentsToCart()
    {
        $searchResponse = $this->graphQlQuery($this->getFetchProductQuery('configurable'));
        $productOne = current($searchResponse['products']['items']);
        $searchResponse = $this->graphQlQuery($this->getFetchProductQuery('configurable_12345'));
        $productTwo = current($searchResponse['products']['items']);

        $quantityOne = 1;
        $quantityTwo = 2;
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');
        $parentSkuOne = $productOne['sku'];
        $parentSkuTwo = $productTwo['sku'];
        $skuOne = 'simple_10';
        $skuTwo = 'simple_30';

        $query = <<<QUERY
        mutation {
          addConfigurableProductsToCart(input:{
            cart_id:"{$maskedQuoteId}"
            cart_items:[
              {
                parent_sku:"{$parentSkuOne}"
                data:{
                  sku:"{$skuOne}"
                  quantity:{$quantityOne}
                }
              }
              {
                parent_sku:"{$parentSkuTwo}"
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
                  configured_variant {
                    sku
                    varchar_attribute
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
        $firstCartItem = $cartItems[0];
        self::assertEquals($quantityOne, $firstCartItem['quantity']);
        $secondCartItem = $cartItems[1];
        self::assertEquals($quantityTwo, $secondCartItem['quantity']);
    }

    private function getFetchProductQuery(string $sku): string
    {
        return <<<QUERY
        {
          products(
            filter: {sku: {eq: "$sku"}}
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
