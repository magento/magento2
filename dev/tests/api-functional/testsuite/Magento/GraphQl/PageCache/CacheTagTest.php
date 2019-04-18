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
    protected function setUp()
    {
        $this->markTestSkipped(
            'This test will stay skipped until DEVOPS-4924 is resolved'
        );
    }

    /**
     * Tests if Magento cache tags and debug headers for products are generated properly
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

        /** cache-debug should be a MISS when product is queried for first time */
        $responseMissHeaders = $this->graphQlQueryForHttpHeaders($query);
        $this->assertContains('X-Magento-Cache-Debug: MISS', $responseMissHeaders);

        /** cache-debug should be a HIT for the second round */
        $responseHitHeaders = $this->graphQlQueryForHttpHeaders($query);
        //preg_match('/X-Magento-Cache-Debug: (.*?)\n/', $responseHitHeaders, $matchesHit);
        $this->assertContains('X-Magento-Cache-Debug: HIT', $responseHitHeaders);

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        /** @var Product $product */
        $product =$productRepository->get($productSku, false, null, true);
        /** update the price attribute for the product in test */
        $product->setPrice(15);
        $product->save();
        /** Cache invalidation happens and cache-debug header value is a MISS after product update */
        $responseMissHeaders = $this->graphQlQueryForHttpHeaders($query);
        $this->assertContains('X-Magento-Cache-Debug: MISS', $responseMissHeaders);

        /** checks if cache tags for products are correctly displayed in the response header */
        preg_match('/X-Magento-Tags: (.*?)\n/', $responseMissHeaders, $headerCacheTags);
        $actualCacheTags = explode(',', rtrim($headerCacheTags[1], "\r"));
        $expectedCacheTags=['cat_p','cat_p_' . $product->getId(),'FPC'];
        $this->assertEquals($expectedCacheTags, $actualCacheTags);
    }

    /**
     * Tests if X-Magento-Tags for categories are generated properly. Also tests the use case for cache invalidation
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_in_multiple_categories.php
     */
    public function testCacheTagForCategoriesWithProduct()
    {
        $firstProductSku = 'simple333';
        $secondProductSku = 'simple444';
        $categoryId ='4';
        $variables =[
            'id' => $categoryId,
            'pageSize'=> 10,
            'currentPage' => 1
        ];

        $product1Query = $this->getProductQuery($firstProductSku);
        $product2Query =$this->getProductQuery($secondProductSku);
        $categoryQuery = $this->getCategoryQuery();
        $responseMissHeaders = $this->graphQlQueryForHttpHeaders($categoryQuery, $variables);

        /** cache-debug header value should be a MISS when category is loaded first time */
        $this->assertContains('X-Magento-Cache-Debug: MISS', $responseMissHeaders);

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        /** @var Product $firstProduct */
        $firstProduct = $productRepository->get($firstProductSku, false, null, true);
        /** @var Product $secondProduct */
        $secondProduct = $productRepository->get($secondProductSku, false, null, true);

        /** checks to see if the X-Magento-Tags for category is displayed correctly */
        preg_match('/X-Magento-Tags: (.*?)\n/', $responseMissHeaders, $headerCacheTags);
        $actualCacheTags = explode(',', rtrim($headerCacheTags[1], "\r"));
        $expectedCacheTags =
                             ['cat_c','cat_c_' . $categoryId,'cat_p','cat_p_' . $firstProduct->getId(),
                              'cat_p_' .$secondProduct->getId(),'FPC'
                             ];
        $this->assertEquals($expectedCacheTags, $actualCacheTags);

        // Cache-debug header should be a MISS for product 1 on first request
        $responseHeadersFirstProduct = $this->graphQlQueryForHttpHeaders($product1Query);
        $this->assertContains('X-Magento-Cache-Debug: MISS', $responseHeadersFirstProduct);

        // Cache-debug header should be a MISS for product 2 during first load
        $responseHeadersSecondProduct = $this->graphQlQueryForHttpHeaders($product2Query);
        $this->assertContains('X-Magento-Cache-Debug: MISS', $responseHeadersSecondProduct);

        /** Cache-debug header value should be MISS after  updating product1 and reloading the Category */
        $firstProduct->setPrice(20);
        $firstProduct->save();
        $responseMissHeaders = $this->graphQlQueryForHttpHeaders($categoryQuery, $variables);
        preg_match('/X-Magento-Cache-Debug: (.*?)\n/', $responseMissHeaders, $matchesMiss);
        $this->assertEquals('MISS', rtrim($matchesMiss[1], "\r"));

        /** Cache-debug should be a MISS for product 1 after it is updated - cache invalidation */
        $responseHeadersFirstProduct = $this->graphQlQueryForHttpHeaders($product1Query);
        $this->assertContains('X-Magento-Cache-Debug: MISS', $responseHeadersFirstProduct);

        // Cach-debug header should be a HIT for prod 2 during second load since prod 2 is fetched from cache.
        $responseHeadersSecondProduct = $this->graphQlQueryForHttpHeaders($product2Query);
        $this->assertContains('X-Magento-Cache-Debug: HIT', $responseHeadersSecondProduct);
    }

    /**
     * Get Product query
     *
     * @param  string $productSku
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
