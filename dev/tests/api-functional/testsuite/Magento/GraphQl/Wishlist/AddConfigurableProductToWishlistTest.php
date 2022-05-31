<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Wishlist;

use Exception;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\WishlistFactory;

/**
 * Test coverage for adding a configurable product to wishlist
 */
class AddConfigurableProductToWishlistTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var WishlistFactory
     */
    private $wishlistFactory;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->wishlistFactory = $objectManager->get(WishlistFactory::class);
    }

    /**
     * @magentoConfigFixture default_store wishlist/general/active 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     *
     * @throws Exception
     */
    public function testAddConfigurableProductWithOptions(): void
    {
        $product = $this->getConfigurableProductInfo();
        $customerId = 1;
        $qty = 2;
        $childSku = $product['variants'][0]['product']['sku'];
        $selectedOptions = array_column($product['variants'][0]['attributes'], 'uid');
        $parentSku = $product['sku'];
        $additionalInput = $this->getSelectedOptionsQuery($selectedOptions);

        $query = $this->getQuery($parentSku, $childSku, $qty, $additionalInput);

        $response = $this->graphQlMutation($query, [], '', $this->getHeadersMap());
        $wishlist = $this->wishlistFactory->create()->loadByCustomerId($customerId, true);
        /** @var Item $wishlistItem */
        $wishlistItem = $wishlist->getItemCollection()->getFirstItem();

        $this->assertArrayHasKey('addProductsToWishlist', $response);
        $this->assertArrayHasKey('wishlist', $response['addProductsToWishlist']);
        $this->assertEmpty($response['addProductsToWishlist']['user_errors']);
        $wishlistResponse = $response['addProductsToWishlist']['wishlist'];
        $this->assertEquals($wishlist->getItemsCount(), $wishlistResponse['items_count']);
        $this->assertEquals($wishlist->getSharingCode(), $wishlistResponse['sharing_code']);
        $this->assertEquals($wishlist->getUpdatedAt(), $wishlistResponse['updated_at']);
        $this->assertEquals($wishlistItem->getId(), $wishlistResponse['items_v2']['items'][0]['id']);
        $this->assertEquals($wishlistItem->getData('qty'), $wishlistResponse['items_v2']['items'][0]['quantity']);
        $this->assertEquals($wishlistItem->getDescription(), $wishlistResponse['items_v2']['items'][0]['description']);
        $this->assertEquals($wishlistItem->getAddedAt(), $wishlistResponse['items_v2']['items'][0]['added_at']);
        $this->assertNotEmpty($wishlistResponse['items_v2']['items'][0]['configurable_options']);
        $configurableOptions = $wishlistResponse['items_v2']['items'][0]['configurable_options'];
        $this->assertEquals('Test Configurable', $configurableOptions[0]['option_label']);
        $this->assertEquals('Option 1', $configurableOptions[0]['value_label']);
        $this->assertEquals($childSku, $wishlistResponse['items_v2']['items'][0]['configured_variant']['sku']);
    }

    /**
     * @magentoConfigFixture default_store wishlist/general/active 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     *
     * @throws Exception
     */
    public function testAddConfigurableProductWithoutOption(): void
    {
        $product = $this->getConfigurableProductInfo();
        $query = $this->getQuery($product['sku'], $product['sku'], 2);
        $response = $this->graphQlMutation($query, [], '', $this->getHeadersMap());

        $this->assertArrayHasKey('addProductsToWishlist', $response);
        $this->assertArrayHasKey('wishlist', $response['addProductsToWishlist']);
        $this->assertEmpty(
            $response['addProductsToWishlist']['user_errors'],
            json_encode($response['addProductsToWishlist']['user_errors'])
        );
        $wishlistResponse = $response['addProductsToWishlist']['wishlist'];

        $this->assertCount(1, $wishlistResponse['items_v2']['items']);
        $this->assertEquals($product['sku'], $wishlistResponse['items_v2']['items'][0]['product']['sku']);
        $this->assertEmpty($wishlistResponse['items_v2']['items'][0]['configurable_options']);
        $this->assertNull($wishlistResponse['items_v2']['items'][0]['configured_variant']);
    }

    /**
     * @magentoConfigFixture default_store wishlist/general/active 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable_with_custom_option_dropdown.php
     *
     * @throws Exception
     */
    public function testAddConfigurableProductWithCustomOptions(): void
    {
        $product = $this->getConfigurableProductInfo();
        $qty = 2;
        $childSku = $product['variants'][0]['product']['sku'];
        $parentSku = $product['sku'];
        $selectedOptions = array_column($product['variants'][0]['attributes'], 'uid');
        $optionId = $product['options'][0]['uid'];
        $optionValue = $product['options'][0]['value'][0]['option_type_id'];
        $customOptions = [$optionId => $optionValue];
        $additionalInput = $this->getSelectedOptionsQuery($selectedOptions)
            . PHP_EOL
            . $this->getCustomOptionsQuery($customOptions);

        $query = $this->getQuery($parentSku, $childSku, $qty, $additionalInput);
        $response = $this->graphQlMutation($query, [], '', $this->getHeadersMap());

        $this->assertArrayHasKey('addProductsToWishlist', $response);
        $this->assertArrayHasKey('wishlist', $response['addProductsToWishlist']);
        $this->assertEmpty($response['addProductsToWishlist']['user_errors']);

        $wishlistResponse = $response['addProductsToWishlist']['wishlist'];
        $this->assertCount(1, $wishlistResponse['items_v2']['items']);

        $this->assertEquals($childSku, $wishlistResponse['items_v2']['items'][0]['configured_variant']['sku']);

        $this->assertNotEmpty($wishlistResponse['items_v2']['items'][0]['configurable_options']);
        $configurableOptions = $wishlistResponse['items_v2']['items'][0]['configurable_options'];
        $this->assertEquals('Test Configurable', $configurableOptions[0]['option_label']);
        $this->assertEquals('Option 1', $configurableOptions[0]['value_label']);

        $this->assertNotEmpty($wishlistResponse['items_v2']['items'][0]['customizable_options']);
        $customizableOptions = $wishlistResponse['items_v2']['items'][0]['customizable_options'];
        $this->assertEquals($optionId, $customizableOptions[0]['customizable_option_uid']);
        $this->assertEquals($optionValue, $customizableOptions[0]['values'][0]['value']);
    }

    /**
     * Authentication header map
     *
     * @param string $username
     * @param string $password
     *
     * @return array
     *
     * @throws AuthenticationException
     */
    private function getHeadersMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);

        return ['Authorization' => 'Bearer ' . $customerToken];
    }

    /**
     * Returns GraphQl mutation string
     *
     * @param string|null $parentSku
     * @param string $childSku
     * @param int $qty
     * @param string $additionalInput
     * @param int $wishlistId
     *
     * @return string
     */
    private function getQuery(
        string $parentSku,
        string $childSku,
        int $qty,
        string $additionalInput = '',
        int $wishlistId = 0
    ): string {
        return <<<MUTATION
mutation {
  addProductsToWishlist(
    wishlistId: {$wishlistId},
    wishlistItems: [
      {
        sku: "{$childSku}"
        parent_sku: "{$parentSku}"
        quantity: {$qty}
        {$additionalInput}
      }
    ]
) {
    user_errors {
      code
      message
    }
    wishlist {
      id
      sharing_code
      items_count
      updated_at
      items_v2(currentPage:1,pageSize:1) {
      items{
          id
        description
        quantity
        added_at
        ... on ConfigurableWishlistItem {
          configurable_options {
            id
            configurable_product_option_uid
            option_label
            value_id
            configurable_product_option_value_uid
            value_label
          }
          configured_variant {
            sku
          }
        }
        customizable_options {
          customizable_option_uid
          is_required
          label
          type
          values {
            customizable_option_value_uid
            label
            value
            price {
              value
            }
          }
        }
        product {
          sku
        }
      }
      }
    }
  }
}
MUTATION;
    }

    /**
     * Generates GQL for selected_options
     *
     * @param array $options
     * @return string
     */
    private function getSelectedOptionsQuery(array $options): string
    {
        return 'selected_options: ' . json_encode($options);
    }

    /**
     * Generates GQL for entered_options
     *
     * @param array $options
     * @return string
     */
    private function getCustomOptionsQuery(array $options): string
    {
        $output = [];
        foreach ($options as $id => $value) {
            $output[] = [
                'uid' => $id,
                'value' => $value
            ];
        }
        return 'entered_options: ' . preg_replace('/"([^"]+)"\s*:\s*/', '$1:', json_encode($output));
    }

    /**
     * Returns information about testable configurable product retrieved from GraphQl query
     *
     * @return array
     *
     * @throws Exception
     */
    private function getConfigurableProductInfo(): array
    {
        $searchResponse = $this->graphQlQuery($this->getFetchProductQuery('configurable'));

        return current($searchResponse['products']['items']);
    }

    /**
     * Returns GraphQl query for fetching configurable product information
     *
     * @param string $term
     *
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
      ... on CustomizableProductInterface {
        options {
          uid
          ... on CustomizableDropDownOption {
            value {
              option_type_id
            }
          }
        }
      }
      ... on ConfigurableProduct {
        variants {
          attributes {
            uid
          }
          product {
            sku
          }
        }
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
