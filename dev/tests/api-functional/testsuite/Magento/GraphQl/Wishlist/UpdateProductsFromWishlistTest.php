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

/**
 * Test coverage for updating a product from wishlist
 */
class UpdateProductsFromWishlistTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @magentoConfigFixture default_store wishlist/general/active 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Wishlist/_files/wishlist_with_simple_product.php
     */
    public function testUpdateSimpleProductFromWishlist(): void
    {
        $wishlist = $this->getWishlist();
        $qty = 5;
        $description = 'New Description';
        $wishlistId = $wishlist['customer']['wishlist']['id'];
        $wishlistItem = $wishlist['customer']['wishlist']['items'][0];
        self::assertNotEquals($description, $wishlistItem['description']);
        self::assertNotEquals($qty, $wishlistItem['qty']);

        $query = $this->getQuery((int) $wishlistId, (int) $wishlistItem['id'], $qty, $description);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('updateProductsInWishlist', $response);
        self::assertArrayHasKey('wishlist', $response['updateProductsInWishlist']);
        $wishlistResponse = $response['updateProductsInWishlist']['wishlist'];
        self::assertEquals($qty, $wishlistResponse['items'][0]['qty']);
        self::assertEquals($description, $wishlistResponse['items'][0]['description']);
    }

    /**
     * update the wishlist by setting an qty = 0
     *
     * @magentoConfigFixture default_store wishlist/general/active 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Wishlist/_files/wishlist_with_simple_product.php
     */
    public function testUpdateProductInWishlistWithZeroQty()
    {
        $wishlist = $this->getWishlist();
        $wishlistId = $wishlist['customer']['wishlist']['id'];
        $wishlistItem = $wishlist['customer']['wishlist']['items'][0];
        $qty = 0;
        $description = 'Description for zero quantity';
        $updateWishlistQuery = $this->getQuery((int) $wishlistId, (int) $wishlistItem['id'], $qty, $description);
        $response = $this->graphQlMutation($updateWishlistQuery, [], '', $this->getHeaderMap());
        self::assertEquals(1, $response['updateProductsInWishlist']['wishlist']['items_count']);
        self::assertNotEmpty($response['updateProductsInWishlist']['wishlist']['items'], 'empty wish list items');
        self::assertCount(1, $response['updateProductsInWishlist']['wishlist']['items']);
        self::assertArrayHasKey('user_errors', $response['updateProductsInWishlist']);
        self::assertCount(1, $response['updateProductsInWishlist']['user_errors']);
        $message = 'The quantity of a wish list item cannot be 0';
        self::assertEquals(
            $message,
            $response['updateProductsInWishlist']['user_errors'][0]['message']
        );
    }

    /**
     * update the wishlist by setting qty to a valid value and no description
     *
     * @magentoConfigFixture default_store wishlist/general/active 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Wishlist/_files/wishlist_with_simple_product.php
     */
    public function testUpdateProductWithValidQtyAndNoDescription()
    {
        $wishlist = $this->getWishlist();
        $wishlistId = $wishlist['customer']['wishlist']['id'];
        $wishlistItem = $wishlist['customer']['wishlist']['items'][0];
        $qty = 2;
        $updateWishlistQuery = $this->getQueryWithNoDescription((int) $wishlistId, (int) $wishlistItem['id'], $qty);
        $response = $this->graphQlMutation($updateWishlistQuery, [], '', $this->getHeaderMap());
        self::assertEquals(1, $response['updateProductsInWishlist']['wishlist']['items_count']);
        self::assertNotEmpty($response['updateProductsInWishlist']['wishlist']['items'], 'empty wish list items');
        self::assertCount(1, $response['updateProductsInWishlist']['wishlist']['items']);
        $itemsInWishlist = $response['updateProductsInWishlist']['wishlist']['items'][0];
        self::assertEquals($qty, $itemsInWishlist['qty']);
        self::assertEquals('simple-1', $itemsInWishlist['product']['sku']);
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
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);

        return ['Authorization' => 'Bearer ' . $customerToken];
    }

    /**
     * Returns GraphQl mutation string
     *
     * @param int $wishlistId
     * @param int $wishlistItemId
     * @param int $qty
     * @param string $description
     *
     * @return string
     */
    private function getQuery(
        int $wishlistId,
        int $wishlistItemId,
        int $qty,
        string $description
    ): string {
        return <<<MUTATION
mutation {
  updateProductsInWishlist(
    wishlistId: {$wishlistId},
    wishlistItems: [
      {
        wishlist_item_id: "{$wishlistItemId}"
        quantity: {$qty}
        description: "{$description}"
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
      items {
        id
        description
        qty
      }
    }
  }
}
MUTATION;
    }

    /**
     * Returns GraphQl mutation string
     *
     * @param int $wishlistId
     * @param int $wishlistItemId
     * @param int $qty
     *
     * @return string
     */
    private function getQueryWithNoDescription(
        int $wishlistId,
        int $wishlistItemId,
        int $qty
    ): string {
        return <<<MUTATION
mutation {
  updateProductsInWishlist(
    wishlistId: {$wishlistId},
    wishlistItems: [
      {
        wishlist_item_id: "{$wishlistItemId}"
        quantity: {$qty}

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
      items {
        id
        qty
        product{sku name}
      }
    }
  }
}
MUTATION;
    }

    /**
     * Get wishlist result
     *
     * @return array
     *
     * @throws Exception
     */
    public function getWishlist(): array
    {
        return $this->graphQlQuery($this->getCustomerWishlistQuery(), [], '', $this->getHeaderMap());
    }

    /**
     * Get customer wishlist query
     *
     * @return string
     */
    private function getCustomerWishlistQuery(): string
    {
        return <<<QUERY
query {
  customer {
    wishlist {
      id
      items_count
      items {
        id
        qty
        description
      }
    }
  }
}
QUERY;
    }
}
