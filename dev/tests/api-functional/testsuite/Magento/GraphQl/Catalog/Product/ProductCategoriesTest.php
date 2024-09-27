<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GraphQl\Catalog\Product;

use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Store\Test\Fixture\Group as GroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for product categories
 */
class ProductCategoriesTest extends GraphQlAbstract
{
    #[
        AppArea('adminhtml'),
        DataFixture(CategoryFixture::class, ['name' => 'Category 1', 'parent_id' => '2'], 'c11'),
        DataFixture(CategoryFixture::class, ['name' => 'Category 2', 'parent_id' => '1'], 'c2'),
        DataFixture(CategoryFixture::class, ['name' => 'Category 2.1', 'parent_id' => '$c2.id$'], 'c21'),
        DataFixture(CategoryFixture::class, ['name' => 'Category 2.1.1', 'parent_id' => '$c21.id$'], 'c211'),
        DataFixture(WebsiteFixture::class, as: 'w2'),
        DataFixture(GroupFixture::class, ['website_id' => '$w2.id$', 'root_category_id' => '$c2.id$'], 's2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$s2.id$']),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'in-stock-product',
                'category_ids' => ['2', '$c11.id$', '$c2.id$', '$c21.id$', '$c211.id$'],
                'website_ids' => ['1', '$w2.id$']
            ],
        ),
    ]
    public function testProductCategoriesInDefaultStore(): void
    {
        $response = $this->graphQlQuery(
            $this->getQuery('in-stock-product'),
            [],
            '',
            ['Store' => 'default']
        );

        $product = current($response['products']['items']);
        $categories = $product['categories'];

        self::assertCount(1, $categories);
        self::assertEquals('Category 1', $categories[0]['name']);
        self::assertEquals('category-1', $categories[0]['url_path']);
        self::assertNull($categories[0]['breadcrumbs']);
    }

    #[
        AppArea('adminhtml'),
        DataFixture(CategoryFixture::class, ['name' => 'Category 1', 'parent_id' => '2'], 'c11'),
        DataFixture(CategoryFixture::class, ['name' => 'Category 2', 'parent_id' => '1'], 'c2'),
        DataFixture(CategoryFixture::class, ['name' => 'Category 2.1', 'parent_id' => '$c2.id$'], 'c21'),
        DataFixture(CategoryFixture::class, ['name' => 'Category 2.1.1', 'parent_id' => '$c21.id$'], 'c211'),
        DataFixture(WebsiteFixture::class, as: 'w2'),
        DataFixture(GroupFixture::class, ['website_id' => '$w2.id$', 'root_category_id' => '$c2.id$'], 's2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$s2.id$'], as: 'store2'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'in-stock-product',
                'category_ids' => ['2', '$c11.id$', '$c2.id$', '$c21.id$', '$c211.id$'],
                'website_ids' => ['1', '$w2.id$']
            ],
        ),
    ]
    public function testProductCategoriesInNonDefaultStore(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $storeCode = $fixtures->get('store2')->getCode();

        $response = $this->graphQlQuery(
            $this->getQuery('in-stock-product'),
            [],
            '',
            ['Store' => $storeCode]
        );

        $product = current($response['products']['items']);
        $categories = $product['categories'];

        self::assertCount(2, $categories);
        self::assertEquals('Category 2.1', $categories[0]['name']);
        self::assertEquals('category-2-1', $categories[0]['url_path']);
        self::assertNull($categories[0]['breadcrumbs']);
        self::assertEquals('Category 2.1.1', $categories[1]['name']);
        self::assertEquals('category-2-1/category-2-1-1', $categories[1]['url_path']);
        self::assertCount(1, $categories[1]['breadcrumbs']);
        self::assertEquals('Category 2.1', $categories[1]['breadcrumbs'][0]['category_name']);
        self::assertEquals(2, $categories[1]['breadcrumbs'][0]['category_level']);
    }

    #[
        AppArea('adminhtml'),
        DataFixture(CategoryFixture::class, ['name' => 'Category 1', 'parent_id' => '2'], 'c11'),
        DataFixture(CategoryFixture::class, ['name' => 'Category 2', 'parent_id' => '1'], 'c2'),
        DataFixture(CategoryFixture::class, ['name' => 'Category 2.1', 'parent_id' => '$c2.id$'], 'c21'),
        DataFixture(CategoryFixture::class, ['name' => 'Category 2.1.1', 'parent_id' => '$c21.id$'], 'c211'),
        DataFixture(WebsiteFixture::class, as: 'w2'),
        DataFixture(GroupFixture::class, ['website_id' => '$w2.id$', 'root_category_id' => '$c2.id$'], 's2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$s2.id$'], as: 'store2'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'in-stock-product',
                'category_ids' => ['2', '$c11.id$', '$c2.id$', '$c21.id$', '$c211.id$']
            ],
        ),
    ]
    public function testProductCategoriesInNotRelevantStore(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $storeCode = $fixtures->get('store2')->getCode();

        $response = $this->graphQlQuery(
            $this->getQuery('in-stock-product'),
            [],
            '',
            ['Store' => $storeCode]
        );

        self::assertEmpty($response['products']['items']);
    }

    /**
     * Get query
     *
     * @param string $sku
     * @return string
     */
    private function getQuery(string $sku): string
    {
        return  <<<QUERY
{
  products(filter: { sku: { eq: "{$sku}"} }){
   	items {
      categories {
        name
        id
        url_path
        breadcrumbs {
          category_id
          category_name
          category_level
        }
      }
    }
  }
}
QUERY;
    }
}
