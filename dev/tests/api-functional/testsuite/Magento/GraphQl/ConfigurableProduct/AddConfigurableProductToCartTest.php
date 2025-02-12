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
    protected function setUp(): void
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
        self::assertEquals('varchar10', $firstCartItem['configured_variant']['varchar_attribute']);
        $secondCartItem = $cartItems[1];
        self::assertEquals($quantityTwo, $secondCartItem['quantity']);
        self::assertEquals('varchar20', $secondCartItem['configured_variant']['varchar_attribute']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/configurable_products_with_custom_attribute_layered_navigation.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     *
     */
    public function testAddVariationFromAnotherConfigurableProductWithTheSameSuperAttributeToCart()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Could not find specified product.');

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
     */
    public function testAddVariationFromAnotherConfigurableProductWithDifferentSuperAttributeToCart()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Could not find specified product.');

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
     */
    public function testAddProductIfQuantityIsNotAvailable()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Not enough items for sale');

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
     */
    public function testAddNonExistentConfigurableProductParentToCart()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Could not find a product with SKU "configurable_no_exist"');

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
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote_not_default_website.php
     */
    public function testAddConfigurableProductNotAssignedToWebsite()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Could not find a product with SKU "configurable"');

        $reservedOrderId = 'test_order_2';
        $parentSku = 'configurable';
        $sku = 'simple_20';
        $headerMap = ['Store' => 'fixture_second_store'];

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($reservedOrderId);
        $query = $this->getQuery($maskedQuoteId, $parentSku, $sku, 1);
        $this->graphQlMutation($query, [], '', $headerMap);
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

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Could not add the product with SKU configurable to the shopping cart: Could not find specified product.'
        );

        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_product_only_parent_two_websites.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote_not_default_website.php
     */
    public function testAddUnassignedToWebsiteConfigurableProductVariationToCart()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Could not add the product with SKU configurable to the shopping cart: Could not find specified product.'
        );

        $reservedOrderId = 'test_order_2';
        $parentSku = 'configurable';
        $sku = 'simple_20';
        $headerMap = ['Store' => 'fixture_second_store'];

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($reservedOrderId);
        $query = $this->getQuery($maskedQuoteId, $parentSku, $sku, 1);
        $this->graphQlMutation($query, [], '', $headerMap);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable_disable_first_child.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddDisabledVariationToCart()
    {
        $searchResponse = $this->graphQlQuery($this->getFetchProductQuery('configurable'));
        $product = current($searchResponse['products']['items']);
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');
        $parentSku = $product['sku'];
        $sku = 'simple_10';
        $query = $this->getQuery(
            $maskedQuoteId,
            $parentSku,
            $sku,
            1
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Could not add the product with SKU configurable to the shopping cart'
        );

        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable_zero_qty_first_child.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testOutOfStockVariationToCart()
    {
        $searchResponse = $this->graphQlQuery($this->getFetchProductQuery('configurable'));
        $product = current($searchResponse['products']['items']);
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');
        $parentSku = $product['sku'];
        $sku = 'simple_10';
        $query = $this->getQuery(
            $maskedQuoteId,
            $parentSku,
            $sku,
            1
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Could not add the product with SKU configurable to the shopping cart'
        );

        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable_with_custom_option_dropdown.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddConfigurableProductToCartWithCustomOption()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');
        $sku = 'configurable';
        $variantSku = 'simple_10';
        $productOptions = $this->getAvailableProductCustomOption($sku);
        $optionId = $productOptions[0]['option_id'];
        $optionValueId = $productOptions[0]['value'][1]['option_type_id'];

        $mutation = <<<QUERY
mutation {
  addConfigurableProductsToCart(input: {
    cart_id: "{$maskedQuoteId}",
    cart_items: [
      {
        parent_sku: "{$sku}",
        variant_sku: "{$variantSku}",
        data: {
          sku: "{$variantSku}",
          quantity: 1
        },
        customizable_options: [
          {id: {$optionId}, value_string: "{$optionValueId}"}]
      }
    ]
  }) {
    cart {
      items {
        id
        quantity
        product {
          sku
          name
        }
        ... on ConfigurableCartItem {
          configurable_options {
            option_label
            value_label
          }
          customizable_options {
            id
            label
            values{
              label
              value
            }
          }
        }
      }
    }
  }
}
QUERY;

        $response = $this->graphQlMutation($mutation);
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertCount(1, $response['addConfigurableProductsToCart']['cart']['items']);
        $item = $response['addConfigurableProductsToCart']['cart']['items'][0];
        $this->assertEquals($sku, $item['product']['sku']);
        $expectedOptions = [
            'configurable_options' => [
                [
                    'option_label' => 'Test Configurable',
                    'value_label' => 'Option 1'
                ]
            ],
            'customizable_options' => [
                [
                    'id' => $optionId,
                    'label' => 'Dropdown Options',
                    'values' => [
                        [
                            'label' => 'Option 2',
                            'value' => $optionValueId
                        ]
                    ]
                ]
            ]
        ];

        $this->assertResponseFields($item['configurable_options'], $expectedOptions['configurable_options']);
        $this->assertResponseFields($item['customizable_options'], $expectedOptions['customizable_options']);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     */
    public function testAddConfigurableProductToCartWithDifferentStoreHeader()
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
        $headerMap = ['Store' => 'fixture_second_store'];
        $response = $this->graphQlMutation($query, [], '', $headerMap);

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
     * @magentoConfigFixture default_store checkout/cart/configurable_product_image itself
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_child_products_with_images.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddConfigurableProductWithImageToCartItselfImage(): void
    {
        $searchResponse = $this->graphQlQuery($this->getFetchProductQuery('configurable'));
        $product = current($searchResponse['products']['items']);

        $quantity = 1;
        $parentSku = $product['sku'];
        $sku = 'simple_20';

        $query = $this->graphQlQueryForVariant(
            $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1'),
            $parentSku,
            $sku,
            $quantity
        );

        $response = $this->graphQlMutation($query);

        $cartItem = current($response['addConfigurableProductsToCart']['cart']['items']);
        self::assertEquals($quantity, $cartItem['quantity']);
        self::assertEquals($parentSku, $cartItem['product']['sku']);
        self::assertArrayHasKey('configured_variant', $cartItem);

        $variant = $cartItem['configured_variant'];
        $expectedThumbnailUrl = 'magento_thumbnail.jpg';
        $expectedThumbnailLabel = 'Thumbnail Image';
        $variantImage = basename($variant['thumbnail']['url']);

        self::assertEquals($expectedThumbnailUrl, $variantImage);
        self::assertEquals($expectedThumbnailLabel, $variant['thumbnail']['label']);
        self::assertEquals($sku, $variant['sku']);
    }

    /**
     * @magentoConfigFixture default_store checkout/cart/configurable_product_image parent
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_child_products_with_images.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddConfigurableProductWithImageToCartParentImage(): void
    {
        $searchResponse = $this->graphQlQuery($this->getFetchProductQuery('configurable'));
        $product = current($searchResponse['products']['items']);

        $quantity = 1;
        $parentSku = $product['sku'];
        $sku = 'simple_20';

        $query = $this->graphQlQueryForVariant(
            $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1'),
            $parentSku,
            'simple_20',
            $quantity
        );

        $response = $this->graphQlMutation($query);

        $cartItem = current($response['addConfigurableProductsToCart']['cart']['items']);
        self::assertEquals($quantity, $cartItem['quantity']);
        self::assertEquals($parentSku, $cartItem['product']['sku']);
        self::assertArrayHasKey('configured_variant', $cartItem);

        $variant = $cartItem['configured_variant'];
        $expectedThumbnailUrl = 'magento_thumbnail.jpg';
        $expectedThumbnailLabel = 'Thumbnail Image';
        $variantImage = basename($variant['thumbnail']['url']);

        self::assertEquals($expectedThumbnailUrl, $variantImage);
        self::assertEquals($expectedThumbnailLabel, $variant['thumbnail']['label']);
        self::assertEquals($sku, $variant['sku']);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     */
    public function testAddMoreProductsFromAnotherStore()
    {
        $parentSku = 'configurable';
        $sku = 'simple_20';
        $quantity = 400; // the number should be greater than the available qty / 3
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');
        $query = $this->getQuery($maskedQuoteId, $parentSku, $sku, $quantity);

        $headerMap = ['Store' => 'default'];
        $this->graphQlMutation($query, [], '', $headerMap);
        $headerMap = ['Store' => 'fixture_second_store'];
        $response = $this->graphQlMutation($query, [], '', $headerMap);
        self::assertEquals($maskedQuoteId, $response['addConfigurableProductsToCart']['cart']['id']);
        $cartItem = current($response['addConfigurableProductsToCart']['cart']['items']);
        self::assertEquals($parentSku, $cartItem['product']['sku']);
        self::assertEquals($quantity * 2, $cartItem['quantity']);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable_in_multiple_websites_disable_first_child.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddConfigurableProductWithDisabledChildToCart(): void
    {
        $quantity = 1;
        $parentSku = 'configurable';
        $sku = 'simple_Option_1';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Could not find specified product.');

        $query = $this->getQuery($maskedQuoteId, $parentSku, $sku, $quantity);
        $headerMap = ['Store' => 'default'];
        $this->graphQlMutation($query, [], '', $headerMap);
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
      id
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

    /**
     * @param string $maskedQuoteId
     * @param string $parentSku
     * @param string $sku
     * @param int $quantity
     * @return string
     */
    private function graphQlQueryForVariant(
        string $maskedQuoteId,
        string $parentSku,
        string $sku,
        int $quantity
    ): string {
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
          configured_variant {
            sku
            thumbnail {
              label
              url
            }
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

    /**
     * Get product customizable dropdown options
     *
     * @param string $productSku
     * @return array
     * @throws Exception
     */
    private function getAvailableProductCustomOption(string $productSku): array
    {
        $query = <<<QUERY
{
  products(filter: {sku: {eq: "{$productSku}"}}) {
    items {
      name
      ... on CustomizableProductInterface {
        options {
          option_id
          title
          ... on CustomizableDropDownOption {
            value {
              option_type_id
              title
            }
          }
        }
      }
    }
  }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertNotEmpty($response['products']['items'], "No result for product with sku: '{$productSku}'");
        return $response['products']['items'][0]['options'];
    }
}
