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
 * Test coverage for deleting a product from wishlist
 */
class DeleteProductsFromWishlistTest extends GraphQlAbstract
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
    public function testDeleteWishlistItemFromWishlist(): void
    {
        $wishlist = $this->getWishlist();
        $customerWishlists = $wishlist['customer']['wishlists'][0];
        $wishlistId = $customerWishlists['id'];

        $wishlistItems = $customerWishlists['items_v2']['items'];
        $this->assertEquals(1, $customerWishlists['items_count']);

        $query = $this->getQuery($wishlistId, $wishlistItems[0]['id']);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        $this->assertArrayHasKey('removeProductsFromWishlist', $response);
        $this->assertArrayHasKey('wishlist', $response['removeProductsFromWishlist']);
        $this->assertEmpty($response['removeProductsFromWishlist']['user_errors'], 'User error is not empty');
        $wishlistResponse = $response['removeProductsFromWishlist']['wishlist'];
        $this->assertEquals(0, $wishlistResponse['items_count']);
        $this->assertEmpty($wishlistResponse['items_v2']['items'], 'Wishlist item is not removed');
    }

    /**
     * Test deleting the wishlist item of another customer
     *
     * @magentoConfigFixture default_store wishlist/general/active 1
     * @magentoApiDataFixture Magento/Wishlist/_files/two_wishlists_for_two_diff_customers.php
     */
    public function testUnauthorizedWishlistItemDelete()
    {
        $wishlist = $this->getWishlist();
        $wishlistItem = $wishlist['customer']['wishlists'][0]['items_v2']['items'];
        $wishlist2 = $this->getWishlist('customer_two@example.com');
        $wishlist2Id = $wishlist2['customer']['wishlists'][0]['id'];
        $query = $this->getQuery($wishlist2Id, $wishlistItem[0]['id']);
        $response = $this->graphQlMutation(
            $query,
            [],
            '',
            $this->getHeaderMap('customer_two@example.com')
        );
        self::assertEquals(1, $response['removeProductsFromWishlist']['wishlist']['items_count']);
        self::assertNotEmpty($response['removeProductsFromWishlist']['wishlist']['items_v2']['items'], 'empty wish list items');
        self::assertCount(1, $response['removeProductsFromWishlist']['wishlist']['items_v2']['items']);
        self::assertEquals(
            'The wishlist item with ID "' . $wishlistItem[0]['id'] . '" does not belong to the wishlist',
            $response['removeProductsFromWishlist']['user_errors'][0]['message']
        );
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
     *
     * @return string
     */
    private function getQuery(
        string $wishlistId,
        string $wishlistItemId
    ): string {
        return <<<MUTATION
mutation {
  removeProductsFromWishlist(
    wishlistId: "{$wishlistId}",
    wishlistItemsIds: ["{$wishlistItemId}"]
) {
    user_errors {
      code
      message
    }
    wishlist {
      id
      sharing_code
      items_count
      items_v2 {
        items {id description quantity product {name sku}}
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
    public function getWishlist(string $username = 'customer@example.com'): array
    {
        return $this->graphQlQuery($this->getCustomerWishlistQuery(), [], '', $this->getHeaderMap($username));
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
    wishlists {
      id
      items_count
      sharing_code
      updated_at
      items_v2 {
       items {
        id
        quantity
        description
         product {
          sku
        }
      }
      }
    }
  }
}
QUERY;
    }
}
