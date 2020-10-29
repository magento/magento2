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
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Ui\Component\Form\Element\Select;

/**
 * Test coverage for updating a bundle product from wishlist
 */
class UpdateBundleProductsFromWishlistTest extends GraphQlAbstract
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
     * @var mixed
     */
    private $productRepository;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->wishlistFactory = $objectManager->get(WishlistFactory::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
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
     * Get Bundle wishlist result
     *
     * @param string $username
     * @return array
     *
     * @throws Exception
     */
    private function getBundleWishlist(string $username = 'customer@example.com'): array
    {
        return $this->graphQlQuery($this->getCustomerBundleWishlistQuery(), [], '', $this->getHeaderMap($username));
    }

    private function getCustomerBundleWishlistQuery(): string
    {
        return <<<QUERY
query {
    customer {
        wishlists {
            id
            items_count
            items_v2 {
                id
                quantity
                ... on BundleWishlistItem{
                    bundle_options{
                        id
                        label
                        type
                        values {
                            id
                            label
                            quantity
                            price
                        }
                    }
                }
            }
        }
    }
}
QUERY;
    }

    /**
     * @magentoConfigFixture default_store wishlist/general/active 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Bundle/_files/product_1.php
     *
     * @throws Exception
     */
    public function testUpdateBundleProductWithOptions(): void
    {
        $wishlist = $this->getBundleWishlist();
        $qty = 2;
        $optionQty = 1;
        $optionId = $wishlist['customer']['wishlists'][0]['items_v2'][0]['bundle_options'][0]['id'];

        $sku = 'bundle-product';
        $product = $this->productRepository->get($sku);
        /** @var Type $typeInstance */
        $typeInstance = $product->getTypeInstance();
        $typeInstance->setStoreFilter($product->getStoreId(), $product);
        /** @var Option $option */
        $option = $typeInstance->getOptionsCollection($product)->getLastItem();
        /** @var Product $selection */
        $selection = $typeInstance->getSelectionsCollection([$option->getId()], $product)->getLastItem();
        $optionId = $option->getId();
        $selectionId = $selection->getSelectionId();
        $bundleOptions = $this->generateBundleOptionUid((int) $optionId, (int) $selectionId, $optionQty);

        $wishlistId = $wishlist['customer']['wishlists'][0]['id'];
        $wishlistItemId = $wishlist['customer']['wishlists'][0]['items_v2'][0]['id'];
        $itemsCount = $wishlist['customer']['wishlists'][0]['items_count'];
        $query = $this->getBundleQuery((int)$wishlistItemId, $qty, $bundleOptions, (int)$wishlistId);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        $this->assertArrayHasKey('updateProductsInWishlist', $response);
        $this->assertArrayHasKey('wishlist', $response['updateProductsInWishlist']);
        $response = $response['updateProductsInWishlist']['wishlist'];
        $this->assertEquals($itemsCount, $response['items_count']);
        $this->assertEquals($qty, $response['items_v2'][0]['quantity']);
        $this->assertNotEmpty($response['items_v2'][0]['bundle_options']);
        $bundleOptions = $response['items_v2'][0]['bundle_options'];
        $this->assertEquals('Bundle Product Items', $bundleOptions[0]['label']);
        $this->assertEquals(Select::NAME, $bundleOptions[0]['type']);
    }

    /**
     * Returns GraphQl mutation string
     *
     * @param int $wishlistItemId
     * @param int $qty
     * @param string $bundleOptions
     * @param int $wishlistId
     *
     * @return string
     */
    private function getBundleQuery(
        int $wishlistItemId,
        int $qty,
        string $bundleOptions,
        int $wishlistId = 0
    ): string {
        return <<<MUTATION
mutation {
  updateProductsInWishlist(
    wishlistId: {$wishlistId},
    wishlistItems: [
      {
        wishlist_item_id: "{$wishlistItemId}"
        quantity: {$qty}
        selected_options: [
          "{$bundleOptions}"
        ]
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
        id
        description
        quantity
        ... on BundleWishlistItem {
          bundle_options {
            id
            label
            type
            values {
              id
              label
              quantity
              price
            }
          }
        }
      }
    }
  }
}
MUTATION;
    }

    /**
     * @param int $optionId
     * @param int $selectionId
     *
     * @param int $quantity
     *
     * @return string
     */
    private function generateBundleOptionUid(int $optionId, int $selectionId, int $quantity): string
    {
        return base64_encode("bundle/$optionId/$selectionId/$quantity");
    }
}
