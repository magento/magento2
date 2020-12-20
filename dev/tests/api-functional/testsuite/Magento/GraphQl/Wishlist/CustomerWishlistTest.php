<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Wishlist;

use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\ResourceModel\Wishlist\CollectionFactory;

class CustomerWishlistTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var CollectionFactory
     */
    private $wishlistCollectionFactory;

    protected function setUp(): void
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->wishlistCollectionFactory = Bootstrap::getObjectManager()->get(CollectionFactory::class);
    }

    /**
     * @magentoConfigFixture default_store wishlist/general/active 1
     * @magentoApiDataFixture Magento/Wishlist/_files/wishlist.php
     */
    public function testCustomerWishlist(): void
    {
        /** @var \Magento\Wishlist\Model\Wishlist $wishlist */
        $collection = $this->wishlistCollectionFactory->create()->filterByCustomerId(1);

        /** @var Item $wishlistItem */
        $wishlistItem = $collection->getFirstItem();
        $query =
            <<<QUERY
{
  customer {
    wishlist {
      id
      items_count
      sharing_code
      updated_at
      items {
        product {
          sku
        }
      }
    }
  }
}
QUERY;

        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders('customer@example.com', 'password')
        );
        $this->assertEquals((string)$wishlistItem->getId(), $response['customer']['wishlist']['id']);
        $this->assertEquals($wishlistItem->getItemsCount(), $response['customer']['wishlist']['items_count']);
        $this->assertEquals($wishlistItem->getSharingCode(), $response['customer']['wishlist']['sharing_code']);
        $this->assertEquals($wishlistItem->getUpdatedAt(), $response['customer']['wishlist']['updated_at']);
        $this->assertEquals('simple', $response['customer']['wishlist']['items'][0]['product']['sku']);
    }

    /**
     * @magentoConfigFixture default_store wishlist/general/active 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testCustomerAlwaysHasWishlist(): void
    {
        $query =
            <<<QUERY
{
  customer {
    wishlist {
      id
    }
  }
}
QUERY;

        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders('customer@example.com', 'password')
        );

        $this->assertNotEmpty($response['customer']['wishlist']['id']);
    }

    /**
     * @magentoConfigFixture default_store wishlist/general/active 1
     */
    public function testGuestCannotGetWishlist()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');

        $query =
            <<<QUERY
{
  customer {
    wishlist {
      items_count
      sharing_code
      updated_at
    }
  }
}
QUERY;
        $this->graphQlQuery($query);
    }

    /**
     * Add product to wishlist with quantity 0
     *
     * @magentoConfigFixture default_store wishlist/general/active 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     */
    public function testAddProductToWishlistWithZeroQty()
    {
        $customerWishlistQuery =
            <<<QUERY
{
  customer {
    wishlist {
      id
    }
  }
}
QUERY;

        $response = $this->graphQlQuery(
            $customerWishlistQuery,
            [],
            '',
            $this->getCustomerAuthHeaders('customer@example.com', 'password')
        );
        $qty = 0;
        $sku = 'simple-1';
        $wishlistId = $response['customer']['wishlist']['id'];
        $addProductToWishlistQuery =
            <<<QUERY
mutation{
   addProductsToWishlist(
     wishlistId:{$wishlistId}
     wishlistItems:[
      {
        sku:"{$sku}"
        quantity:{$qty}
      }
    ])
  {
     wishlist{
     id
     items_count
     items{product{name sku} description qty}
    }
    user_errors{code message}
  }
}

QUERY;
        $addToWishlistResponse = $this->graphQlMutation(
            $addProductToWishlistQuery,
            [],
            '',
            $this->getCustomerAuthHeaders('customer@example.com', 'password')
        );
        $this->assertArrayHasKey('user_errors', $addToWishlistResponse['addProductsToWishlist']);
        $this->assertCount(1, $addToWishlistResponse['addProductsToWishlist']['user_errors']);
        $this->assertEmpty($addToWishlistResponse['addProductsToWishlist']['wishlist']['items']);
        $this->assertEquals(
            0,
            $addToWishlistResponse['addProductsToWishlist']['wishlist']['items_count'],
            'Count is greater than 0'
        );
        $message = 'The quantity of a wish list item cannot be 0';
        $this->assertEquals(
            $message,
            $addToWishlistResponse['addProductsToWishlist']['user_errors'][0]['message']
        );
    }

    /**
     * Add disabled product to wishlist
     *
     * @magentoConfigFixture default_store wishlist/general/active 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Catalog/_files/simple_product_disabled.php
     */
    public function testAddProductToWishlistWithDisabledProduct()
    {
        $customerWishlistQuery =
            <<<QUERY
{
  customer {
    wishlist {
      id
    }
  }
}
QUERY;

        $response = $this->graphQlQuery(
            $customerWishlistQuery,
            [],
            '',
            $this->getCustomerAuthHeaders('customer@example.com', 'password')
        );
        $qty = 2;
        $sku = 'product_disabled';
        $wishlistId = $response['customer']['wishlist']['id'];
        $addProductToWishlistQuery =
            <<<QUERY
mutation{
   addProductsToWishlist(
     wishlistId:{$wishlistId}
     wishlistItems:[
      {
        sku:"{$sku}"
        quantity:{$qty}
      }
    ])
  {
     wishlist{
     id
     items_count
     items{product{name sku} description qty}
    }
    user_errors{code message}
  }
}

QUERY;
        $addToWishlistResponse = $this->graphQlMutation(
            $addProductToWishlistQuery,
            [],
            '',
            $this->getCustomerAuthHeaders('customer@example.com', 'password')
        );
        $this->assertArrayHasKey('user_errors', $addToWishlistResponse['addProductsToWishlist']);
        $this->assertCount(1, $addToWishlistResponse['addProductsToWishlist']['user_errors']);
        $this->assertEmpty($addToWishlistResponse['addProductsToWishlist']['wishlist']['items']);
        $this->assertEquals(
            0,
            $addToWishlistResponse['addProductsToWishlist']['wishlist']['items_count'],
            'Count is greater than 0'
        );
        $message = 'The product is disabled';
        $this->assertEquals(
            $message,
            $addToWishlistResponse['addProductsToWishlist']['user_errors'][0]['message']
        );
    }

    /**
     * @magentoConfigFixture default_store wishlist/general/active 0
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testCustomerCannotGetWishlistWhenDisabled()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The wishlist configuration is currently disabled.');

        $query =
            <<<QUERY
{
  customer {
    wishlist {
      items_count
      sharing_code
      updated_at
    }
  }
}
QUERY;
        $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders('customer@example.com', 'password')
        );
    }

    /**
     * @param string $email
     * @param string $password
     * @return array
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    private function getCustomerAuthHeaders(string $email, string $password): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, $password);
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
