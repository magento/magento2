<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CatalogGraphQl;

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
    /**
     * @var ObjectManager|null
     */
    private $objectManager;

    /**
     * Test setup
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
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
}
