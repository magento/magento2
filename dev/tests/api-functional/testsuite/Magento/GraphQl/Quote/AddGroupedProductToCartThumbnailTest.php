<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\GroupedProduct\Test\Fixture\Product as GroupedProductFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask as QuoteMaskFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test adding grouped products returns default thumbnail/product image thumbnail
 */
#[
    DataFixture(CategoryFixture::class, as: 'category'),
    DataFixture(
        ProductFixture::class,
        [
            'category_ids' => ['$category.id$'],
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
            'category_ids' => ['$category.id$'],
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
class AddGroupedProductToCartThumbnailTest extends GraphQlAbstract
{
    private const DEFAULT_THUMBNAIL_PATH = 'Magento_Catalog/images/product/placeholder/thumbnail.jpg';

    /**
     * @throws LocalizedException
     */
    #[
        ConfigFixture('checkout/cart/grouped_product_image', 'parent'),
    ]
    public function testAddGroupedProductToCartWithImageShouldUseParentImageAsThumbnail()
    {
        $thumbnails = [
            'product1' => self::DEFAULT_THUMBNAIL_PATH,
            'product2' => self::DEFAULT_THUMBNAIL_PATH
        ];
        $this->assertProductThumbnailUrl($thumbnails);
    }

    /**
     * @throws LocalizedException
     */
    #[
        ConfigFixture('checkout/cart/grouped_product_image', 'itself'),
    ]
    public function testAddGroupedProductToCartWithImageShouldUseProductImageAsThumbnail()
    {
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $product1Sku = DataFixtureStorageManager::getStorage()->get('product1')->getSku();
        $product2Sku = DataFixtureStorageManager::getStorage()->get('product2')->getSku();
        $thumbnails = [
            'product1' => $productRepository->get($product1Sku)->getThumbnail(),
            'product2' => $productRepository->get($product2Sku)->getThumbnail()
        ];

        $this->assertProductThumbnailUrl($thumbnails);
    }

    /**
     * Assert product thumbnail url
     *
     * @param array $thumbnails
     * @return void
     * @throws LocalizedException
     * @throws Exception
     */
    private function assertProductThumbnailUrl(array $thumbnails): void
    {
        $cartId = DataFixtureStorageManager::getStorage()->get('quoteIdMask')->getMaskedId();
        $groupedProductSku = DataFixtureStorageManager::getStorage()->get('grouped-product')->getSku();
        $response = $this->graphQlMutation($this->getMutation($cartId, $groupedProductSku));

        $this->assertArrayHasKey('addProductsToCart', $response);
        $this->assertEquals(2, count($response['addProductsToCart']['cart']['itemsV2']['items']));

        $this->assertStringContainsString(
            $thumbnails['product1'],
            $response['addProductsToCart']['cart']['itemsV2']['items'][0]['product']['thumbnail']['url']
        );
        $this->assertStringContainsString(
            $thumbnails['product2'],
            $response['addProductsToCart']['cart']['itemsV2']['items'][1]['product']['thumbnail']['url']
        );
    }

    /**
     * @throws LocalizedException
     */
    #[
        ConfigFixture('checkout/cart/grouped_product_image', 'itself'),
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
        $thumbnails = [
            'product1' => self::DEFAULT_THUMBNAIL_PATH,
            'product2' => self::DEFAULT_THUMBNAIL_PATH
        ];
        $this->assertProductThumbnailUrl($thumbnails);
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
