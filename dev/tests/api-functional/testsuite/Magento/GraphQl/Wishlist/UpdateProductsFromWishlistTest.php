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
        $wishlistItem = $wishlist['customer']['wishlist']['items_v2'][0];
        $this->assertNotEquals($description, $wishlistItem['description']);
        $this->assertNotEquals($qty, $wishlistItem['quantity']);

        $query = $this->getQuery((int) $wishlistId, (int) $wishlistItem['id'], $qty, $description);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        $this->assertArrayHasKey('updateProductsInWishlist', $response);
        $this->assertArrayHasKey('wishlist', $response['updateProductsInWishlist']);
        $wishlistResponse = $response['updateProductsInWishlist']['wishlist'];
        $this->assertEquals($qty, $wishlistResponse['items_v2'][0]['quantity']);
        $this->assertEquals($description, $wishlistResponse['items_v2'][0]['description']);
    }

    /**
     * Test updating the wishlist item of another customer
     *
     * @magentoConfigFixture default_store wishlist/general/active 1
     * @magentoApiDataFixture Magento/Customer/_files/two_customers.php
     * @magentoApiDataFixture Magento/Wishlist/_files/two_wishlists_for_two_diff_customers.php
     */
    public function testUnauthorizedWishlistItemUpdate()
    {
        $wishlist = $this->getWishlist();
        $wishlistItem = $wishlist['customer']['wishlist']['items_v2'][0];
        $wishlist2 = $this->getWishlist('customer_two@example.com');
        $wishlist2Id = $wishlist2['customer']['wishlist']['id'];
        $qty = 2;
        $description = 'New Description';
        $updateWishlistQuery = $this->getQuery((int) $wishlist2Id, (int) $wishlistItem['id'], $qty, $description);
        $response = $this->graphQlMutation(
            $updateWishlistQuery,
            [],
            '',
            $this->getHeaderMap('customer_two@example.com')
        );
        self::assertEquals(1, $response['updateProductsInWishlist']['wishlist']['items_count']);
        self::assertNotEmpty($response['updateProductsInWishlist']['wishlist']['items_v2'], 'empty wish list items');
        self::assertCount(1, $response['updateProductsInWishlist']['wishlist']['items_v2']);
        self::assertEquals(
            'The wishlist item with ID "'.$wishlistItem['id'].'" does not belong to the wishlist',
            $response['updateProductsInWishlist']['user_errors'][0]['message']
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
     * @param string $username
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
