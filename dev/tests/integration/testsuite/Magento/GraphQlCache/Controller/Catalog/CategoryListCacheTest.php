<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller\Catalog;

use Magento\GraphQlCache\Controller\AbstractGraphqlCacheTest;

/**
 * Test caching works for categoryList query
 *
 * @magentoAppArea graphql
 * @magentoCache full_page enabled
 * @magentoDbIsolation disabled
 */
class CategoryListCacheTest extends AbstractGraphqlCacheTest
{
    /**
     * Test cache tags are generated
     *
     * @magentoDataFixture Magento/Catalog/_files/category_product.php
     */
    public function testRequestCacheTagsForCategoryList(): void
    {
        $categoryId ='333';
        $query
            = <<<QUERY
        {
            categoryList(filters: {ids: {in: ["$categoryId"]}}) {
                id
                name
                url_key
                description
                product_count
           }
       }
QUERY;
        $response = $this->dispatchGraphQlGETRequest(['query' => $query]);
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $actualCacheTags = explode(',', $response->getHeader('X-Magento-Tags')->getFieldValue());
        $expectedCacheTags = ['cat_c','cat_c_' . $categoryId, 'FPC'];
        $this->assertEquals($expectedCacheTags, $actualCacheTags);
    }

    /**
     * Test request is served from cache
     *
     * @magentoDataFixture Magento/Catalog/_files/category_product.php
     */
    public function testSecondRequestIsServedFromCache()
    {
        $categoryId ='333';
        $query
            = <<<QUERY
        {
            categoryList(filters: {ids: {in: ["$categoryId"]}}) {
                id
                name
                url_key
                description
                product_count
           }
       }
QUERY;
        $expectedCacheTags = ['cat_c','cat_c_' . $categoryId, 'FPC'];

        $response = $this->dispatchGraphQlGETRequest(['query' => $query]);
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $actualCacheTags = explode(',', $response->getHeader('X-Magento-Tags')->getFieldValue());
        $this->assertEquals($expectedCacheTags, $actualCacheTags);

        $cacheResponse = $this->dispatchGraphQlGETRequest(['query' => $query]);
        $this->assertEquals('HIT', $cacheResponse->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $actualCacheTags = explode(',', $cacheResponse->getHeader('X-Magento-Tags')->getFieldValue());
        $this->assertEquals($expectedCacheTags, $actualCacheTags);
    }

    /**
     * Test cache tags are generated
     *
     * @magentoDataFixture Magento/Catalog/_files/category_tree.php
     */
    public function testRequestCacheTagsForCategoryListOnMultipleIds(): void
    {
        $categoryId1 ='400';
        $categoryId2 = '401';
        $query
            = <<<QUERY
        {
            categoryList(filters: {ids: {in: ["$categoryId1", "$categoryId2"]}}) {
                id
                name
                url_key
                description
                product_count
           }
       }
QUERY;
        //added the previous category in expected tags as it is cached
        $expectedCacheTags = ['cat_c','cat_c_' .'333', 'cat_c_' . $categoryId1, 'cat_c_' . $categoryId2, 'FPC'];

        $response = $this->dispatchGraphQlGETRequest(['query' => $query]);
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $actualCacheTags = explode(',', $response->getHeader('X-Magento-Tags')->getFieldValue());
        $this->assertEquals($expectedCacheTags, $actualCacheTags);
    }

    /**
     * Test request is served from cache
     *
     * @magentoDataFixture Magento/Catalog/_files/category_tree.php
     */
    public function testSecondRequestIsServedFromCacheOnMultipleIds()
    {
        $categoryId1 ='400';
        $categoryId2 = '401';
        $query
            = <<<QUERY
        {
            categoryList(filters: {ids: {in: ["$categoryId1", "$categoryId2"]}}) {
                id
                name
                url_key
                description
                product_count
           }
       }
QUERY;
        //added the previous category in expected tags as it is cached
        $expectedCacheTags = ['cat_c','cat_c_' .'333', 'cat_c_' . $categoryId1, 'cat_c_' . $categoryId2, 'FPC'];

        $response = $this->dispatchGraphQlGETRequest(['query' => $query]);
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $actualCacheTags = explode(',', $response->getHeader('X-Magento-Tags')->getFieldValue());
        $this->assertEquals($expectedCacheTags, $actualCacheTags);

        $cacheResponse = $this->dispatchGraphQlGETRequest(['query' => $query]);
        $this->assertEquals('HIT', $cacheResponse->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $actualCacheTags = explode(',', $cacheResponse->getHeader('X-Magento-Tags')->getFieldValue());
        $this->assertEquals($expectedCacheTags, $actualCacheTags);
    }
}
