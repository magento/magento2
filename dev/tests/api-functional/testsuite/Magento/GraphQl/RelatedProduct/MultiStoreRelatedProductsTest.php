<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\RelatedProduct;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test coverage for multi store related products.
 */
class MultiStoreRelatedProductsTest extends GraphQlAbstract
{
    /**
     * Test query with multi store related products count test
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    #[
        DataFixture(WebsiteFixture::class, ['name' => 'Website 1'], as: 'website1'),
        DataFixture(StoreGroupFixture::class, ['name' => 'StoreGroup 1',
            'website_id' => '$website1.id$'], as: 'store_group1'),
        DataFixture(StoreFixture::class, ['name' => 'Store 1',
            'store_group_id' => '$store_group1.id$'], as: 'store1'),
        DataFixture(WebsiteFixture::class, ['name' => 'Website 2'], as: 'website2'),
        DataFixture(StoreGroupFixture::class, ['name' => 'StoreGroup 2',
            'website_id' => '$website2.id$'], as: 'store_group2'),
        DataFixture(StoreFixture::class, ['name' => 'Store 2',
            'store_group_id' => '$store_group2.id$'], as: 'store2'),
        DataFixture(ProductFixture::class, ['name' =>'Website 1 Product A',
            'sku' => 'Website 1 Product A', 'price' => 20,
            'website_ids' => ['$website1.id$']], as: 'product2'),
        DataFixture(ProductFixture::class, ['name' =>'Website 1 Product B',
            'sku' => 'Website 1 Product B', 'price' => 30,
            'website_ids' => ['$website1.id$']], as: 'product3'),
        DataFixture(ProductFixture::class, ['name' =>'Website 2 Product Y',
            'sku' => 'Website 2 Product Y', 'price' => 20,
            'website_ids' => ['$website2.id$']], as: 'product4'),
        DataFixture(ProductFixture::class, ['name' =>'Website 2 Product Z',
            'sku' => 'Website 2 Product Z', 'price' => 30,
            'website_ids' => ['$website2.id$']], as: 'product5'),
        DataFixture(ProductFixture::class, ['name' =>'Website 1-2 Product M',
            'sku' => 'Website 1-2 Product M', 'price' => 30,
            'website_ids' => ['$website1.id$', '$website2.id$']], as: 'product6'),
        DataFixture(ProductFixture::class, ['name' =>'Global Product', 'sku' => 'global-product', 'price' => 10,
            'website_ids' => [1, '$website1.id$', '$website2.id$'],
            'product_links' => ['$product2.sku$','$product3.sku$',
                '$product4.sku$','$product5.sku$','$product6.sku$']], as: 'product1'),
    ]
    public function testQueryRelatedProductsForMultiStore()
    {
        $headerMapWebsite1Store['Store'] = DataFixtureStorageManager::getStorage()->get('store1')->getCode();
        $headerMapWebsite2Store['Store'] = DataFixtureStorageManager::getStorage()->get('store2')->getCode();

        $productSku = 'global-product';

        $query = <<<QUERY
        {
            products(filter: {sku: {eq: "{$productSku}"}})
            {
                items {
                    sku,
                    name,
                    related_products
                    {
                        sku
                        name
                        websites {
                            code
                        }
                    }
                }
            }
        }
QUERY;
        //graphQl query for store1
        $response = $this->graphQlQuery($query, [], '', $headerMapWebsite1Store);
        $this->assertRelatedProducts($response);

        //graphQl query for store2
        $response = $this->graphQlQuery($query, [], '', $headerMapWebsite2Store);
        $this->assertRelatedProducts($response);
    }

    /**
     * Assert related products with count
     *
     * @param float|int|bool|array|string $response
     * @return void
     */
    private function assertRelatedProducts(float|int|bool|array|string $response): void
    {
        self::assertArrayHasKey('products', $response);
        self::assertArrayHasKey('items', $response['products']);
        self::assertCount(1, $response['products']['items']);
        self::assertArrayHasKey(0, $response['products']['items']);
        self::assertArrayHasKey('related_products', $response['products']['items'][0]);
        $relatedProducts = $response['products']['items'][0]['related_products'];
        self::assertCount(3, $relatedProducts);
    }
}
