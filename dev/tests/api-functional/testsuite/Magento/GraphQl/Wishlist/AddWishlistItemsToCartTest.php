<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare (strict_types = 1);

namespace Magento\GraphQl\Wishlist;

use Exception;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test coverage for add requisition list items to cart
 */
class AddWishlistItemsToCartTest extends GraphQlAbstract
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
    public function testAddItemsToCart(): void
    {
        $wishlist = $this->getWishlist();
        $customerWishlist = $wishlist['customer']['wishlists'][0];
        $wishlistUid = $customerWishlist['uid'];
        $wishlistItem = $customerWishlist['items_v2']['items'][0];
        $itemUid = $wishlistItem['uid'];

        $query = $this->getQuery($wishlistUid, $itemUid);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        $this->assertArrayHasKey('addWishlistItemsToCart', $response);
        $this->assertArrayHasKey('status', $response['addWishlistItemsToCart']);
        $this->assertEquals($response['addWishlistItemsToCart']['status'], 1);
    }

    /**
     * @magentoConfigFixture default_store wishlist/general/active 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Wishlist/_files/wishlist_with_simple_product.php
     */
    public function testAddItemsToCartForInvalidUser(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("The account sign-in was incorrect or your account is disabled temporarily. Please wait and try again later.");

        $wishlist = $this->getWishlist();
        $customerWishlist = $wishlist['customer']['wishlists'][0];
        $wishlistUid = $customerWishlist['uid'];
        $wishlistItem = $customerWishlist['items_v2']['items'][0];
        $itemUid = $wishlistItem['uid'];

        $query = $this->getQuery($wishlistUid, $itemUid);
        $this->graphQlMutation($query, [], '', $this->getHeaderMap('customer2@example.com', 'password'));
    }

    /**
     * @magentoConfigFixture default_store wishlist/general/active 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Wishlist/_files/wishlist_with_simple_product.php
     */
    public function testAddItemsToCartForGuestUser(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The current user cannot perform operations on wishlist');

        $wishlist = $this->getWishlist();
        $customerWishlist = $wishlist['customer']['wishlists'][0];
        $wishlistUid = $customerWishlist['uid'];
        $wishlistItem = $customerWishlist['items_v2']['items'][0];
        $itemUid = $wishlistItem['uid'];

        $query = $this->getQuery($wishlistUid, $itemUid);

        $this->graphQlMutation($query, [], '', ['Authorization' => 'Bearer test_token']);
    }

    /**
     * @magentoConfigFixture default_store wishlist/general/active 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Wishlist/_files/wishlist_with_simple_product.php
     */
    public function testAddItemsToCartWithoutId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('"wishlistUid" value should be specified');

        $wishlistUid = '';
        $wishlist = $this->getWishlist();
        $customerWishlist = $wishlist['customer']['wishlists'][0];
        $wishlistItem = $customerWishlist['items_v2']['items'][0];
        $itemUid = $wishlistItem['uid'];
        $query = $this->getQuery($wishlistUid, $itemUid);
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoConfigFixture default_store wishlist/general/active 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Wishlist/_files/wishlist_with_simple_product.php
     */
    public function testAddItemsToCartWithInvalidId(): void
    {
        $wishlistUid = '9999';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The wishlist was not found.');

        $wishlistUid = base64_encode((string) $wishlistUid);
        $wishlist = $this->getWishlist();
        $customerWishlist = $wishlist['customer']['wishlists'][0];
        $wishlistItem = $customerWishlist['items_v2']['items'][0];
        $itemUid = $wishlistItem['uid'];

        $query = $this->getQuery($wishlistUid, $itemUid);
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
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
     * @param string $wishlistUid
     * @param string $itemId
     * @return string
     */
    private function getQuery(
        string $wishlistUid,
        string $itemId
    ): string {
        return <<<MUTATION
mutation {
    addWishlistItemsToCart
    (
      wishlistUid: "{$wishlistUid}"
      wishlistItemUids: ["{$itemId}"]
    ) {
    status
    add_wishlist_items_to_cart_user_errors{
        message
        code
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
    wishlists {
      id
      uid
      items_count
      sharing_code
      updated_at
      items_v2 {
       items {
        id
        uid
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
