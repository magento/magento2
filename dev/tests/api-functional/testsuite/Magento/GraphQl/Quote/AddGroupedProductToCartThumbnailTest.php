<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained from
 * Adobe.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\GroupedProduct\Test\Fixture\Product as GroupedProductFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask as QuoteMaskFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test adding grouped products returns default thumbnail/product image thumbnail
 */
class AddGroupedProductToCartThumbnailTest extends GraphQlAbstract
{
    private const DEFAULT_THUMBNAIL_PATH = 'Magento_Catalog/images/product/placeholder/thumbnail.jpg';

    #[
        DataFixture(CategoryFixture::class, ['name' => 'Category'], 'category'),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Product 1',
                'sku' => 'product-1',
                'category_ids' => ['$category.id$'],
                'price' => 10
            ],
            'product1'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Product 2',
                'sku' => 'product-2',
                'category_ids' => ['$category.id$'],
                'price' => 15
            ],
            'product2'
        ),
        DataFixture(
            GroupedProductFixture::class,
            [
                'sku' => 'grouped-product',
                'category_ids' => ['$category.id$'],
                'product_links' => [
                    ['sku' => '$product1.sku$', 'qty' => 1],
                    ['sku' => '$product2.sku$', 'qty' => 1]
                ]
            ],
            'grouped-product'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testAddGroupedProductToCartWithoutImageShouldUseThumbnail()
    {
        $cartId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $groupedProductId = DataFixtureStorageManager::getStorage()->get('grouped-product')->getSku();
        $response = $this->graphQlMutation($this->getMutation($cartId, $groupedProductId));

        $this->assertArrayHasKey('addProductsToCart', $response);
        $this->assertEquals(2, count($response['addProductsToCart']['cart']['itemsV2']['items']));
        $this->assertStringContainsString(
            self::DEFAULT_THUMBNAIL_PATH,
            $response['addProductsToCart']['cart']['itemsV2']['items'][0]['product']['thumbnail']['url']
        );
        $this->assertStringContainsString(
            self::DEFAULT_THUMBNAIL_PATH,
            $response['addProductsToCart']['cart']['itemsV2']['items'][1]['product']['thumbnail']['url']
        );
    }

    #[
        ConfigFixture('checkout/cart/grouped_product_image', 'itself'),
        DataFixture(CategoryFixture::class, ['name' => 'Category'], 'category'),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Product 1',
                'sku' => 'product-1',
                'category_ids' => ['$category.id$'],
                'price' => 10,
                'media_gallery_entries' => [
                    [
                        'label' => 'image',
                        'media_type' => 'image',
                        'position' => 1,
                        'disabled' => false,
                        'types' => [
                            'image',
                            'small_image',
                            'thumbnail'
                        ],
                        'file' => '/m/product1.jpg',
                    ],
                ],
            ],
            'product1'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Product 2',
                'sku' => 'product-2',
                'category_ids' => ['$category.id$'],
                'price' => 15,
                'media_gallery_entries' => [
                    [
                        'label' => 'image',
                        'media_type' => 'image',
                        'position' => 1,
                        'disabled' => false,
                        'types' => [
                            'image',
                            'small_image',
                            'thumbnail'
                        ],
                        'file' => '/m/product2.jpg',
                    ],
                ],
            ],
            'product2'
        ),
        DataFixture(
            GroupedProductFixture::class,
            [
                'sku' => 'grouped-product',
                'category_ids' => ['$category.id$'],
                'product_links' => [
                    ['sku' => '$product1.sku$', 'qty' => 1],
                    ['sku' => '$product2.sku$', 'qty' => 1]
                ]
            ],
            'grouped-product'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testAddGroupedProductToCartWithImageShouldUseProductImageAsThumbnail()
    {
        $cartId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $groupedProductId = DataFixtureStorageManager::getStorage()->get('grouped-product')->getSku();
        $product1 = DataFixtureStorageManager::getStorage()->get('product1');
        $product2 = DataFixtureStorageManager::getStorage()->get('product2');

        $response = $this->graphQlMutation($this->getMutation($cartId, $groupedProductId));

        $this->assertArrayHasKey('addProductsToCart', $response);
        $this->assertEquals(2, count($response['addProductsToCart']['cart']['itemsV2']['items']));
        $this->assertStringContainsString(
            $product1->getCustomAttribute('thumbnail')->getValue(),
            $response['addProductsToCart']['cart']['itemsV2']['items'][0]['product']['thumbnail']['url']
        );
        $this->assertStringContainsString(
            $product2->getCustomAttribute('thumbnail')->getValue(),
            $response['addProductsToCart']['cart']['itemsV2']['items'][1]['product']['thumbnail']['url']
        );
    }

    /**
     * Get addProductsToCart mutation based on passed parameters
     *
     * @param string $cartId
     * @param string $sku
     * @return string
     */
    private function getMutation(
        string $cartId,
        string $sku
    ): string {
        return <<<MUTATION
mutation {
	addProductsToCart(
		cartId: "$cartId"
		cartItems: [
			{
				quantity: 1
				sku: "$sku"
			}
		]
	) {
		cart {
			itemsV2(
				pageSize: 20
				currentPage: 1
				sort: { field: CREATED_AT, order: ASC }
			) {
				total_count
				items {
					product {
						thumbnail {
							url
						}
					}
				}
			}
		}
		user_errors {
			code
			message
		}
	}
}
MUTATION;
    }
}
