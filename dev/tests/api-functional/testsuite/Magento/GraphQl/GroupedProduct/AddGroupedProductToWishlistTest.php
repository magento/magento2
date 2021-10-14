<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\GroupedProduct;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\WishlistFactory;

class AddGroupedProductToWishlistTest extends GraphQlAbstract
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
     * Set Up
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->wishlistFactory = $objectManager->get(WishlistFactory::class);
    }

    /**
     * @magentoApiDataFixture Magento/GroupedProduct/_files/product_grouped.php
     * @magentoConfigFixture default_store wishlist/general/active 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testAllFieldsGroupedProduct()
    {
        $productSku = 'grouped-product';
        $customerId = 1;
        $qty = 1;
        $mutation = $this->getMutation($productSku, $qty);
        $response = $this->graphQlMutation($mutation, [], '', $this->getHeaderMap());

        $wishlist = $this->wishlistFactory->create()->loadByCustomerId($customerId, true);
        /** @var Item $item */
        $item = $wishlist->getItemCollection()->getFirstItem();

        $this->assertArrayHasKey('addProductsToWishlist', $response);
        $this->assertArrayHasKey('wishlist', $response['addProductsToWishlist']);
        $response = $response['addProductsToWishlist']['wishlist'];
        $this->assertEquals($wishlist->getItemsCount(), $response['items_count']);
        $this->assertEquals($wishlist->getSharingCode(), $response['sharing_code']);
        $this->assertEquals($wishlist->getUpdatedAt(), $response['updated_at']);
        $this->assertEquals((int) $item->getQty(), $response['items_v2']['items'][0]['quantity']);
        $this->assertEquals($item->getAddedAt(), $response['items_v2']['items'][0]['added_at']);
        $this->assertEquals($productSku, $response['items_v2']['items'][0]['product']['sku']);
    }

    private function getMutation(
        string $sku,
        int $qty,
        int $wishlistId = 0
    ): string {
        return <<<MUTATION
mutation {
  addProductsToWishlist(
    wishlistId: {$wishlistId},
    wishlistItems: [
      {
        sku: "{$sku}"
        quantity: {$qty}
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
      updated_at
      items_v2 {
        items{
          id
        description
        quantity
        added_at
        product {
          sku
        }
        }

      }
    }
  }
}
MUTATION;
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
}
