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

class CustomerWishlistsTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var CollectionFactory
     */
    private $_wishlistCollectionFactory;

    protected function setUp()
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->_wishlistCollectionFactory = Bootstrap::getObjectManager()->get(CollectionFactory::class);
    }

    /**
     * @magentoApiDataFixture Magento/Wishlist/_files/wishlist.php
     */
    public function testGetCustomerWishlists(): void
    {
        /** @var \Magento\Wishlist\Model\Wishlist $wishlist */
        $collection = $this->_wishlistCollectionFactory->create()->filterByCustomerId(1);

        /** @var Item $wishlistItem */
        $wishlistItem = $collection->getFirstItem();
        $query =
            <<<QUERY
{
  customer
 {
  wishlists {
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

        $this->assertEquals($wishlistItem->getItemsCount(), $response['customer']['wishlists'][0]['items_count']);
        $this->assertEquals($wishlistItem->getSharingCode(), $response['customer']['wishlists'][0]['sharing_code']);
        $this->assertEquals($wishlistItem->getUpdatedAt(), $response['customer']['wishlists'][0]['updated_at']);
        $this->assertEquals('simple', $response['customer']['wishlists'][0]['items'][0]['product']['sku']);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The current customer isn't authorized.
     */
    public function testGetGuestWishlist()
    {
        $query =
            <<<QUERY
{
  customer
 {
      wishlists {
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
