<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Wishlist;

use Exception;
use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\WishlistFactory;

/**
 * Test coverage for adding a bundle product to wishlist
 */
class AddBundleProductToWishlistTest extends GraphQlAbstract
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
     * @magentoConfigFixture default_store wishlist/general/active 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Bundle/_files/product_1.php
     *
     * @throws Exception
     */
    public function testAddBundleProductWithOptions(): void
    {
        $sku = 'bundle-product';
        $product = $this->productRepository->get($sku);
        $customerId = 1;
        $qty = 2;
        $optionQty = 1;

        /** @var Type $typeInstance */
        $typeInstance = $product->getTypeInstance();
        $typeInstance->setStoreFilter($product->getStoreId(), $product);
        /** @var Option $option */
        $option = $typeInstance->getOptionsCollection($product)->getFirstItem();
        /** @var Product $selection */
        $selection = $typeInstance->getSelectionsCollection([$option->getId()], $product)->getFirstItem();
        $optionId = $option->getId();
        $selectionId = $selection->getSelectionId();
        $bundleOptions = $this->generateBundleOptionUid((int) $optionId, (int) $selectionId, $optionQty);

        $query = $this->getQuery($sku, $qty, $bundleOptions);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());
        $wishlist = $this->wishlistFactory->create()->loadByCustomerId($customerId, true);
        /** @var Item $item */
        $item = $wishlist->getItemCollection()->getFirstItem();

        $this->assertArrayHasKey('addProductsToWishlist', $response);
        $this->assertArrayHasKey('wishlist', $response['addProductsToWishlist']);
        $this->assertEmpty($response['addProductsToWishlist']['user_errors']);
        $response = $response['addProductsToWishlist']['wishlist'];
        $this->assertEquals($wishlist->getItemsCount(), $response['items_count']);
        $this->assertEquals($wishlist->getSharingCode(), $response['sharing_code']);
        $this->assertEquals($wishlist->getUpdatedAt(), $response['updated_at']);
        $this->assertEquals($item->getData('qty'), $response['items_v2']['items'][0]['quantity']);
        $this->assertEquals($item->getDescription(), $response['items_v2']['items'][0]['description']);
        $this->assertEquals($item->getAddedAt(), $response['items_v2']['items'][0]['added_at']);
        $this->assertNotEmpty($response['items_v2']['items'][0]['bundle_options']);
        $bundleOptions = $response['items_v2']['items'][0]['bundle_options'];
        $this->assertEquals('Bundle Product Items', $bundleOptions[0]['label']);
        $this->assertEquals(Select::NAME, $bundleOptions[0]['type']);
        $bundleOptionValuesResponse = $bundleOptions[0]['values'][0];
        $this->assertNotNull($bundleOptionValuesResponse['id']);
        unset($bundleOptionValuesResponse['id']);
        $this->assertResponseFields(
            $bundleOptionValuesResponse,
            [
                'label' => 'Simple Product',
                'quantity' => 1,
                'price' => 2.75
            ]
        );
    }

    /**
     * @magentoApiDataFixture Magento/Bundle/_files/product_with_multiple_options_and_custom_quantity.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     *
     * @throws Exception
     */
    public function testAddingBundleItemWithCustomOptionQuantity()
    {
        $response = $this->graphQlQuery($this->getProductQuery("bundle-product"));
        $bundleItem = $response['products']['items'][0];
        $sku = $bundleItem['sku'];
        $bundleOptions = $bundleItem['items'];
        $customerId = 1;
        $uId0 = $bundleOptions[0]['options'][0]['uid'];
        $uId1 = $bundleOptions[1]['options'][0]['uid'];
        $query= $this->getQueryWithCustomOptionQuantity($sku, 5, $uId0, $uId1);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());
        $wishlist = $this->wishlistFactory->create()->loadByCustomerId($customerId, true);
        /** @var Item $item */
        $item = $wishlist->getItemCollection()->getFirstItem();

        $this->assertArrayHasKey('addProductsToWishlist', $response);
        $this->assertArrayHasKey('wishlist', $response['addProductsToWishlist']);
        $this->assertEmpty($response['addProductsToWishlist']['user_errors']);
        $response = $response['addProductsToWishlist']['wishlist'];
        $this->assertEquals($wishlist->getItemsCount(), $response['items_count']);
        $this->assertEquals($wishlist->getSharingCode(), $response['sharing_code']);
        $this->assertEquals($wishlist->getUpdatedAt(), $response['updated_at']);
        $this->assertEquals($item->getData('qty'), $response['items_v2']['items'][0]['quantity']);
        $this->assertEquals($item->getDescription(), $response['items_v2']['items'][0]['description']);
        $this->assertEquals($item->getAddedAt(), $response['items_v2']['items'][0]['added_at']);
        $this->assertNotEmpty($response['items_v2']['items'][0]['bundle_options']);
        $bundleOptions = $response['items_v2']['items'][0]['bundle_options'];
        $this->assertEquals('Option 1', $bundleOptions[0]['label']);
        $bundleOptionFirstValue = $bundleOptions[0]['values'];
        $this->assertEquals(7, $bundleOptionFirstValue[0]['quantity']);
        $this->assertEquals('Option 2', $bundleOptions[1]['label']);
        $bundleOptionSecondValue = $bundleOptions[1]['values'];
        $this->assertEquals(1, $bundleOptionSecondValue[0]['quantity']);
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
     * @param string $sku
     * @param int $qty
     * @param string $bundleOptions
     * @param int $wishlistId
     *
     * @return string
     */
    private function getQuery(
        string $sku,
        int $qty,
        string $bundleOptions,
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
        items {
           id
        description
        quantity
        added_at
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
}
MUTATION;
    }

    /**
     * Query with custom option quantity
     *
     * @param string $sku
     * @param int $qty
     * @param string $uId0
     * @param string $uId1
     * @param int $wishlistId
     * @return string
     */
    private function getQueryWithCustomOptionQuantity(
        string $sku,
        int $qty,
        string $uId0,
        string $uId1,
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
        entered_options: [
            {
              uid:"{$uId0}",
              value:"7"
            },
            {
              uid:"{$uId1}",
              value:"7"
            }
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
        items {
           id
        description
        quantity
        added_at
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
}
MUTATION;
    }

    /**
     * Returns GraphQL query for retrieving a product with customizable options
     *
     * @param string $sku
     * @return string
     */
    private function getProductQuery(string $sku): string
    {
        return <<<QUERY
{
  products(search: "{$sku}") {
    items {
      sku
       ... on BundleProduct {
              items {
                sku
                option_id
                required
                type
                title
                options {
                  uid
                  label
                  product {
                    sku
                  }
                  can_change_quantity
                  id
                  price

                  quantity
                }
              }
       }
    }
  }
}
QUERY;
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
