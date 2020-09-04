<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\PageCache;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test the caching works properly for products and categories
 */
class CacheTagTest extends GraphQlAbstract
{
    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->markTestSkipped(
            'This test will stay skipped until DEVOPS-4924 is resolved'
        );
    }

    /**
     * Test if Magento cache tags and debug headers for products are generated properly
     *
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testCacheTagsAndCacheDebugHeaderForProducts()
    {
        $productSku='simple2';
        $query
            = <<<QUERY
 {
           products(filter: {sku: {eq: "{$productSku}"}})
           {
               items {
                   id
                   name
                   sku
               }
           }
       }
QUERY;

        // Cache-debug should be a MISS when product is queried for first time
        $responseMiss = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey('X-Magento-Cache-Debug', $responseMiss['headers']);
        $this->assertEquals('MISS', $responseMiss['headers']['X-Magento-Cache-Debug']);

        // Cache-debug should be a HIT for the second round
        $responseHit = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey('X-Magento-Cache-Debug', $responseHit['headers']);
        $this->assertEquals('HIT', $responseHit['headers']['X-Magento-Cache-Debug']);

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        /** @var Product $product */
        $product = $productRepository->get($productSku, false, null, true);
        $product->setPrice(15);
        $productRepository->save($product);
        // Cache invalidation happens and cache-debug header value is a MISS after product update
        $responseMiss = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey('X-Magento-Cache-Debug', $responseMiss['headers']);
        $this->assertEquals('MISS', $responseMiss['headers']['X-Magento-Cache-Debug']);
        $this->assertArrayHasKey('X-Magento-Tags', $responseMiss['headers']);
        $expectedCacheTags = ['cat_p','cat_p_' . $product->getId(),'FPC'];
        $actualCacheTags  = explode(',', $responseMiss['headers']['X-Magento-Tags']);
        foreach ($expectedCacheTags as $expectedCacheTag) {
            $this->assertContains($expectedCacheTag, $actualCacheTags);
        }
    }

    /**
     * Test if X-Magento-Tags for categories are generated properly
     *
     * Also tests the use case for cache invalidation
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_in_multiple_categories.php
     */
    public function testCacheTagForCategoriesWithProduct()
    {
        $firstProductSku = 'simple333';
        $secondProductSku = 'simple444';
        $categoryId ='4';

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        /** @var Product $firstProduct */
        $firstProduct = $productRepository->get($firstProductSku, false, null, true);
        /** @var Product $secondProduct */
        $secondProduct = $productRepository->get($secondProductSku, false, null, true);

        $categoryQueryVariables =[
            'id' => $categoryId,
            'pageSize'=> 10,
            'currentPage' => 1
        ];

        $product1Query = $this->getProductQuery($firstProductSku);
        $product2Query =$this->getProductQuery($secondProductSku);
        $categoryQuery = $this->getCategoryQuery();

        // cache-debug header value should be a MISS when category is loaded first time
        $responseMiss = $this->graphQlQueryWithResponseHeaders($categoryQuery, $categoryQueryVariables);
        $this->assertArrayHasKey('X-Magento-Cache-Debug', $responseMiss['headers']);
        $this->assertEquals('MISS', $responseMiss['headers']['X-Magento-Cache-Debug']);
        $this->assertArrayHasKey('X-Magento-Tags', $responseMiss['headers']);
        $actualCacheTags = explode(',', $responseMiss['headers']['X-Magento-Tags']);
        $expectedCacheTags =
            [
                'cat_c',
                'cat_c_' . $categoryId,
                'cat_p',
                'cat_p_' . $firstProduct->getId(),
                'cat_p_' . $secondProduct->getId(),
                'FPC'
            ];
        $this->assertEquals($expectedCacheTags, $actualCacheTags);

        // Cache-debug header should be a MISS for product 1 on first request
        $responseFirstProduct = $this->graphQlQueryWithResponseHeaders($product1Query);
        $this->assertEquals('MISS', $responseFirstProduct['headers']['X-Magento-Cache-Debug']);
        // Cache-debug header should be a MISS for product 2 during first load
        $responseSecondProduct = $this->graphQlQueryWithResponseHeaders($product2Query);
        $this->assertEquals('MISS', $responseSecondProduct['headers']['X-Magento-Cache-Debug']);

        $firstProduct->setPrice(20);
        $productRepository->save($firstProduct);
        // cache-debug header value should be MISS after  updating product1 and reloading the Category
        $responseMissCategory = $this->graphQlQueryWithResponseHeaders($categoryQuery, $categoryQueryVariables);
        $this->assertArrayHasKey('X-Magento-Cache-Debug', $responseMissCategory['headers']);
        $this->assertEquals('MISS', $responseMissCategory['headers']['X-Magento-Cache-Debug']);

        // cache-debug should be a MISS for product 1 after it is updated - cache invalidation
        $responseMissFirstProduct = $this->graphQlQueryWithResponseHeaders($product1Query);
        $this->assertArrayHasKey('X-Magento-Cache-Debug', $responseMissFirstProduct['headers']);
        $this->assertEquals('MISS', $responseMissFirstProduct['headers']['X-Magento-Cache-Debug']);
        // Cache-debug header should be a HIT for product 2
        $responseHitSecondProduct = $this->graphQlQueryWithResponseHeaders($product2Query);
        $this->assertArrayHasKey('X-Magento-Cache-Debug', $responseHitSecondProduct['headers']);
        $this->assertEquals('HIT', $responseHitSecondProduct['headers']['X-Magento-Cache-Debug']);
    }

    /**
     * Get Product query
     *
     * @param string $productSku
     * @return string
     */
    private function getProductQuery(string $productSku): string
    {
        $productQuery = <<<QUERY
       {
           products(filter: {sku: {eq: "{$productSku}"}})
           {
               items {
                   id
                   name
                   sku
               }
           }
       }
QUERY;
        return $productQuery;
    }

    /**
     * Get category query
     *
     * @return string
     */
    private function getCategoryQuery(): string
    {
        $categoryQueryString = <<<QUERY
query GetCategoryQuery(\$id: Int!, \$pageSize: Int!, \$currentPage: Int!) {
        category(id: \$id) {
            id
            description
            name
            product_count
            products(pageSize: \$pageSize, currentPage: \$currentPage) {
                items {
                    id
                    name
                    url_key
                }
                total_count
            }
        }
    }
QUERY;

        return $categoryQueryString;
    }
}
