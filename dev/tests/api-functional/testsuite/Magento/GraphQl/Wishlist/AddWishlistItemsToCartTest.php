<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare (strict_types = 1);

namespace Magento\GraphQl\Wishlist;

use Exception;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Test\Fixture\ProductStock as ProductStockFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Indexer\Test\Fixture\Indexer as IndexerFixture;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CartFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test coverage for add requisition list items to cart
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddWishlistItemsToCartTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
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
        $this->assertEquals($userErrors[0]['code'], 'REQUIRED_PARAMETER_MISSING');
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
        $addProductsToWishlistQuery = $this->getAddProductToWishlistQuery($wishlistId, $sku2, $quantity2);
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

    #[
        DataFixture(AttributeFixture::class, as: 'attribute'),
        DataFixture(ProductFixture::class, as: 'simple_product'),
        DataFixture(ProductFixture::class, as: 'conf_option_product1'),
        DataFixture(ProductFixture::class, as: 'conf_option_product2'),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$simple_product.id$', 'prod_qty' => 100]),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$conf_option_product1.id$', 'prod_qty' => 100]),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$conf_option_product2.id$', 'prod_qty' => 100]),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                'name' => 'Configurable Product',
                '_options' => ['$attribute$'],
                '_links' => ['$conf_option_product1$', '$conf_option_product2$']
            ],
            'configurable_product'
        ),
        DataFixture(IndexerFixture::class),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CartFixture::class, ['customer_id' => '$customer.id$'], 'cart'),
        DataFixture(AddProductToCartFixture::class, [
            'cart_id' => '$cart.id$',
            'product_id' => '$simple_product.id$',
            'qty' => 1,
        ])
    ]
    public function testAddAllWishlistItemsToCartWithoutSelectingConfOption()
    {
        $customerEmail = $this->fixtures->get('customer')->getEmail();
        $simpleSku = $this->fixtures->get('simple_product')->getSku();
        $confSku = $this->fixtures->get('configurable_product')->getSku();

        $wishlist = $this->getWishlist($customerEmail);
        $wishlistId = $wishlist['customer']['wishlists'][0]['id'];

        // Add configurable product to wishlist without selecting any options
        $addProductsToWishlistQuery = $this->getAddProductToWishlistQuery($wishlistId, $confSku, 1);
        $this->graphQlMutation($addProductsToWishlistQuery, [], '', $this->getHeaderMap($customerEmail));
        // Next add simple product to wishlist
        $addProductsToWishlistQuery = $this->getAddProductToWishlistQuery($wishlistId, $simpleSku, 1);
        $this->graphQlMutation($addProductsToWishlistQuery, [], '', $this->getHeaderMap($customerEmail));

        // Add all wishlist items to cart
        $query = $this->getAddAllItemsToCartQuery($wishlistId);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap($customerEmail));
        // Assert the response has error stating that the configurable product option is missing
        $this->assertArrayHasKey('addWishlistItemsToCart', $response);
        $this->assertFalse($response['addWishlistItemsToCart']['status']);
        $this->assertCount(1, $response['addWishlistItemsToCart']['add_wishlist_items_to_cart_user_errors']);
        $this->assertEquals(
            'REQUIRED_PARAMETER_MISSING',
            $response['addWishlistItemsToCart']['add_wishlist_items_to_cart_user_errors'][0]['code']
        );

        // Get the customer cart
        $customerCart = $this->getCustomerCart($customerEmail);
        $this->assertArrayHasKey('customerCart', $customerCart);
        // Assert that the customer cart has simple product with quantity 2
        // Initially added simple product to cart with quantity 1
        // After adding wishlist items to cart, the simple product quantity in cart becomes 2,
        // irrespective of error in configurable product
        $this->assertCount(1, $customerCart['customerCart']['items']);
        $this->assertEquals(2, $customerCart['customerCart']['items'][0]['quantity']);
        $this->assertEquals($simpleSku, $customerCart['customerCart']['items'][0]['product']['sku']);
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
     * @param string $sku
     * @param int $quantity
     * @return string
     */
    private function getAddProductToWishlistQuery(
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
