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
use Magento\Wishlist\Model\ResourceModel\Wishlist as WishlistResourceModel;
use Magento\Wishlist\Model\WishlistFactory;

class WishlistTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var WishlistFactory
     */
    private $wishlistFactory;

    /**
     * @var WishlistResourceModel
     */
    private $wishlistResource;

    protected function setUp()
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->wishlistFactory = Bootstrap::getObjectManager()->get(WishlistFactory::class);
        $this->wishlistResource = Bootstrap::getObjectManager()->get(WishlistResourceModel::class);
    }

    /**
     * @magentoApiDataFixture Magento/Wishlist/_files/wishlist.php
     */
    public function testGetCustomerWishlist(): void
    {
        /** @var \Magento\Wishlist\Model\Wishlist $wishlist */
        $wishlist = $this->wishlistFactory->create();
        $this->wishlistResource->load($wishlist, 1, 'customer_id');

        /** @var Item $wishlistItem */
        $wishlistItem = $wishlist->getItemCollection()->getFirstItem();
        $wishlistItemProduct = $wishlistItem->getProduct();
        $query =
            <<<QUERY
{
  wishlist {
    items_count
    name
    sharing_code
    updated_at
    items {
      id
      qty
      description
      added_at
      product {
        sku
        name
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

        $this->assertEquals($wishlist->getItemsCount(), $response['wishlist']['items_count']);
        $this->assertEquals($wishlist->getName(), $response['wishlist']['name']);
        $this->assertEquals($wishlist->getSharingCode(), $response['wishlist']['sharing_code']);
        $this->assertEquals($wishlist->getUpdatedAt(), $response['wishlist']['updated_at']);

        $this->assertEquals($wishlistItem->getId(), $response['wishlist']['items'][0]['id']);
        $this->assertEquals($wishlistItem->getData('qty'), $response['wishlist']['items'][0]['qty']);
        $this->assertEquals($wishlistItem->getDescription(), $response['wishlist']['items'][0]['description']);
        $this->assertEquals($wishlistItem->getAddedAt(), $response['wishlist']['items'][0]['added_at']);

        $this->assertEquals($wishlistItemProduct->getSku(), $response['wishlist']['items'][0]['product']['sku']);
        $this->assertEquals($wishlistItemProduct->getName(), $response['wishlist']['items'][0]['product']['name']);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The current user cannot perform operations on wishlist
     */
    public function testGetGuestWishlist()
    {
        $query =
            <<<QUERY
{
  wishlist {
    items_count
    name
    sharing_code
    updated_at
    items {
      id
      qty
      description
      added_at
      product {
        sku
        name
      }
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
