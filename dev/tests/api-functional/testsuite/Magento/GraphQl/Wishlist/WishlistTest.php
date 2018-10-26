<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Wishlist;

use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Wishlist\Model\Item;

class WishlistTest extends GraphQlAbstract
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->customerTokenService = $this->objectManager->get(CustomerTokenServiceInterface::class);
    }

    /**
     * Verify the fields of CMS Block selected by identifiers
     *
     * @magentoApiDataFixture Magento/Wishlist/_files/wishlist.php
     * @throws \Magento\Framework\Exception\AuthenticationException
     * @throws \Exception
     */
    public function testGetCustomersWishlist(): void
    {
        /** @var \Magento\Wishlist\Model\Wishlist $wishlist */
        $wishlist = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Wishlist\Model\Wishlist::class
        );
        $wishlist->loadByCustomerId(1, true);
        /** @var Item $wishlistItem */
        $wishlistItem = $wishlist->getItemCollection()->getFirstItem();
        $wishlistItemProduct = $wishlistItem->getProduct();
        $query =
            <<<QUERY
{
  wishlist {
    items {
      id
      qty
      product {
        sku
        name
      }
      description
      added_at
    }
    sharing_code
    updated_at
  }
}
QUERY;

        $response = $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders('customer@example.com', 'password'));
        $this->assertEquals($wishlist->getSharingCode(), $response['wishlist']['sharing_code']);
        $this->assertEquals($wishlistItem->getData('qty'), $response['wishlist']['items'][0]['qty']);
        $this->assertEquals($wishlistItem->getDescription(), $response['wishlist']['items'][0]['description']);
        $this->assertEquals($wishlistItemProduct->getSku(), $response['wishlist']['items'][0]['product']['sku']);
        $this->assertEquals($wishlistItemProduct->getName(), $response['wishlist']['items'][0]['product']['name']);
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
