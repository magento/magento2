<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller\Catalog;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\GraphQlCache\Controller\AbstractGraphqlCacheTest;

/**
 * Tests cache debug headers and cache tag validation for a simple product query
 *
 * @magentoAppArea graphql
 * @magentoCache full_page enabled
 * @magentoDbIsolation disabled
 */
class ProductsCacheTest extends AbstractGraphqlCacheTest
{
    /**
     * Test request is dispatched and response is checked for debug headers and cache tags
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_url_key.php
     */
    public function testRequestCacheTagsForProducts(): void
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);

        $product = $productRepository->get('simple1');

        $query
            = <<<QUERY
 {
           products(filter: {sku: {eq: "simple1"}})
           {
               items {
                   id
                   name
                   sku
                   description {
                   html
                   }
               }
           }
       }
QUERY;

        $response = $this->dispatchGraphQlGETRequest(['query' => $query]);
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $actualCacheTags = explode(',', $response->getHeader('X-Magento-Tags')->getFieldValue());
        $expectedCacheTags = ['cat_p', 'cat_p_' . $product->getId(), 'FPC'];
        $this->assertEquals($expectedCacheTags, $actualCacheTags);
    }

    /**
     * Test request is checked for debug headers and no cache tags for not existing product
     */
    public function testRequestNoTagsForNonExistingProducts(): void
    {
        $query
            = <<<QUERY
            {
           products(filter: {sku: {eq: "simple10"}})
           {
               items {
                   id
                   name
                   sku
                   description {
                   html
                   }
               }
           }
       }

QUERY;
        $response = $this->dispatchGraphQlGETRequest(['query' => $query]);
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $actualCacheTags = explode(',', $response->getHeader('X-Magento-Tags')->getFieldValue());
        $expectedCacheTags = ['FPC'];
        $this->assertEquals($expectedCacheTags, $actualCacheTags);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_url_key.php
     */
    public function testConsecutiveRequestsAreServedFromThePageCache(): void
    {
        $query
            = <<<QUERY
{
   products(filter: {sku: {eq: "simple1"}})
   {
       items {
           id
           name
           sku
           description {
           html
           }
       }
   }
}
QUERY;
        $response1 = $this->dispatchGraphQlGETRequest(['query' => $query]);
        $response2 = $this->dispatchGraphQlGETRequest(['query' => $query]);

        $this->assertEquals('MISS', $response1->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $this->assertEquals('HIT', $response2->getHeader('X-Magento-Cache-Debug')->getFieldValue());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_url_key.php
     */
    public function testDifferentProductsRequestsUseDifferentPageCacheRecords(): void
    {
        $queryTemplate
            = <<<QUERY
{
   products(filter: {sku: {eq: "%s"}})
   {
       items {
           id
           name
           sku
           description {
           html
           }
       }
   }
}
QUERY;
        $responseProduct1 = $this->dispatchGraphQlGETRequest(['query' => sprintf($queryTemplate, 'simple1')]);
        $responseProduct2 = $this->dispatchGraphQlGETRequest(['query' => sprintf($queryTemplate, 'simple2')]);

        $this->assertEquals('MISS', $responseProduct1->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $this->assertEquals('MISS', $responseProduct2->getHeader('X-Magento-Cache-Debug')->getFieldValue());
    }
}
