<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
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
        $wishlistId = $customerWishlist['id'];
        $wishlistItem = $customerWishlist['items_v2']['items'][0];
        $itemId = $wishlistItem['id'];

        $query = $this->getQuery($wishlistId, $itemId);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        $this->assertArrayHasKey('addWishlistItemsToCart', $response);
        $wishlistAfterAddingToCart = $response['addWishlistItemsToCart']['wishlist'];
        $wishlistItems = $wishlistAfterAddingToCart['items_v2']['items'];
        $this->assertEmpty($wishlistItems);
        $this->assertArrayHasKey('status', $response['addWishlistItemsToCart']);
        $this->assertEquals($response['addWishlistItemsToCart']['status'], true);
    }

    /**
     * @magentoConfigFixture default_store wishlist/general/active 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Wishlist/_files/wishlist_with_configurable_product.php
     */
    public function testAddIncompleteItemsToCart(): void
    {
        $wishlist = $this->getWishlist();
        $customerWishlist = $wishlist['customer']['wishlists'][0];
        $wishlistId = $customerWishlist['id'];
        $wishlistItem = $customerWishlist['items_v2']['items'][0];
        $itemId = $wishlistItem['id'];

        $query = $this->getQuery($wishlistId, $itemId);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        $this->assertArrayHasKey('addWishlistItemsToCart', $response);
        $wishlistAfterAddingToCart = $response['addWishlistItemsToCart']['wishlist'];
        $userErrors = $response['addWishlistItemsToCart']['add_wishlist_items_to_cart_user_errors'];
        $this->assertEquals($userErrors[0]['message'], 'You need to choose options for your item.');
        $this->assertEquals($userErrors[0]['code'], 'UNDEFINED');
        $this->assertEquals($userErrors[0]['wishlistId'], $wishlistId);
        $this->assertEquals($userErrors[0]['wishlistItemId'], $itemId);
        $wishlistItems = $wishlistAfterAddingToCart['items_v2']['items'];
        $this->assertNotEmpty($wishlistItems);
        $this->assertArrayHasKey('status', $response['addWishlistItemsToCart']);
        $this->assertEquals($response['addWishlistItemsToCart']['status'], false);
    }

    /**
     * @magentoConfigFixture default_store wishlist/general/active 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Wishlist/_files/wishlist_with_multiple_products.php
     */
    public function testAddAllItemsToCart(): void
    {
        $wishlist = $this->getWishlist();
        $customerWishlist = $wishlist['customer']['wishlists'][0];
        $wishlistId = $customerWishlist['id'];

        $query = $this->getAddAllItemsToCartQuery($wishlistId);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        $this->assertArrayHasKey('addWishlistItemsToCart', $response);
        $wishlistAfterAddingToCart = $response['addWishlistItemsToCart']['wishlist'];
        $wishlistItems = $wishlistAfterAddingToCart['items_v2']['items'];
        $this->assertEmpty($wishlistItems);
        $this->assertArrayHasKey('status', $response['addWishlistItemsToCart']);
        $this->assertEquals($response['addWishlistItemsToCart']['status'], true);
    }

    /**
     * @magentoConfigFixture default_store wishlist/general/active 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Wishlist/_files/wishlist_with_simple_product.php
     */
    public function testAddItemsToCartForInvalidUser(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "The account sign-in was incorrect or your account is disabled temporarily. " .
            "Please wait and try again later."
        );

        $wishlist = $this->getWishlist();
        $customerWishlist = $wishlist['customer']['wishlists'][0];
        $wishlistId = $customerWishlist['id'];
        $wishlistItem = $customerWishlist['items_v2']['items'][0];
        $itemId = $wishlistItem['id'];

        $query = $this->getQuery($wishlistId, $itemId);
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
        $wishlistId = $customerWishlist['id'];
        $wishlistItem = $customerWishlist['items_v2']['items'][0];
        $itemId = $wishlistItem['id'];

        $query = $this->getQuery($wishlistId, $itemId);

        $this->graphQlMutation($query);
    }

    /**
     * @magentoConfigFixture default_store wishlist/general/active 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Wishlist/_files/wishlist_with_simple_product.php
     */
    public function testAddItemsToCartWithoutId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('"wishlistId" value should be specified');

        $wishlistId = '';
        $wishlist = $this->getWishlist();
        $customerWishlist = $wishlist['customer']['wishlists'][0];
        $wishlistItem = $customerWishlist['items_v2']['items'][0];
        $itemId = $wishlistItem['id'];
        $query = $this->getQuery($wishlistId, $itemId);
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoConfigFixture default_store wishlist/general/active 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Wishlist/_files/wishlist_with_simple_product.php
     */
    public function testAddItemsToCartWithInvalidId(): void
    {
        $wishlistId = '9999';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The wishlist was not found.');

        $wishlist = $this->getWishlist();
        $customerWishlist = $wishlist['customer']['wishlists'][0];
        $wishlistItem = $customerWishlist['items_v2']['items'][0];
        $itemId = $wishlistItem['id'];

        $query = $this->getQuery($wishlistId, $itemId);
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoConfigFixture default_store wishlist/general/active 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Wishlist/_files/wishlist_with_simple_product.php
     */
    public function testAddItemsToCartWithInvalidItemId(): void
    {
        $itemId = '9999';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The wishlist item ids "9999" were not found.');

        $wishlist = $this->getWishlist();
        $customerWishlist = $wishlist['customer']['wishlists'][0];

        $query = $this->getQuery($customerWishlist['id'], $itemId);
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

     /**
      * Add all items from customer's wishlist to cart
      *
      * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
      * @magentoConfigFixture wishlist/general/active 1
      * @magentoApiDataFixture Magento/Wishlist/_files/wishlist_with_simple_product.php
      */
    public function testAddAllWishlistItemsToCart(): void
    {
        $wishlist = $this->getWishlist();
        $this->assertNotEmpty($wishlist['customer']['wishlists'], 'No wishlist found');
        $customerWishlist = $wishlist['customer']['wishlists'][0];
        $wishlistId = $customerWishlist['id'];

        $sku2 = 'simple_product';
        $quantity2 = 2;
        $addProductsToWishlistQuery = $this->addSecondProductToWishlist($wishlistId, $sku2, $quantity2);
        $this->graphQlMutation($addProductsToWishlistQuery, [], '', $this->getHeaderMap());
        $addWishlistToCartQuery = $this->getAddAllItemsToCartQuery($wishlistId);

        $response = $this->graphQlMutation($addWishlistToCartQuery, [], '', $this->getHeaderMap());

        $this->assertArrayHasKey('addWishlistItemsToCart', $response);
        $this->assertArrayHasKey('status', $response['addWishlistItemsToCart']);
        $this->assertEquals($response['addWishlistItemsToCart']['status'], true);
        $wishlistAfterItemsAddedToCart = $this->getWishlist();
        $this->assertEmpty($wishlistAfterItemsAddedToCart['customer']['wishlists'][0]['items_v2']['items']);
        $customerCart = $this->getCustomerCart('customer@example.com');
        $this->assertCount(2, $customerCart['customerCart']['items']);
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
     * @param string $wishlistId
     * @param string $itemId
     * @return string
     */
    private function getQuery(
        string $wishlistId,
        string $itemId
    ): string {
        return <<<MUTATION
mutation {
    addWishlistItemsToCart
    (
      wishlistId: "{$wishlistId}"
      wishlistItemIds: ["{$itemId}"]
    ) {
    status
    wishlist {
      items_v2 {
        items {
          id
        }
      }
    }
    add_wishlist_items_to_cart_user_errors{
        message
        code
        wishlistId
        wishlistItemId
    }
   }
}
MUTATION;
    }

    /**
     * Returns GraphQl mutation string
     *
     * @param string $wishlistId
     * @param string $itemId
     * @return string
     */
    private function getAddAllItemsToCartQuery(
        string $wishlistId
    ): string {
        return <<<MUTATION
mutation {
    addWishlistItemsToCart
    (
      wishlistId: "{$wishlistId}"
    ) {
    status
    wishlist {
      items_v2 {
        items {
          id
        }
      }
    }
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
     * Get customer cart details
     *
     * @param string $username
     * @return array
     * @throws AuthenticationException
     */
    public function getCustomerCart(string $username): array
    {
        return $this->graphQlQuery($this->getCustomerCartQuery(), [], '', $this->getHeaderMap($username));
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

    /**
     * Returns the GraphQl mutation string for products added to wishlist
     *
     * @param string $wishlistId
     * @param string $sku2
     * @param int $quantity2
     * @return string
     */
    private function addSecondProductToWishlist(
        string $wishlistId,
        string $sku,
        int $quantity
    ): string {
        return <<<MUTATION
mutation {
  addProductsToWishlist(
    wishlistId: "{$wishlistId}",
    wishlistItems: [
    {
      sku: "{$sku}"
      quantity: {$quantity}
    }
    ]
) {
    user_errors {
      code
      message
    }
    wishlist {
      id
      items_count
        items_v2 {
          items {
           quantity
            id
            product {sku name}
         }
        page_info {current_page page_size total_pages}
      }
    }
  }
}
MUTATION;
    }

    /**
     * Get customer cart query
     *
     * @return string
     */
    private function getCustomerCartQuery(): string
    {
        return <<<QUERY
{customerCart {
  id
  total_quantity
  items {
  uid
  quantity
  product{sku}
   }
 }
}
QUERY;
    }
}
