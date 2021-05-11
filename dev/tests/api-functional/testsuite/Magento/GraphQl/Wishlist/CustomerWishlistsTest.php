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
use Magento\Wishlist\Model\ResourceModel\Wishlist\CollectionFactory;
use Magento\Wishlist\Model\Wishlist;

/**
 * Test coverage for customer wishlists
 */
class CustomerWishlistsTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var CollectionFactory
     */
    private $wishlistCollectionFactory;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->wishlistCollectionFactory = Bootstrap::getObjectManager()->get(CollectionFactory::class);
    }

    /**
     * Test fetching customer wishlist
     *
     * @magentoConfigFixture default_store wishlist/general/active 1
     * @magentoApiDataFixture Magento/Wishlist/_files/wishlist.php
     */
    public function testCustomerWishlist(): void
    {
        $customerId = 1;
        /** @var Wishlist $wishlist */
        $collection = $this->wishlistCollectionFactory->create()->filterByCustomerId($customerId);
        /** @var Item $wishlistItem */
        $wishlistItem = $collection->getFirstItem();
        $response = $this->graphQlQuery(
            $this->getQuery(),
            [],
            '',
            $this->getCustomerAuthHeaders('customer@example.com', 'password')
        );
        $this->assertArrayHasKey('wishlists', $response['customer']);
        $wishlist = $response['customer']['wishlists'][0];
        $this->assertEquals($wishlistItem->getItemsCount(), $wishlist['items_count']);
        $this->assertEquals($wishlistItem->getSharingCode(), $wishlist['sharing_code']);
        $this->assertEquals($wishlistItem->getUpdatedAt(), $wishlist['updated_at']);
        $wishlistItemResponse = $wishlist['items_v2']['items'][0];
        $this->assertEquals('simple', $wishlistItemResponse['product']['sku']);
    }

    /**
     * Testing fetching the wishlist when wishlist is disabled
     *
     * @magentoConfigFixture default_store wishlist/general/active 0
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testCustomerCannotGetWishlistWhenDisabled(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The wishlist configuration is currently disabled.');
        $this->graphQlQuery(
            $this->getQuery(),
            [],
            '',
            $this->getCustomerAuthHeaders('customer@example.com', 'password')
        );
    }

    /**
     * Test wishlist fetching for a guest customer
     *
     * @magentoConfigFixture default_store wishlist/general/active 1
     */
    public function testGuestCannotGetWishlist(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');
        $this->graphQlQuery($this->getQuery());
    }

    /**
     * Returns GraphQl query string
     *
     * @return string
     */
    private function getQuery(): string
    {
        return <<<QUERY
query {
  customer {
    wishlists {
      items_count
      sharing_code
      updated_at
      items_v2 {
        items {product {name sku}
        }
      }
    }
  }
}
QUERY;
    }

    /**
     * Getting customer auth headers
     *
     * @param string $email
     * @param string $password
     *
     * @return array
     *
     * @throws AuthenticationException
     */
    private function getCustomerAuthHeaders(string $email, string $password): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, $password);

        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
