<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GraphQl\ConfigurableProduct;

use Exception;
use Magento\CatalogInventory\Model\Configuration;
use Magento\Config\Model\ResourceModel\Config;
use Magento\ConfigurableProductGraphQl\Model\Options\SelectionUidFormatter;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Add configurable product to cart testcases
 */
class AddConfigurableProductToCartSingleMutationTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @var Config $config
     */
    private $resourceConfig;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ReinitableConfigInterface
     */
    private $reinitConfig;

    /**
     * @var SelectionUidFormatter
     */
    private $selectionUidFormatter;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->resourceConfig = $objectManager->get(Config::class);
        $this->scopeConfig = $objectManager->get(ScopeConfigInterface::class);
        $this->reinitConfig = $objectManager->get(ReinitableConfigInterface::class);
        $this->selectionUidFormatter = $objectManager->get(SelectionUidFormatter::class);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddConfigurableProductToCart(): void
    {
        $product = $this->getConfigurableProductInfo();
        $quantity = 2;
        $parentSku = $product['sku'];
        $attributeId = (int) $product['configurable_options'][0]['attribute_id'];
        $valueIndex = $product['configurable_options'][0]['values'][1]['value_index'];
        $productRowId = (string) $product['configurable_options'][0]['product_id'];
        $selectedConfigurableOptionsQuery = $this->generateSuperAttributesUIDQuery($attributeId, $valueIndex);

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');

        $query = $this->getQuery(
            $maskedQuoteId,
            $product['sku'],
            2,
            $selectedConfigurableOptionsQuery
        );

        $response = $this->graphQlMutation($query);

        $expectedProductOptionsValueUid = $this->selectionUidFormatter->encode($attributeId, $valueIndex);
        $expectedProductOptionsUid = base64_encode("configurable/$productRowId/$attributeId");
        $cartItem = current($response['addProductsToCart']['cart']['items']);
        self::assertEquals($quantity, $cartItem['quantity']);
        self::assertEquals($parentSku, $cartItem['product']['sku']);
        self::assertEquals(base64_encode((string)$cartItem['product']['id']), $cartItem['product']['uid']);
        self::assertArrayHasKey('configurable_options', $cartItem);

        $option = current($cartItem['configurable_options']);
        self::assertEquals($attributeId, $option['id']);
        self::assertEquals($valueIndex, $option['value_id']);
        self::assertEquals($expectedProductOptionsValueUid, $option['configurable_product_option_value_uid']);
        self::assertEquals($expectedProductOptionsUid, $option['configurable_product_option_uid']);
        self::assertArrayHasKey('option_label', $option);
        self::assertArrayHasKey('value_label', $option);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/configurable_products_with_custom_attribute_layered_navigation.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddConfigurableProductWithWrongSuperAttributes(): void
    {
        $product = $this->getConfigurableProductInfo();
        $quantity = 2;
        $parentSku = $product['sku'];

        $selectedConfigurableOptionsQuery = $this->generateSuperAttributesUIDQuery(0, 0);
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');

        $query = $this->getQuery(
            $maskedQuoteId,
            $parentSku,
            $quantity,
            $selectedConfigurableOptionsQuery
        );

        $response =  $this->graphQlMutation($query);

        self::assertEquals(
            'You need to choose options for your item.',
            $response['addProductsToCart']['user_errors'][0]['message']
        );
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable_sku.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddProductIfQuantityIsNotAvailable(): void
    {
        $product = $this->getConfigurableProductInfo();
        $parentSku = $product['sku'];
        $attributeId = (int) $product['configurable_options'][0]['attribute_id'];
        $valueIndex = $product['configurable_options'][0]['values'][1]['value_index'];

        $selectedConfigurableOptionsQuery = $this->generateSuperAttributesUIDQuery($attributeId, $valueIndex);
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');

        $query = $this->getQuery(
            $maskedQuoteId,
            $parentSku,
            2000,
            $selectedConfigurableOptionsQuery
        );

        $response = $this->graphQlMutation($query);

        self::assertEquals(
            'Not enough items for sale',
            $response['addProductsToCart']['user_errors'][0]['message']
        );
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable_sku.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testAddNonExistentConfigurableProductParentToCart(): void
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');
        $parentSku = 'configurable_no_exist';

        $query = $this->getQuery(
            $maskedQuoteId,
            $parentSku,
            1,
            ''
        );

        $response = $this->graphQlMutation($query);

        self::assertEquals(
            'Could not find a product with SKU "configurable_no_exist"',
            $response['addProductsToCart']['user_errors'][0]['message']
        );
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable_zero_qty_first_child.php
     * @magentoApiDataFixture Magento/Checkout/_files/active_quote.php
     */
    public function testOutOfStockVariationToCart(): void
    {
        $showOutOfStock = $this->scopeConfig->getValue(Configuration::XML_PATH_SHOW_OUT_OF_STOCK);

        // Changing SHOW_OUT_OF_STOCK to show the out of stock option, otherwise graphql won't display it.
        $this->resourceConfig->saveConfig(Configuration::XML_PATH_SHOW_OUT_OF_STOCK, 1);
        $this->reinitConfig->reinit();

        $product = $this->getConfigurableProductInfo();
        $attributeId = (int) $product['configurable_options'][0]['attribute_id'];
        $valueIndex = $product['configurable_options'][0]['values'][0]['value_index'];
        // Asserting that the first value is the right option we want to add to cart
        self::assertEquals(
            $product['configurable_options'][0]['values'][0]['label'],
            'Option 1'
        );
        $parentSku = $product['sku'];

        $configurableOptionsQuery = $this->generateSuperAttributesUIDQuery($attributeId, $valueIndex);
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_order_1');

        $query = $this->getQuery(
            $maskedQuoteId,
            $parentSku,
            1,
            $configurableOptionsQuery
        );

        $response = $this->graphQlMutation($query);

        $expectedErrorMessages = [
            'There are no source items with the in stock status',
            'This product is out of stock.'
        ];
        self::assertContains(
            $response['addProductsToCart']['user_errors'][0]['message'],
            $expectedErrorMessages
        );
        $this->resourceConfig->saveConfig(Configuration::XML_PATH_SHOW_OUT_OF_STOCK, $showOutOfStock);
        $this->reinitConfig->reinit();
    }

    /**
     * @param string $maskedQuoteId
     * @param string $parentSku
     * @param int $quantity
     * @param string $selectedOptionsQuery
     * @return string
     */
    private function getQuery(
        string $maskedQuoteId,
        string $parentSku,
        int $quantity,
        string $selectedOptionsQuery
    ): string {
        return <<<QUERY
mutation {
    addProductsToCart(
        cartId:"{$maskedQuoteId}"
        cartItems: [
            {
                sku: "{$parentSku}"
                quantity: $quantity
                {$selectedOptionsQuery}
            }
        ]
    ) {
        cart {
            items {
                id
                uid
                quantity
                product {
                    sku
                    uid
                    id
                }
                ... on ConfigurableCartItem {
                    configurable_options {
                        id
                        configurable_product_option_uid
                        option_label
                        value_id
                        configurable_product_option_value_uid
                        value_label
                    }
                }
            }
        },
        user_errors {
            message
        }
    }
}
QUERY;
    }

    /**
     * Returns information about testable configurable product retrieved from GraphQl query
     *
     * @return array
     * @throws Exception
     */
    private function getConfigurableProductInfo(): array
    {
        $searchResponse = $this->graphQlQuery($this->getFetchProductQuery('configurable'));
        return current($searchResponse['products']['items']);
    }

    /**
     * Generates UID for super configurable product super attributes
     *
     * @param int $attributeId
     * @param int $valueIndex
     * @return string
     */
    private function generateSuperAttributesUIDQuery(int $attributeId, int $valueIndex): string
    {
        return 'selected_options: ["' . $this->selectionUidFormatter->encode($attributeId, $valueIndex) . '"]';
    }

    /**
     * Returns GraphQl query for fetching configurable product information
     *
     * @param string $term
     * @return string
     */
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
      uid
      ... on ConfigurableProduct {
        configurable_options {
          attribute_id
          attribute_uid
          attribute_code
          id
          uid
          label
          position
          product_id
          use_default
          values {
            uid
            default_label
            label
            store_label
            use_default_value
            value_index
          }
        }
        configurable_product_options_selection {
          options_available_for_selection {
            attribute_code
            option_value_uids
          }
          configurable_options {
            uid
            attribute_code
            label
            values {
              uid
              is_available
              is_use_default
              label
            }
          }
          variant {
            uid
            sku
            url_key
            url_path
          }
          media_gallery {
            url
            label
            disabled
          }
        }
      }
    }
  }
}
QUERY;
    }
}
