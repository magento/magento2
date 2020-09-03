<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Wishlist;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\WishlistFactory;

/**
 * Test coverage for adding a downloadable product to wishlist
 */
class AddDownloadableProductToWishlistTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var WishlistFactory
     */
    private $wishlistFactory;

    /**
     * @var GetCustomOptionsWithIDV2ForQueryBySku
     */
    private $getCustomOptionsWithIDV2ForQueryBySku;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $this->objectManager->get(CustomerTokenServiceInterface::class);
        $this->wishlistFactory = $this->objectManager->get(WishlistFactory::class);
        $this->getCustomOptionsWithIDV2ForQueryBySku =
            $this->objectManager->get(GetCustomOptionsWithIDV2ForQueryBySku::class);
    }

    /**
     * @magentoConfigFixture default_store wishlist/general/active 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable_with_custom_options.php
     */
    public function testAddDownloadableProductWithOptions(): void
    {
        $customerId = 1;
        $sku = 'downloadable-product-with-purchased-separately-links';
        $qty = 2;
        $links = $this->getProductsLinks($sku);
        $linkId = key($links);
        $itemOptions = $this->getCustomOptionsWithIDV2ForQueryBySku->execute($sku);
        $itemOptions['selected_options'][] = $this->generateProductLinkSelectedOptions($linkId);
        $productOptionsQuery = preg_replace(
            '/"([^"]+)"\s*:\s*/',
            '$1:',
            json_encode($itemOptions)
        );
        $query = $this->getQuery($qty, $sku, trim($productOptionsQuery, '{}'));
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());
        $wishlist = $this->wishlistFactory->create();
        $wishlist->loadByCustomerId($customerId, true);
        /** @var Item $wishlistItem */
        $wishlistItem = $wishlist->getItemCollection()->getFirstItem();

        self::assertArrayHasKey('addProductsToWishlist', $response);
        self::assertArrayHasKey('wishlist', $response['addProductsToWishlist']);
        $wishlistResponse = $response['addProductsToWishlist']['wishlist'];
        self::assertEquals($wishlist->getItemsCount(), $wishlistResponse['items_count']);
        self::assertEquals($wishlist->getSharingCode(), $wishlistResponse['sharing_code']);
        self::assertEquals($wishlist->getUpdatedAt(), $wishlistResponse['updated_at']);
        self::assertEquals($wishlistItem->getId(), $wishlistResponse['items'][0]['id']);
        self::assertEquals($wishlistItem->getData('qty'), $wishlistResponse['items'][0]['qty']);
        self::assertEquals($wishlistItem->getDescription(), $wishlistResponse['items'][0]['description']);
        self::assertEquals($wishlistItem->getAddedAt(), $wishlistResponse['items'][0]['added_at']);
    }

    /**
     * @magentoConfigFixture default_store wishlist/general/active 0
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable_with_custom_options.php
     */
    public function testAddDownloadableProductOnDisabledWishlist(): void
    {
        $qty = 2;
        $sku = 'downloadable-product-with-purchased-separately-links';
        $links = $this->getProductsLinks($sku);
        $linkId = key($links);
        $itemOptions = $this->getCustomOptionsWithIDV2ForQueryBySku->execute($sku);
        $itemOptions['selected_options'][] = $this->generateProductLinkSelectedOptions($linkId);
        $productOptionsQuery = trim(preg_replace(
            '/"([^"]+)"\s*:\s*/',
            '$1:',
            json_encode($itemOptions)
        ), '{}');
        $query = $this->getQuery($qty, $sku, $productOptionsQuery);
        $this->expectExceptionMessage('The wishlist configuration is currently disabled.');
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * Function returns array of all product's links
     *
     * @param string $sku
     *
     * @return array
     */
    private function getProductsLinks(string $sku): array
    {
        $result = [];
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($sku, false, null, true);

        foreach ($product->getDownloadableLinks() as $linkObject) {
            $result[$linkObject->getLinkId()] = [
                'title' => $linkObject->getTitle(),
                'price' => $linkObject->getPrice(),
            ];
        }

        return $result;
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
     * @param int $qty
     * @param string $sku
     * @param string $customizableOptions
     *
     * @return string
     */
    private function getQuery(
        int $qty,
        string $sku,
        string $customizableOptions
    ): string {
        return <<<MUTATION
mutation {
  addProductsToWishlist(
    wishlistId: 0,
    wishlistItems: [
      {
        sku: "{$sku}"
        quantity: {$qty}
        {$customizableOptions}
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
      items {
        id
        description
        qty
        added_at
      }
    }
  }
}
MUTATION;
    }

    /**
     * Generates Id_v2 for downloadable links
     *
     * @param int $linkId
     *
     * @return string
     */
    private function generateProductLinkSelectedOptions(int $linkId): string
    {
        return base64_encode("downloadable/$linkId");
    }
}
