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
        $wishlistId = $wishlist['customer']['wishlist']['id'];
        $wishlist = $wishlist['customer']['wishlist'];
        $wishlistItems = $wishlist['items_v2'];
        $this->assertEquals(1, $wishlist['items_count']);

        $query = $this->getQuery((int) $wishlistId, (int) $wishlistItems[0]['id']);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        $this->assertArrayHasKey('removeProductsFromWishlist', $response);
        $this->assertArrayHasKey('wishlist', $response['removeProductsFromWishlist']);
        $wishlistResponse = $response['removeProductsFromWishlist']['wishlist'];
        $this->assertEquals(0, $wishlistResponse['items_count']);
        $this->assertEmpty($wishlistResponse['items_v2']);
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
        $wishlistItem = $wishlist['customer']['wishlist']['items_v2'][0];
        $wishlist2 = $this->getWishlist('customer_two@example.com');
        $wishlist2Id = $wishlist2['customer']['wishlist']['id'];
        $query = $this->getQuery((int) $wishlist2Id, (int) $wishlistItem['id']);
        $response = $this->graphQlMutation(
            $query,
            [],
            '',
            $this->getHeaderMap('customer_two@example.com')
        );
        self::assertEquals(1, $response['removeProductsFromWishlist']['wishlist']['items_count']);
        self::assertNotEmpty($response['removeProductsFromWishlist']['wishlist']['items_v2'], 'empty wish list items');
        self::assertCount(1, $response['removeProductsFromWishlist']['wishlist']['items_v2']);
        self::assertEquals(
            'The wishlist item with ID "'.$wishlistItem['id'].'" does not belong to the wishlist',
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
        int $wishlistId,
        int $wishlistItemId
    ): string {
        return <<<MUTATION
mutation {
  removeProductsFromWishlist(
    wishlistId: {$wishlistId},
    wishlistItemsIds: [{$wishlistItemId}]
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
        id
        description
        quantity
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
    wishlist {
      id
      items_count
      items_v2 {
        id
        quantity
        description
      }
    }
  }
}
QUERY;
    }
}
