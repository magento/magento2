<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Wishlist;

use Exception;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\ObjectManagerInterface;
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
    public function testAddDownloadableProductWithOptions(): void
    {
        $product = $this->getConfigurableProductInfo();
        $customerId = 1;
        $qty = 2;
        $attributeId = (int) $product['configurable_options'][0]['attribute_id'];
        $valueIndex = $product['configurable_options'][0]['values'][0]['value_index'];
        $childSku = $product['variants'][0]['product']['sku'];
        $parentSku = $product['sku'];
        $selectedConfigurableOptionsQuery = $this->generateSuperAttributesIdV2Query($attributeId, $valueIndex);

        $query = $this->getQuery($parentSku, $childSku, $qty, $selectedConfigurableOptionsQuery);

        $response = $this->graphQlMutation($query, [], '', $this->getHeadersMap());
        $wishlist = $this->wishlistFactory->create()->loadByCustomerId($customerId, true);
        /** @var Item $wishlistItem */
        $wishlistItem = $wishlist->getItemCollection()->getFirstItem();

        self::assertArrayHasKey('addProductsToWishlist', $response);
        self::assertArrayHasKey('wishlist', $response['addProductsToWishlist']);
        $wishlistResponse = $response['addProductsToWishlist']['wishlist'];
        self::assertEquals($wishlist->getItemsCount(), $wishlistResponse['items_count']);
        self::assertEquals($wishlist->getSharingCode(), $wishlistResponse['sharing_code']);
        self::assertEquals($wishlist->getUpdatedAt(), $wishlistResponse['updated_at']);
        self::assertEquals($wishlistItem->getId(), $wishlistResponse['items'][0]['id']);
        self::assertEquals($wishlistItem->getData('qty'), $wishlistResponse['items'][0]['qty']);
        self::assertEquals($wishlistItem->getDescription(), $wishlistResponse['items'][0]['description']);
        self::assertEquals($wishlistItem->getAddedAt(), $wishlistResponse['items'][0]['added_at']);
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
     * @param string $parentSku
     * @param string $childSku
     * @param int $qty
     * @param string $customizableOptions
     * @param int $wishlistId
     *
     * @return string
     */
    private function getQuery(
        string $parentSku,
        string $childSku,
        int $qty,
        string $customizableOptions,
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
        {$customizableOptions}
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
      items {
        id
        description
        qty
        added_at
      }
    }
  }
}
MUTATION;
    }

    /**
     * Generates Id_v2 for super configurable product super attributes
     *
     * @param int $attributeId
     * @param int $valueIndex
     *
     * @return string
     */
    private function generateSuperAttributesIdV2Query(int $attributeId, int $valueIndex): string
    {
        return 'selected_options: ["' . base64_encode("configurable/$attributeId/$valueIndex") . '"]';
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
      ... on ConfigurableProduct {
        variants {
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
