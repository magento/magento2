<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\PageCache;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Cms\Model\BlockRepository;
use Magento\Cms\Test\Fixture\Block as BlockFixture;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\PageCache\Model\Config;
use Magento\Store\Model\Store;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/**
 * Test the cache works properly for products and categories
 */
class CacheTagTest extends GraphQLPageCacheAbstract
{
    /**
     * Test cache invalidation for products
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testCacheInvalidationForProducts()
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
        // Cache should be a MISS when product is queried for first time
        $response = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        // Obtain the X-Magento-Cache-Id from the response
        $cacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS the first time
        $this->assertCacheMissAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);
        // Verify we obtain a cache HIT the second time
        $this->assertCacheHitAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        /** @var Product $product */
        $product = $productRepository->get($productSku, false, null, true);
        $product->setPrice(15);
        $productRepository->save($product);

        // Cache invalidation happens and cache header value is a MISS after product update
        $this->assertCacheMissAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);
    }

    /**
     * Test cache is invalidated properly for categories
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Catalog/_files/product_in_multiple_categories.php
     */
    public function testCacheInvalidationForCategoriesWithProduct()
    {
        $firstProductSku = 'simple333';
        $secondProductSku = 'simple444';

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        /** @var Product $firstProduct */
        $firstProduct = $productRepository->get($firstProductSku, false, null, true);

        $product1Query = $this->getProductQuery($firstProductSku);
        $product2Query =$this->getProductQuery($secondProductSku);
        $categoryQuery = $this->getCategoryQuery();

        // cache-debug header value should be a MISS when category is loaded first time
        $responseMissOnCategoryQuery = $this->graphQlQueryWithResponseHeaders($categoryQuery);
        $cacheIdOfCategoryQuery = $responseMissOnCategoryQuery['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS the first time
        $this->assertCacheMissAndReturnResponse(
            $categoryQuery,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheIdOfCategoryQuery]
        );

        // Cache-debug header should be a MISS for product 1 on first request
        $responseFirstProduct = $this->graphQlQueryWithResponseHeaders($product1Query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseFirstProduct['headers']);
        $cacheIdOfFirstProduct = $responseFirstProduct['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS on the first product
        $this->assertCacheMissAndReturnResponse(
            $product1Query,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheIdOfFirstProduct]
        );

        // Cache-debug header should be a MISS for product 2 during first load
        $responseMissSecondProduct = $this->graphQlQueryWithResponseHeaders($product2Query);
        $cacheIdOfSecondProduct = $responseMissSecondProduct['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS the first time for product 2
        $this->assertCacheMissAndReturnResponse(
            $product2Query,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheIdOfSecondProduct]
        );

        // updating product1
        $firstProduct->setPrice(20);
        $productRepository->save($firstProduct);

        // Verify we obtain a cache MISS after the first product update and category reloading
        $this->assertCacheMissAndReturnResponse(
            $categoryQuery,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheIdOfCategoryQuery]
        );

        // cache-debug should be a MISS for product 1 after it is updated - cache invalidation
        // Verify we obtain a cache MISS after the first product update
        $this->assertCacheMissAndReturnResponse(
            $product1Query,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheIdOfFirstProduct]
        );

        // Cache-debug header responses for product 2 and should be a HIT for product 2
        // Verify we obtain a cache HIT on the second product after product 1 update
        $this->assertCacheHitAndReturnResponse(
            $product2Query,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheIdOfSecondProduct]
        );
    }

    #[
        ConfigFixture(Config::XML_PAGECACHE_TYPE, Config::VARNISH),
        DataFixture(BlockFixture::class, ['content' => 'Original Block content'], as: 'block'),
        DataFixture(CategoryFixture::class, as: 'category1'),
        DataFixture(CategoryFixture::class, ['description' => 'Original Category description'], as: 'category2'),
    ]
    public function testCacheInvalidationForCategoriesWithWidget(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $block = $fixtures->get('block');
        $category1 = $fixtures->get('category1');
        $category2 = $fixtures->get('category2');
        $queryForCategory1 = $this->getCategoriesQuery((int) $category1->getId());
        $queryForCategory2 = $this->getCategoriesQuery((int) $category2->getId());

        $this->updateCategoryDescription((int) $category1->getId(), $this->getBlockWidget((int) $block->getId()));

        $responseCacheIdForCategory1 = $this->getQueryResponseCacheKey($queryForCategory1);
        // Verify we get MISS for category1 query in the first request
        $responseMissForCategory1 = $this->assertCacheMissAndReturnResponse(
            $queryForCategory1,
            [CacheIdCalculator::CACHE_ID_HEADER => $responseCacheIdForCategory1]
        );

        // Verify we get HIT for category 1 query in the second request
        $responseHitForCategory1 = $this->assertCacheHitAndReturnResponse(
            $queryForCategory1,
            [CacheIdCalculator::CACHE_ID_HEADER => $responseCacheIdForCategory1]
        );

        $this->assertEquals($responseMissForCategory1['body'], $responseHitForCategory1['body']);

        // Verify category1 description contains block content
        $this->assertCategoryDescription('Original Block content', $responseHitForCategory1);

        $responseCacheIdForCategory2 = $this->getQueryResponseCacheKey($queryForCategory2);
        // Verify we get MISS for category2 query in the first request
        $responseMissForCategory2 = $this->assertCacheMissAndReturnResponse(
            $queryForCategory2,
            [CacheIdCalculator::CACHE_ID_HEADER => $responseCacheIdForCategory2]
        );

        // Verify we get HIT for category 2 query in the second request
        $responseHitForCategory2 = $this->assertCacheHitAndReturnResponse(
            $queryForCategory2,
            [CacheIdCalculator::CACHE_ID_HEADER => $responseCacheIdForCategory2]
        );

        $this->assertEquals($responseMissForCategory2['body'], $responseHitForCategory2['body']);

        // Verify category2 description is the same as created
        $this->assertCategoryDescription('Original Category description', $responseHitForCategory2);

        // Update block content
        $newBlockContent = 'New block content!!!';
        $this->updateBlockContent((int) $block->getId(), $newBlockContent);

        // Verify we get MISS for category1 query after block is updated
        $this->assertCacheMissAndReturnResponse(
            $queryForCategory1,
            [CacheIdCalculator::CACHE_ID_HEADER => $responseCacheIdForCategory1]
        );

        // Verify we get HIT for category1 query in the second request after block is updated
        $responseHitForCategory1 = $this->assertCacheHitAndReturnResponse(
            $queryForCategory1,
            [CacheIdCalculator::CACHE_ID_HEADER => $responseCacheIdForCategory1]
        );

        // Verify we get HIT for category2 query after block is updated
        $responseHitForCategory2 = $this->assertCacheHitAndReturnResponse(
            $queryForCategory2,
            [CacheIdCalculator::CACHE_ID_HEADER => $responseCacheIdForCategory2]
        );

        // Verify the updated block data is returned in category1 query response
        $this->assertCategoryDescription($newBlockContent, $responseHitForCategory1);

        // Verify category2 description is the same as created
        $this->assertCategoryDescription('Original Category description', $responseHitForCategory2);
    }

    private function assertCategoryDescription(string $expected, array $response): void
    {
        $responseBody = $response['body'];
        $this->assertIsArray($responseBody);
        $this->assertArrayNotHasKey('errors', $responseBody);
        $this->assertStringContainsString($expected, $responseBody['categories']['items'][0]['description']);
    }

    private function getQueryResponseCacheKey(string $query): string
    {
        $response = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        return $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];
    }

    private function updateBlockContent(int $id, string $content): void
    {
        $blockRepository = Bootstrap::getObjectManager()->get(BlockRepository::class);
        $block = $blockRepository->getById($id);
        $block->setContent($content);
        $blockRepository->save($block);
    }

    private function updateCategoryDescription(int $id, string $description): void
    {
        $categoryRepository = Bootstrap::getObjectManager()->get(CategoryRepositoryInterface::class);
        $category = $categoryRepository->get($id, Store::DEFAULT_STORE_ID);
        $category->setCustomAttribute('description', $description);
        $categoryRepository->save($category);
    }

    private function getBlockWidget(int $blockId): string
    {
        return "{{widget type=\"Magento\\Cms\\Block\\Widget\\Block\" " .
            "template=\"widget/static_block/default.phtml\" " .
            "block_id=\"$blockId\" " .
            "type_name=\"CMS Static Block\"}}";
    }

    private function getCategoriesQuery(int $categoryId): string
    {
        return <<<QUERY
{
    categories(filters: {ids: {in: ["$categoryId"]}}) {
        items{
           description
        }
    }
}
QUERY;
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
query {
        category(id: 4) {
            id
            description
            name
            product_count
            products(pageSize: 10, currentPage: 1) {
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
