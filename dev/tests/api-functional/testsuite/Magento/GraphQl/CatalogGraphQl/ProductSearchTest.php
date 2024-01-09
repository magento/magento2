<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CatalogGraphQl;

use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Indexer\Model\IndexerFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\Catalog\Test\Fixture\Product;
use Magento\Indexer\Test\Fixture\Indexer;

/**
 * Test class to verify product search, used for GraphQL resolver
 * for configurable product returns only visible products.
 */
class ProductSearchTest extends GraphQlAbstract
{
    #[
        DataFixture(CategoryFixture::class, as: 'cat1'),
        DataFixture(
            ProductFixture::class,
            [
                'category_ids' => ['$cat1.id$'],
            ],
            'product'
        )
    ]
    public function testSearchProductsWithCategoriesAliasPresentInQuery(): void
    {
        $this->reindexCatalogCategory();
        /** @var \Magento\Catalog\Model\Product $product */
        $product = DataFixtureStorageManager::getStorage()->get('product');
        /** @var \Magento\Catalog\Model\Category $category */
        $category = DataFixtureStorageManager::getStorage()->get('cat1');
        $response = $this->graphQlQuery($this->getProductSearchQueryWithCategoriesAlias($product->getSku()));

        $this->assertNotEmpty($response['products']);
        $this->assertNotEmpty($response['products']['items']);
        $this->assertEquals(
            $category->getUrlKey(),
            $response['products']['items'][0]['custom_categories'][0]['url_key']
        );
    }

    /**
     * Make catalog_category reindex.
     *
     * @return void
     * @throws \Throwable
     */
    private function reindexCatalogCategory(): void
    {
        $indexerFactory = Bootstrap::getObjectManager()->create(IndexerFactory::class);
        $indexer = $indexerFactory->create();
        $indexer->load('catalog_category_product')->reindexAll();
    }

    #[
        DataFixture(Product::class, as: 'product')
    ]
    public function testSearchProductsWithSkuEqFilterQuery(): void
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = DataFixtureStorageManager::getStorage()->get('product');
        $response = $this->graphQlQuery($this->getProductSearchQuery($product->getName(), $product->getSku()));

        $this->assertNotEmpty($response['products']);
        $this->assertEquals(1, $response['products']['total_count']);
        $this->assertNotEmpty($response['products']['items']);
        $this->assertEquals($product->getName(), $response['products']['items'][0]['name']);
        $this->assertEquals($product->getSku(), $response['products']['items'][0]['sku']);
    }

    #[
        DataFixture(Product::class, as: 'product1'),
        DataFixture(Product::class, as: 'product2'),
        DataFixture(Product::class, as: 'product3')
    ]
    public function testSearchProductsWithMultipleSkuInFilterQuery(): void
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $response = $this->graphQlQuery(
            $this->getProductSearchQueryWithMultipleSkusFilter([
                DataFixtureStorageManager::getStorage()->get('product1'),
                DataFixtureStorageManager::getStorage()->get('product2'),
                DataFixtureStorageManager::getStorage()->get('product3')
            ], "simple")
        );

        $this->assertNotEmpty($response['products']);
        $this->assertEquals(3, $response['products']['total_count']);
        $this->assertNotEmpty($response['products']['items']);
    }

    #[
        DataFixture(Product::class, as: 'product1'),
        DataFixture(Product::class, as: 'product2'),
        DataFixture(Indexer::class, as: 'indexer')
    ]
    public function testSearchSuggestions(): void
    {
        $response = $this->graphQlQuery($this->getSearchQueryWithSuggestions());
        $this->assertNotEmpty($response['products']);
        $this->assertEmpty($response['products']['items']);
        $this->assertNotEmpty($response['products']['suggestions']);
    }

    /**
     * Get a query which contains alias for product categories data.
     *
     * @param string $productSku
     * @return string
     */
    private function getProductSearchQueryWithCategoriesAlias(string $productSku): string
    {
        return <<<QUERY
        {
        products(filter: {
            sku: {
                eq: "{$productSku}"
            }})
            {
                items {
                    name
                    sku
                    categories {
                        uid
                        name
                    }
                    custom_categories: categories {
                        url_key
                    }
                }
            }
        }
        QUERY;
    }

    /**
     * Get a query which user filter for product sku and search by product name
     *
     * @param string $productName
     * @param string $productSku
     * @return string
     */
    private function getProductSearchQuery(string $productName, string $productSku): string
    {
        return <<<QUERY
        {
        products(filter: {
            sku: {
                eq: "{$productSku}"
            }},
            search: "$productName",
            sort: {},
            pageSize: 200,
            currentPage: 1)
            {
                total_count
                page_info {
                    total_pages
                    current_page
                    page_size
                }
                items {
                    name
                    sku
                }
            }
        }
        QUERY;
    }

    /**
     * Get a query which filters list of found products by array of SKUs
     *
     * @param array $products
     * @param string $product
     * @return string
     */
    private function getProductSearchQueryWithMultipleSkusFilter(array $products, string $product): string
    {
        return <<<QUERY
        {
        products(filter: {
            sku: {
                in: [
                    "{$products[0]->getSku()}",
                    "{$products[1]->getSku()}",
                    "{$products[2]->getSku()}"
                ]
            }},
            search: "$product",
            sort: {},
            pageSize: 200,
            currentPage: 1)
            {
                total_count
                page_info {
                    total_pages
                    current_page
                    page_size
                }
                items {
                    name
                    sku
                }
            }
        }
        QUERY;
    }

    /**
     * Prepare search query with suggestions
     *
     * @return string
     */
    private function getSearchQueryWithSuggestions(): string
    {
        return <<<QUERY
        {
            products(
                search: "smiple"
            ) {
                items {
                    name
                    sku
                }
                suggestions {
                    search
                }
            }
        }
        QUERY;
    }

    #[
        DataFixture(CategoryFixture::class, as: 'category'),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Lifelong 1',
                'sku' => 'lifelong1',
                'description' => 'Life product 1',
                'category_ids' => ['$category.id$'],
            ],
            'product1'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Life 2',
                'sku' => 'life2',
                'description' => 'Lifelong product 2',
                'category_ids' => ['$category.id$'],
            ],
            'product2'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Life 3',
                'sku' => 'life3',
                'description' => 'Life product 3',
                'category_ids' => ['$category.id$'],
            ],
            'product3'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Lifelong 4',
                'sku' => 'lifelong4',
                'description' => 'Lifelong product 4',
                'category_ids' => ['$category.id$'],
            ],
            'product4'
        ),
    ]
    public function testSearchProductsWithFilterAndMatchTypeInQuery(): void
    {
        $response1 = $this->graphQlQuery($this->getProductSearchQueryWithMatchType(true, false, '', ''));
        $response2 = $this->graphQlQuery($this->getProductSearchQueryWithMatchType(true, false, 'FULL', ''));
        $response3 = $this->graphQlQuery($this->getProductSearchQueryWithMatchType(true, false, 'PARTIAL', ''));

        $response4 = $this->graphQlQuery($this->getProductSearchQueryWithMatchType(false, true, '', ''));
        $response5 = $this->graphQlQuery($this->getProductSearchQueryWithMatchType(false, true, '', 'FULL'));
        $response6 = $this->graphQlQuery($this->getProductSearchQueryWithMatchType(false, true, '', 'PARTIAL'));

        $response7 = $this->graphQlQuery($this->getProductSearchQueryWithMatchType(true, true, '', ''));
        $response8 = $this->graphQlQuery($this->getProductSearchQueryWithMatchType(true, true, 'FULL', 'FULL'));
        $response9 = $this->graphQlQuery($this->getProductSearchQueryWithMatchType(true, true, 'PARTIAL', 'PARTIAL'));
        $response10 = $this->graphQlQuery($this->getProductSearchQueryWithMatchType(true, true, 'FULL', 'PARTIAL'));
        $response11 = $this->graphQlQuery($this->getProductSearchQueryWithMatchType(true, true, 'PARTIAL', 'FULL'));

        $this->assertEquals($response1, $response2);
        $this->assertNotEquals($response2, $response3);
        $this->assertEquals(2, $response1['products']['total_count']); // product 2, product 3
        $this->assertEquals(2, $response2['products']['total_count']); // product 2, product 3
        $this->assertEquals(4, $response3['products']['total_count']); // all
        $this->assertEquals('life2', $response1['products']['items'][1]['sku']);
        $this->assertEquals('life2', $response2['products']['items'][1]['sku']);
        $this->assertEquals('lifelong4', $response3['products']['items'][0]['sku']);

        $this->assertEquals($response4, $response5);
        $this->assertNotEquals($response5, $response6);
        $this->assertEquals(2, $response4['products']['total_count']); // product 1, product 3
        $this->assertEquals(2, $response5['products']['total_count']); // product 1, product 3
        $this->assertEquals(4, $response6['products']['total_count']); // all
        $this->assertEquals('lifelong1', $response4['products']['items'][1]['sku']);
        $this->assertEquals('lifelong1', $response5['products']['items'][1]['sku']);
        $this->assertEquals('lifelong4', $response6['products']['items'][0]['sku']);

        $this->assertEquals($response7, $response8);
        $this->assertNotEquals($response8, $response9);
        $this->assertEquals(1, $response7['products']['total_count']); // product 3
        $this->assertEquals(1, $response8['products']['total_count']); // product 3
        $this->assertEquals(4, $response9['products']['total_count']); // all
        $this->assertEquals(2, $response10['products']['total_count']); // product 2, product 3
        $this->assertEquals(2, $response11['products']['total_count']); // product 1, product 3
        $this->assertEquals('life3', $response7['products']['items'][0]['sku']);
        $this->assertEquals('life3', $response8['products']['items'][0]['sku']);
        $this->assertEquals('lifelong4', $response9['products']['items'][0]['sku']);
        $this->assertEquals('life2', $response10['products']['items'][1]['sku']);
        $this->assertEquals('lifelong1', $response11['products']['items'][1]['sku']);
    }

    /**
     * Get a combinations of queries which contain different match_type
     *
     * @param bool $filterByName
     * @param bool $filterByDescription
     * @param string $matchTypeName
     * @param string $matchTypeDescription
     * @return string
     */
    private function getProductSearchQueryWithMatchType(
        bool $filterByName,
        bool $filterByDescription,
        string $matchTypeName = '',
        string $matchTypeDescription = ''
    ): string {
        $matchTypeName = $matchTypeName ? 'match_type:' . $matchTypeName : '';
        $matchTypeDescription = $matchTypeDescription ? 'match_type:' . $matchTypeDescription : '';
        $name = $filterByName ? 'name : { match : "Life", '.$matchTypeName.'}' : '';
        $description = $filterByDescription ? 'description:  { match: "Life", '.$matchTypeDescription.'}' : '';
        return <<<QUERY
        {
        products(filter :
        {
            $name
            $description
         }){
            total_count
            items {
                name
                sku
                description {
                    html
                }
            }
           }
        }
        QUERY;
    }
}
